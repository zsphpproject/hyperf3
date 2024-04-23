<?php

namespace Zsgogo\middleware;

use FastRoute\Dispatcher;
use Hyperf\Context\Context;
use Hyperf\Di\ReflectionManager;
use Hyperf\HttpServer\Router\Dispatched;
use Hyperf\Server\Exception\ServerException;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use RuntimeException;
use Zsgogo\utils\Pojo;

class PoPoMiddleware implements MiddlewareInterface
{
    public function __construct(private ContainerInterface $container)
    {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var Dispatched $dispatched */
        $dispatched = $request->getAttribute(Dispatched::class);

        if (! $dispatched instanceof Dispatched) {
            throw new ServerException(sprintf('The dispatched object is not a %s object.', Dispatched::class));
        }

        Context::set(ServerRequestInterface::class, $request);

        if ($this->shouldHandle($dispatched)) {
            [$requestHandler, $method] = $this->prepareHandler($dispatched->handler->callback);
            if ($method) {
                $reflectionMethod = ReflectionManager::reflectMethod($requestHandler, $method);
                $parameters = $reflectionMethod->getParameters();
                foreach ($parameters as $parameter) {
                    if ($parameter->getType() === null) {
                        continue;
                    }
                    $parameterType = $parameter->getType();
                    if ($parameterType->isBuiltin()) {
                        continue;
                    }
                    $formRequest = $this->container->get($parameterType->getName());
                    if ($formRequest instanceof Pojo) {
                        $formRequest->setData();
                    }
                }
            }
        }

        return $handler->handle($request);
    }

    protected function shouldHandle(Dispatched $dispatched): bool
    {
        return $dispatched->status === Dispatcher::FOUND && ! $dispatched->handler->callback instanceof Closure;
    }

    /**
     * @see \Hyperf\HttpServer\CoreMiddleware::prepareHandler()
     */
    protected function prepareHandler(array|string $handler): array
    {
        if (is_string($handler)) {
            if (str_contains($handler, '@')) {
                return explode('@', $handler);
            }
            $array = explode('::', $handler);
            if (! isset($array[1]) && class_exists($handler) && method_exists($handler, '__invoke')) {
                $array[1] = '__invoke';
            }
            return [$array[0], $array[1] ?? null];
        }
        if (is_array($handler) && isset($handler[0], $handler[1])) {
            return $handler;
        }
        throw new RuntimeException('Handler not exist.');
    }
}

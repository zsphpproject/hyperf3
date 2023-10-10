<?php

namespace Zsgogo\utils;

use App\common\constant\ErrorNums;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\Stringable\Str;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;
use Zsgogo\exception\AppException;

/**
 * 重写Pojo
 */
abstract class InitRequestMiddleware {
    /**
     * @var array $data 数据
     */
    private array $data = [];


    /**
     * @var array $notFilterField 无需全局过滤的字段
     */
    protected array $notFilterField = [];

    /**
     * @var ReflectionClass
     */
    private ReflectionClass $reflectionClass;


    public function toArray(): array {
        $properties = $this->reflectionClass->getProperties(ReflectionProperty::IS_PRIVATE);
        foreach ($properties as $property) {
            $propertyName = $property->getName();
            $getDataFuncName = 'get' . ucfirst($propertyName);
            $this->data[Str::snake($propertyName)] = $this->$getDataFuncName();
        }
        return $this->data;
    }

    /**
     * @param RequestInterface $request
     * @param array $param
     * @throws ReflectionException
     */
    public function __construct(RequestInterface $request, array $param = []) {
        $inputData = match ($request->getMethod()) {
            "GET", "DELETE" => $request->query(),
            "POST", "PUT" => $request->post(),
            default => [],
        };

        $inputData = $this->fitterData($inputData ?? []);

        if (!empty($param)) {
            $inputData = array_merge($inputData,$param);
        }

        $this->reflectionClass = new ReflectionClass($this);
        $this->setData($inputData);
    }





    public function fitterData(array $params): array {
        foreach ($params as $paramKey => $paramValue) {
            if (in_array($paramKey, $this->notFilterField)) {
                unset($params[$paramKey]);
            }
        }
        return $params;
    }


    /**
     * @param $inputData
     * @return void
     * @throws ReflectionException
     */
    private function setData($inputData): void {
        $properties = $this->reflectionClass->getProperties(ReflectionProperty::IS_PRIVATE);
        foreach ($properties as $property) {
            $propertySnakeName = Str::snake($property->getName());
            if (isset($inputData[$propertySnakeName])) {
                $propertyValue = $inputData[$propertySnakeName] != "" ? $inputData[$propertySnakeName] : $property->getDefaultValue();
                $propertyName = $property->getName();
                $setDataFuncName = 'set' . ucfirst($propertyName);
                if (!$this->reflectionClass->hasMethod($setDataFuncName)) {
                    throw new AppException(ErrorNums::METHOD_NOT_EXISTS,'method ' . $this->reflectionClass->getName() . '::' . $setDataFuncName . ' not exists!');
                }
                $reflectionMethod = $this->reflectionClass->getMethod($setDataFuncName);
                if (!$reflectionMethod->isPublic()) {
                    throw new AppException(ErrorNums::METHOD_NOT_PUBLIC,'method ' . $this->reflectionClass->getName() . '::' . $setDataFuncName . ' is not public!');
                }
                $reflectionMethod->invokeArgs($this, [$propertyValue]);
            }

        }
    }
}
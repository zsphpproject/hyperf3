<?php
declare(strict_types=1);

namespace Zsgogo\utils;

use Hyperf\Stringable\Str;
use Hyperf\Validation\Request\FormRequest;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;

abstract class Pojo extends FormRequest
{

    /**
     * @var ReflectionClass
     */
    private ReflectionClass $reflectionClass;
    private array $properties;

    public function __construct(protected ContainerInterface $container)
    {
        $this->reflectionClass = new ReflectionClass($this);
        $this->properties = $this->reflectionClass->getProperties(ReflectionProperty::IS_PRIVATE);
        parent::__construct($container);
    }

    /**
     * 根据对象设置的属性转为数组
     * @return array
     */
    public function toArray(): array
    {
        $data = [];
        foreach ($this->properties as $property) {
            $propertySnakeName = Str::snake($property->getName());
            $data[$propertySnakeName] = $this->getRequestProperty($property->getName());
        }
        return $data;
    }

    public function authorize(): bool
    {
        return true;
    }

    /**
     * 通过中间件触发
     * 每次请求过来的时候，set 数据
     * @return void
     * @throws ReflectionException
     */
    public function setData(): void
    {
        $inputData = $this->getInputData();
        foreach ($inputData as $key => $value) {
            $key = lcfirst(Str::studly($key));
            if ($value == '') {
                // 前端如果传''，则取属性默认值
                $value = $this->reflectionClass->getProperty($key)->getDefaultValue();
            }
            $this->storeRequestProperty($key, $value);
        }
    }
}

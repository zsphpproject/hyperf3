<?php
declare(strict_types=1);

namespace Zsgogo\utils;

use Hyperf\Context\Context;
use Hyperf\Stringable\Str;
use Hyperf\Validation\Request\FormRequest;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionProperty;
use Zsgogo\utils\popo\ObjArray;

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
     * @param array $inputData
     * @return void
     */
    public function setData(array $inputData): void
    {
        foreach ($this->properties as $property) {
            $propertySnakeName = Str::snake($property->getName());
            if (!isset($inputData[$propertySnakeName]) || $inputData[$propertySnakeName] == '') {
                // 没传或者传空字符串，则用属性默认值
                $propertyOriginValue = $property->getDefaultValue();
            } else {
                $propertyOriginValue = $propertyValue = $inputData[$propertySnakeName];
                // 处理对象数据，一维数据
                if ($property->getType() instanceof ReflectionNamedType) {
                    if (!$property->getType()->isBuiltin()) {
                        $objName = $property->getType()->getName();
                        // 获取容器
                        $valueObj = make($objName);
                        if ($valueObj instanceof Pojo) {
                            $valueObj->setData($propertyValue);
                            $propertyOriginValue = $valueObj->toArray();
                            $propertyValue = $valueObj;
                        } else {
                            unset($valueObj);
                        }
                    }
                }
                $attributes = $property->getAttributes();
                // 处理对象数组数据，二维数据
                foreach ($attributes as $attribute) {
                    if ($attribute->getName() === ObjArray::class) {
                        $objName = $attribute->getArguments()[0];
                        foreach ($propertyValue as $key => $value) {
                            /**
                             * @var Pojo $valueObj
                             */
                            $valueObj = make($objName);
                            $valueObj->setData($value);
                            $propertyValue[$key] = $valueObj;
                            $propertyOriginValue[$key] = $valueObj->toArray();
                        }
                    }
                }
                $propertyOriginValue = match ($property->getType()?->getName()) {
                    'int' => (int)$propertyOriginValue,
                    'float' => (float)$propertyOriginValue,
                    'double' => (double)$propertyOriginValue,
                    'bool' => (bool)$propertyOriginValue,
                    default => $propertyOriginValue,
                };
            }
            $this->storeRequestProperty($property->getName(), $propertyOriginValue);
        }
    }
}

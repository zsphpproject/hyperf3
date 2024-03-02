<?php

namespace Zsgogo\utils;

use App\common\constant\ErrorNums;
use Hyperf\Stringable\Str;
use phpDocumentor\Reflection\DocBlock\Tag;
use phpDocumentor\Reflection\DocBlock\Tags\Param;
use phpDocumentor\Reflection\DocBlockFactory;
use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\Types\Array_;
use phpDocumentor\Reflection\Types\Compound;
use phpDocumentor\Reflection\Types\Object_;
use ReflectionClass;
use ReflectionException;
use ReflectionNamedType;
use ReflectionProperty;
use Zsgogo\exception\AppException;

class ObjUtil
{

    /**
     * 对象设置成员变量
     * @param object $response
     * @param array $inputData
     * @return void
     * @throws ReflectionException
     */
    public function setData(object $response, array $inputData): void
    {
        $reflection = new ReflectionClass($response);
        $properties = $reflection->getProperties(ReflectionProperty::IS_PROTECTED);
        foreach ($properties as $property) {
            $propertySnakeName = $property->getName();
            $propertyValue = (isset($inputData[$propertySnakeName]) && $inputData[$propertySnakeName] != "") ? $inputData[$propertySnakeName] : $property->getDefaultValue();

            if ($propertyValue == null && Str::contains($property->getDocComment(), "int32")) {
                $propertyValue = 0;
            }

            $propertyName = $property->getName();
            $setDataFuncName = 'set' . $this->toHump($propertyName);
            if (!$reflection->hasMethod($setDataFuncName)) {
                // not found setXxx method continue.
                continue;
            }
            $reflectionMethod = $reflection->getMethod($setDataFuncName);
            if (!$reflectionMethod->isPublic()) {
                continue;
            }
            $reflectionMethod->invokeArgs($response, [$propertyValue]);
        }
    }

    /**
     * 蛇形转驼峰
     * @param string $str
     * @return string
     */
    public function toHump(string $str): string
    {
        $value = ucwords(str_replace(['-', '_'], ' ', $str));
        return str_replace(' ', '', $value);
    }

    /**
     * 对象设置成员变量 for protobuf message
     * @param object $response
     * @param array $inputData
     * @return void
     * @throws ReflectionException
     */
    public function setDataV2(object $response, array $inputData): void
    {
        $reflection = new ReflectionClass($response);
        $properties = $reflection->getProperties(ReflectionProperty::IS_PROTECTED | ReflectionProperty::IS_PRIVATE);
        foreach ($properties as $property) {
            $propertySnakeName = $property->getName();
            $propertyValue = (isset($inputData[$propertySnakeName]) && $inputData[$propertySnakeName] != "") ? $inputData[$propertySnakeName] : $property->getDefaultValue();
            if ($propertyValue == null && Str::contains($property->getDocComment(), ['int32', 'float'])) {
                if (!Str::contains($property->getDocComment(), ['repeated'])) {
                    $propertyValue = 0;
                }
            }

            $propertyName = $property->getName();
            $setDataFuncName = 'set' . $this->toHump($propertyName);
            if (!$reflection->hasMethod($setDataFuncName)) {
                // not found setXxx method continue.
                continue;
            }
            $reflectionMethod = $reflection->getMethod($setDataFuncName);
            if (!$reflectionMethod->isPublic()) {
                continue;
            }
            $docBlockFactory = DocBlockFactory::createInstance();
            $docBlock = $docBlockFactory->create($reflectionMethod);
            foreach ($docBlock->getTagsByName('param') as $paramTag) {
                /**
                 * Obj[] 格式
                 * @var Param $paramTag
                 */
                if ($paramTag->getType() instanceof Compound) {
                    /**
                     * @var ?Array_ $paramType
                     */
                    $paramType = $paramTag->getType()->get(0);
                    if ($paramType->getValueType() instanceof Object_) {
                        $obj = $paramType->getValueType()->getFqsen()->__toString();
                        if (is_array($propertyValue)) {
                            foreach ($propertyValue as &$item) {
                                $paramObj = new $obj();
                                $this->setDataV2($paramObj, $item);
                                $item = $paramObj;
                            }
                        }
                    }
                } else if ($paramTag->getType() instanceof Object_) {
                    /**
                     * Obj 格式
                     */
                    $obj = $paramTag->getType()->getFqsen()->__toString();
                    $paramObj = new $obj();
                    $this->setDataV2($paramObj, $propertyValue);
                    $propertyValue = $paramObj;
                }
            }
            $reflectionMethod->invokeArgs($response, [$propertyValue]);
        }
    }
}

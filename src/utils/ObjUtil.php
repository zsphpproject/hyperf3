<?php

namespace Zsgogo\utils;

use App\common\constant\ErrorNums;
use Hyperf\Stringable\Str;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;

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
     * 对象设置成员变量
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
            $reflectionMethod->invokeArgs($response, [$propertyValue]);
        }
    }
}
<?php

namespace Zsgogo\utils;

use App\common\constant\ErrorNums;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;
use Zsgogo\exception\AppException;

class ObjUtil {

    /**
     * 对象设置成员变量
     * @param object $response
     * @param array $inputData
     * @return void
     * @throws ReflectionException
     */
    public function setData(object $response, array $inputData): void {
        $reflection = new ReflectionClass($response);
        $properties = $reflection->getProperties(ReflectionProperty::IS_PROTECTED);
        foreach ($properties as $property){
            $propertySnakeName = $property->getName();
            $propertyValue = (isset($inputData[$propertySnakeName]) && $inputData[$propertySnakeName] != "") ? $inputData[$propertySnakeName] : $property->getDefaultValue();
            // if ($propertyValue == null) continue;
            $propertyName = $property->getName();
            $setDataFuncName = 'set' . $this->toHump($propertyName);
            if (!$reflection->hasMethod($setDataFuncName)) {
                throw new AppException(ErrorNums::METHOD_NOT_EXISTS,'method ' . $reflection->getName() . '::' . $setDataFuncName . ' not exists!');
            }
            $reflectionMethod = $reflection->getMethod($setDataFuncName);
            if (!$reflectionMethod->isPublic()) {
                throw new AppException(ErrorNums::METHOD_NOT_PUBLIC,'method ' . $reflection->getName() . '::' . $setDataFuncName . ' is not public!');
            }
            $reflectionMethod->invokeArgs($response, [$propertyValue]);
        }
    }

    /**
     * 蛇形转驼峰
     * @param string $str
     * @return string
     */
    public function toHump(string $str): string {
        $value = ucwords(str_replace(['-', '_'], ' ', $str));
        return str_replace(' ', '', $value);
    }
}
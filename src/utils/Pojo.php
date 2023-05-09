<?php
declare(strict_types=1);

namespace Zsgogo\utils;

use Hyperf\Contract\Arrayable;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Stringable\Str;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;
use Hyperf\HttpServer\Contract\RequestInterface;
use App\common\constant\ErrorNums;
use Zsgogo\exception\AppException;


abstract class Pojo implements Arrayable {

    /**
     * @var array $data 数据
     */
    private $data = [];

    /**
     * @var array $validates 验证器
     */
    protected $validates = [];

    /**
     * @var bool $autoValidate 自动验证
     */
    protected $autoValidate = true;

    /**
     * @var array $notFilterField 无需全局过滤的字段
     */
    protected $notFilterField = [];

    /**
     * @var \ReflectionClass
     */
    private $reflectionClass;


    public function toArray(): array {
        $properties = $this->reflectionClass->getProperties(ReflectionProperty::IS_PRIVATE);
        foreach ($properties as $property) {
            $propertyName = $property->getName();
            $getDataFuncName = 'get' . ucfirst($propertyName);
            $this->data[Str::snake($propertyName)] = $this->$getDataFuncName();
        }
        return $this->data;
    }


    public function __construct(RequestInterface $request, array $param = []) {
        $inputData = $request->all();
        $inputData = isset($inputData[0]) ? $inputData[0] : $inputData;
        $inputData = $this->fitterData($inputData ?? []);
        if ($request->getMethod() == "GET") {
            $getArray = $request->all();
            foreach ($getArray as $key => $value){
                if ($value == "") unset($getArray[$key]);
            }
            $inputData = array_merge($inputData,$getArray);
        }

        if (!empty($param)) {
            $inputData = array_merge($inputData,$param);
        }


        $this->reflectionClass = new ReflectionClass($this);
        $this->setData($inputData);
        // if ($this->autoValidate) {
        //     $this->validate();
        // }
    }


    // public function validate() {
    //     foreach ($this->validates as $validate => $scene) {
    //         if (is_string($scene)) {
    //             validate($validate)->scene($scene)->check($this->toArray());
    //         } else if (is_array($scene)) {
    //             foreach ($scene as $item) {
    //                 validate($validate)->scene($item)->check($this->toArray());
    //             }
    //         }
    //     }
    // }


    public function fitterData(array $params): array {
        foreach ($params as $paramKey => $paramValue) {
            if (in_array($paramKey, $this->notFilterField)) {
                unset($params[$paramKey]);
            }
        }
        return $params;
    }


    /**
     * @throws AppException
     * @throws ReflectionException
     */
    private function setData($inputData) {
        $properties = $this->reflectionClass->getProperties(ReflectionProperty::IS_PRIVATE);
        foreach ($properties as $property) {
            $propertySnakeName = Str::snake($property->getName());
            if (isset($inputData[$propertySnakeName])) {
                $propertyValue = $inputData[$propertySnakeName] ?? $property->getDefaultValue();
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
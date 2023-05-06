<?php

namespace Zsgogo\utils;

use Godruoyi\Snowflake\Snowflake;

class SnowFlakeUtil {

    private static $_instance;
    protected $datacenterId = 0;
    protected $workId = 0;
    protected $currentId = "";

    private function __construct() {}

    private function __clone() {}


    public function createId(): string {
        $snowflake = new Snowflake($this->datacenterId,$this->workId);
        $id = $snowflake->id();
        $this->currentId = Date("Ymd").$id;
        return $this->currentId;
    }

    public function setCurrentId(string $currentId): void {
        $this->currentId = $currentId;
    }

    public function getCurrentId() :string {
        return $this->currentId;
    }

    public static function getInstance(): SnowFlakeUtil {
        if (!(self::$_instance instanceof SnowFlakeUtil)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

}
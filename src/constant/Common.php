<?php

namespace Zsgogo\constant;

class Common {

    const DEFAULT_SIZE = 10;  // 默认每页数量

    // 排序
    const SORT = [
        "" => "",
        "+" => "created_at asc","-" => "created_at desc",
        "updated_at+" => "updated_at asc","updated_at-" => "updated_at desc",
        "start_time+" => "start_time asc","start_time-" => "start_time desc",
        "end_time+" => "end_time asc","end_time-" => "end_time desc",
    ];

    /**
     * 获取排序
     * @param string $sort
     * @return string
     */
    public static function getSort(string $sort): string {
        if ($sort != ""){
            $array = explode(",", $sort);
            foreach ($array as $key => $value){
                $value = trim($value);
                $array[$key] = self::SORT[$value];
            }
            return implode(",",$array);
        }
        return "created_at desc";
    }

}
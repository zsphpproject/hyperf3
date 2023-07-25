<?php

namespace Zsgogo\model;

use App\common\constant\Common;
use Hyperf\Contract\LengthAwarePaginatorInterface;
use Hyperf\Database\Model\Builder;
use Hyperf\Database\Model\Collection;
use Hyperf\DbConnection\Model\Model;
use Hyperf\Paginator\Paginator;

class BaseModel extends Model {

    /**
     * 返回list/分页list 默认分页
     * @param array $param
     * @param array $where
     * @param array $allowFields
     * @param array $with
     * @param int $noPaging
     * @return object
     */
    public function getList(array $param,array $where, array $allowFields,array $with = [],int $noPaging = 0): object {
        $param["sort"] = $param["sort"] ?? "";
        // $order = Common::getSort($param["sort"]) . "," . $this->primaryKey . " desc";
        $order = Common::getSort($param["sort"]);

        if ($noPaging == 1){
            return self::where($where)
                ->with($with)
                ->orderByRaw($order)
                ->select($allowFields)
                ->get();
        }else{
            return self::where($where)
                ->with($with)
                ->orderByRaw($order)
                ->select($allowFields)
                ->paginate($param["size"],$allowFields,"page",$param["page"]);
        }

    }

    /**
     * 返回分页
     * @param Collection|array $collection
     * @param array $param
     * @return Paginator
     */
    public function getPageList(Collection|array $collection, array $param): Paginator {
        if (is_array($collection)) $collection = new Collection($collection);
        $list = array_values($collection->forPage((int)$param["page"], (int)$param["size"])->toArray());
        return new Paginator($list,(int)$param["size"],(int)$param["page"]);
    }
}
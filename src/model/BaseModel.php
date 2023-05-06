<?php

namespace Zsgogo\model;

use App\common\constant\Common;
use Hyperf\Database\Model\Collection;
use Hyperf\DbConnection\Model\Model;
use Hyperf\Paginator\Paginator;

class BaseModel extends Model {

    /**
     * 返回list
     * @param array $param
     * @param array $where
     * @param array $allowFields
     * @param array $with
     * @return Collection|array
     */
    public function getList(array $param,array $where, array $allowFields,array $with = []): Collection|array {
        $param["sort"] = $param["sort"] ?? "";
        // $order = Common::getSort($param["sort"]) . "," . $this->primaryKey . " desc";
        $order = Common::getSort($param["sort"]);
        return self::where($where)
            ->with($with)
            ->orderByRaw($order)
            ->select($allowFields)
            ->get();
    }

    /**
     * 返回分页
     * @param Collection|array $voteList
     * @param array $param
     * @return Paginator
     */
    public function getPageList(Collection|array $voteList, array $param): Paginator {
        $collection = new Collection($voteList->toArray());
        $list = $collection->forPage((int)$param["page"], (int)$param["size"])->toArray();
        return new Paginator($list,(int)$param["size"],(int)$param["page"]);
    }
}
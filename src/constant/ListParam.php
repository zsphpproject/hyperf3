<?php

namespace Zsgogo\constant;

/**
 * @method getPage()
 * @method setPage(int $page)
 * @method getSize
 * @method setSize(int $size)
 * @method getSort()
 * @method setSort(string $sort)
 */
trait ListParam {

    private int $page = 1;

    private int $size = Common::DEFAULT_SIZE;

    private string $sort = "";
}

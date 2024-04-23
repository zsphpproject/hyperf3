<?php

namespace Zsgogo\constant;

/**
 * @property int $page
 * @property int $size
 * @property string $sort
 */
trait ListParam {

    private int $page = 1;

    private int $size = Common::DEFAULT_SIZE;

    private string $sort = "";
}

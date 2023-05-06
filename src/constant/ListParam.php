<?php

namespace Zsgogo\constant;

trait ListParam {

    private int|string $page = 1;

    private int|string $size = Common::DEFAULT_SIZE;

    private string $sort = "";

    /**
     * @return int|string
     */
    public function getPage(): int|string {
        return $this->page;
    }

    /**
     * @param int|string $page
     */
    public function setPage(int|string $page): void {
        $this->page = $page;
    }

    /**
     * @return int|string
     */
    public function getSize(): int|string {
        return $this->size;
    }

    /**
     * @param int|string $size
     */
    public function setSize(int|string $size): void {
        $this->size = $size;
    }


    /**
     * @return string
     */
    public function getSort(): string {
        return $this->sort;
    }

    /**
     * @param string $sort
     */
    public function setSort(string $sort): void {
        $this->sort = $sort;
    }

}
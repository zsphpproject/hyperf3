<?php

namespace Zsgogo\constant;

trait ListParam {

    private int $page = 1;

    private int $size = Common::DEFAULT_SIZE;

    private string $sort = "";

    /**
     * @return int
     */
    public function getPage(): int {
        return $this->page;
    }

    /**
     * @param int $page
     */
    public function setPage(int $page): void {
        $this->page = $page;
    }

    /**
     * @return int
     */
    public function getSize(): int {
        return $this->size;
    }

    /**
     * @param int $size
     */
    public function setSize(int $size): void {
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

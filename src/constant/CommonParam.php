<?php

namespace Zsgogo\constant;

trait CommonParam {

    private string $requestId = "";

    /**
     * @return string
     */
    public function getRequestId(): string {
        return $this->requestId;
    }

    /**
     * @param string $requestId
     */
    public function setRequestId(string $requestId): void {
        $this->requestId = $requestId;
    }

}
<?php

namespace Zsgogo\utils\popo;

use Attribute;

#[Attribute]
class ObjArray
{
    public function __construct(public string $objectName) {}
}

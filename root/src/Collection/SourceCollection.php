<?php

namespace App\Collection;

use App\Model\Source;
use InvalidArgumentException;

class SourceCollection implements CollectionInterface
{
    use CollectionTrait;
    public function offsetGet(mixed $offset): Source
    {
        return $this->items[$offset];
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        if(!$value instanceof Source){
            throw new InvalidArgumentException('$value must be instance of Source');
        }
        if($offset === null){
            $offset = $this->position;
        }
        $this->items[$offset] = $value;
    }
}
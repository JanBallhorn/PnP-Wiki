<?php

namespace App\Collection;

use App\Model\Category;
use InvalidArgumentException;

class CategoryCollection implements CollectionInterface
{
    use CollectionTrait;
    public function offsetGet(mixed $offset): Category
    {
        return $this->items[$offset];
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        if(!$value instanceof Category){
            throw new InvalidArgumentException('$value must be instance of Category');
        }
        if($offset === null){
            $offset = $this->position;
        }
        $this->items[$offset] = $value;
    }
}
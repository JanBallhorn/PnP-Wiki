<?php

namespace App\Collection;

use App\Model\CategoryInfoTemplate;
use InvalidArgumentException;

class CategoryInfoTemplateCollection implements CollectionInterface
{
    use CollectionTrait;
    public function offsetGet(mixed $offset): CategoryInfoTemplate
    {
        return $this->items[$offset];
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        if(!$value instanceof CategoryInfoTemplate){
            throw new InvalidArgumentException('$value must be instance of CategoryInfoTemplate');
        }
        if($offset === null){
            $offset = $this->position;
        }
        $this->items[$offset] = $value;
    }
}

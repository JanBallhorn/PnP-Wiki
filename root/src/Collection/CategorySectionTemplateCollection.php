<?php

namespace App\Collection;

use App\Model\CategorySectionTemplate;
use InvalidArgumentException;

class CategorySectionTemplateCollection implements CollectionInterface
{
    use CollectionTrait;
    public function offsetGet(mixed $offset): CategorySectionTemplate
    {
        return $this->items[$offset];
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        if(!$value instanceof CategorySectionTemplate){
            throw new InvalidArgumentException('$value must be instance of CategorySectionTemplate');
        }
        if($offset === null){
            $offset = $this->position;
        }
        $this->items[$offset] = $value;
    }
}

<?php

namespace App\Collection;

use App\Model\Project;
use InvalidArgumentException;

class ProjectCollection implements CollectionInterface
{
    use CollectionTrait;
    public function offsetGet(mixed $offset): Project
    {
        return $this->items[$offset];
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        if(!$value instanceof Project){
            throw new InvalidArgumentException('$value must be instance of Project');
        }
        if($offset === null){
            $offset = $this->position;
        }
        $this->items[$offset] = $value;
    }
}
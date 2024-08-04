<?php

namespace App\Collection;

use App\Model\User;
use InvalidArgumentException;

class UserCollection implements CollectionInterface
{
    use CollectionTrait;
    public function offsetGet(mixed $offset): User
    {
        return $this->items[$offset];
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        if(!$value instanceof User){
            throw new InvalidArgumentException('$value must be instance of User');
        }
        if($offset === null){
            $offset = $this->position;
        }
        $this->items[$offset] = $value;
    }
}
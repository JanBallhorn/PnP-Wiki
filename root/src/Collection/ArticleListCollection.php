<?php

namespace App\Collection;

use App\Model\ArticleList;
use InvalidArgumentException;

class ArticleListCollection implements CollectionInterface
{
    use CollectionTrait;
    public function offsetGet(mixed $offset): ArticleList
    {
        return $this->items[$offset];
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        if(!$value instanceof ArticleList){
            throw new InvalidArgumentException('$value must be instance of ArticleList');
        }
        if($offset === null){
            $offset = $this->position;
        }
        $this->items[$offset] = $value;
    }
}
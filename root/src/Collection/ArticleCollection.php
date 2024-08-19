<?php

namespace App\Collection;

use App\Model\Article;
use InvalidArgumentException;

class ArticleCollection implements CollectionInterface
{
    use CollectionTrait;
    public function offsetGet(mixed $offset): Article
    {
        return $this->items[$offset];
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        if(!$value instanceof Article){
            throw new InvalidArgumentException('$value must be instance of Article');
        }
        if($offset === null){
            $offset = $this->position;
        }
        $this->items[$offset] = $value;
    }
}
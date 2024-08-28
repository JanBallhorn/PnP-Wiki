<?php

namespace App\Collection;

use App\Model\ArticleInfoContent;
use InvalidArgumentException;

class ArticleInfoContentCollection implements CollectionInterface
{
    use CollectionTrait;
    public function offsetGet(mixed $offset): ArticleInfoContent
    {
        return $this->items[$offset];
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        if(!$value instanceof ArticleInfoContent){
            throw new InvalidArgumentException('$value must be instance of ArticleInfoContent');
        }
        if($offset === null){
            $offset = $this->position;
        }
        $this->items[$offset] = $value;
    }
}
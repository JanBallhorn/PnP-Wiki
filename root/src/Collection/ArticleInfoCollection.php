<?php

namespace App\Collection;

use App\Model\ArticleInfo;
use InvalidArgumentException;

class ArticleInfoCollection implements CollectionInterface
{
    use CollectionTrait;
    public function offsetGet(mixed $offset): ArticleInfo
    {
        return $this->items[$offset];
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        if(!$value instanceof ArticleInfo){
            throw new InvalidArgumentException('$value must be instance of ArticleInfo');
        }
        if($offset === null){
            $offset = $this->position;
        }
        $this->items[$offset] = $value;
    }
}
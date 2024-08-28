<?php

namespace App\Collection;

use App\Model\ArticleInfoGallery;
use InvalidArgumentException;

class ArticleInfoGalleryCollection implements CollectionInterface
{
    use CollectionTrait;
    public function offsetGet(mixed $offset): ArticleInfoGallery
    {
        return $this->items[$offset];
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        if(!$value instanceof ArticleInfoGallery){
            throw new InvalidArgumentException('$value must be instance of ArticleInfoGallery');
        }
        if($offset === null){
            $offset = $this->position;
        }
        $this->items[$offset] = $value;
    }
}
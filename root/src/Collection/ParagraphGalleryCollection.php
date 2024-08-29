<?php

namespace App\Collection;

use App\Model\ParagraphGallery;
use InvalidArgumentException;

class ParagraphGalleryCollection implements CollectionInterface
{
    use CollectionTrait;
    public function offsetGet(mixed $offset): ParagraphGallery
    {
        return $this->items[$offset];
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        if(!$value instanceof ParagraphGallery){
            throw new InvalidArgumentException('$value must be instance of ParagraphGallery');
        }
        if($offset === null){
            $offset = $this->position;
        }
        $this->items[$offset] = $value;
    }
}
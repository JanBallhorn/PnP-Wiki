<?php

namespace App\Collection;

use App\Model\Paragraph;
use InvalidArgumentException;

class ParagraphCollection implements CollectionInterface
{
    use CollectionTrait;
    public function offsetGet(mixed $offset): Paragraph
    {
        return $this->items[$offset];
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        if(!$value instanceof Paragraph){
            throw new InvalidArgumentException('$value must be instance of Paragraph');
        }
        if($offset === null){
            $offset = $this->position;
        }
        $this->items[$offset] = $value;
    }
}
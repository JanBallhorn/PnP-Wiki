<?php

namespace App\Collection;

trait CollectionTrait
{
    private int $position = 0;

    public function __construct(private array $items = [])
    {
    }

    public function current(): mixed
    {
        return $this->items[$this->position];
    }

    public function next(): void
    {
        ++$this->position;
    }

    public function key(): mixed
    {
        return $this->position;
    }

    public function valid(): bool
    {
        return isset($this->items[$this->position]);
    }

    public function rewind(): void
    {
        $this->position = 0;
    }

    public function count(): int
    {
        return count($this->items);
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->items[$offset]);
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->items[$offset]);
    }

    public function serialize(): ?string
    {
        return serialize($this->items);
    }

    public function unserialize(string $data): void
    {
        $this->items = unserialize($data);
    }

    public function __serialize(): array
    {
        return $this->items;
    }

    public function __unserialize(array $data): void
    {
        $this->items = $data;
    }

    public function __toString()
    {
        return serialize($this);
    }
}
<?php

namespace App\Repository;
use App\Collection\CollectionInterface;

interface RepositoryInterface
{
    public function findAll(string $order = '');
    public function findById(int $id);
    public function findBy(string $column, mixed $value, string $order = '');
    public function findOneBy(string $column, mixed $value);
    public function save(object $entity): void;
    public function delete(object $entity): void;
    public function closeDB(): void;
}
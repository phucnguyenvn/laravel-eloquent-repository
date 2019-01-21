<?php
namespace phucnguyenvn\EloquentRepository\Repositories;

interface CriteriaInterface
{
    public function filter($field, $value, $operator);

    public function order($orderBy, $sortDirection);

    public function applyOrder($queryBuilder);

    public function applyFilters($queryBuilder);

    public function resetFilters();

    public function resetOrder();
}
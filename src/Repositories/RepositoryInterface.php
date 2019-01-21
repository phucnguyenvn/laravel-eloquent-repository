<?php 
namespace phucnguyenvn\EloquentRepository\Repositories;

interface RepositoryInterface
{
    public function find($id);
    public function findAll();
    public function paginate($perPage);
    public function count();

    public function with($relations);

    public function create(array $data);
    public function update($id, array $data);
    public function delete($id);

    public function filter($field, $value, $operator);
    public function order($field, $sort);
}
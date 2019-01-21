<?php
namespace phucnguyenvn\EloquentRepository\Repositories;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

abstract class EloquentRepository implements RepositoryInterface
{
    /**
     * var $model Model
     */
    protected $model;

    /**
     * var $modelClassName string
     */
    protected $modelClassName;

    /**
     * var $with array|string
     */
    protected $with;

    /**
     * var $criteria EloquentCriteria
     */
    protected $criteria;

    /**
     * EloquentRepository constructor.
     * 
     */
    public function __construct(Model $model){
        $this->model = $model;

        // A clean copy of the model is needed when the scope needs to be reset.
        $reflection = new \ReflectionClass($this->model);
        $this->modelClassName = $reflection->getName();

        // Criteria
        $this->criteria = new EloquentCriteria();
    }

    /**
     * Finds one item by the provided field.
     * 
     * @param $id
     * @return mixed
     */
    public function find($id){
        $this->eagerLoadRelations();
        $this->applyCriteria();

        $result = $this->getModel()->find($id);
        $this->resetScope();

        return $result;
    }

    /**
     * Finds all items.
     * 
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function findAll(){
        $this->eagerLoadRelations();
        $this->applyCriteria();

        if ($this->model instanceof Builder) {
            $result = $this->model->get();
        }else{
            $result = $this->model->all();
        }
        
        $this->resetScope();

        return $result;
    }

    /**
     * Return first item.
     * 
     * @return Illuminate\Database\Eloquent\Model
     */
    public function first(){
        $this->eagerLoadRelations();
        $this->applyCriteria();
        $result = $this->model->first();
        
        $this->resetScope();

        return $result;
    }

    /**
     * Returns a Paginator that based on the criteria or filters given.
     * 
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function paginate($perPage=10){
        $this->eagerLoadRelations();
        $this->applyCriteria();

        $result = $this->model->paginate($perPage);
        $result->appends(request()->query());
        $this->resetScope();

        return $result;
    }

    /**
     * Count
     * 
     * @return int
     */
    public function count(){
        $this->eagerLoadRelations();

        $result = $this->getModel()->count();
        $this->resetScope();

        return $result;
    }

    /**
     * With
     * 
     * @param array|string $relations
     * @return $this
     */
    public function with($relations){
        if (is_string($relations)) $relations = func_get_args();
        $this->with = $relations;
        
        return $this;
    }

    /**
     * Create
     * 
     * @param array $attributes
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function create(array $attributes){
        return $this->getModel()->create($attributes);
    }

    /**
     * Force Create
     * 
     * @param array $attributes
     * @return \Illuminate\Database\Eloquent\Model
     * 
     * @throws \Throwable
     */
    public function forceCreate(array $attributes){
        return $this->getModel()->create($attributes);
    }

    /**
     * Update
     * 
     * @param array $attributes
     * @param $id
     * @return \Illuminate\Database\Eloquent\Model | bool
     */
    public function update($id, array $attributes){
        $result = $this->find($id);
        if($result) {
            $result->update($attributes);
            return $result;
        }
        return false;
    }

    /**
     * Force Update
     * 
     * @param array $attributes
     * @param $id
     * 
     * @return \Illuminate\Database\Eloquent\Model
     * 
     * @throws \Throwable
     */
    public function forceUpdate($id, array $attributes){
        $result = $this->getModel()->forceFill($attributes)->saveOrFail();
        return $result;
    }

    /**
     * Delete  
     * 
     * @param $id
     * @return bool
     */
    public function delete($id){
        $result = $this->find($id);
        if($result) {
            $result->delete();
            return true;
        }

        return false;
    }

    /**
     * Filter
     * 
     * @return $this
     */
    public function filter($field, $value, $operator=null){
        $this->criteria->filter($field, $value, $operator);
        return $this;
    }

    /**
     * Order
     * 
     * @return $this
     */
    public function order($field, $sort = null) {
        $this->criteria->order($field, $sort);
        return $this;
    }

    /**
     * Get model
     * 
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function getModel(){
        return $this->model instanceof Model
            ? $this->model
            : $this->model->getModel();
    }

    /**
     * Get a new query builder for the model's table.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function newQuery(){
        return $this->model instanceof Model
            ? $this->model->newQuery()
            : $this->model;
    }

    /**
     * Find by attribute
     * 
     * @param $attribute
     * @param $value
     * @param array $columns
     * @return mixed
     */
    public function findBy($attribute, $value) {
        return $this->model->where($attribute, '=', $value)->first();
    }

    /**
     * Get property from findBy magic method
     *
     * @param $method
     * @return mixed
     */
    private function getFindByProperty($method){
        return strtolower(str_replace('findBy', '', $method));
    }
    
    /**
     * Check that if a magic method argument is findable or not
     *
     * @param $method
     * @return bool
     */
    private function isFindable($method){
        if (Str::startsWith($method, 'findBy')){
            return true;
        }
        
        return false;
    }

    /**
     * Eager load relations
     *
     * @return mixed
     */
    protected function eagerLoadRelations(){
        if (is_array($this->with)){
            $this->model = $this->model->with($this->with);
        }
    }

    /**
     * Apply criteria
     * 
     * @return $this
     */
    protected function applyCriteria(){
        $this->model = $this->criteria->applyFilters($this->model);
        $this->model = $this->criteria->applyOrder($this->model);

        return $this; 
    }

    /**
     * Reset scope
     * 
     * @return self
     */
    protected function resetScope(){
        $this->model = new $this->modelClassName;
        $this->criteria->resetFilters();
        $this->criteria->resetOrder();
        return $this;
    }

    /**
     * Add the ability to find by property
     *
     * @param $method
     * @param $args
     * 
     * @return mixed
     * @throws Exception
     */
    public function __call($method, $args){
        if (method_exists($this, $method)) {
            return call_user_func($method, $args);
        }
        
        if ($this->isFindable($method)){
            $property = $this->getFindByProperty($method);
            return call_user_func_array([$this, 'findBy'], [$property, $args[0]]);
        }
        
        throw new \Exception("No method with name: [{$method}] found");
    }
}
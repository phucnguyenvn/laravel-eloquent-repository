<?php
namespace phucnguyenvn\EloquentRepository\Repositories;

use Illuminate\Support\Str;

class EloquentCriteria implements CriteriaInterface
{
    /**
     * var $order array
     */
    public $order   = [];

    /**
     * var $filters array
     */
    public $filters = [];

    /**
     * var $allowedOperators array
     */
    protected $allowedOperators = ['=', 'like', '<', '<=', '>', '>=', '!='];

    /**
     * Add filter
     *
     * @param $field string
     * @param $value string
     * @param $operator string
     * 
     * @return self
     */
    public function filter($field, $value, $operator = null) {
        if (! $operator) {
            $operator = '=';
        }
        $this->filters[] = array($field, $value, $operator);
        return $this;
    }

    /**
     * Add order
     *
     * @param $orderBy string
     * @param $sortDirection string
     * 
     * @return self
     */
    public function order($orderBy, $sortDirection = null) {
        if (! $sortDirection) {
            $sortDirection = 'asc';
        }
        $this->order[$orderBy] = $sortDirection;
        return $this;
    }

    /**
     * Apply filter
     *
     * @param $queryBuilder \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Builder  
     * 
     * @return self
     */
    public function applyFilters($queryBuilder) {
        foreach ($this->filters as $where) {
            $operator = $where[2];

            // @: dimsav/laravel-translatable package
            // https://github.com/dimsav/laravel-translatable
            if(property_exists($queryBuilder, 'defaultLocale') 
                && in_array($where[0], $queryBuilder->translatedAttributes)){
                switch($operator){
                    case 'like':
                        $queryBuilder = $queryBuilder->whereTranslationLike($where[0], $where[1]);
                        break;
                    default:
                        $queryBuilder = $queryBuilder->whereTranslation($where[0], $where[1]);
                }
                continue;
            }

            // @: normal
            switch($operator){
                case 'in':
                    $queryBuilder = $queryBuilder->whereIn($where[0], $where[1]);
                    break;
                default:
                    $queryBuilder = $queryBuilder->where($where[0], $where[2], $where[1]);
            }
        }
        return $queryBuilder;
    }
     
    /**
     * Apply order
     *
     * @param $queryBuilder \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Builder  
     * 
     * @return self
     */
    public function applyOrder($queryBuilder) {
        foreach ($this->order as $orderBy => $sortDirection) {
            // @: dimsav/laravel-translatable package
            // https://github.com/dimsav/laravel-translatable
            if(property_exists($queryBuilder, 'defaultLocale') 
                && in_array($orderBy, $queryBuilder->translatedAttributes)){
                    //: ignore
                continue;
            }

            // @: normal
            $queryBuilder = $queryBuilder->orderBy($orderBy, $sortDirection);
        }
        return $queryBuilder;
    }

    /**
     * Reset filters
     * 
     * @return self
     */
    public function resetFilters(){
        $this->filters = [];
        return $this;
    }

    /**
     * Reset order
     *
     * @return self
     */
    public function resetOrder(){
        $this->order  = [];
        return $this;
    }
}
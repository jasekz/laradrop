<?php
namespace Jasekz\Laradrop\Services;

class Base  {

    /**
     * These will be excluded from where clauses
     * 
     * @var array
     */
    protected  $excludeFilters = [
            'sortby',
            'dir',
            'page',
            'ids',
            'showEntries',
        ];

    /**
     * Return list of entries;
     * This is a generic function which is suitable for most use cases, but can 
     * easily be overridden as needed
     * 
     * @param array $params
     * @return \App\Models\Base
     */
    public function all($params = [])
    {
        if (array_get($params, 'sortby')) {
            $this->model = $this->model->orderBy(array_get($params, 'sortby'), array_get($params, 'dir') ? array_get($params, 'dir') : 'asc');
        }
        
        if (array_get($params, 'ids')) {
            $this->model = $this->model->whereIn('id', array_get($params, 'ids'));
        }

        foreach ($params as $search => $value) {
            
            if (in_array($search, $this->excludeFilters)) { // we don't want to search by these
                continue;
            }
            
            if( ! $value){ // must have a value
                continue;
            }
            
            // if the passed in param is an array, as in filter[some_attribute], drill down
            if (is_array($value)) {
                $this->model = $this->model->where(function ($query) use($value, $search)
                {
                    foreach ($value as $key2 => $value2) {
                        $orWheres = explode('|', $key2); // if fields are separated by a pipe, |, then we'll treat them as 'orWhere' statements
                        foreach($orWheres as $orWhere) {
                            $query->orWhere($orWhere, 'like', '%' . $value2 . '%');
                        }
                    }
                });
            } else { // if the passed in param is not an array
                $this->model = $this->model->where($search, 'like', '%' . $value . '%');
            }
        }
        
        return $this->model;
    }
}
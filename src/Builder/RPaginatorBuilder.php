<?php declare(strict_types=1);

namespace RPaginate\Builder;

use RPaginate\Builder\RPaginator;

class RPaginatorBuilder
{
    public $limit;
    public string $key;
    public object $model;
    public $query;
    public $data;
    public array $rwith;

    public function __construct($model)
    {
        $this->model = new $model;

        $this->key();
        $this->customQuery();
        $this->getRelations();
    }

    /**
     * setting the redis key
     *
     * @return $this
     */
    public function key()
    {
        $this->key = $this->model->key();
    }

    /**
     * If you have relations, you can add the query
     *
     * @return void
     */
    public function getRelations()
    {
        if ( is_null($this->query) ) {
            if ( property_exists($this->model, 'rwith') & is_array($this->model->rwith) ) {
                
                if ( count($this->model->rwith) ) {
    
                    $this->query = $this->model::with($this->model->rwith)->get();
                }
            }
        }

        return $this;
    }
    
    /**
     * This function will execute your specific query in your model
     *
     * @return $this
     */
    public function customQuery()
    {
        if ( method_exists($this->model, 'RQuery') ) {
            
            $this->query = $this->model->RQuery();
        }

        return $this;
    }

    /**
     * Determine how many data will add to redis. 
     *
     * @param  int $limit
     * @return void
     */
    public function limit(int $limit = 0)
    {
        $this->limit = $limit;
        
        if ( $this->limit != 0 ) {
            $this->limit = $limit;
            $this->query = $this->query->take($this->limit);
        }

        return $this;
    }
    
    /**
     * set data from model
     *
     * @return void
     */
    public function setData()
    {
        $this->data = $this->query;
        
        return $this;
    }
    
    /**
     * This function execute RPaginator and send data to add redis
     *
     * @return RPaginator
     */
    public function build(): RPaginator
    {
        $this->setData();
        
        return new RPaginator($this);
    }
}
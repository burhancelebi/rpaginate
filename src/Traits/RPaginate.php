<?php

namespace RPaginate\Traits;

use RPaginate\Builder\RPaginatorBuilder;
use RPaginate\RPaginate as Pagination;
use ReflectionClass;

trait RPaginate
{    
    private $key = false;
    private static int $limit = 10;

    /**
     * Set the model redis key
     *
     * @return $this->key
     */
    public function key($key = false)
    {
        $this->key = $key;
        
        if ( !$key ) {
            
            $this->key = strtolower($this->getClassName());
            
            if ( property_exists($this, 'rkey') ) {
                
                $this->key = $this->rkey;
            }
        }
        
        return $this->key;
    }

    public static function rpaginate($limit = false)
    {
        $paginate = new Pagination(new self, $limit);
        
        return $paginate->rpaginate();
    }

    public function getClassName()
    {
        return (new \ReflectionClass($this))->getShortName();
    }
}
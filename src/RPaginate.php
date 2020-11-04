<?php declare(script_types=1);

namespace RPaginate;

use Predis\Client as Redis;
use RPaginate\Collection;

class RPaginate
{
    protected $key;
    protected $start;
    protected $limit;
    protected $perPage;
    protected $page;
    protected $total;
    protected object $model;
    protected object $redis;

    public function __construct($model, $limit, $key = null)
    {
        $this->model = new $model;
        $this->setKey($key);
        $this->redis = new Redis();
        $this->page = abs(request()->input('page', 1));
        $this->limit = $limit;
        $this->perPage = $this->limit;
        $this->start = -$this->limit;
        $this->setRange();
    }
    
    public function rpaginate()
    {
        $lrange = $this->redis->lrange($this->key, $this->start, $this->limit);
        
        $data = $this->unserialize($lrange);
        
        $collection = (new Collection($data))->paginate($this->perPage, $this->total);

        $newCollection = (function() use ($data) { $this->items = collect($data); return $this; })
                            ->call($collection);

        return $newCollection;
    }

    public function unserialize($data = [])
    {
        $collect = [];
        
        foreach ($data as $key => $value) {
            
            $collect[] = unserialize($value);
        }

        return $collect;
    }

    public function setKey($key = null)
    {
        $this->key = $this->model->key();

        if ( !is_null($key) ) {
            $this->key = $key;
        }
    }

    public function getTotal()
    {
        $this->total = intval($this->redis->get($this->key . '_total'));

        return $this->total;
    }

    public function setRange()
    {
        $lastPage = intval(ceil($this->getTotal() / $this->limit));
        
        if ( request()->has('page') ) {

            if ( $this->page > $lastPage ){
                $this->page = 1;
            }
            
            $this->limit = $this->page * $this->limit - 1;

            if ( $this->limit > $this->getTotal() ) {
                
                $this->start = abs((int)($this->limit / $this->page + 1));
                $this->limit = $this->getTotal() - 1;

                return;
            }
            
            $this->start = $this->start + $this->limit + 1;
        }
    }
}
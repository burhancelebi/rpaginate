<?php

namespace RPaginate;

use Illuminate\Support\ServiceProvider;
use RPaginate\Commands\Publisher;
use RPaginate\Commands\Consumer;

class CommandServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if($this->app->runningInConsole()){
            $this->commands([
                Publisher::class,
                Consumer::class,
            ]);
        }
    }
}
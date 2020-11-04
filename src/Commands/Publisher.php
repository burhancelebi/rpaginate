<?php

namespace RPaginate\Commands;

use Illuminate\Console\Command;
use RPaginate\Builder\RPaginatorBuilder;

class Publisher extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rabbit:publisher {model} {--limit=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publish data to add queue';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if (is_null($this->argument('model'))) {
            $this->error('Model name is required');
        }

        $model = $this->argument('model');
        
        $limit = $this->option('limit');
        
        if ( !is_null($limit) AND !is_numeric($limit) )
        {
            return $this->error('Invalid limit');
        }

        $this->line("Data publishing...");
        
        $build = (new RPaginatorBuilder($model))
                ->limit((int)abs($limit))
                ->build();

        $this->info("\n Data published successfully");

        return 0;
    }
}

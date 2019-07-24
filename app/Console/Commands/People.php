<?php

namespace App\Console\Commands;

use App\Services\Console\People\PeopleHandler;
use Illuminate\Console\Command;

class People extends Command
{

    /**
     * @var PeopleHandler
     */
    private $people;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'people:handle
            {separator : \'comma\' or \'semicolon\'}
            {action : \'countAverageLineCount\' or \'replaceDates\'}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(PeopleHandler $people)
    {
        parent::__construct();
        $this->people = $people;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if(strcasecmp($this->argument('separator'), 'comma') == 0) { 
            $separator = ',';
        } elseif(strcasecmp($this->argument('separator'), 'semicolon') == 0) {
            $separator = ';';
        }
        $action = $this->argument('action');
        
        $this->people->$action($separator);
    }
}

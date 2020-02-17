<?php

namespace Novatio\TranslationManager\Console\Commands;

use Illuminate\Console\Command;
use Novatio\TranslationManager\Manager;

class FindFillExportCommand extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'translations:findfillexport';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Find, Fill from Key and export translations strings';

    /**
     * @var Manager
     */
    protected $manager;

    public function __construct(Manager $manager)
    {
        $this->manager = $manager;
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->call('translations:import', [
            '--replace' => 1
        ]);
        $this->call('translations:find');
        $this->call('translations:fillfromkey');
        $this->call('translations:export', [
            'group' => '*'
        ]);
    }
}

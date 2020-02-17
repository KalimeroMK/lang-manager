<?php

namespace Novatio\TranslationManager\Console\Commands;

use Illuminate\Console\Command;
use Novatio\TranslationManager\Manager;

class FillFromKeyCommand extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'translations:fillfromkey';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Populate empty values from keys';

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
        $this->manager->fillFromKey();
        $this->info("All Empty translations are set from last key part");
    }
}

<?php

namespace Novatio\TranslationManager\Console\Commands;

use Illuminate\Console\Command;
use Novatio\TranslationManager\Manager;

class ResetCommand extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'translations:reset';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete all translations from the database';

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
        $this->manager->truncateTranslations();
        $this->info("All translations are deleted");
    }
}
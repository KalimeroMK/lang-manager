<?php

namespace Novatio\TranslationManager\Console\Commands;

use Novatio\TranslationManager\Manager;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;

class ExportCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'translations:export';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export translations to PHP files';

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
        $group = $this->argument('group');

        $this->manager->exportTranslations($group);

        $this->info("Done writing language files for " . (($group == '*') ? 'ALL groups' : $group . " group"));

        $this->call('cache:clear');
        $this->call('view:clear');
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            [ 'group', InputArgument::REQUIRED, "The group to export ('*' for all)." ],
        ];
    }
}

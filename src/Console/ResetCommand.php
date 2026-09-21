<?php

declare(strict_types=1);

namespace Kalimero\TranslationManager\Console;

use Illuminate\Console\Command;
use Kalimero\TranslationManager\Manager;

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

    public function __construct(protected Manager $manager)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->manager->truncateTranslations();
        $this->info('All translations are deleted');
    }
}

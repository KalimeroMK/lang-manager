<?php

declare(strict_types=1);

namespace Kalimero\TranslationManager\Console;

use Illuminate\Console\Command;
use Kalimero\TranslationManager\Manager;

class CleanCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'translations:clean';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean empty translations';

    public function __construct(protected Manager $manager)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->manager->cleanTranslations();
        $this->info('Done cleaning translations');
    }
}

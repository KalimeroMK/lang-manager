<?php

declare(strict_types=1);

namespace Kalimero\TranslationManager\Console;

use Illuminate\Console\Command;
use Kalimero\TranslationManager\Manager;

class FindCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'translations:find';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Find translations in php/twig files';

    public function __construct(protected Manager $manager)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $counter = $this->manager->findTranslations();
        $this->info('Done importing, processed '.$counter.' items!');
    }
}

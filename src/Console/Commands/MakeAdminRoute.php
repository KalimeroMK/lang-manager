<?php


namespace Novatio\TranslationManager\Console\Commands;

use Novatio\Admin\Console\Commands\AbstractMakeAdminCommand;

class MakeAdminRoute extends AbstractMakeAdminCommand
{
    /**
     * @var string
     */
    protected $signature = 'adminroute:translations';

    /**
     * @var string
     */
    protected $description = 'Create an Admin Route for the Translation Manager';

    public function handle()
    {
        $this->createRoutes();
    }
}

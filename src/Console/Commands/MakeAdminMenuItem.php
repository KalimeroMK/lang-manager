<?php


namespace Novatio\TranslationManager\Console\Commands;

use Novatio\Admin\Console\Commands\AbstractMakeAdminCommand;

class MakeAdminMenuItem extends AbstractMakeAdminCommand
{
    /**
     * @var string
     */
    protected $signature = 'adminmenu:translations';

    /**
     * @var string
     */
    protected $description = 'Create an Admin Menu Item for the Translation Manager';

    public function handle()
    {
        $this->createMenuItem();
    }
}

<?php

namespace Novatio\TranslationManager;

use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Novatio\TranslationManager\Console\Commands\FindFillExportCommand;
use Novatio\TranslationManager\Models\Translation;
use Novatio\TranslationManager\Policies\TranslationPolicy;
use Novatio\TranslationManager\Console\Commands\FindCommand;
use Novatio\TranslationManager\Console\Commands\CleanCommand;
use Novatio\TranslationManager\Console\Commands\ResetCommand;
use Novatio\TranslationManager\Console\Commands\ExportCommand;
use Novatio\TranslationManager\Console\Commands\ImportCommand;
use Novatio\TranslationManager\Console\Commands\MakeAdminRoute;
use Novatio\TranslationManager\Console\Commands\MakeAdminMenuItem;
use Novatio\TranslationManager\Console\Commands\FillFromKeyCommand;

class ManagerServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap the application events.
     *
     * @param  \Illuminate\Routing\Router $router
     *
     * @return void
     */
    public function boot(Router $router)
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'translation-manager');
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'translation-manager');

        // add context translation hook
        app('translator')->addNamespace('langcontext', base_path('resources/langcontext'));

        $config              = $this->app['config']->get('translation-manager.route', []);
        $config['namespace'] = 'Novatio\TranslationManager';

        $this->bootPolicies();
        $this->publish();
    }

    /**
     * @return void
     */
    public function bootPolicies()
    {
        Gate::policy(Translation::class, TranslationPolicy::class);
    }

    /**
     * @return void
     */
    public function publish()
    {
        /*
         * Publish the admin config
         */
        $this->publishes([
            __DIR__ . '/../config/translation-manager.php' => config_path('translation-manager.php'),
        ], 'config');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/translation-manager.php', 'translation-manager');

        $this->registerCommands();
        $this->registerProviders();
    }

    /**
     * @return void
     */
    public function registerCommands()
    {
        if ($this->app->runningInConsole()) {
            $commands = [
                'command.translation-manager.reset'          => ResetCommand::class,
                'command.translation-manager.import'         => ImportCommand::class,
                'command.translation-manager.find'           => FindCommand::class,
                'command.translation-manager.export'         => ExportCommand::class,
                'command.translation-manager.clean'          => CleanCommand::class,
                'command.translation-manager.menuitem'       => MakeAdminMenuItem::class,
                'command.translation-manager.route'          => MakeAdminRoute::class,
                'command.translation-manager.fillfromkey'    => FillFromKeyCommand::class,
                'command.translation-manager.findfillexport' => FindFillExportCommand::class,
            ];

            $this->commands($commands);
        }
    }

    private function registerProviders()
    {
        $this->app->register(TranslationServiceProvider::class);
    }

}

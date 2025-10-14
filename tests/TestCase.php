<?php

namespace Kalimero\TranslationManager\Tests;

use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // Clean up any existing test files
        $this->cleanupTestFiles();
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }

    protected function tearDown(): void
    {
        $this->cleanupTestFiles();
        parent::tearDown();
    }

    private function cleanupTestFiles(): void
    {
        $langPath = lang_path();
        if (is_dir($langPath)) {
            \Illuminate\Support\Facades\File::deleteDirectory($langPath);
        }
        
        $testFile = base_path('test_translations.php');
        if (file_exists($testFile)) {
            \Illuminate\Support\Facades\File::delete($testFile);
        }
    }

    protected function getPackageProviders($app): array
    {
        return [
            \Kalimero\TranslationManager\ManagerServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
        
        $app['config']->set('translation-manager', [
            'route' => [
                'prefix' => 'translations',
                'middleware' => 'web',
            ],
            'delete_enabled' => true,
            'exclude_groups' => [],
            'exclude_langs' => [],
            'sort_keys' => false,
            'trans_functions' => [
                'trans',
                'trans_choice',
                'Lang::get',
                'Lang::choice',
                'Lang::trans',
                'Lang::transChoice',
                '@lang',
                '@choice',
                '__',
                '$trans.get',
            ],
            'models' => [],
            'model-field-source' => 'translatable',
            'db_connection' => 'testbench',
            'pagination_enabled' => false,
            'per_page' => 40,
            'layout' => 'translation-manager::layout',
            'template' => 'bootstrap5',
        ]);
    }
}

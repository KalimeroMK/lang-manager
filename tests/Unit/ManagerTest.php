<?php

namespace Kalimero\TranslationManager\Tests\Unit;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\File;
use Kalimero\TranslationManager\Manager;
use Kalimero\TranslationManager\Models\Translation;
use Kalimero\TranslationManager\Tests\TestCase;

class ManagerTest extends TestCase
{
    protected Manager $manager;

    protected Filesystem $files;

    protected Dispatcher $events;

    protected function setUp(): void
    {
        parent::setUp();

        $this->files = new Filesystem;
        $this->events = app('events');
        $this->manager = new Manager($this->app, $this->files, $this->events);
    }

    public function test_can_create_manager_instance(): void
    {
        $this->assertInstanceOf(Manager::class, $this->manager);
    }

    public function test_json_group_constant(): void
    {
        $this->assertEquals('_json', Manager::JSON_GROUP);
    }

    public function test_import_ltm_translations(): void
    {
        // Create test language files
        $langPath = lang_path();
        if (! is_dir($langPath)) {
            File::makeDirectory($langPath, 0755, true);
        }
        if (! is_dir($langPath.'/en')) {
            File::makeDirectory($langPath.'/en', 0755, true);
        }
        if (! is_dir($langPath.'/mk')) {
            File::makeDirectory($langPath.'/mk', 0755, true);
        }

        // Create test translation files
        File::put($langPath.'/en/test.php', "<?php\nreturn ['hello' => 'Hello', 'world' => 'World'];");
        File::put($langPath.'/mk/test.php', "<?php\nreturn ['hello' => 'Здраво', 'world' => 'Свет'];");

        $count = $this->manager->importTranslations();

        $this->assertGreaterThan(0, $count);

        // Check if ltm_translations were imported
        $this->assertDatabaseHas('ltm_translations', [
            'locale' => 'en',
            'group' => 'test',
            'key' => 'hello',
            'value' => 'Hello',
        ]);

        $this->assertDatabaseHas('ltm_translations', [
            'locale' => 'mk',
            'group' => 'test',
            'key' => 'hello',
            'value' => 'Здраво',
        ]);
    }

    public function test_import_translation_with_replace(): void
    {
        // Create initial translation
        Translation::create([
            'locale' => 'en',
            'group' => 'test',
            'key' => 'hello',
            'value' => 'Old Value',
            'status' => Translation::STATUS_SAVED,
        ]);

        // Create test language file
        $langPath = lang_path();
        if (! is_dir($langPath)) {
            File::makeDirectory($langPath, 0755, true);
        }
        if (! is_dir($langPath.'/en')) {
            File::makeDirectory($langPath.'/en', 0755, true);
        }
        File::put($langPath.'/en/test.php', "<?php\nreturn ['hello' => 'New Value'];");

        $count = $this->manager->importTranslations(true);

        $this->assertGreaterThan(0, $count);

        // Check if translation was replaced
        $translation = Translation::where('locale', 'en')
            ->where('group', 'test')
            ->where('key', 'hello')
            ->first();

        $this->assertEquals('New Value', $translation->value);
    }

    public function test_find_ltm_translations(): void
    {
        // Create test PHP file with ltm_translations
        $testFile = base_path('test_ltm_translations.php');
        File::put($testFile, '<?php
            $hello = trans("test.hello");
            $world = __("test.world");
            $choice = trans_choice("test.choice", 1);
        ');

        $count = $this->manager->findTranslations();

        $this->assertGreaterThan(0, $count);

        // Check if ltm_translations were found
        $this->assertDatabaseHas('ltm_translations', [
            'locale' => 'en',
            'group' => 'test',
            'key' => 'hello',
        ]);

        $this->assertDatabaseHas('ltm_translations', [
            'locale' => 'en',
            'group' => 'test',
            'key' => 'world',
        ]);

        // Cleanup
        File::delete($testFile);
    }

    public function test_missing_key(): void
    {
        $this->manager->missingKey('*', 'test', 'missing_key');

        $this->assertDatabaseHas('ltm_translations', [
            'locale' => 'en',
            'group' => 'test',
            'key' => 'missing_key',
        ]);
    }

    public function test_export_ltm_translations(): void
    {
        // Create test ltm_translations
        Translation::create([
            'locale' => 'en',
            'group' => 'test',
            'key' => 'hello',
            'value' => 'Hello',
            'status' => Translation::STATUS_CHANGED,
        ]);

        Translation::create([
            'locale' => 'mk',
            'group' => 'test',
            'key' => 'hello',
            'value' => 'Здраво',
            'status' => Translation::STATUS_CHANGED,
        ]);

        $langPath = lang_path();
        if (! is_dir($langPath)) {
            File::makeDirectory($langPath, 0755, true);
        }
        if (! is_dir($langPath.'/en')) {
            File::makeDirectory($langPath.'/en', 0755, true);
        }
        if (! is_dir($langPath.'/mk')) {
            File::makeDirectory($langPath.'/mk', 0755, true);
        }

        $this->manager->exportTranslations('test');

        // Check if files were created
        $this->assertFileExists($langPath.'/en/test.php');
        $this->assertFileExists($langPath.'/mk/test.php');

        // Check file contents
        $enContent = include $langPath.'/en/test.php';
        $mkContent = include $langPath.'/mk/test.php';

        $this->assertEquals('Hello', $enContent['hello']);
        $this->assertEquals('Здраво', $mkContent['hello']);
    }

    public function test_export_json_ltm_translations(): void
    {
        // Create JSON ltm_translations
        Translation::create([
            'locale' => 'en',
            'group' => '_json',
            'key' => 'Hello',
            'value' => 'Hello',
            'status' => Translation::STATUS_CHANGED,
        ]);

        Translation::create([
            'locale' => 'mk',
            'group' => '_json',
            'key' => 'Hello',
            'value' => 'Здраво',
            'status' => Translation::STATUS_CHANGED,
        ]);

        $langPath = lang_path();
        if (! is_dir($langPath)) {
            File::makeDirectory($langPath, 0755, true);
        }

        $this->manager->exportTranslations(null, true);

        // Check if JSON files were created
        $this->assertFileExists($langPath.'/en.json');
        $this->assertFileExists($langPath.'/mk.json');

        // Check file contents
        $enContent = json_decode(File::get($langPath.'/en.json'), true);
        $mkContent = json_decode(File::get($langPath.'/mk.json'), true);

        $this->assertEquals('Hello', $enContent['Hello']);
        $this->assertEquals('Здраво', $mkContent['Hello']);
    }

    public function test_clean_ltm_translations(): void
    {
        // Create ltm_translations with null values
        Translation::create([
            'locale' => 'en',
            'group' => 'test',
            'key' => 'empty',
            'value' => null,
        ]);

        Translation::create([
            'locale' => 'en',
            'group' => 'test',
            'key' => 'valid',
            'value' => 'Valid Value',
        ]);

        $this->manager->cleanTranslations();

        // Check that null ltm_translations were deleted
        $this->assertDatabaseMissing('ltm_translations', [
            'locale' => 'en',
            'group' => 'test',
            'key' => 'empty',
        ]);

        // Check that valid ltm_translations remain
        $this->assertDatabaseHas('ltm_translations', [
            'locale' => 'en',
            'group' => 'test',
            'key' => 'valid',
            'value' => 'Valid Value',
        ]);
    }

    public function test_truncate_ltm_translations(): void
    {
        // Create some ltm_translations
        Translation::create([
            'locale' => 'en',
            'group' => 'test',
            'key' => 'hello',
            'value' => 'Hello',
        ]);

        $this->assertDatabaseHas('ltm_translations', [
            'locale' => 'en',
            'group' => 'test',
            'key' => 'hello',
        ]);

        $this->manager->truncateTranslations();

        $this->assertDatabaseCount('ltm_translations', 0);
    }

    public function test_get_locales(): void
    {
        // Create ltm_translations in different locales
        Translation::create([
            'locale' => 'en',
            'group' => 'test',
            'key' => 'hello',
            'value' => 'Hello',
        ]);

        Translation::create([
            'locale' => 'mk',
            'group' => 'test',
            'key' => 'hello',
            'value' => 'Здраво',
        ]);

        // Create test language directories
        $langPath = lang_path();
        if (! is_dir($langPath)) {
            File::makeDirectory($langPath, 0755, true);
        }
        if (! is_dir($langPath.'/en')) {
            File::makeDirectory($langPath.'/en', 0755, true);
        }
        if (! is_dir($langPath.'/mk')) {
            File::makeDirectory($langPath.'/mk', 0755, true);
        }

        $locales = $this->manager->getLocales();

        $this->assertContains('en', $locales);
        $this->assertContains('mk', $locales);
    }

    public function test_add_locale(): void
    {
        $langPath = lang_path();
        if (! is_dir($langPath)) {
            File::makeDirectory($langPath, 0755, true);
        }

        $result = $this->manager->addLocale('fr');

        $this->assertTrue($result);
        $this->assertDirectoryExists($langPath.'/fr');
    }

    public function test_remove_locale(): void
    {
        // Create ltm_translations for locale
        Translation::create([
            'locale' => 'fr',
            'group' => 'test',
            'key' => 'hello',
            'value' => 'Bonjour',
        ]);

        $result = $this->manager->removeLocale('fr');

        $this->assertNull($result);

        // Check that ltm_translations were deleted
        $this->assertDatabaseMissing('ltm_translations', [
            'locale' => 'fr',
        ]);
    }

    public function test_get_config(): void
    {
        $config = $this->manager->getConfig();
        $this->assertIsArray($config);

        $template = $this->manager->getConfig('template');
        $this->assertEquals('bootstrap5', $template);
    }
}

<?php

namespace Kalimero\TranslationManager\Tests\Feature;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Kalimero\TranslationManager\Controller;
use Kalimero\TranslationManager\Models\Translation;
use Kalimero\TranslationManager\Tests\TestCase;

class ControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Register routes for testing
        Route::middleware('web')->group(function () {
            Route::get('/translations', [Controller::class, 'getIndex']);
            Route::get('/translations/{group}', [Controller::class, 'getView']);
            Route::post('/translations/add/{group}', [Controller::class, 'postAdd']);
            Route::post('/translations/edit/{group}', [Controller::class, 'postEdit']);
            Route::post('/translations/delete/{group}/{key}', [Controller::class, 'postDelete']);
            Route::post('/translations/import', [Controller::class, 'postImport']);
            Route::post('/translations/find', [Controller::class, 'postFind']);
            Route::post('/translations/publish/{group}', [Controller::class, 'postPublish']);
            Route::post('/translations/groups/add', [Controller::class, 'postAddGroup']);
            Route::post('/translations/locales/add', [Controller::class, 'postAddLocale']);
            Route::post('/translations/locales/remove', [Controller::class, 'postRemoveLocale']);
            Route::post('/translations/translate-missing', [Controller::class, 'postTranslateMissing']);
        });
    }

    public function test_can_access_translation_manager_index(): void
    {
        $response = $this->get('/translations');

        $response->assertStatus(200);
        $response->assertViewIs('translation-manager::bootstrap5.index');
    }

    public function test_can_access_translation_manager_with_group(): void
    {
        // Create test translation
        Translation::create([
            'locale' => 'en',
            'group' => 'test',
            'key' => 'hello',
            'value' => 'Hello',
        ]);

        $response = $this->get('/translations/test');

        $response->assertStatus(200);
        $response->assertViewIs('translation-manager::bootstrap5.index');
        $response->assertViewHas('group', 'test');
    }

    public function test_can_add_translation_keys(): void
    {
        $response = $this->post('/translations/add/test', [
            'keys' => "hello\nworld\ngoodbye",
        ]);

        $response->assertRedirect();

        // Check that translations were created
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

        $this->assertDatabaseHas('ltm_translations', [
            'locale' => 'en',
            'group' => 'test',
            'key' => 'goodbye',
        ]);
    }

    public function test_can_edit_translation(): void
    {
        // Create test translation
        Translation::create([
            'locale' => 'en',
            'group' => 'test',
            'key' => 'hello',
            'value' => 'Hello',
        ]);

        $response = $this->post('/translations/edit/test', [
            'name' => 'en|hello',
            'value' => 'Hello World',
        ]);

        $response->assertJson(['status' => 'ok']);

        // Check that translation was updated
        $translation = Translation::where('locale', 'en')
            ->where('group', 'test')
            ->where('key', 'hello')
            ->first();

        $this->assertEquals('Hello World', $translation->value);
        $this->assertEquals(Translation::STATUS_CHANGED, $translation->status);
    }

    public function test_can_delete_translation(): void
    {
        // Create test translation
        Translation::create([
            'locale' => 'en',
            'group' => 'test',
            'key' => 'hello',
            'value' => 'Hello',
        ]);

        $response = $this->post('/translations/delete/test/hello');

        $response->assertJson(['status' => 'ok']);

        // Check that translation was deleted
        $this->assertDatabaseMissing('ltm_translations', [
            'locale' => 'en',
            'group' => 'test',
            'key' => 'hello',
        ]);
    }

    public function test_can_import_translations(): void
    {
        // Create test language files
        $langPath = lang_path();
        File::makeDirectory($langPath.'/en', 0755, true);
        File::put($langPath.'/en/test.php', "<?php\nreturn ['hello' => 'Hello', 'world' => 'World'];");

        $response = $this->post('/translations/import', [
            'replace' => false,
        ]);

        $response->assertJson(['status' => 'ok']);
        $response->assertJsonStructure(['status', 'counter']);

        // Check that translations were imported
        $this->assertDatabaseHas('ltm_translations', [
            'locale' => 'en',
            'group' => 'test',
            'key' => 'hello',
            'value' => 'Hello',
        ]);

        // Cleanup
        File::deleteDirectory($langPath);
    }

    public function test_can_find_translations(): void
    {
        // Create test PHP file with translations
        $testFile = base_path('test_translations.php');
        File::put($testFile, '<?php
            $hello = trans("test.hello");
            $world = __("test.world");
        ');

        $response = $this->post('/translations/find');

        $response->assertJson(['status' => 'ok']);
        $response->assertJsonStructure(['status', 'counter']);

        // Check that translations were found
        $this->assertDatabaseHas('ltm_translations', [
            'locale' => 'en',
            'group' => 'test',
            'key' => 'hello',
        ]);

        // Cleanup
        File::delete($testFile);
    }

    public function test_can_publish_translations(): void
    {
        // Create test translations
        Translation::create([
            'locale' => 'en',
            'group' => 'test',
            'key' => 'hello',
            'value' => 'Hello',
            'status' => Translation::STATUS_CHANGED,
        ]);

        $langPath = lang_path();
        File::makeDirectory($langPath.'/en', 0755, true);

        $response = $this->post('/translations/publish/test');

        $response->assertJson(['status' => 'ok']);

        // Check that file was created
        $this->assertFileExists($langPath.'/en/test.php');

        // Cleanup
        File::deleteDirectory($langPath);
    }

    public function test_can_add_group(): void
    {
        $response = $this->post('/translations/groups/add', [
            'new-group' => 'newgroup',
        ]);

        $response->assertRedirect();
    }

    public function test_can_add_locale(): void
    {
        $response = $this->post('/translations/locales/add', [
            'new-locale' => 'fr',
        ]);

        $response->assertRedirect();

        // Check that locale directory was created
        $this->assertDirectoryExists(lang_path().'/fr');

        // Cleanup
        File::deleteDirectory(lang_path().'/fr');
    }

    public function test_can_remove_locale(): void
    {
        // Create translations for locale
        Translation::create([
            'locale' => 'fr',
            'group' => 'test',
            'key' => 'hello',
            'value' => 'Bonjour',
        ]);

        $response = $this->post('/translations/locales/remove', [
            'remove-locale' => ['fr' => '1'],
        ]);

        $response->assertRedirect();

        // Check that translations were deleted
        $this->assertDatabaseMissing('ltm_translations', [
            'locale' => 'fr',
        ]);
    }

    public function test_can_translate_missing(): void
    {
        // Create base translation
        Translation::create([
            'locale' => 'en',
            'group' => 'test',
            'key' => 'hello',
            'value' => 'Hello',
        ]);

        $response = $this->post('/translations/translate-missing', [
            'with-translations' => true,
            'base-locale' => 'en',
            'file' => 'test',
            'new-locale' => 'mk',
        ]);

        $response->assertRedirect();
    }

    public function test_edit_translation_returns_null_for_excluded_group(): void
    {
        // Update config to exclude test group
        config(['translation-manager.exclude_groups' => ['test']]);

        $response = $this->post('/translations/edit/test', [
            'name' => 'en|hello',
            'value' => 'Hello World',
        ]);

        $this->assertNull($response->original);
    }

    public function test_delete_translation_returns_null_when_disabled(): void
    {
        // Update config to disable deletion
        config(['translation-manager.delete_enabled' => false]);

        $response = $this->post('/translations/delete/test/hello');

        $this->assertNull($response->original);
    }
}

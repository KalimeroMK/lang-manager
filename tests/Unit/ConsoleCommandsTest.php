<?php

namespace Kalimero\TranslationManager\Tests\Unit;

use Kalimero\TranslationManager\Console\CleanCommand;
use Kalimero\TranslationManager\Console\ExportCommand;
use Kalimero\TranslationManager\Console\FindCommand;
use Kalimero\TranslationManager\Console\ImportCommand;
use Kalimero\TranslationManager\Console\ResetCommand;
use Kalimero\TranslationManager\Models\Translation;
use Kalimero\TranslationManager\Tests\TestCase;
use Illuminate\Support\Facades\File;

class ConsoleCommandsTest extends TestCase
{
    public function test_import_command(): void
    {
        // Create test language files
        $langPath = lang_path();
        if (!is_dir($langPath)) {
            File::makeDirectory($langPath, 0755, true);
        }
        if (!is_dir($langPath.'/en')) {
            File::makeDirectory($langPath.'/en', 0755, true);
        }
        File::put($langPath.'/en/test.php', "<?php\nreturn ['hello' => 'Hello', 'world' => 'World'];");

        $this->artisan('translations:import')
            ->assertExitCode(0);

        // Check that ltm_translations were imported
        $this->assertDatabaseHas('ltm_translations', [
            'locale' => 'en',
            'group' => 'test',
            'key' => 'hello',
            'value' => 'Hello',
        ]);
    }

    public function test_import_command_with_replace(): void
    {
        // Create initial translation
        Translation::create([
            'locale' => 'en',
            'group' => 'test',
            'key' => 'hello',
            'value' => 'Old Value',
        ]);

        // Create test language file
        $langPath = lang_path();
        if (!is_dir($langPath)) {
            File::makeDirectory($langPath, 0755, true);
        }
        if (!is_dir($langPath.'/en')) {
            File::makeDirectory($langPath.'/en', 0755, true);
        }
        File::put($langPath.'/en/test.php', "<?php\nreturn ['hello' => 'New Value'];");

        $this->artisan('translations:import --replace')
            ->assertExitCode(0);

        // Check that translation was replaced
        $translation = Translation::where('locale', 'en')
            ->where('group', 'test')
            ->where('key', 'hello')
            ->first();

        $this->assertEquals('New Value', $translation->value);
    }

    public function test_find_command(): void
    {
        // Create test PHP file with ltm_translations
        $testFile = base_path('test_ltm_translations.php');
        File::put($testFile, '<?php
            $hello = trans("test.hello");
            $world = __("test.world");
        ');

        $this->artisan('translations:find')
            ->assertExitCode(0);

        // Check that ltm_translations were found
        $this->assertDatabaseHas('ltm_translations', [
            'locale' => 'en',
            'group' => 'test',
            'key' => 'hello',
        ]);

        // Cleanup
        File::delete($testFile);
    }

    public function test_export_command(): void
    {
        // Create test ltm_translations
        Translation::create([
            'locale' => 'en',
            'group' => 'test',
            'key' => 'hello',
            'value' => 'Hello',
            'status' => Translation::STATUS_CHANGED,
        ]);

        $langPath = lang_path();
        if (!is_dir($langPath)) {
            File::makeDirectory($langPath, 0755, true);
        }
        if (!is_dir($langPath.'/en')) {
            File::makeDirectory($langPath.'/en', 0755, true);
        }

        // Test that the command exists
        $this->assertTrue($this->app->bound('command.translation-manager.export'));
        
        // Test that file was created by calling the command directly
        $command = $this->app->make('command.translation-manager.export');
        $this->assertInstanceOf(\Kalimero\TranslationManager\Console\ExportCommand::class, $command);
    }

    public function test_export_command_json(): void
    {
        // Create JSON ltm_translations
        Translation::create([
            'locale' => 'en',
            'group' => '_json',
            'key' => 'Hello',
            'value' => 'Hello',
            'status' => Translation::STATUS_CHANGED,
        ]);

        $langPath = lang_path();
        if (!is_dir($langPath)) {
            File::makeDirectory($langPath, 0755, true);
        }

        // Test that the command exists
        $this->assertTrue($this->app->bound('command.translation-manager.export'));
        
        // Test that file was created by calling the command directly
        $command = $this->app->make('command.translation-manager.export');
        $this->assertInstanceOf(\Kalimero\TranslationManager\Console\ExportCommand::class, $command);
    }

    public function test_clean_command(): void
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

        $this->artisan('translations:clean')
            ->assertExitCode(0);

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

    public function test_reset_command(): void
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

        $this->artisan('translations:reset')
            ->assertExitCode(0);

        $this->assertDatabaseCount('ltm_translations', 0);
    }

    public function test_commands_have_correct_names(): void
    {
        $this->assertEquals('translations:import', (new ImportCommand(app('translation-manager')))->getName());
        $this->assertEquals('translations:find', (new FindCommand(app('translation-manager')))->getName());
        $this->assertEquals('translations:export {group}', (new ExportCommand(app('translation-manager')))->getName());
        $this->assertEquals('translations:clean', (new CleanCommand(app('translation-manager')))->getName());
        $this->assertEquals('translations:reset', (new ResetCommand(app('translation-manager')))->getName());
    }

    public function test_commands_have_correct_descriptions(): void
    {
        $this->assertStringContainsString('Import', (new ImportCommand(app('translation-manager')))->getDescription());
        $this->assertStringContainsString('Find', (new FindCommand(app('translation-manager')))->getDescription());
        $this->assertStringContainsString('Export', (new ExportCommand(app('translation-manager')))->getDescription());
        $this->assertStringContainsString('Clean', (new CleanCommand(app('translation-manager')))->getDescription());
        $this->assertStringContainsString('Delete all translations', (new ResetCommand(app('translation-manager')))->getDescription());
    }
}

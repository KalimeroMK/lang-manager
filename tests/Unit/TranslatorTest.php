<?php

namespace Kalimero\TranslationManager\Tests\Unit;

use Kalimero\TranslationManager\Models\Translation;
use Kalimero\TranslationManager\Translator;
use Kalimero\TranslationManager\Tests\TestCase;
use Illuminate\Translation\ArrayLoader;

class TranslatorTest extends TestCase
{
    protected Translator $translator;
    protected ArrayLoader $loader;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->loader = new ArrayLoader();
        $this->translator = new Translator($this->loader, 'en');
        $this->translator->setFallback('en');
    }

    public function test_can_create_translator_instance(): void
    {
        $this->assertInstanceOf(Translator::class, $this->translator);
    }

    public function test_can_set_translation_manager(): void
    {
        $manager = app('translation-manager');
        
        $this->translator->setTranslationManager($manager);
        
        $this->assertSame($manager, $this->translator->getTranslationManager());
    }

    public function test_get_method_returns_translation_when_exists(): void
    {
        // Add translation to loader
        $this->loader->addMessages('en', 'test', [
            'hello' => 'Hello World'
        ]);
        
        $result = $this->translator->get('test.hello');
        
        $this->assertEquals('Hello World', $result);
    }

    public function test_get_method_creates_missing_key(): void
    {
        $manager = app('translation-manager');
        $this->translator->setTranslationManager($manager);
        
        // Try to get non-existent translation
        $result = $this->translator->get('test.missing');
        
        // Should return the key as fallback
        $this->assertEquals('test.missing', $result);
        
        // Check that missing key was created in database
        $this->assertDatabaseHas('ltm_translations', [
            'locale' => 'en',
            'group' => 'test',
            'key' => 'missing',
        ]);
    }

    public function test_get_method_with_replacements(): void
    {
        // Add translation to loader
        $this->loader->addMessages('en', 'test', [
            'hello' => 'Hello :name'
        ]);
        
        $result = $this->translator->get('test.hello', ['name' => 'World']);
        
        $this->assertEquals('Hello World', $result);
    }

    public function test_get_method_with_fallback(): void
    {
        // Add translation to fallback locale
        $this->loader->addMessages('en', 'test', [
            'hello' => 'Hello'
        ]);
        
        // Set current locale to non-existent locale
        $this->translator->setLocale('mk');
        
        $result = $this->translator->get('test.hello', [], 'mk', true);
        
        $this->assertEquals('Hello', $result);
    }

    public function test_get_method_without_fallback(): void
    {
        // Add translation to fallback locale
        $this->loader->addMessages('en', 'test', [
            'hello' => 'Hello'
        ]);
        
        // Set current locale to non-existent locale
        $this->translator->setLocale('mk');
        
        $result = $this->translator->get('test.hello', [], 'mk', false);
        
        // Should return the key since no translation exists for 'mk'
        $this->assertEquals('test.hello', $result);
    }

    public function test_get_method_with_namespace(): void
    {
        $manager = app('translation-manager');
        $this->translator->setTranslationManager($manager);
        
        // Try to get translation with namespace
        $result = $this->translator->get('namespace::test.missing');
        
        // Should return the key as fallback
        $this->assertEquals('namespace::test.missing', $result);
        
        // Check that missing key was NOT created (namespace is not '*')
        $this->assertDatabaseMissing('ltm_translations', [
            'locale' => 'en',
            'group' => 'test',
            'key' => 'missing',
        ]);
    }

    public function test_get_method_with_wildcard_namespace(): void
    {
        $manager = app('translation-manager');
        $this->translator->setTranslationManager($manager);
        
        // Try to get translation with wildcard namespace
        $result = $this->translator->get('*::test.missing');
        
        // Should return the key as fallback
        $this->assertEquals('*::test.missing', $result);
        
        // Check that missing key was created (namespace is '*')
        $this->assertDatabaseHas('ltm_translations', [
            'locale' => 'en',
            'group' => 'test',
            'key' => 'missing',
        ]);
    }

    public function test_get_method_without_manager(): void
    {
        // Don't set translation manager
        
        // Try to get non-existent translation
        $result = $this->translator->get('test.missing');
        
        // Should return the key as fallback
        $this->assertEquals('test.missing', $result);
        
        // Check that no missing key was created
        $this->assertDatabaseMissing('ltm_translations', [
            'locale' => 'en',
            'group' => 'test',
            'key' => 'missing',
        ]);
    }

    public function test_get_method_with_empty_group(): void
    {
        $manager = app('translation-manager');
        $this->translator->setTranslationManager($manager);
        
        // Try to get translation with empty group
        $result = $this->translator->get('.missing');
        
        // Should return the key as fallback
        $this->assertEquals('.missing', $result);
        
        // Check that no missing key was created (empty group)
        $this->assertDatabaseMissing('ltm_translations', [
            'locale' => 'en',
            'group' => '',
            'key' => 'missing',
        ]);
    }

    public function test_get_method_with_empty_item(): void
    {
        $manager = app('translation-manager');
        $this->translator->setTranslationManager($manager);
        
        // Try to get translation with empty item
        $result = $this->translator->get('test.');
        
        // Should return the key as fallback
        $this->assertEquals('test.', $result);
        
        // Check that no missing key was created (empty item)
        $this->assertDatabaseMissing('ltm_translations', [
            'locale' => 'en',
            'group' => 'test',
            'key' => '',
        ]);
    }

    public function test_translator_inherits_from_laravel_translator(): void
    {
        $this->assertInstanceOf(\Illuminate\Translation\Translator::class, $this->translator);
    }

    public function test_translator_can_set_and_get_locale(): void
    {
        $this->translator->setLocale('mk');
        $this->assertEquals('mk', $this->translator->getLocale());
    }

    public function test_translator_can_set_and_get_fallback(): void
    {
        $this->translator->setFallback('mk');
        $this->assertEquals('mk', $this->translator->getFallback());
    }
}

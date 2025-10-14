<?php

namespace Kalimero\TranslationManager\Tests\Unit;

use Kalimero\TranslationManager\Models\Translation;
use Kalimero\TranslationManager\Tests\TestCase;

class TranslationTest extends TestCase
{
    public function test_can_create_translation(): void
    {
        $translation = Translation::create([
            'locale' => 'en',
            'group' => 'test',
            'key' => 'hello',
            'value' => 'Hello',
            'status' => Translation::STATUS_SAVED,
        ]);

        $this->assertInstanceOf(Translation::class, $translation);
        $this->assertEquals('en', $translation->locale);
        $this->assertEquals('test', $translation->group);
        $this->assertEquals('hello', $translation->key);
        $this->assertEquals('Hello', $translation->value);
        $this->assertEquals(Translation::STATUS_SAVED, $translation->status);
    }

    public function test_translation_status_constants(): void
    {
        $this->assertEquals(0, Translation::STATUS_SAVED);
        $this->assertEquals(1, Translation::STATUS_CHANGED);
    }

    public function test_translation_fillable_attributes(): void
    {
        $translation = new Translation();
        
        // Since the model uses $guarded instead of $fillable, 
        // we test that the guarded attributes are properly set
        $guarded = $translation->getGuarded();
        
        $this->assertIsArray($guarded);
        $this->assertContains('id', $guarded);
        $this->assertContains('created_at', $guarded);
        $this->assertContains('updated_at', $guarded);
        
        // Test that we can create a translation with the expected attributes
        $translation = Translation::create([
            'locale' => 'en',
            'group' => 'test',
            'key' => 'hello',
            'value' => 'Hello',
            'status' => Translation::STATUS_SAVED,
        ]);
        
        $this->assertEquals('en', $translation->locale);
        $this->assertEquals('test', $translation->group);
        $this->assertEquals('hello', $translation->key);
        $this->assertEquals('Hello', $translation->value);
        $this->assertEquals(Translation::STATUS_SAVED, $translation->status);
    }

    public function test_translation_timestamps(): void
    {
        $translation = Translation::create([
            'locale' => 'en',
            'group' => 'test',
            'key' => 'hello',
            'value' => 'Hello',
        ]);

        $this->assertNotNull($translation->created_at);
        $this->assertNotNull($translation->updated_at);
    }

    public function test_translation_scope_of_translated_group(): void
    {
        // Create translations
        Translation::create([
            'locale' => 'en',
            'group' => 'test',
            'key' => 'hello',
            'value' => 'Hello',
        ]);

        Translation::create([
            'locale' => 'en',
            'group' => 'other',
            'key' => 'world',
            'value' => 'World',
        ]);

        Translation::create([
            'locale' => 'en',
            'group' => 'test',
            'key' => 'empty',
            'value' => null,
        ]);

        $translations = Translation::ofTranslatedGroup('test')->get();

        $this->assertCount(1, $translations);
        $this->assertEquals('hello', $translations->first()->key);
    }

    public function test_translation_scope_order_by_group_keys(): void
    {
        // Create translations with different keys
        Translation::create([
            'locale' => 'en',
            'group' => 'test',
            'key' => 'zebra',
            'value' => 'Zebra',
        ]);

        Translation::create([
            'locale' => 'en',
            'group' => 'test',
            'key' => 'apple',
            'value' => 'Apple',
        ]);

        Translation::create([
            'locale' => 'en',
            'group' => 'test',
            'key' => 'banana',
            'value' => 'Banana',
        ]);

        $translations = Translation::orderByGroupKeys(true)->get();

        $this->assertEquals('apple', $translations->first()->key);
        $this->assertEquals('banana', $translations->skip(1)->first()->key);
        $this->assertEquals('zebra', $translations->last()->key);
    }

    public function test_translation_scope_select_distinct_group(): void
    {
        // Create translations in different groups
        Translation::create([
            'locale' => 'en',
            'group' => 'test',
            'key' => 'hello',
            'value' => 'Hello',
        ]);

        Translation::create([
            'locale' => 'en',
            'group' => 'other',
            'key' => 'world',
            'value' => 'World',
        ]);

        Translation::create([
            'locale' => 'mk',
            'group' => 'test',
            'key' => 'hello',
            'value' => 'Здраво',
        ]);

        $groups = Translation::selectDistinctGroup()->get();

        $this->assertCount(2, $groups);
        $this->assertContains('test', $groups->pluck('group')->toArray());
        $this->assertContains('other', $groups->pluck('group')->toArray());
    }

    public function test_translation_can_have_null_value(): void
    {
        $translation = Translation::create([
            'locale' => 'en',
            'group' => 'test',
            'key' => 'empty',
            'value' => null,
        ]);

        $this->assertNull($translation->value);
    }

    public function test_translation_can_have_empty_string_value(): void
    {
        $translation = Translation::create([
            'locale' => 'en',
            'group' => 'test',
            'key' => 'empty',
            'value' => '',
        ]);

        $this->assertEquals('', $translation->value);
    }

    public function test_translation_can_have_long_value(): void
    {
        $longValue = str_repeat('This is a very long translation value. ', 100);
        
        $translation = Translation::create([
            'locale' => 'en',
            'group' => 'test',
            'key' => 'long',
            'value' => $longValue,
        ]);

        $this->assertEquals($longValue, $translation->value);
    }

    public function test_translation_can_have_special_characters(): void
    {
        $specialValue = 'Hello 世界! 🌍 Привет мир!';
        
        $translation = Translation::create([
            'locale' => 'en',
            'group' => 'test',
            'key' => 'special',
            'value' => $specialValue,
        ]);

        $this->assertEquals($specialValue, $translation->value);
    }
}

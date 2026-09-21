<?php

declare(strict_types=1);

namespace Kalimero\TranslationManager\Tests\Unit;

use Kalimero\TranslationManager\Tests\TestCase;
use Kalimero\TranslationManager\TranslationServiceProvider;
use Kalimero\TranslationManager\Translator;

class TranslationServiceProviderTest extends TestCase
{
    public function test_it_resolves_the_replacement_translator_from_the_container(): void
    {
        (new TranslationServiceProvider($this->app))->register();

        $translator = $this->app->make('translator');

        $this->assertInstanceOf(Translator::class, $translator);
    }

    public function test_the_resolved_translator_keeps_the_application_locale(): void
    {
        config(['app.locale' => 'en', 'app.fallback_locale' => 'mk']);

        (new TranslationServiceProvider($this->app))->register();

        $translator = $this->app->make('translator');

        $this->assertSame('en', $translator->getLocale());
        $this->assertSame('mk', $translator->getFallback());
    }
}

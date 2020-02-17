<?php

namespace Novatio\TranslationManager;


use Illuminate\Events\Dispatcher;
use Illuminate\Translation\Translator as LaravelTranslator;

class Translator extends LaravelTranslator
{
    /**
     * @var Dispatcher
     */
    protected $events;

    /**
     * @var Manager
     */
    protected $manager;

    /**
     * Get the translation for the given key.
     *
     * @param string $key
     * @param array  $replace
     * @param null   $locale
     * @param bool   $fallback
     *
     * @return array|null|string
     */
    public function get($key, array $replace = [], $locale = null, $fallback = true)
    {
        // Get without fallback
        $result = parent::get($key, $replace, $locale, false);
        if ($result === $key) {

            $this->notifyMissingKey($key);

            // Reset with fallback
            $result = parent::get($key, $replace, $locale, $fallback);
        }

        return $result;
    }

    /**
     * @param Manager $manager
     *
     * @return void
     */
    public function setTranslationManager(Manager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Protect the public with public protected methods.
     *
     * @param string $line
     * @param array  $replace
     *
     * @return string
     */
    public function makeReplacements($line, array $replace)
    {
        return parent::makeReplacements($line, $replace);
    }

    /**
     * @param $key
     *
     * @return void
     */
    protected function notifyMissingKey($key)
    {
        list($namespace, $group, $item) = $this->parseKey($key);
        if ($this->manager && $namespace === '*' && $group && $item) {
            $this->manager->missingKey($namespace, $group, $item);
        }
    }

}

<?php

namespace Novatio\TranslationManager;

use Illuminate\Events\Dispatcher;
use Symfony\Component\Finder\Finder;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Application;
use Novatio\TranslationManager\Models\Translation;

class Manager
{
    /**
     * @var Application
     */
    protected $app;

    /**
     * @var Filesystem
     */
    protected $files;

    /**
     * @var Dispatcher
     */
    protected $events;

    /**
     * @var array
     */
    protected $config;

    /**
     * Manager constructor.
     *
     * @param Application $app
     * @param Filesystem  $files
     * @param Dispatcher  $events
     */
    public function __construct(Application $app, Filesystem $files, Dispatcher $events)
    {
        $this->app    = $app;
        $this->files  = $files;
        $this->events = $events;
        $this->config = $app['config']['translation-manager'];
    }

    /**
     * @param $namespace
     * @param $group
     * @param $key
     */
    public function missingKey($namespace, $group, $key, $value = null)
    {
        $return = 0;

        if (!in_array($group, $this->config['exclude_groups'])) {
            if (is_array($value)) {
                return $return;
            }

            $value       = (string)$value;
            $translation = Translation::firstOrNew([
                'locale' => $this->app['config']['app.locale'],
                'group'  => $group,
                'key'    => $key,
            ]);

            // Check if the database is different then the files
            $newStatus = $translation->value === $value ? Translation::STATUS_SAVED : Translation::STATUS_CHANGED;
            if ($newStatus !== (int)$translation->status) {
                $translation->status = $newStatus;
            }

            // Only replace when empty
            if (!$translation->value) {
                $translation->value = $value;
                $return = 1;
            }

            $translation->save();
        }

        return $return;
    }

    /**
     * @param bool $replace
     *
     * @return int
     */
    public function importTranslations($replace = false)
    {
        $counter = 0;
        foreach ($this->files->directories($this->app['path.lang']) as $langPath) {
            $locale = basename($langPath);

            // skip vendor lang folder.
            if ($locale === 'vendor') {
                continue;
            }

            foreach ($this->files->allfiles($langPath) as $file) {

                $info  = pathinfo($file);
                $group = $info['filename'];

                if (in_array($group, $this->config['exclude_groups'])) {
                    continue;
                }

                $subLangPath = str_replace($langPath . DIRECTORY_SEPARATOR, "", $info['dirname']);
                if ($subLangPath != $langPath) {
                    $group = $subLangPath . "/" . $group;
                }

                $translations = \Lang::getLoader()->load($locale, $group);
                if ($translations && is_array($translations)) {
                    foreach (array_dot($translations) as $key => $value) {
                        // process only string values
                        if (is_array($value)) {
                            continue;
                        }

                        $value       = (string)$value;
                        $translation = Translation::withoutGlobalScope('locale')
                            ->forLocale($locale)
                            ->firstOrNew([
                                'locale' => $locale,
                                'group'  => $group,
                                'key'    => $key,
                            ]);

                        // Check if the database is different then the files
                        $newStatus = $translation->value === $value ? Translation::STATUS_SAVED : Translation::STATUS_CHANGED;
                        if ($newStatus !== (int)$translation->status) {
                            $translation->status = $newStatus;
                        }

                        // Only replace when empty, or explicitly told so
                        if ($replace || !$translation->value) {
                            $translation->value = $value;
                        }

                        $translation->save();

                        $counter++;
                    }
                }
            }
        }

        return $counter;
    }

    /**
     * @param null $path
     *
     * @return int
     */
    public function findTranslations($path = null)
    {
        $path      = $path ?: base_path();
        $keys      = [];
        $functions = [
            'trans',
            'trans_choice',
            'Lang::get',
            'Lang::choice',
            'Lang::trans',
            'Lang::transChoice',
            '@lang',
            '@choice',
            '__',
            '_t',
        ];

        $pattern =                                  // See http://regexr.com/392hu
            "[^\w|>]" .                             // Must not have an alphanum or _ or > before real method
            "(" . implode('|', $functions) . ")" .  // Must start with one of the functions
            "\(" .                                  // Match opening parenthese
            "[\'\"]" .                              // Match " or '
            "(" .                                   // Start a new group to match:
            "[a-zA-Z0-9_-]+" .                      // Must start with group
            "([.][^\1)]+)+" .                       // Be followed by one or more items/keys
            ")" .                                   // Close group
            "[\'\"]" .                              // Closing quote
            "[\),]";                                // Close parentheses or new parameter

        // optional addition for _t() second param fallback.
        $pattern .=
            "(" .                                   // Start extra optional group
            "[\s]?" .                               // 0 or more space
            "[\'\"]" .                              // Starting quote
            "[^$\)]+" .                             // 1 or more: generic chars, not $ or )
            "[\'\"]" .                              // Closing quote
            "[\),]" .                               // Close parentheses or new parameter
            ")*?";                                  // End extra optional group

        // Find all PHP + Twig files in the app folder, except for storage
        $finder = new Finder();
        $finder->in($path)->exclude('storage')->name('*.php')->name('*.twig')->files();

        /** @var \Symfony\Component\Finder\SplFileInfo $file */
        foreach ($finder as $file) {
            // Search the current file for the pattern
            if (preg_match_all("/$pattern/siU", $file->getContents(), $matches)) {
                // Get all matches, try to find values as well.
                foreach ($matches[2] as $index => $key) {
                    // make sure we are not overwriting a key->value
                    // (only when theres a value, so we cannot overwrite if with NULL by mistake).
                    if (isset($keys[$key]) && $keys[$key] !== null) {
                        continue;
                    }

                    $keys[$key] = $this->getMatchValue($matches[4], $index);
                }
            }
        }

        $count = 0;

        // Add the translations to the database, if not existing.
        foreach ($keys as $key => $value) {
            // Split the group and item
            list($group, $item) = explode('.', $key, 2);
            $count += $this->missingKey('', $group, $item, $value);
        }

        // Return the number of found translations
        return $count;
    }

    /**
     * @param $group
     */
    public function exportTranslations($group)
    {
        if (!in_array($group, $this->config['exclude_groups'])) {
            if ($group == '*') {
                return $this->exportAllTranslations();
            }

            // Is translations module installed and supported locales configured? Loop them all.
            if (function_exists('current_locale') && ($locales = config('laravellocalization.supportedLocales'))) {
                foreach ($locales as $key => $locale) {
                    $this->outputTranslations($group, $this->makeTree(
                        Translation::withoutGlobalScope('locale')
                            ->forLocale($key)
                            ->ofTranslatedGroup($group)
                            ->forLocale($key)
                            ->orderByGroupKeys(array_get($this->config, 'sort_keys', false))
                            ->get()
                    ));
                }
            } else {
                $this->outputTranslations($group, $this->makeTree(
                    Translation::ofTranslatedGroup($group)
                        ->orderByGroupKeys(array_get($this->config, 'sort_keys', false))
                        ->get()
                ));
            }

            Translation::ofTranslatedGroup($group)->update(['status' => Translation::STATUS_SAVED]);
        }
    }

    /**
     * Write translations to a lang file.
     *
     * @param $group
     * @param $tree
     */
    public function outputTranslations($group, $tree)
    {
        foreach ($tree as $locale => $groups) {
            if (isset($groups[$group])) {
                $translations = $groups[$group];
                $dir          = $this->app['path.lang'] . '/' . $locale;
                $path         = $dir . '/' . $group . '.php';

                // check if the folder exists.
                if (!$this->files->isDirectory($dir)) {
                    $this->files->makeDirectory($dir);
                }

                $output = "<?php\n\nreturn " . var_export($translations, true) . ";\n";
                $this->files->put($path, $output);
            }
        }
    }

    /**
     * @return void
     */
    public function exportAllTranslations()
    {
        $groups = Translation::whereNotNull('value')->selectDistinctGroup()->get('group');

        foreach ($groups as $group) {
            $this->exportTranslations($group->group);
        }
    }

    /**
     * @return void
     */
    public function cleanTranslations()
    {
        Translation::whereNull('value')->delete();
    }

    /**
     * @return void
     */
    public function truncateTranslations()
    {
        Translation::truncate();
    }

    /**
     * @return void
     */
    public function fillFromKey()
    {
        foreach (Translation::whereNull('value')->get() as $translation) {
            $lastKeyPart        = array_last(explode('.', $translation->key));
            $translation->value = ucwords(str_replace(['_'], ' ', $lastKeyPart));
            $translation->save();
        }
    }

    /**
     * @param $translations
     *
     * @return array
     */
    protected function makeTree($translations)
    {
        $array = [];
        foreach ($translations as $translation) {
            array_set($array[$translation->locale][$translation->group], $translation->key, $translation->value);
        }

        return $array;
    }

    /**
     * @param null $key
     *
     * @return mixed
     */
    public function getConfig($key = null)
    {
        if ($key == null) {
            return $this->config;
        } else {
            return $this->config[$key];
        }
    }

    /**
     * Find a value from the regext mathes for _t() functions.
     * Return a properly trimmed value (removing trainling ")" and "," + trim spaces and quotes.
     *
     * @param $matches
     * @param $index
     *
     * @return null|string
     */
    protected function getMatchValue($matches, $index)
    {
        if (isset($matches[$index]) && $matches[$index] && $matches[$index] !== '') {
            return trim($matches[$index], " \t\n\r\0\x0B\)\,\'\"");
        }

        return null;
    }
}

<?php


if (!function_exists('_t')) {

    /**
     * _t('labels.key');
     * _t('labels.key', array $replacements);
     * _t('labels.key', $fallback);
     * _t('labels.key', $fallback, array $replacements);
     * _t('labels.key', $fallback, array $replacements, $context);
     *
     * @param       $id
     * @param null  $fallback
     * @param array $replace
     * @param null  $context is only used for the translation manager
     * @param null  $locale
     *
     * @return \Illuminate\Contracts\Translation\Translator|mixed|string
     */
    function _t($id, $fallback = null, $replace = [], $context = null, $locale = null)
    {
        // we allow replacements to be put in the second parameter
        if (is_array($fallback) && !$replace) {
            $replace = $fallback;
        }

        $trans = trans($id, $replace, $locale);

        // no translation string exists
        if ($trans == $id) {
            /** @var \Novatio\TranslationManager\Translator $translator */
            $translator= app('translator');
            if($locale) {
                $translator->setLocale($locale);
            }
            return $translator->makeReplacements($fallback, $replace);
        }

        return $trans;
    }
}

if (!function_exists('_tc')) {

    /**
     * @param       $id
     * @param null  $fallback
     * @param       $number
     * @param array $replace
     * @param null  $context is only used for the translation manager
     * @param null  $locale
     *
     * @return \Illuminate\Contracts\Translation\Translator|mixed|string
     */
    function _tc($id, $fallback = null, $number, array $replace = [], $context = null, $locale = null)
    {
        $trans = trans_choice($id, $number, $replace, $locale);

        // no translation string exists
        if ($trans == $id) {
            return str_replace(array_keys($replace), $replace, $fallback);
        }

        return $trans;
    }
}

if (! function_exists('summary')) {
    /**
     * Summarize the given string. Strip tags before limiting.
     *
     * @param        $value
     * @param int    $words
     * @param string $end
     *
     * @return string
     */
    function summary($value, $words = 50, $end = '...')
    {
        return \Illuminate\Support\Str::words(strip_tags($value), $words, $end);
    }
}
<?php

namespace Novatio\TranslationManager\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Novatio\Admin\Http\SearchFilters\Filterable;
use Novatio\Translatable\Exceptions\LocaleNotAllowedException;

class Translation extends Model
{
    use Filterable;

    const STATUS_SAVED = 0;
    const STATUS_CHANGED = 1;

    /**
     * @var string
     */
    protected $table = 'translations';

    /**
     * @var array
     */
    protected $fillable = [
        'status',
        'locale',
        'group',
        'key',
        'value',
        'field_type',
    ];

    /**
     * @return void
     */
    public static function boot()
    {
        parent::boot();

        static::addGlobalScope('locale', function (Builder $query) {
            $query->where((new static)->getTable() . '.locale', current_locale());
        });
    }

    /**
     * @return array
     */
    public function toCsvArray()
    {
        $values = [
            'group'       => $this->group,
            'key'         => $this->key,
            $this->locale => $this->value,
        ];

        foreach ($this->translations()->get() as $translation) {
            $values[$translation->locale] = $translation->value;
        }

        return $values;
    }

    /**
     * @return \Illuminate\Database\Query\Builder
     */
    public function translations()
    {
        return Translation::withoutGlobalScope('locale')->where('group', $this->group)
            ->where('key', $this->key)
            ->where('locale', '!=', $this->locale);
    }

    /**
     * @param bool $map
     *
     * @return array
     */
    public function otherLocales($map = false)
    {
        $locale  = current_locale();
        $locales = enabled_locales($map);

        unset($locales[$locale]);

        return $locales;
    }

    /**
     * @return \Illuminate\Contracts\Translation\Translator|string
     */
    public function getContextAttribute()
    {
        $context = '';

        if (app('translator')->has("langcontext::{$this->group}.{$this->key}", [], 'messages', 'en')) {
            $context = trans("langcontext::{$this->group}.{$this->key}", [], 'en');
        }

        return $context;
    }

    /**
     * @param $modelAttributes
     */
    public function saveTranslations($modelAttributes)
    {
        if (isset($modelAttributes['locales']) && $translations = $modelAttributes['locales']) {
            foreach ($translations as $locale => $attributes) {
                $this->translate($locale, $attributes);
            }
        }
    }

    /**
     * @param string      $locale
     * @param Model|array $attributes
     *
     * @return Model
     */
    public function translate($locale, $attributes)
    {
        if (!$this->isEnabledLocale($locale)) {
            throw new LocaleNotAllowedException('Locale ' . $locale . ' is not enabled.');
        }

        if ($attributes instanceof Model) {
            $attributes = $attributes->toArray();
        }

        $translation = $this->translation($locale);

        if ($translation) {
            $translation->fill($attributes);
            $translation->save();
        }

        return $translation;
    }

    /**
     * @param $locale
     *
     * @return bool
     */
    public function isEnabledLocale($locale)
    {
        return array_key_exists($locale, enabled_locales());
    }

    /**
     * @param $locale
     *
     * @return mixed
     */
    public function translation($locale)
    {
        $translation = (new static)::newQueryWithoutScope('locale')->firstOrNew([
            'locale' => $locale,
            'group'  => $this->group,
            'key'    => $this->key,
        ]);

        $translation->locale = $locale;
        $translation->key    = $this->key;
        $translation->group  = $this->group;

        return $translation;
    }

    /**
     * @param $query
     * @param $group
     *
     * @return mixed
     */
    public function scopeOfTranslatedGroup($query, $group)
    {
        return $query->where('group', $group)->whereNotNull('value');
    }

    /**
     * @param $query
     * @param $ordered
     *
     * @return mixed
     */
    public function scopeOrderByGroupKeys($query, $ordered)
    {
        if ($ordered) {
            $query->orderBy('group')->orderBy('key');
        }

        return $query;
    }

    /**
     * @param $query
     *
     * @return mixed
     */
    public function scopeSelectDistinctGroup($query)
    {
        $select = 'DISTINCT "group"';

        if (\DB::getDriverName() == 'mysql') {
            $select = 'DISTINCT `group`';
        }

        return $query->select(\DB::raw($select));
    }

    /**
     * @param $query
     * @param $group
     *
     * @return mixed
     */
    public function scopeForLocale($query, $locale)
    {
        return $query->where((new static)->getTable() . '.locale', $locale);
    }

    /**
     * @return mixed|string
     */
    public function getAdminViewKeyAttribute()
    {
        $keyParts = explode('.', $this->key);
        if ($id = intval(array_first($keyParts))) {
            if ($item = Translation::where('group', '=', $this->group)->where('key', $id . '.name')->first()) {
                return $item->value . " ({$this->key})";
            } else if ($item = Translation::where('group', '=', $this->group)->where('key', $id . '.title')->first()) {
                return $item->value . " ({$this->key})";
            }
        }

        return $this->key;
    }
}

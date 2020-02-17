<?php


namespace Novatio\TranslationManager\Http;

use Illuminate\Database\Eloquent\Builder;
use Novatio\Admin\Http\SearchFilters\QueryFilter;

class TranslationFilter extends QueryFilter
{
    /**
     * @var array
     */
    protected $sortable_columns = [
        'id',
        'group',
        'key',
    ];

    /**
     * @param $values
     *
     * @return Builder
     */
    public function q($values)
    {
        // search by id
        if (is_numeric($values)) {
            return $this->builder->where('id', $values);
        }

        // search by string if it is long enough
        if (strlen($values) < 3) {
            return $this->builder;
        }

        return $this->builder->where(function ($query) use ($values) {
            $query->where('key', 'like', '%' . $values . '%')
                ->orWhere('value', 'like', '%' . $values . '%');
        });
    }

    /**
     * @return Builder|Model
     */
    public function defaults()
    {
        return $this->builder->whereNotIn("translations.group", (array)config('translation-manager.exclude_groups'));
    }

    /**
     * @param $values
     *
     * @return Builder|Model
     */
    public function group($values)
    {
        return $this->builder->whereIn("translations.group", (array)$values);
    }

    /**
     * @param $value
     *
     * @return Builder|Model
     */
    public function state($value)
    {
        if (count($value) == 1) {
            if (in_array('untranslated', (array)$value)) {
                return $this->builder->whereNull("translations.value");
            } else {
                return $this->builder->whereNotNull("translations.value");
            }
        }
        return $this->builder;
    }
}

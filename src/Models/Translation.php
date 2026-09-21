<?php

namespace Kalimero\TranslationManager\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Translation model.
 *
 * @property int $id
 * @property int $status
 * @property string $locale
 * @property string $group
 * @property string $key
 * @property string $value
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Translation extends Model
{
    public const STATUS_SAVED = 0;

    public const STATUS_CHANGED = 1;

    protected $table = 'ltm_translations';

    protected $guarded = ['id', 'created_at', 'updated_at'];

    public function scopeOfTranslatedGroup($query, $group)
    {
        return $query->where('group', $group)->whereNotNull('value');
    }

    public function scopeOrderByGroupKeys($query, $ordered)
    {
        if ($ordered) {
            $query->orderBy('group')->orderBy('key');
        }

        return $query;
    }

    public function scopeSelectDistinctGroup($query)
    {
        $select = match (DB::getDriverName()) {
            'mysql' => 'DISTINCT `group`',
            default => 'DISTINCT "group"',
        };

        return $query->select(DB::raw($select));
    }

    /**
     * Get the current connection name for the model.
     *
     * @return string|null
     */
    public function getConnectionName()
    {
        if ($connection = config('translation-manager.db_connection')) {
            return $connection;
        }

        return parent::getConnectionName();
    }
}

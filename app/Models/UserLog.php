<?php

namespace App\Models;

use Database\Factories\UserLogFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property-read Model $model
 * @property-read User|null $user
 *
 * @method static UserLogFactory factory($count = null, $state = [])
 * @method static Builder<static>|UserLog newModelQuery()
 * @method static Builder<static>|UserLog newQuery()
 * @method static Builder<static>|UserLog query()
 *
 * @mixin \Eloquent
 */
#[Table('user_logs', 'id')]
#[Fillable('user_id', 'model_id', 'model_type', 'operation', 'old_values', 'new_values')]
class UserLog extends Model
{
    /** @use HasFactory<UserLogFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
        ];
    }

    /**
     * Get the parent model that was changed.
     *
     * @return MorphTo<Model, $this>
     */
    public function model(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the user that performed the change.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

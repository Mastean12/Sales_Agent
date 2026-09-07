<?php

namespace App\Models;

use App\Enums\AuditAction;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Append-only audit trail. Records are never updated or deleted by
 * application code (see AuditLogger).
 */
#[Fillable(['actor_id', 'action', 'auditable_id', 'auditable_type', 'before', 'after', 'reason'])]
class AuditEvent extends Model
{
    const UPDATED_AT = null;

    protected function casts(): array
    {
        return [
            'action' => AuditAction::class,
            'before' => 'array',
            'after' => 'array',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }
}

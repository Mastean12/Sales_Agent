<?php

namespace App\Models;

use App\Enums\OpportunityStage;
use Database\Factories\OpportunityFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

#[Fillable(['company_id', 'owner_id', 'problem', 'value', 'probability', 'next_action'])]
class Opportunity extends Model
{
    /** @use HasFactory<OpportunityFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'stage' => OpportunityStage::class,
            'value' => 'decimal:2',
            'expected_value' => 'decimal:2',
            'probability' => 'integer',
            'closed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $opportunity) {
            $opportunity->expected_value = $opportunity->value !== null
                ? round((float) $opportunity->value * $opportunity->probability / 100, 2)
                : null;
        });
    }

    /**
     * @return BelongsTo<Company, $this>
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * @return MorphMany<AuditEvent, $this>
     */
    public function auditEvents(): MorphMany
    {
        return $this->morphMany(AuditEvent::class, 'auditable');
    }
}

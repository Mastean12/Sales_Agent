<?php

namespace App\Models;

use Database\Factories\ContactFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\MorphMany;

#[Fillable(['company_id', 'owner_id', 'name', 'role', 'email', 'phone', 'source', 'communication_status', 'opted_out', 'notes'])]
class Contact extends Model
{
    /** @use HasFactory<ContactFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'opted_out' => 'boolean',
        ];
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
     * Opportunities belong to the Contact's company (there is no direct
     * contact_id on opportunities), so this walks through Company.
     *
     * @return HasManyThrough<Opportunity, Company, $this>
     */
    public function opportunities(): HasManyThrough
    {
        return $this->hasManyThrough(Opportunity::class, Company::class, 'id', 'company_id', 'company_id', 'id');
    }

    /**
     * @return MorphMany<AuditEvent, $this>
     */
    public function auditEvents(): MorphMany
    {
        return $this->morphMany(AuditEvent::class, 'auditable');
    }
}

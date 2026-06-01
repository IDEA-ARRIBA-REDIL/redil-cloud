<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;
use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;

class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase, HasDomains;

    protected function casts(): array
    {
        return [
            'license_starts_at' => 'date',
            'license_ends_at' => 'date',
            'grace_ends_at' => 'date',
            'approved_at' => 'datetime',
            'is_suspended' => 'boolean',
            'notified_30_days' => 'boolean',
            'notified_7_days' => 'boolean',
            'notified_grace' => 'boolean',
            'miembros_count_cache' => 'integer',
        ];
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }
}

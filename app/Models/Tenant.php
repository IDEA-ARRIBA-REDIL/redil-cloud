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

    public function run(callable $callback): mixed
    {
        $original = tenant();
        try {
            tenancy()->initialize($this);

            return $callback($this);
        } finally {
            if ($original) {
                tenancy()->initialize($original);
            } else {
                tenancy()->end();
            }
        }
    }

    public static function getCustomColumns(): array
    {
        return [
            'id', 'created_at', 'updated_at', 'plan_id', 'license_starts_at',
            'license_ends_at', 'grace_ends_at', 'status', 'is_suspended',
            'suspension_reason', 'miembros_count_cache', 'notified_30_days',
            'notified_7_days', 'notified_grace', 'approved_at', 'approved_by',
            'notes', 'provisioned_at',
        ];
    }

    public function permiteAcceso(): bool
    {
        return $this->status === 'active'
            && ! $this->is_suspended
            && $this->license_starts_at !== null
            && $this->license_starts_at->startOfDay()->lte(now())
            && $this->license_ends_at !== null
            && ($this->grace_ends_at ?? $this->license_ends_at)->endOfDay()->gte(now());
    }

    protected function casts(): array
    {
        return [
            'license_starts_at' => 'date',
            'license_ends_at' => 'date',
            'grace_ends_at' => 'date',
            'approved_at' => 'datetime',
            'provisioned_at' => 'datetime',
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

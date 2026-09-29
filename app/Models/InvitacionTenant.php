<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvitacionTenant extends Model
{
    use \Stancl\Tenancy\Database\Concerns\CentralConnection;

    protected $table = 'invitaciones_tenant';

    protected $guarded = ['id'];

    protected $hidden = ['token_hash'];

    protected function casts(): array
    {
        return ['expires_at' => 'datetime', 'used_at' => 'datetime', 'revoked_at' => 'datetime', 'paid_at' => 'datetime'];
    }

    public function disponible(): bool
    {
        return ! $this->used_at && ! $this->revoked_at && $this->paid_at && $this->expires_at?->isFuture();
    }
}

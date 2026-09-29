<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvitacionAccesoTenant extends Model
{
    use \Stancl\Tenancy\Database\Concerns\CentralConnection;

    protected $table = 'invitaciones_acceso_tenant';

    protected $guarded = ['id'];

    protected $hidden = ['token_hash'];

    protected function casts(): array
    {
        return ['expires_at' => 'datetime', 'used_at' => 'datetime'];
    }
}

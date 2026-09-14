<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Stancl\Tenancy\Database\Concerns\CentralConnection;

class LoginBrandingDefault extends Model
{
    use CentralConnection;

    protected $guarded = ['id', 'singleton_key'];

    protected function casts(): array
    {
        return [
            'singleton_key' => 'boolean',
            'carousel_interval_ms' => 'integer',
        ];
    }

    public function slides(): HasMany
    {
        return $this->hasMany(LoginBrandingDefaultSlide::class)->orderBy('sort_order');
    }
}

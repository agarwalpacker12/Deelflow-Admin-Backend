<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Organization extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'uuid',
        'slug',
        'subscription_status',
        'industry',
        'organization_size',
        'business_email',
        'business_phone',
        'website',
        'support_email',
        'street_address',
        'city',
        'state_province',
        'zip_postal_code',
        'country',
        'timezone',
        'language',
    ];

    protected $casts = [
        'uuid' => 'string',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PropertySave extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'property_id',
    ];

    const CREATED_AT = 'saved_at';
    const UPDATED_AT = null;

    /**
     * Get the user who saved the property.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the saved property.
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }
}

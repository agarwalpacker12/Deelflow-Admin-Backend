<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserAchievement extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'achievement_type',
        'achievement_name',
        'points_earned',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    const CREATED_AT = 'earned_at';
    const UPDATED_AT = null;

    /**
     * Get the user who earned the achievement.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

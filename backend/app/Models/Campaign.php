<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\BelongsToTenant;

class Campaign extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'user_id',
        'name',
        'campaign_type',
        'channel',
        'target_criteria',
        'geofence_center',
        'geofence_radius',
        'subject_line',
        'preview_text',
        'email_content',
        'sms_content',
        'voice_script',
        'landing_page_id',
        'use_ai_personalization',
        'ai_tone',
        'status',
        'scheduled_at',
        'started_at',
        'completed_at',
        'total_recipients',
        'sent_count',
        'open_count',
        'click_count',
        'response_count',
        'conversion_count',
        'budget',
        'spent',
        'organization_id',
    ];

    protected $casts = [
        'target_criteria' => 'array',
        'geofence_center' => 'array',
        'use_ai_personalization' => 'boolean',
        'scheduled_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'budget' => 'decimal:2',
        'spent' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function recipients(): HasMany
    {
        return $this->hasMany(CampaignRecipient::class);
    }
}

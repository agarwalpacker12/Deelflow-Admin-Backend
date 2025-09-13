<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiConversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'uuid',
        'lead_id',
        'property_id',
        'channel',
        'external_id',
        'messages',
        'sentiment_score',
        'urgency_score',
        'motivation_score',
        'qualification_score',
        'extracted_data',
        'identified_pain_points',
        'detected_keywords',
        'status',
        'transferred_to_human',
        'transfer_reason',
        'outcome',
        'next_steps',
    ];

    protected $casts = [
        'messages' => 'array',
        'extracted_data' => 'array',
        'identified_pain_points' => 'array',
        'detected_keywords' => 'array',
        'transferred_to_human' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }
}

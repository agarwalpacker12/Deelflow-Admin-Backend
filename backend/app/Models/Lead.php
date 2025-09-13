<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\BelongsToTenant;

class Lead extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'user_id',
        'lead_type',
        'client_id',
        'uuid',
        'first_name',
        'last_name',
        'email',
        'phone',
        'alternate_phone',
        'property_address',
        'property_city',
        'property_state',
        'property_zip',
        'property_type',
        'ai_score',
        'motivation_score',
        'urgency_score',
        'financial_score',
        'source',
        'source_details',
        'estimated_value',
        'mortgage_balance',
        'asking_price',
        'status',
        'disposition',
        'preferred_contact_method',
        'best_time_to_call',
        'ai_insights',
        'conversation_summary',
        'next_action',
        'next_action_date',
        'last_contact_at',
        'organization_id',
    ];

    protected $casts = [
        'source_details' => 'array',
        'ai_insights' => 'array',
        'estimated_value' => 'decimal:2',
        'mortgage_balance' => 'decimal:2',
        'asking_price' => 'decimal:2',
        'next_action_date' => 'date',
        'last_contact_at' => 'datetime',
    ];

    /**
     * Get the user that owns the lead.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the client associated with this lead.
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Get the deals for the lead.
     */
    public function deals(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Deal::class);
    }

    /**
     * Get the AI conversations for the lead.
     */
    public function aiConversations(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(AiConversation::class);
    }
}

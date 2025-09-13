<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\Attribute;
use App\Traits\BelongsToTenant;

/**
 * Client Model
 * 
 * Represents sellers and buyers managed by wholesalers.
 * IMPORTANT: Clients are NOT users and cannot log into the system.
 * They are managed BY wholesaler users, not independent system users.
 */
class Client extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'user_id',
        'uuid',
        'client_type',
        'first_name',
        'last_name',
        'email',
        'phone',
        'alternate_phone',
        'date_of_birth',
        'address',
        'city',
        'state',
        'zip',
        'country',
        'occupation',
        'employer',
        'annual_income',
        'net_worth',
        'liquid_assets',
        'credit_score',
        'has_financing_preapproval',
        'financing_amount',
        'investment_criteria',
        'investment_goals',
        'investment_experience',
        'owned_properties',
        'selling_motivation',
        'selling_timeline',
        'preferred_contact_method',
        'best_time_to_call',
        'communication_notes',
        'status',
        'source',
        'notes',
        'relationship_score',
        'last_contact_at',
        'next_followup_at',
        'custom_fields',
        'tags',
        'organization_id',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'annual_income' => 'decimal:2',
        'net_worth' => 'decimal:2',
        'liquid_assets' => 'decimal:2',
        'financing_amount' => 'decimal:2',
        'has_financing_preapproval' => 'boolean',
        'investment_criteria' => 'array',
        'investment_goals' => 'array',
        'owned_properties' => 'array',
        'communication_notes' => 'array',
        'custom_fields' => 'array',
        'tags' => 'array',
        'last_contact_at' => 'datetime',
        'next_followup_at' => 'datetime',
    ];

    /**
     * Get the client's full name.
     */
    protected function name(): Attribute
    {
        return new Attribute(
            get: fn () => "{$this->first_name} {$this->last_name}",
        );
    }

    /**
     * Get the wholesaler that owns this client.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the leads associated with this client.
     */
    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class);
    }

    /**
     * Scope to filter by client type.
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('client_type', $type);
    }

    /**
     * Scope to filter by status.
     */
    public function scopeWithStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to filter clients needing follow-up.
     */
    public function scopeNeedingFollowup($query)
    {
        return $query->whereNotNull('next_followup_at')
                    ->where('next_followup_at', '<=', now());
    }

    /**
     * Check if client is a seller.
     */
    public function isSeller(): bool
    {
        return $this->client_type === 'seller';
    }

    /**
     * Check if client is a buyer.
     */
    public function isBuyer(): bool
    {
        return $this->client_type === 'buyer';
    }

    /**
     * Get the client's primary contact method.
     */
    public function getPrimaryContact(): string
    {
        if ($this->preferred_contact_method === 'email' && $this->email) {
            return $this->email;
        }
        
        return $this->phone ?: $this->alternate_phone ?: 'No contact info';
    }
}

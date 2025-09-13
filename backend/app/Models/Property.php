<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\Attribute;
use App\Traits\BelongsToTenant;

class Property extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'user_id',
        'uuid',
        'address',
        'unit',
        'city',
        'state',
        'zip',
        'county',
        'location',
        'property_type',
        'bedrooms',
        'bathrooms',
        'square_feet',
        'lot_size',
        'year_built',
        'stories',
        'garage_spaces',
        'purchase_price',
        'arv',
        'repair_estimate',
        'holding_costs',
        'ai_score',
        'ai_analysis',
        'market_analysis',
        'repair_analysis',
        'transaction_type',
        'escrow_amount',
        'assignment_fee',
        'status',
        'listed_at',
        'expires_at',
        'sold_at',
        'blockchain_hash',
        'smart_contract_address',
        'description',
        'seller_notes',
        'images',
        'documents',
        'seller_info',
        'property_condition',
        'neighborhood_data',
        'view_count',
        'save_count',
        'inquiry_count',
        'organization_id',
    ];

    protected $casts = [
        'location' => 'array',
        'bathrooms' => 'decimal:1',
        'lot_size' => 'decimal:2',
        'purchase_price' => 'decimal:2',
        'arv' => 'decimal:2',
        'repair_estimate' => 'decimal:2',
        'holding_costs' => 'decimal:2',
        'ai_analysis' => 'array',
        'market_analysis' => 'array',
        'repair_analysis' => 'array',
        'escrow_amount' => 'decimal:2',
        'assignment_fee' => 'decimal:2',
        'listed_at' => 'datetime',
        'expires_at' => 'datetime',
        'sold_at' => 'datetime',
        'images' => 'array',
        'documents' => 'array',
        'seller_info' => 'array',
        'property_condition' => 'array',
        'neighborhood_data' => 'array',
    ];

    protected $appends = ['profit_potential'];

    /**
     * Get the user that owns the property.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the saves for the property.
     */
    public function saves(): HasMany
    {
        return $this->hasMany(PropertySave::class);
    }

    /**
     * Get the deals for the property.
     */
    public function deals(): HasMany
    {
        return $this->hasMany(Deal::class);
    }

    /**
     * Get the AI conversations for the property.
     */
    public function aiConversations(): HasMany
    {
        return $this->hasMany(AiConversation::class);
    }

    /**
     * Calculate the profit potential.
     */
    protected function profitPotential(): Attribute
    {
        return new Attribute(
            get: fn () => $this->arv - $this->purchase_price - $this->repair_estimate - $this->holding_costs,
        );
    }
}

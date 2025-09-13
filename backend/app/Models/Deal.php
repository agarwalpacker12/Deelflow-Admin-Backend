<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\BelongsToTenant;

class Deal extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'uuid',
        'property_id',
        'lead_id',
        'buyer_id',
        'seller_id',
        'funder_id',
        'deal_type',
        'purchase_price',
        'sale_price',
        'assignment_fee',
        'funding_amount',
        'funding_fee',
        'funding_duration',
        'contract_date',
        'closing_date',
        'inspection_period',
        'earnest_money',
        'status',
        'substatus',
        'blockchain_contract_address',
        'escrow_transaction_hash',
        'contract_terms',
        'documents',
        'contingencies',
        'contract_signed_at',
        'escrow_deposited_at',
        'funding_received_at',
        'closed_at',
        'cancelled_at',
        'internal_notes',
        'organization_id',
    ];

    protected $casts = [
        'purchase_price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'assignment_fee' => 'decimal:2',
        'funding_amount' => 'decimal:2',
        'funding_fee' => 'decimal:2',
        'earnest_money' => 'decimal:2',
        'contract_date' => 'date',
        'closing_date' => 'date',
        'contract_terms' => 'array',
        'documents' => 'array',
        'contingencies' => 'array',
        'contract_signed_at' => 'datetime',
        'escrow_deposited_at' => 'datetime',
        'funding_received_at' => 'datetime',
        'closed_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function funder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'funder_id');
    }

    public function milestones(): HasMany
    {
        return $this->hasMany(DealMilestone::class);
    }
}

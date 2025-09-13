<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected $guard_name = 'api';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'uuid',
        'email',
        'phone',
        'password',
        'first_name',
        'last_name',
        'level',
        'points',
        'avatar_url',
        'blockchain_wallet',
        'stripe_customer_id',
        'is_verified',
        'is_active',
        'status',
        'preferences',
        'metadata',
        'last_login_at',
        'organization_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'is_verified' => 'boolean',
            'is_active' => 'boolean',
            'preferences' => 'array',
            'metadata' => 'array',
        ];
    }

    /**
     * Get the user's full name.
     */
    protected function name(): Attribute
    {
        return new Attribute(
            get: fn () => "{$this->first_name} {$this->last_name}",
        );
    }

    /**
     * Get the user's achievements.
     */
    public function achievements(): HasMany
    {
        return $this->hasMany(UserAchievement::class);
    }

    /**
     * Get the properties for the user.
     */
    public function properties(): HasMany
    {
        return $this->hasMany(Property::class);
    }

    /**
     * Get the property saves for the user.
     */
    public function propertySaves(): HasMany
    {
        return $this->hasMany(PropertySave::class);
    }

    /**
     * Get the leads for the user.
     */
    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class);
    }

    /**
     * Get the deals for the user as a buyer.
     */
    public function dealsAsBuyer(): HasMany
    {
        return $this->hasMany(Deal::class, 'buyer_id');
    }

    /**
     * Get the deals for the user as a seller.
     */
    public function dealsAsSeller(): HasMany
    {
        return $this->hasMany(Deal::class, 'seller_id');
    }

    /**
     * Get the deals for the user as a funder.
     */
    public function dealsAsFunder(): HasMany
    {
        return $this->hasMany(Deal::class, 'funder_id');
    }

    /**
     * Get the AI conversations for the user.
     */
    public function aiConversations(): HasMany
    {
        return $this->hasMany(AiConversation::class);
    }

    /**
     * Get the campaigns for the user.
     */
    public function campaigns(): HasMany
    {
        return $this->hasMany(Campaign::class);
    }

    /**
     * Get the clients for the user.
     */
    public function clients(): HasMany
    {
        return $this->hasMany(Client::class);
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    // roles

    public function isSuperAdmin()
    {
        return $this->hasRole('super_admin') || $this->email === config('auth.super_admin.email');
    }


}

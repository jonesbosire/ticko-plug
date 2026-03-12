<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class OrganizerProfile extends Model
{
    use HasSlug;

    protected $fillable = [
        'user_id', 'organization_name', 'slug', 'description',
        'website', 'facebook_url', 'twitter_url', 'instagram_url',
        'mpesa_paybill', 'mpesa_till', 'bank_account_name',
        'bank_account_number', 'bank_name', 'bank_branch',
        'verified_at', 'payout_schedule', 'total_earned', 'total_paid_out',
    ];

    protected function casts(): array
    {
        return [
            'verified_at'   => 'datetime',
            'total_earned'  => 'decimal:2',
            'total_paid_out'=> 'decimal:2',
        ];
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('organization_name')
            ->saveSlugsTo('slug');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function isVerified(): bool
    {
        return $this->verified_at !== null;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class, 'organizer_id', 'user_id');
    }

    public function payouts(): HasMany
    {
        return $this->hasMany(Payout::class, 'organizer_id', 'user_id');
    }
}

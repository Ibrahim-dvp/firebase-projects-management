<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserGoogleAccount extends Model
{
    /** @use HasFactory<\Database\Factories\UserGoogleAccountFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'access_token',
        'refresh_token',
        'expires_at',
        'expires_in',
        'google_id',
        'email',
        'name',
        'given_name',
        'family_name',
        'picture',
    ];
    protected $casts = [
        'expires_at' => 'datetime',

    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function firebaseProjects(): HasMany
    {
        return $this->hasMany(FirebaseProject::class);
    }
}

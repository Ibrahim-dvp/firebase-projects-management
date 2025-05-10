<?php

namespace App\Models;

use App\Models\UserGoogleAccount;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FirebaseProject extends Model
{
    /** @use HasFactory<\Database\Factories\FirebaseProjectFactory> */
    use HasFactory;
    protected $fillable = [
        'user_google_account_id',
        'email',
        'project_id',
        'name',
        'credentials_path'
    ];

    public function UserGoogleAccount(): BelongsTo
    {
        return $this->belongsTo(UserGoogleAccount::class);
    }
}

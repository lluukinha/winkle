<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    public function status() {
        return $this->belongsTo(UserStatus::class);
    }

    public function sales() {
        return $this->hasMany(Sale::class);
    }

    public function lastActiveSale() {
        return $this
            ->sales()
            ->whereIn('status_id', [3,4])
            ->orderBy('created_at', 'desc')
            ->first();
    }

    public function currentPlan() {
        $lastSale = $this->lastActiveSale();
        if (!$lastSale) return null;
        return $lastSale->plan;
    }

    public function expirationDate() {
        $lastSale = $this->lastActiveSale();
        if (!$lastSale) return null;
        $startPlan = $lastSale->created_at;
        $months = $this->currentPlan()->duration;
        $endPlan = (new Carbon($startPlan))->addMonths($months);
        return $endPlan;
    }

    public function passwords() {
        return $this->hasMany(Password::class);
    }

    public function folders() {
        return $this->hasMany(Folder::class);
    }

    public function canUpdateMasterPassword() {
        $passwordsCount = $this->passwords()
            ->where(function ($query) {
                return $query
                    ->whereNotNull('login')
                    ->orWhereNotNull('password');
            })
            ->count();

        return $passwordsCount == 0;
    }

    public function isAdmin() {
        return $this->admin;
    }
}

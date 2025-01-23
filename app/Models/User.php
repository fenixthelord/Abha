<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Support\Str;

class User extends Authenticatable  implements Auditable
{
    use HasApiTokens, HasFactory, Notifiable, softDeletes, HasRoles;
    use \OwenIt\Auditing\Auditable;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'email',
        'password',
        'first_name',
        'last_name',
        'phone',
        'image',
        'alt',
        'gender',
        'uuid',
        'otp_code',
        'job',
        'job_id',
        'role',
        'verify_code',
        'is_admin',
        'active',
        'otp_expires_at',
        'refresh_token',
        'refresh_token_expires_at',
    ];
    protected $dates = ['deleted_at','refresh_token_expires_at'];


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
        'password' => 'hashed',
        'refresh_token_expires_at' => 'datetime',
    ];

    public function linkedSocialAccounts()
    {
        return $this->hasOne(LinkedSocialAccount::class);
    }

    public function transformAudit(array $data): array
    {
        // Include user details in the audit metadata
        $data['user_uuid'] = $this->uuid; // Store the user's UUID
        $data['user_full_name'] = "{$this->first_name} {$this->last_name}"; // Store the user's full name

        // Include additional details (optional)
        $data['ip_address'] = request()->ip();
        $data['user_agent'] = request()->header('User-Agent');

        return $data;
    }

    // Automatically generate UUID when creating a new NotifyGroup
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->uuid = Str::uuid();
        });
    }

    // Relationship with users
    public function users()
    {
        return $this->belongsToMany(User::class, 'notify_group_user');
    }

}

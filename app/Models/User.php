<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Http\Traits\HasAutoPermissions;
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
    use HasAutoPermissions;

    protected $keyType = 'string';
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        "department_id",
        'first_name',
        'last_name',
        'phone',
        'email',
        'email_verified_at',
        'password',
        'image',
        'alt',
        'gender',
        'job',
        'job_id',
        'role',
        'is_admin',
        'active',
        'otp_code',
        'otp_expires_at',
        'otp_verified',
        'verify_code',
        'refresh_token',
        'refresh_token_expires_at',
    ];
    protected $dates = ['deleted_at', 'refresh_token_expires_at'];


    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'OTP',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'otp_expires_at' => 'datetime',
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
        $data['user_id'] = $this->id; // Store the user's UUID
        $data['user_full_name'] = "{$this->first_name} {$this->last_name}"; // Store the user's full name

        // Include additional details (optional)
        $data['ip_address'] = request()->ip();
        $data['user_agent'] = request()->header('User-Agent');

        return $data;
    }

    protected $auditExclude = [
        'password',
    ];

    // Relationship with notify groups
    public function groups()
    {
        return $this->belongsToMany(NotifyGroup::class, 'notify_group_user', 'user_id', 'notify_group_id', 'id', 'id');
    }

    // Relationship with device tokens
    public function deviceTokens()
    {
        return $this->hasMany(DeviceToken::class);
    }
    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    // public function mangers()
    // {
    //     return $this->hasMany(Organization::class, 'manger_id');
    // }
    public function employees()
    {
        return $this->hasMany(Organization::class, 'manger_id');
    }
    public function organization()
    {
        return $this->hasOne(Organization::class, 'employee_id');
    }

    public function scopeMangersInDepartment($query, $departmentId)
    {
        return $query->whereHas("mangers", function ($q) use ($departmentId) {
            $q->whereHas('department',  function ($q) use ($departmentId) {
                $q->where("id", $departmentId);
            });
        });
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = Str::uuid();
            }
        });
    }
}

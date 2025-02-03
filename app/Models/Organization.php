<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Support\Str;
use Spatie\Translatable\HasTranslations;

class Organization extends Model  implements Auditable

{
    use HasFactory, SoftDeletes, HasTranslations, \OwenIt\Auditing\Auditable;

    private $translatable = ['position'];

  protected $fillable = [
        "department_id",
        "manger_id",
        "employee_id",
        "position",
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->uuid = Str::uuid();
        });
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function manger()
    {
        return $this->belongsTo(User::class, 'manger_id');
    }

    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    public function scopeWithSearch($query, $value)
    {
        return $query
            ->where('id', 'like', '%' . $value . '%')
            ->orWhere('position', 'like', '%' . $value . '%')
            ->orWhereHas('department', function ($query) use ($value) {
                $query->where('name', 'like', '%' . $value . '%');
            })
            ->orWhereHas('manger', function ($query) use ($value) {
                $query->where('first_name', 'like', '%' . $value . '%')
                    ->orWhere('last_name', 'like', '%' . $value . '%');
            })
            ->orWhereHas('employee', function ($query) use ($value) {
                $query->where('first_name', 'like', '%' . $value . '%')
                    ->orWhere('last_name', 'like', '%' . $value . '%');
            });
    }
}

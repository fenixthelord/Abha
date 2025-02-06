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

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    // The relation with User Table , my manger
    public function manger()
    {
        return $this->belongsTo(User::class, 'manger_id');
    }

    // Self Join : my Employees in Organization Table
    public function employee()
    {
        return $this->hasMany(Organization::class, 'manger_id');
    }

    public function User()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    // public function 

    public function scopeWithSearch($query, $value)
    {
        return $query
            ->Where('position', 'like', '%' . $value . '%')
            ->orWhereHas('department', function ($query) use ($value) {
                $query->where('name', 'like', '%' . $value . '%');
            })
            ->orWhereHas('manger', function ($query) use ($value) {
                $query->where('first_name', 'like', '%' . $value . '%')
                    ->orWhere('last_name', 'like', '%' . $value . '%');
            })
            ->orWhereHas('user', function ($query) use ($value) {
                $query->where('first_name', 'like', '%' . $value . '%')
                    ->orWhere('last_name', 'like', '%' . $value . '%');
            });
    }

    public function scopeOnlyHeadMangers($query, $departmentId)
    {

        $employeesIDs = $this->distinct()->pluck("employee_id")->toArray();
        // dd($employeesIDs);  
        return $query
            ->whereHas('department', function ($q) use ($departmentId) {
                $q->where('id', $departmentId);
            })
            ->distinct()
            ->whereNotIn("manger_id", $employeesIDs)
            ->pluck("manger_id")
            ->toArray();
    }
}

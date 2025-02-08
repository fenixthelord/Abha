<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Support\Str;
use Spatie\Translatable\HasTranslations;

class Organization extends BaseModel  implements Auditable

{
    use HasFactory, SoftDeletes, HasTranslations, \OwenIt\Auditing\Auditable;

    private $translatable = ['position'];

    protected $fillable = [
        "department_id",
        "manager_id",
        "employee_id",
        "position",
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    // The relation with User Table , my manager
    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    // Self Join : my Employees in Organization Table
    public function employee()
    {
        return $this->hasMany(Organization::class, 'manager_id');
    }

    public function User()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    public function scopeWithSearch($query, $value)
    {
        return $query
            ->Where('position', 'like', '%' . $value . '%')
            ->orWhereHas('department', function ($query) use ($value) {
                $query->where('name', 'like', '%' . $value . '%');
            })
            ->orWhereHas('manager', function ($query) use ($value) {
                $query->where('first_name', 'like', '%' . $value . '%')
                    ->orWhere('last_name', 'like', '%' . $value . '%');
            })
            ->orWhereHas('user', function ($query) use ($value) {
                $query->where('first_name', 'like', '%' . $value . '%')
                    ->orWhere('last_name', 'like', '%' . $value . '%');
            });
    }

    public function scopeOnlyHeadManagers($query, $departmentId)
    {
        $employeesIDs = $this->distinct()->pluck("employee_id")->toArray();
        // dd($employeesIDs);  
        return $query
            ->whereHas('department', function ($q) use ($departmentId) {
                $q->where('id', $departmentId);
            })
            ->distinct()
            ->whereNotIn("manager_id", $employeesIDs)
            ->pluck("manager_id")
            ->toArray();
    }

    public function scopeMangersAndEmployees($query, $departmentId)
    {

        $employeesWithOutHead = $query
            ->whereHas('department', function ($q) use ($departmentId) {
                $q->where('id', $departmentId);
            })
            ->pluck("employee_id")
            ->toArray();

        dd($employeesWithOutHead);

        $employees = $employeesWithOutHead  ;
        $employees[] = $query
            ->onlyHeadManagers($departmentId);
    }
}

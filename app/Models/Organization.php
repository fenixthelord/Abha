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
    ];    protected $casts = [
        "department_id" => "string",
        "manager_id" => "string",
        "employee_id" => "string",
        "position" => "json",
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

    // Scope to filter by department.
    public function scopeForDepartment($query, $departmentId)
    {
        return $query->whereHas('department', function ($q) use ($departmentId) {
            $q->where('id', $departmentId);
        });
    }

    public function scopeOnlyHeadManagers($query, array $employeeIds)
    {
        return $query->whereNotIn('manager_id', $employeeIds)->distinct();
    }

    public static function getOnlyHeadManager($departmentId) {
        $employeeIds = static::forDepartment($departmentId)
            ->pluck('employee_id')
            ->toArray();

        return static::forDepartment($departmentId)
            ->onlyHeadManagers($employeeIds)
            ->pluck('manager_id')
            ->toArray();
    }

    public static function getManagersAndEmployees($departmentId)
    {
        $employeeIds = static::forDepartment($departmentId)
            ->pluck('employee_id')
            ->toArray();

        $managerIds = static::getOnlyHeadManager($departmentId);

        return array_unique(array_merge($employeeIds, $managerIds));
    }

    public static function getAllChildIds($employeeId)
    {
        $employee = self::find($employeeId);
        
        if (!$employee) {
            return [];
        }
        
        $childrenIds = [];

        foreach ($employee->employee as $child) {
            $childrenIds[] = $child->id;
            $childrenIds = array_merge($childrenIds, self::getAllChildIds($child->id));
        }
        
        return $childrenIds;
    }
}

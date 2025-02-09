<?php

namespace App\Models;

use App\Models\Forms\Form;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use Spatie\Translatable\HasTranslations;

class Category extends BaseModel implements Auditable
{
    use HasFactory, SoftDeletes, \OwenIt\Auditing\Auditable, HasTranslations;

    private $translatable = ['name'];

    protected $fillable = ["name", "parent_id", "department_id"];
    protected $casts = [
        "name" => "json",
        "parent_id" => "string",
        "department_id" => "string"];

    public function forms(): MorphMany
    {
        return $this->morphMany(Form::class, 'formable');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, "department_id");
    }

    public function allChildren()
    {
        return $this->children()->with('allChildren');
    }

    public function deleteWithChildren()
    {
        $this->children->each->deleteWithChildren();

        $this->delete();
    }

    public function scopeWithSearch($query, $value)
    {
        return $query
            ->where('id', 'like', '%' . $value . '%')
            ->orWhere('name', 'like', '%' . $value . '%')
            ->orWhereHas('department', function ($query) use ($value) {
                $query->where('name', 'like', '%' . $value . '%');
            })
            ->orWhereHas('parent', function ($query) use ($value) {
                $query->where('name', 'like', '%' . $value . '%');
            });
    }
}

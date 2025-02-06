<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use OwenIt\Auditing\Auditable;

class Audit extends BaseModel
{
    use HasFactory;
    use Auditable;
}

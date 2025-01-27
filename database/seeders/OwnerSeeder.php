<?php

namespace Database\Seeders;

use App\Models\Role\Permission;
use App\Models\Role\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Http\Traits\ResponseTrait;
use function PHPUnit\Framework\isEmpty;

class OwnerSeeder extends Seeder
{
    use ResponseTrait;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $owner = Role::where('name', "Master_Owner")->first();
        if ($owner) {

        $permission = Permission::where('is_admin', 0)->get();

        if (!$permission->isEmpty()) {

            $owner->syncPermissions($permission);
        }}
    }
}

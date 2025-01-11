<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Roles\Rolesresource;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class RoleAndPermissionController extends Controller
{
    public function index()
    {
        $roles = Role::all();
        return Rolesresource::collection($roles);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'name' =>'required|string|unique:roles,name',
        ]);

        Role::create([
            'name' => $request->name,
        ]);
    }


    public function update()
    {

    }

    public function destroy()
    {

    }
}

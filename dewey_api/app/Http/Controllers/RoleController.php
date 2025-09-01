<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    //
    public function getRole()
    {
        $roles = Role::all();
        return response()->json($roles);
    }
}

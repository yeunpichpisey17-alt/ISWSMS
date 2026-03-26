<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    public function index(): Response
    {
        $permissions = Permission::all()->pluck('name');
        $grouped = [];

        foreach ($permissions as $permission) {
            $parts = explode('.', $permission);
            $module = $parts[0];
            $grouped[$module][] = $permission;
        }

        return Inertia::render('Admin/Permissions/Index', [
            'permissions' => $grouped,
        ]);
    }
}

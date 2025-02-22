<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $listRole = Role::all();
        return view('admins.roles.index', compact('listRole'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admins.roles.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $singerRole = Role::findOrFail($id);
        $users = $singerRole->users()->with('role')->get();
        $roles = Role::all();
        return view('admins.roles.show', compact('singerRole', 'users', 'roles'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {   
        $singerRole = Role::findOrFail($id);
        $users = $singerRole->users()->with('role')->paginate(10);
        return view('admins.roles.edit', compact('singerRole','users'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}

<?php

// app/Http/Controllers/AdminController.php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminController extends Controller
{
    public function index()
    {
        // eager-load roles if you have a relationship
        $users = User::orderBy('name')->get(['id', 'name', 'email', 'role']);
        return Inertia::render('Admin', [
            'users' => $users,
        ]);
    }

    public function store(Request $request)
    {
        $attrs = $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8',
            'role'  => 'required|in:user,admin',
        ]);

        User::create([
            ...$attrs,
            'password' => bcrypt($attrs['password']),
        ]);

        return back()->with('success', 'User created');
    }

    public function destroy(User $user)
    {
        abort_if($user->id === auth()->id(), 403, "You can't delete yourself.");
        $user->delete();
        return back()->with('success', 'User deleted');
    }
}

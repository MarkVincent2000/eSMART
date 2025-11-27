<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    /**
     * Show the user management page.
     */
    public function index()
    {
        return view('user-management.index');
    }

    /**
     * Return users as JSON for the React table.
     */
    public function list(Request $request)
    {
        $users = User::select('id', 'name', 'email', 'active_status', 'created_at')->get();

        return response()->json($users);
    }

    /**
     * Store a newly created user from the modal form.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'active_status' => ['required', 'in:0,1'],
            'avatar' => ['nullable', 'image', 'max:2048'], // 2MB max
        ]);

        $avatarPath = null;
        if ($request->hasFile('avatar')) {
            // Store avatar in public disk under avatars/ and keep relative path
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
        }

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'avatar' => $avatarPath,
            'active_status' => $validated['active_status'],
        ]);

        return redirect()
            ->route('user-management.index')
            ->with('success', 'User created successfully.');
    }
}


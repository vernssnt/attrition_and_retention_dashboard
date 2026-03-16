<?php

namespace App\Http\Controllers;


use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:admin')->only([
            'create',
            'store',
            'edit',
            'update',
            'destroy'
        ]);
    }

    // Show users list
    public function index()
    {
        $users = User::all();
        return view('users.index', compact('users'));
    }

    // Show add user form
    public function create()
    {
        return view('users.create');
    }

    // Save new user
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required',
            'role' => 'required'
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'is_active' => 1
        ]);

        return redirect()->route('users.index')->with('success','User created successfully');
    }

    // Show edit form
    public function edit($id)
    {
        $user = User::findOrFail($id);
        return view('users.edit', compact('user'));
    }

    // Update user
   public function update(Request $request, $id)
{
    $user = User::findOrFail($id);

    $request->validate([
        'name' => 'required',
        'email' => 'required|email|unique:users,email,' . $id,
        'role' => 'required'
    ]);

    $user->update([
        'name' => $request->name,
        'email' => $request->email,
        'role' => $request->role
    ]);

    return redirect()->route('users.index')
        ->with('success','User updated successfully');
}

    // Delete user
   public function destroy($id)
{
    $user = User::findOrFail($id);

    // Prevent admin from deleting own account
    if ($user->id === Auth::id()) {
        return redirect()->route('users.index')
            ->with('error', 'You cannot delete your own account.');
    }

    $user->delete();

    return redirect()->route('users.index')
        ->with('success', 'User deleted successfully');
}
public function resetPassword($id)
{
    $user = User::findOrFail($id);

    if ($user->id === Auth::id()) {
        return redirect()->route('users.index')
            ->with('error', 'You cannot reset your own password.');
    }

    $user->password = bcrypt('Temp@123');
    $user->save();

    return redirect()->route('users.index')
        ->with('success', 'Password has been reset to Temp@123');
}
}

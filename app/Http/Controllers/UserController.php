<?php

namespace App\Http\Controllers;

use App\Models\User;
use ErrorException;
use Illuminate\Http\Request;

use function Laravel\Prompts\error;

class UserController extends Controller
{
    public function index()
    {
        return response()->json(User::all());
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'username' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|min:6',
            ]);
    
            $user = User::create([
                'username' => $request->username,
                'email' => $request->email,
                'password' => bcrypt($request->password),
            ]);

            if(!$user) {
                return response()->json('error', status: 300 );
            }
    
            return $user;

        }  catch (ErrorException $error) {

            return response()->json($error , status: 500);

        }


    }

    public function show(User $user)
    {
        return response()->json($user);
    }

    public function update(Request $request, User $user)
    {
        $user->update($request->only(['name', 'email']));
        return response()->json($user);
    }

    public function destroy(User $user)
    {
        $user->delete();
        return response()->json(['message' => 'User deleted successfully']);
    }
}

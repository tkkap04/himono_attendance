<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    public function list(Request $request)
    {
        $perPage = 10;

        $users = User::paginate($perPage);

        return view('list', compact('users'));
    }
}

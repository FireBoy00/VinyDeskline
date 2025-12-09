<?php

namespace App\Http\Controllers;

use App\Models\DeskUser;
use Illuminate\Http\Request;

class DeskUserController extends Controller
{
public function index()
{
    $users = DeskUser::all();
    return view('user-management', compact('users'));
}


    public function store(Request $request)
    {
        DeskUser::create($request->all());
        return response()->json(['success' => true]);
    }

    public function destroy(DeskUser $deskUser)
    {
        $deskUser->delete();
        return response()->json(['success' => true]);
    }
}


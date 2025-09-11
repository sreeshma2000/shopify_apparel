<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AccountController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('account.profile');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
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
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $user_id)
    {
        $this->validate($request,[
            'name'=>'required',
            'email'=>'required|email']);
            $user = User::find($user_id);
            $user->update($request->all());
            return redirect()->route('account.index')->with('success_message','Profile Updated Successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
    public function update_password(Request $request){
        $this->validate($request,[
            'current_password'=>'required',
            'password'=>'required|min:6',
            'password_confirmation'=>'required|same:password']);

        $user = User::find(Auth::user()->id);
        if(!Hash::check($request->current_password,$user->password)){
            return redirect()->route('account.index')->with('error_message','Password does not match');
        }

        $user->password = Hash::make($request->password);
        $user->save();
        return redirect()->route('account.index')->with('success_message','Password Updated Successfully');
    }
}

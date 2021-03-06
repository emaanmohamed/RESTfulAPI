<?php

namespace App\Http\Controllers\User;

use App\Mail\UserCreated;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Mail;

class UserController extends ApiController
{

    public function index()
    {
        $users = User::all();

        return $this->showAll($users);
    }

    public function store(Request $request)
    {
        $rules = [
            'name'  => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6|confirmed'
        ];

        $this->validate($request, $rules);
        $data = $request->all();
        $data['password'] = bcrypt($request->password);
        $data['verified'] = User::UNVERIFIED_USER;
        $data['verification_token'] = User::generateVerificationCode();
        $data['admin'] = User::REGULAR_USER;
        $user = User::create($data);

        return $this->showOne($user, 201);
    }

    public function show(User $user)
    {
      //  $user = User::findOrFail($id);
        return $this->showOne($user, 201);
    }

    public function update(Request $request, User $user)
    {
       // $user = User::findOrFail($id);
        //dd($user);
        $rules = [
            'email' => 'required|email|unique:users',
            'password' => 'min:6|confirmed',
            'admin'  => 'in:' . User::ADMIN_USER . ',' . User::REGULAR_USER,
        ];
        // dd($rules);

        if ($request->has('name')){
            $user->name = $request->name;
        }

        if ($request->has('email') && $user->email != $request->email){
            $user->verified = User::UNVERIFIED_USER;
            $user->verification_token = User::generateVerificationCode();
            $user->email = $request->email;
        }

        if ($request->has('password')) {
            $user->password = bcrypt($request->password);
        }
        // 409 Conflict
        if ($request->has('admin')) {
            if (!$user->isVerified()) {
                return $this->errorResponse('Only verified users can modify the admin field', 409);
            }
            $user->admin = $request->admin;
        }
        //422 UNPROCESSABLE ENTITY

        if(!$user->isDirty()) {
            return $this->errorResponse('you need to specify a different value to update', 422);
        }
        $user->save();

        return $this->showOne($user);
    }

    public function destroy(User $user)
    {
       // $user = User::findOrFail($id);

        $user->delete();

        return response()->json(['data' => $user], 200);

    }
    public function verify($token)
    {
        $user = User::where('verification_token', $token)->firstOrFail();
       //  dd($user);
        $user->verified = User::VERIFIED_USER;
        $user->verification_token = null;
        $user->save();
        return $this->showMessage('The account has been verified succesfully');
    }
    public function resend(User $user)
    {
        if ($user->isVerified()) {
            return $this->errorResponse('This user is already verified ', 409);
        }
        retry(5, function () use ($user) {
            Mail::to($user)->send(new UserCreated($user));
        }, 100);
        return $this->showMessage('The verification email has been resend');
    }

}

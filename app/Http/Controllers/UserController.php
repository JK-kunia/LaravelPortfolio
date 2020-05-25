<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use Auth;
use Validator;

class UserController extends Controller
{
    public function show(string $name)
    {
        $user = User::where('name', $name)->first();
        
        $articles = $user->articles->sortByDesc('created_at');
 
        return view('users.show', [
            'user' => $user,
            'articles' => $articles,
        ]);
    }

    public function edit(string $name)
    {
        $user = Auth::user('name', $name);
            
        return view('users.edit', [
            'user' => $user
        ]);
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all() , [
            'user_name' => 'required|string|max:255',
            'user_password' => 'required|string|min:6|confirmed',
            ]);

        if ($validator->fails())
        {
          return redirect()->back()->withErrors($validator->errors())->withInput();
        }
        
        $user = User::find($request->id);
        $user->name = $request->user_name;
        if ($request->user_profile_photo !=null) {
            $request->user_profile_photo->storeAs('public/user_images', $user->id . '.jpg');
            $user->profile_photo = $user->id . '.jpg';
        }
        $user->password = bcrypt($request->user_password);
        $user->save();

        return redirect()->route('articles.index');
    }

    public function likes(string $name)
    {
        $user = User::where('name', $name)->first();
 
        $articles = $user->likes->sortByDesc('created_at');
 
        return view('users.likes', [
            'user' => $user,
            'articles' => $articles,
        ]);
    }

    public function followings(string $name)
    {
        $user = User::where('name', $name)->first();
 
        $followings = $user->followings->sortByDesc('created_at');
 
        return view('users.followings', [
            'user' => $user,
            'followings' => $followings,
        ]);
    }
    
    public function followers(string $name)
    {
        $user = User::where('name', $name)->first();
 
        $followers = $user->followers->sortByDesc('created_at');
 
        return view('users.followers', [
            'user' => $user,
            'followers' => $followers,
        ]);
    }

    public function follow(Request $request, string $name)
    {
        $user = User::where('name', $name)->first();
 
        if ($user->id === $request->user()->id)
        {
            return abort('404', 'Cannot follow yourself.');
        }
 
        $request->user()->followings()->detach($user);
        $request->user()->followings()->attach($user);
 
        return ['name' => $name];
    }
    
    public function unfollow(Request $request, string $name)
    {
        $user = User::where('name', $name)->first();
 
        if ($user->id === $request->user()->id)
        {
            return abort('404', 'Cannot follow yourself.');
        }
 
        $request->user()->followings()->detach($user);
 
        return ['name' => $name];
    }
}

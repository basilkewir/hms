<?php

namespace App\Http\Responses;

use Illuminate\Http\Request;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Illuminate\Support\Facades\Auth;

class LoginResponse implements LoginResponseContract
{
    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toResponse($request)
    {
        $user = Auth::user();
        
        if ($user) {
            $user->load('roles');
            
            // Check if user has admin role
            if ($user->roles->contains('name', 'admin')) {
                return redirect()->route('admin.dashboard');
            }
            
            // Check if user has manager role
            if ($user->roles->contains('name', 'manager')) {
                return redirect()->route('manager.dashboard');
            }
            
            // Check if user has front_desk role
            if ($user->roles->contains('name', 'front_desk')) {
                return redirect()->route('front-desk.dashboard');
            }
        }
        
        // Default redirect to dashboard
        return redirect()->route('dashboard');
    }
}

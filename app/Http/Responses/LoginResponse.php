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

            // Check if user has bartender role
            if ($user->roles->contains('name', 'bartender')) {
                return redirect()->route('bartender.dashboard');
            }

            // Check if user has server or restaurant_staff role
            if ($user->roles->contains('name', 'server') || $user->roles->contains('name', 'restaurant_staff')) {
                return redirect()->route('server.dashboard');
            }

            // Check for other staff roles
            if ($user->roles->contains('name', 'accountant')) {
                return redirect()->route('accountant.dashboard');
            }

            if ($user->roles->contains('name', 'housekeeping')) {
                return redirect()->route('housekeeping.dashboard');
            }

            if ($user->roles->contains('name', 'maintenance')) {
                return redirect()->route('maintenance.dashboard');
            }

            if ($user->roles->contains('name', 'hr')) {
                return redirect()->route('hr.dashboard');
            }
        }

        // Default redirect to dashboard
        return redirect()->route('dashboard');
    }
}

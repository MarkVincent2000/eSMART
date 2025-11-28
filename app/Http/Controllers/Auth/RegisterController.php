<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\Models\User;
use App\Models\Notification;
use App\Models\Role;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->whereNull('deleted_at'), // Allow email if user is soft deleted
            ],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\Models\User
     */
    protected function create(array $data)
    {
        // Generate a default name from email (part before @)
        $name = explode('@', $data['email'])[0];

        // Check if a soft-deleted user exists with this email
        $existingUser = User::withTrashed()->where('email', $data['email'])->first();

        if ($existingUser && $existingUser->trashed()) {
            // Restore and update the soft-deleted user
            $existingUser->restore();
            $existingUser->update([
                'name' => $name,
                'password' => Hash::make($data['password']),
                'active_status' => false, // Reset to inactive
            ]);
            $newUser = $existingUser;
        } else {
            // Create a new user
            $newUser = User::create([
                'name' => $name,
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'active_status' => false, // New users are inactive by default
            ]);
        }

        // Notify all super-admin and admin users about the new registration
        $this->notifyAdminsOfNewUser($newUser);

        return $newUser;
    }

    /**
     * Handle a registration request for the application.
     * Override to prevent automatic login after registration.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        $this->validator($request->all())->validate();

        $user = $this->create($request->all());

        // Don't log in the user automatically - skip $this->guard()->login($user);
        // Instead, redirect to login page with success message
        return $this->registered($request, $user)
            ?: redirect($this->redirectPath());
    }

    /**
     * The user has been registered.
     * Override to prevent automatic login and redirect to login page.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user
     * @return mixed
     */
    protected function registered(Request $request, $user)
    {
        // Don't log in the user automatically
        // Redirect to login page with success message
        return redirect()->route('login')
            ->with('status', 'Registration successful! Your account is pending activation. Please wait for an administrator to activate your account before you can log in.');
    }

    /**
     * Notify all super-admin and admin users about a new user registration.
     *
     * @param  \App\Models\User  $newUser
     * @return void
     */
    protected function notifyAdminsOfNewUser(User $newUser)
    {
        // Get all users with super-admin or admin roles
        $adminUsers = User::role(['super-admin', 'admin'])->get();

        foreach ($adminUsers as $admin) {
            Notification::create([
                'user_id' => $admin->id,
                'type' => 'alert',
                'title' => 'New User Registration',
                'body' => "A new user ({$newUser->email}) has registered and is waiting for activation.",
                'data' => [
                    'user_id' => $newUser->id,
                    'user_email' => $newUser->email,
                    'user_name' => $newUser->name,
                    'action' => 'user_registered',
                ],
                'notifiable_id' => $newUser->id,
                'notifiable_type' => User::class,
            ]);
        }
    }
}

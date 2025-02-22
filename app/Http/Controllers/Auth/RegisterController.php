<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Mail\MentorAssigned;
use App\Mail\MenteeAssigned;

use Illuminate\Support\Facades\Validator;
use App\Mail\MentorVerificationMail;
use App\Mail\MenteeVerificationMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str; // For generating a unique verification token
use Carbon\Carbon; 


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


    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/home';

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
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
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
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);
    }

    public function menteeshow()
    {
        return view('auth.menteereg');  
    }

  
public function menteereg(Request $request)
{
    // Collect form data
    $name = $request->input('name');
    $email = $request->input('email');
    $mobile = $request->input('mobile');
    $dob = $request->input('dob');
    $skills = $request->input('skills'); // This is now an array
    $interestedskills = $request->input('interestedskills');
    $password = $request->input('password');


    DB::beginTransaction();
    try {
        // Insert user into the database
        $userId = DB::table('users')->insertGetId([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'verified_at' => null, // Mark as not verified
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Save skills as JSON
        $skillsJson = json_encode($skills);

        // return [
        //     'skills' => $skills,
        //     'skillsJson' => $skillsJson,
        // ];



        // Insert mentee details
        DB::table('mentees')->insert([
            'user_id' => $userId,
            'name' => $name,
            'email' => $email,
            'mobile' => $mobile,
            'dob' => $dob,
            'skills' => $skillsJson, // Save skills as JSON
            'interestedskills' => $interestedskills,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Generate a token
        $token = Str::random(64);

        // Insert the token into the email_verifications table
        DB::table('email_verifications')->insert([
            'user_id' => $userId,
            'token' => $token,
            'created_at' => now(),
        ]);

        // Generate a verification URL
        $verificationUrl = route('mentee.verify', ['token' => $token]);

        // Send the verification email
        Mail::to($email)->send(new MenteeVerificationMail($name, $verificationUrl));

        DB::commit();

        // Redirect to the login page with a success message
        return redirect()->route('login')->with('success', 'Mentee registered successfully! A verification email has been sent to your registered email.');
    } catch (\Exception $e) {
        DB::rollBack();
        return redirect()->route('login')->with('error', 'Registration failed. Error: ' . $e->getMessage());
    }
}





    public function mentorshow()
    {
        return View('auth.mentorreg');
    }


    public function mentorreg(Request $request)
{
    // Collect form data
    $name = $request->input('name');
    $email = $request->input('email');
    $mobile = $request->input('mobile');
    $companyname = $request->input('companyname');
    $skills = $request->input('skills'); // Array of skills
    $password = $request->input('password');


    DB::beginTransaction();
    try {
        // Insert user into the database
        $userId = DB::table('users')->insertGetId([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'verified_at' => null, // Mark as not verified
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Save skills as JSON
        $skillsJson = json_encode($skills);


        // return [
        //     'skills' => $skills,
        //     'skillsJson' => $skillsJson,

        // ];

        // Insert mentee details
        DB::table('mentors')->insert([
            'user_id' => $userId,
            'name' => $name,
            'email' => $email,
            'mobile' => $mobile,
            'companyname' => $companyname,
            'skills' => $skillsJson,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

    
        // Generate a token
        $token = Str::random(64);

        // Insert the token into the email_verifications table
        DB::table('email_verifications')->insert([
            'user_id' => $userId,
            'token' => $token,
            'created_at' => now(),
        ]);

        // Generate a verification URL
        $verificationUrl = route('mentor.verify', ['token' => $token]);

        // Send the verification email
        Mail::to($email)->send(new MentorVerificationMail($name, $verificationUrl));

        DB::commit();

        // Redirect to the login page with a success message
        return redirect()->route('login')->with('success', 'Mentor registered successfully! A verification email has been sent to your registered email.');
    } catch (\Exception $e) {
        DB::rollBack();
        return redirect()->route('login')->with('error', 'Registration failed. Error: ' . $e->getMessage());
    }
}
   
 
    public function verifymentor($token)
    {
        $email = DB::table('email_verifications')->where('token', $token)->value('email');
        $name = DB::table('mentors')->where('email', $email)->value('name');

        $verification = DB::table('email_verifications')->where('token', $token)->first();

        if (!$verification) {
            return response()->json(['error' => 'Invalid or expired verification link.'], 400);
        }

        // Mark the user's email as verified and set verified column to 1
        DB::table('users')->where('id', $verification->user_id)->update([
            'verified_at' => now(),
            'verified' => 1, // Update verified column to 1
        ]);

        $mentorRoleId = 3; // Replace with the actual role ID for 'mentor'
        DB::table('role_user')->insert([
            'user_id' => $verification->user_id,
            'role_id' => $mentorRoleId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        

        // Delete the verification record
        DB::table('email_verifications')->where('id', $verification->id)->delete();

        // Send the mentor assigned notification
        Mail::to($email)->send(new MentorAssigned($name));

        return redirect()->route('login')->with('success', 'Mentor verified successfully!');
    }

    public function verifyMentee($token)
    {
        // Retrieve the mentee's email using the token
        $email = DB::table('email_verifications')->where('token', $token)->value('email');
        $name = DB::table('mentees')->where('email', $email)->value('name');

        // Find the verification record
        $verification = DB::table('email_verifications')->where('token', $token)->first();

        if (!$verification) {
            return response()->json(['error' => 'Invalid or expired verification link.'], 400);
        }

        // Mark the user's email as verified and set verified column to 1
        DB::table('users')->where('id', $verification->user_id)->update([
            'verified_at' => now(),
            'verified' => 1, // Update verified column to 1
        ]);

        $mentorRoleId = 4; // Replace with the actual role ID for 'mentor'
        DB::table('role_user')->insert([
            'user_id' => $verification->user_id,
            'role_id' => $mentorRoleId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Delete the verification record
        DB::table('email_verifications')->where('id', $verification->id)->delete();

        // Send the mentee assigned notification
        Mail::to($email)->send(new MenteeAssigned($name));

        return redirect()->route('login')->with('success', 'Mentee verified successfully!');
    }



}





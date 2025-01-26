<?php

namespace App\Http\Controllers\Mentor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;


class SupportController extends Controller
{
    public function index()
{
    // Retrieve the logged-in user's details
    $user = Auth::user();

    if (!$user) {
        // Handle the case where the user is not found
        return redirect()->back()->with('error', 'User not found.');
    }

    // Get the logged-in user's email
    $userEmail = $user->email;

    // Get the mentor ID from the mentors table
    $mentor = DB::table('mentors')->where('email', $userEmail)->first();
    
    if (!$mentor) {
        // Handle the case where the mentor is not found
        return redirect()->back()->with('error', 'Mentor not found.');
    }

    $mentorId = $mentor->id;

    // Fetch all support tickets from the ticket_descriptions table
    $supports = DB::table('ticket_descriptions')
        ->join('ticketcategories', 'ticket_descriptions.ticket_category_id', '=', 'ticketcategories.id')
        ->join('users', 'ticket_descriptions.user_id', '=', 'users.id') // Join with users table
        ->select(
            'ticket_descriptions.id',
            'ticket_descriptions.ticket_description',
            'ticket_descriptions.ticket_category_id',
            'users.email as user_email', // Select user email
            'ticket_descriptions.status',
            'ticket_descriptions.created_at',
            'ticket_descriptions.updated_at',
            'ticket_descriptions.deleted_at',
            'ticketcategories.category_description'
        )
        ->get();

    // Map status values for display
    foreach ($supports as $support) {
        $support->status = $support->status == 1 ? 'open' : 'closed';
    }

    // Get all categories for the dropdown
    $categories = DB::table('ticketcategories')->select('id', 'category_description')->get();

    return view('mentor.support.index', compact('categories', 'supports', 'mentorId'));
}


   

public function supportshow($id)
{
    // Retrieve the logged-in user's details
    $user = Auth::user();

    if (!$user) {
        // Handle the case where the user is not found
        return redirect()->back()->with('error', 'User not found.');
    }

    // Get the logged-in user's name
    $userName = $user->name;

    // Get the logged-in user's email
    $userEmail = $user->email;

    // Get the mentor ID from the mentors table
    $mentor = DB::table('mentors')->where('email', $userEmail)->first();
    
    if (!$mentor) {
        // Handle the case where the mentor is not found
        return redirect()->back()->with('error', 'Mentor not found.');
    }

    $mentorId = $mentor->id;

    // Get the mapped mentee IDs from the mappings table
    $menteeIds = DB::table('mappings')->where('mentorname_id', $mentorId)->pluck('menteename_id');

    if ($menteeIds->isEmpty()) {
        // Handle the case where no mentees are found
        $supports = collect(); // Empty collection
    } else {
        // Fetch the support tickets for these mentees
        $supports = DB::table('ticket_descriptions')
            ->join('ticketcategories', 'ticket_descriptions.ticket_category_id', '=', 'ticketcategories.id')
            ->join('users', 'ticket_descriptions.user_id', '=', 'users.id') // Join with users table to get email
            ->select(
                'ticket_descriptions.id',
                'ticket_descriptions.ticket_description',
                'ticket_descriptions.ticket_category_id',
                'users.email as user_email', // Select user email
                'ticket_descriptions.status',
                'ticket_descriptions.created_at',
                'ticket_descriptions.updated_at',
                'ticket_descriptions.deleted_at',
                'ticketcategories.category_description'
            )
            ->whereIn('ticket_descriptions.user_id', $menteeIds)
            ->get();
    }

    // Add the logged-in user's name to each support ticket
    foreach ($supports as $support) {
        $support->user_name = $userName;
        $support->status = $support->status == 1 ? 'open' : 'closed';
    }

    // Get all categories for the dropdown
    $categories = DB::table('ticketcategories')->select('id', 'category_description')->get();

    return view('mentor.support.index', compact('categories', 'supports'));
}

    
    public function edit($id)
    {
        // Retrieve the ticket by ID
        $support = DB::table('ticket_descriptions')->where('id', $id)->first();

        // Retrieve all categories
        $categories = DB::table('ticketcategories')->get();

        // Convert status to display value
        $support->status = $support->status == 1 ? 'open' : 'closed';

        // Pass the ticket and categories to the view
        return view('mentor.support.edit', compact('ticket', 'categories'));
    }

    public function update(Request $request, $id)
    {
        // Validate the request data
        $request->validate([
            'ticket_description' => 'required|string',
            'ticket_category_id' => 'required|integer|exists:ticketcategories,id',
            'user_id' => 'required|integer',
            'status' => 'required|string|in:open,closed',
        ]);
    
        // Use SQL query builder to update the support ticket
        DB::table('ticket_descriptions')
            ->where('id', $id)
            ->update([
                'ticket_description' => $request->input('ticket_description'),
                'ticket_category_id' => $request->input('ticket_category_id'),
                'user_id' => $request->input('user_id'),
                'status' => $request->input('status'),
                'updated_at' => now(), // Update timestamp
            ]);
    
        // Redirect or return response
        return redirect()->route('support.index')->with('success', 'Support ticket updated successfully');
    }
    
}

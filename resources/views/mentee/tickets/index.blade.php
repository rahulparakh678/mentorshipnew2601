@extends('layouts.new_mentee')

@section('content')
<style>
    .container {
        margin-top: 30px;
    }
    .header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }
    .header h2 {
        font-size: 2rem;
        color: #343a40;
    }
    .header .btn {
        background-color: #007bff;
        color: #fff;
        border: none;
        padding: 10px 20px;
        border-radius: 5px;
        cursor: pointer;
        transition: background-color 0.3s;
        font-size: 16px;
        font-weight: bold;
    }
    .header .btn:hover {
        background-color: #0056b3;
    }
    .filter {
        margin-bottom: 20px;
    }
    .table-responsive {
        background-color: #fff;
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        padding: 20px;
    }
    .table thead th {
        background-color: #007bff;
        color: #fff;
        border: none;
    }
    .table tbody tr {
        transition: background-color 0.3s;
    }
    .table tbody tr:hover {
        background-color: #f8f9fa;
    }
    .table tbody td {
        border: none;
    }
    .status-badge {
        padding: 5px 10px;
        border-radius: 5px;
        font-size: 14px;
        font-weight: bold;
    }
    .status-open {
        background-color: #28a745;
        color: #fff;
    }
    .status-closed {
        background-color: #dc3545;
        color: #fff;
    }
    .status-pending {
        background-color: #ffc107;
        color: #212529;
    }
</style>

<div class="container">
    <div class="header">
        <h2>Tickets</h2>
        <a href="{{ route('mentee.tickets.create') }}" class="btn">Create New Ticket</a>
    </div>
    
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Ticket ID</th>
                    <th>Category</th>
                    <th>Description</th>
                    <th>Status</th>
                    <th>Created on</th>
                    <th>Resolved on</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($tickets as $ticket)
                    <tr>
                        <td>{{ $ticket->id }}</td>
                        <td></td>
                        <td>{{ $ticket->ticket_description }}</td>
                        <td>
                            {{--
                            @php
                                $status = 'status-open'; // Default to open
                                if ($ticket->status == 'closed') {
                                    $status = 'status-closed';
                                } elseif ($ticket->status == 'pending') {
                                    $status = 'status-pending';
                                }
                            @endphp
                            <span class="status-badge {{ $status }}">
                                {{ ucfirst($ticket->status) }}
                            </span>--}}
                        </td>
                        <td>{{ $ticket->created_at->format('Y-m-d') }}</td>
                        <td></td>
                        <td>
                            {{--
                            <a href="{{ route('mentee.tickets.show', $ticket->id) }}" class="btn btn-sm btn-info">View</a>
                            --}}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@endsection

@section('scripts')
<script>
    // Optional JavaScript for additional functionality
</script>
@endsection

@extends('layouts.mentor')

@section('content')
@if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif


<div class="ticket-table-section">
    <h4>Ticket Details</h4>
    <table class="table table-bordered ticket-table">
        <thead class="table-dark">
            <tr>
                <th>Ticket Id</th>
                <th>User ID</th>
                <th>Category</th>
                <th>Description</th>
                <th>Status</th>
                <th>File</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($supports as $support)
                <tr>
                    <td>{{ $support->id }}</td>
                    <td>{{ $support->user_email }}</td>
                    <td>{{ $support->category_description }}</td>
                    <td>{{ $support->ticket_description }}</td>
                    <td>{{ $support->status }}</td>
                    <td>
                        @if($task->files)
                            <a href="{{ $task->files }}" target="_blank">Download</a>
                       @else
                           No File
                        @endif
                    </td>
                    <td>
                        <!-- Actions such as view, edit, delete buttons -->
                        <button type="button" class="btn btn-secondary" data-toggle="modal" data-target="#editSupportModal{{ $support->id }}">
                            Edit
                        </button>

                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<!-- Edit Support Modal -->
@foreach($supports as $support)
    <div class="modal fade" id="editSupportModal{{ $support->id }}" tabindex="-1" role="dialog" aria-labelledby="editSupportModalLabel{{ $support->id }}" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editSupportModalLabel{{ $support->id }}">Edit Support Ticket</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form method="POST" action="{{ route('support.update', $support->id) }}">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="ticket_description">Description:</label>
                            <textarea class="form-control" id="ticket_description{{ $support->id }}" name="ticket_description" rows="3" required>{{ $support->ticket_description }}</textarea>
                        </div>
                        <div class="form-group">
                            <label for="ticket_category_id">Category:</label>
                            <select class="form-control" id="ticket_category_id{{ $support->id }}" name="ticket_category_id" required>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ $support->ticket_category_id == $category->id ? 'selected' : '' }}>
                                        {{ $category->category_description }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="user_email">User Email:</label>
                            <input type="text" class="form-control" id="user_email{{ $support->id }}" name="user_email" value="{{ $support->user_email }}" readonly>
                        </div>
                        <div class="form-group">
                            <label for="status">Status:</label>
                            <select class="form-control" id="status{{ $support->id }}" name="status" required>
                                <option value="open" {{ $support->status == 'open' ? 'selected' : '' }}>Open</option>
                                <option value="closed" {{ $support->status == 'closed' ? 'selected' : '' }}>Closed</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endforeach





@endsection

@section('scripts')
<script>
    function openUpdateModal(ticket) {
        document.getElementById('ticket_id').value = ticket.id;
        document.getElementById('ticket_description').value = ticket.ticket_description;
        document.getElementById('ticket_category_id').value = ticket.ticket_category_id;
        document.getElementById('userid').value = ticket.user_id;

        $('#updateTicketModal').modal('show');
    }
</script>

@endsection
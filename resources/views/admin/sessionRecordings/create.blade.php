@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.create') }} {{ trans('cruds.sessionRecording.title_singular') }}
    </div>

    <div class="card-body">
        <form method="POST" action="{{ route('sessionsRecording.create') }}" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
    <label class="required">Select Mentor Name</label>
    <select class="form-control {{ $errors->has('mentor_id') ? 'is-invalid' : '' }}" name="mentor_id" id="mentor_id" required>
        <option value disabled {{ old('mentor_id', null) === null ? 'selected' : '' }}>
            {{ trans('global.pleaseSelect') }}
        </option>
        @foreach($mentors as $mentor)
            <option value="{{ $mentor->id }}" {{ old('mentor_id') == $mentor->id ? 'selected' : '' }}>
                {{ $mentor->name }}
            </option>
        @endforeach
    </select>
    @if($errors->has('mentor_id'))
        <div class="invalid-feedback">
            {{ $errors->first('mentor_id') }}
        </div>
    @endif
</div>

<div class="form-group">
    <label class="required" for="session_title_id">{{ trans('cruds.sessionRecording.fields.session_title') }}</label>
    <select class="form-control select2 {{ $errors->has('session_title') ? 'is-invalid' : '' }}" name="session_title_id" id="session_title_id" required>
        <option value="" disabled selected>{{ trans('global.pleaseSelect') }}</option>
    </select>
    @if($errors->has('session_title'))
        <div class="invalid-feedback">
            {{ $errors->first('session_title') }}
        </div>
    @endif
    <span class="help-block">{{ trans('cruds.sessionRecording.fields.session_title_helper') }}</span>
</div>

            <div class="form-group">
                <label class="required" for="session_video_file">{{ trans('cruds.sessionRecording.fields.session_video_file') }}</label>
                <div class="needsclick dropzone {{ $errors->has('session_video_file') ? 'is-invalid' : '' }}" id="session_video_file-dropzone">
                </div>
                @if($errors->has('session_video_file'))
                    <div class="invalid-feedback">
                        {{ $errors->first('session_video_file') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.sessionRecording.fields.session_video_file_helper') }}</span>
            </div>
            <div class="form-group">
                <button class="btn btn-danger" type="submit">
                    {{ trans('global.save') }}
                </button>
            </div>
        </form>
    </div>
</div>



@endsection

@section('scripts')
<script type="text/javascript">
   $('#mentor_id').on('change', function () {
    var mentorId = $(this).val(); // Get the selected mentor ID
    console.log("Selected Mentor ID: ", mentorId); // Debugging log

    // Reset session dropdown
    $('#session_title_id').empty().append('<option value="" disabled selected>{{ trans("global.pleaseSelect") }}</option>');

    if (mentorId) {
        // AJAX request to fetch sessions for the selected mentor
        console.log("Fetching sessions for mentor ID: " + mentorId);
        $.ajax({
            url: '/getsessions1/' + mentorId, // Ensure the correct URL
            type: 'GET',
            dataType: 'json',  // Expect JSON response
            success: function (data) {
                console.log("AJAX Response: ", data); // Log the response from the server

                // Check if sessions are returned
                if (data.sessions && data.sessions.length > 0) {
                    $.each(data.sessions, function (key, session) {
                        // Populate session dropdown with session titles
                        $('#session_title_id').append('<option value="' + session.id + '">' + session.session_title + ' - ' + session.sessiondatetime + '</option>');
                    });
                } else {
                    alert("No sessions found for this mentor.");
                }
            },
            error: function (xhr, status, error) {
                console.error("AJAX Error: ", status, error); // Log any error
                alert('Could not fetch session data.');
            }
        });
    } else {
        console.log("No mentor selected, skipping session fetch.");
    }
});


    document.getElementById('mentor_id').addEventListener('change', function () {
        const mentorId = this.value;

        // Clear the session titles dropdown
        const sessionDropdown = document.getElementById('session_title_id');
        sessionDropdown.innerHTML = '<option value="" disabled selected>{{ trans("global.pleaseSelect") }}</option>';

        // Fetch sessions for the selected mentor
        // if (mentorId) {
        //     fetch(`/sessions/${mentorId}`)
        //         .then(response => response.json())
        //         .then(data => {
        //             data.forEach(session => {
        //                 const option = document.createElement('option');
        //                 option.value = session.id;
        //                 option.textContent = session.session_title;
        //                 sessionDropdown.appendChild(option);
        //             });
        //         })
        //         .catch(error => console.error('Error fetching sessions:', error));
        // }
    });

    var uploadedSessionVideoFileMap = {}
Dropzone.options.sessionVideoFileDropzone = {
    url: '{{ route('admin.session-recordings.storeMedia') }}',
    maxFilesize: 2048, // MB
    addRemoveLinks: true,
    headers: {
      'X-CSRF-TOKEN': "{{ csrf_token() }}"
    },
    params: {
      size: 2048
    },
    success: function (file, response) {
      $('form').append('<input type="hidden" name="session_video_file[]" value="' + response.name + '">')
      uploadedSessionVideoFileMap[file.name] = response.name
    },
    removedfile: function (file) {
      file.previewElement.remove()
      var name = ''
      if (typeof file.file_name !== 'undefined') {
        name = file.file_name
      } else {
        name = uploadedSessionVideoFileMap[file.name]
      }
      $('form').find('input[name="session_video_file[]"][value="' + name + '"]').remove()
    },
    init: function () {
@if(isset($sessionRecording) && $sessionRecording->session_video_file)
          var files =
            {!! json_encode($sessionRecording->session_video_file) !!}
              for (var i in files) {
              var file = files[i]
              this.options.addedfile.call(this, file)
              file.previewElement.classList.add('dz-complete')
              $('form').append('<input type="hidden" name="session_video_file[]" value="' + file.file_name + '">')
            }
@endif
    },
     error: function (file, response) {
         if ($.type(response) === 'string') {
             var message = response //dropzone sends it's own error messages in string
         } else {
             var message = response.errors.file
         }
         file.previewElement.classList.add('dz-error')
         _ref = file.previewElement.querySelectorAll('[data-dz-errormessage]')
         _results = []
         for (_i = 0, _len = _ref.length; _i < _len; _i++) {
             node = _ref[_i]
             _results.push(node.textContent = message)
         }

         return _results
     }
}
</script>
@endsection
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\CsvImportTrait;
use App\Http\Controllers\Traits\MediaUploadingTrait;
use App\Http\Requests\MassDestroySessionRecordingRequest;
use App\Http\Requests\StoreSessionRecordingRequest;
use App\Http\Requests\UpdateSessionRecordingRequest;
use App\Session;
use App\SessionRecording;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Request;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Symfony\Component\HttpFoundation\Response;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;


class SessionRecordingController extends Controller
{
    use MediaUploadingTrait, CsvImportTrait;

    public function index(Request $request)
    {
        abort_if(Gate::denies('session_recording_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        if ($request->ajax()) {
            $query = SessionRecording::with(['session_title'])->select(sprintf('%s.*', (new SessionRecording)->table));
            $table = Datatables::of($query);

            $table->addColumn('placeholder', '&nbsp;');
            $table->addColumn('actions', '&nbsp;');

            $table->editColumn('actions', function ($row) {
                $viewGate      = 'session_recording_show';
                $editGate      = 'session_recording_edit';
                $deleteGate    = 'session_recording_delete';
                $crudRoutePart = 'session-recordings';

                return view('partials.datatablesActions', compact(
                    'viewGate',
                    'editGate',
                    'deleteGate',
                    'crudRoutePart',
                    'row'
                ));
            });

            $table->editColumn('id', function ($row) {
                return $row->id ? $row->id : '';
            });
            $table->editColumn('session_type', function ($row) {
                return $row->session_type ? SessionRecording::SESSION_TYPE_SELECT[$row->session_type] : '';
            });
            
            
            // $table->addColumn('session_title_session_title', function ($row) {
            //     return $row->session_title ? $row->session_title->session_title : '';
            // });
            $table->addColumn('session_title_session_title', function ($row) {
                return $row->session_title ? $row->session_title->session_title : 'No session title';
            });
            
            

            $table->editColumn('session_video_file', function ($row) {
                if (! $row->session_video_file) {
                    return '';
                }
                $links = [];
                foreach ($row->session_video_file as $media) {
                    $links[] = '<a href="' . $media->getUrl() . '" target="_blank">' . trans('global.downloadFile') . '</a>';
                }

                return implode(', ', $links);
            });

            $table->rawColumns(['actions', 'placeholder', 'session_title', 'session_video_file']);

            return $table->make(true);
        }

        $sessions = Session::get();

        return view('admin.sessionRecordings.index', compact('sessions'));
    }

    public function create()
    {
        // return ("entered crate method");
        
        abort_if(Gate::denies('session_recording_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $session_titles = Session::pluck('session_title', 'id')->prepend(trans('global.pleaseSelect'), '');


        $mentors = DB::table('mentors')->get();

        return view('admin.sessionRecordings.create', compact('mentors','session_titles'));
    }

    public function getSessions1($mentorId)
    {
        return response($mentorId);
    } //getsessions1

    public function getSessions($mentorId)
{
    return $mentorId;
    // Log the mentorId to ensure it's being passed correctly
    dd($mentorId);

    // Fetch sessions for the selected mentor
    $sessions = DB::table('sessions')
                  ->where('mentorname_id', $mentorId)  // Filter by mentor_id
                  ->select('id', 'session_title', 'sessiondatetime')  // Fetch relevant columns
                  ->get();

    // Debug the sessions to check if they are fetched correctly
    dd($sessions);

    // Return sessions as JSON response
    return response()->json(['sessions' => $sessions]);
}


    public function store(StoreSessionRecordingRequest $request)
    {
        $sessionRecording = SessionRecording::create($request->all());

        foreach ($request->input('session_video_file', []) as $file) {
            $sessionRecording->addMedia(storage_path('tmp/uploads/' . basename($file)))->toMediaCollection('session_video_file');
        }

        if ($media = $request->input('ck-media', false)) {
            Media::whereIn('id', $media)->update(['model_id' => $sessionRecording->id]);
        }

        return redirect()->route('admin.session-recordings.index');
    }

    public function edit(SessionRecording $sessionRecording)
    {
        abort_if(Gate::denies('session_recording_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $session_titles = Session::pluck('session_title', 'id')->prepend(trans('global.pleaseSelect'), '');

        $sessionRecording->load('session_title');

        return view('admin.sessionRecordings.edit', compact('sessionRecording', 'session_titles'));
    }

    public function update(UpdateSessionRecordingRequest $request, SessionRecording $sessionRecording)
    {
        $sessionRecording->update($request->all());

        if (count($sessionRecording->session_video_file) > 0) {
            foreach ($sessionRecording->session_video_file as $media) {
                if (! in_array($media->file_name, $request->input('session_video_file', []))) {
                    $media->delete();
                }
            }
        }
        $media = $sessionRecording->session_video_file->pluck('file_name')->toArray();
        foreach ($request->input('session_video_file', []) as $file) {
            if (count($media) === 0 || ! in_array($file, $media)) {
                $sessionRecording->addMedia(storage_path('tmp/uploads/' . basename($file)))->toMediaCollection('session_video_file');
            }
        }

        return redirect()->route('admin.session-recordings.index');
    }

    public function show(SessionRecording $sessionRecording)
    {
        abort_if(Gate::denies('session_recording_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $sessionRecording->load('session_title');

        return view('admin.sessionRecordings.show', compact('sessionRecording'));
    }

    public function destroy(SessionRecording $sessionRecording)
    {
        abort_if(Gate::denies('session_recording_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $sessionRecording->delete();

        return back();
    }

    public function massDestroy(MassDestroySessionRecordingRequest $request)
    {
        $sessionRecordings = SessionRecording::find(request('ids'));

        foreach ($sessionRecordings as $sessionRecording) {
            $sessionRecording->delete();
        }

        return response(null, Response::HTTP_NO_CONTENT);
    }

    public function storeCKEditorImages(Request $request)
    {
        abort_if(Gate::denies('session_recording_create') && Gate::denies('session_recording_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $model         = new SessionRecording();
        $model->id     = $request->input('crud_id', 0);
        $model->exists = true;
        $media         = $model->addMediaFromRequest('upload')->toMediaCollection('ck-media');

        return response()->json(['id' => $media->id, 'url' => $media->getUrl()], Response::HTTP_CREATED);
    }
}

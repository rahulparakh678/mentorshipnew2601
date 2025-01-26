@extends('layouts.mentor')

@section('content')
<div class="container mt-5">

@if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h2>{{ $chapter->chaptername }} - Quiz</h2>
                </div>
                <div class="card-body">
                    <p>Answer the following questions based on the chapter content.</p>
                    
                    <!-- Mentee Quiz Summary Section -->
                    <div class="quiz-summary mb-5">
                        <h4>Mentee Quiz Details</h4>
                        <ul class="list-group">
                            <li class="list-group-item"><strong>Mentee Name:</strong> {{ $mentee->name }}</li>
                            <li class="list-group-item"><strong>Score:</strong> {{$maxResult->score ?? 'N/A' }}</li>
                            <li class="list-group-item"><strong>Chapter Name:</strong> {{ $chapter->chaptername ?? 'N/A' }} </li>

                            {{--<li class="list-group-item"><strong>Attempts:</strong> {{ $maxResult->attempts}}</li>--}}
                            {{--<li class="list-group-item"><strong>Chapters Completed:</strong> {{ $quizResult->chapters_completed ?? '0' }}</li>--}}
                        </ul>
                    </div>

                    <!-- Discussion Questions Section -->
                    <div class="discussion-questions mb-5">
                        <h4>Discussion Points</h4>
                        @if($discussionAnswers->isEmpty())
                            <p>No discussion answers available for this chapter.</p>
                        @else
                            <ul class="list-group">
                                @foreach($discussionAnswers as $answer)
                                    <li class="list-group-item">
                                        <p><strong>Question:</strong> {{ $answer->question_text }}</p>
                                        <p><strong>Answer:</strong> {{ $answer->discussion_answer }}</p>

                                        <!-- Reply Button -->
                                        <button class="btn btn-warning btn-sm mt-2" 
                                            onclick="toggleReplyForm({{ $answer->id }})">
                                            Reply

                                        </button>

                                        <!-- Mentor's reply form (Initially Hidden) -->
                                        <form 
                                            action="{{ route('discussionanswers.reply', $answer->id) }}" 
                                            method="POST" 
                                            id="replyForm_{{ $answer->id }}" 
                                            style="display: none;" 
                                        >
                                            @csrf
                                            <div class="form-group mt-3">
                                                <label for="mentorsreply_{{ $answer->id }}">Mentor's Reply</label>
                                                <textarea 
                                                    name="mentorsreply" 
                                                    id="mentorsreply_{{ $answer->id }}" 
                                                    class="form-control" 
                                                    rows="3"
                                                >{{ $answer->mentorsreply }}</textarea>

                                                <div class="form-group">
                                                    <label for="content">{{ trans('cruds.subchapter.fields.content') }}</label>
                                                    <textarea class="form-control ckeditor {{ $errors->has('content') ? 'is-invalid' : '' }}" name="content" id="content">{!! old('content') !!}</textarea>
                                                    @if($errors->has('content'))
                                                        <div class="invalid-feedback">
                                                            {{ $errors->first('content') }}
                                                        </div>
                                                    @endif
                                                    <span class="help-block">{{ trans('cruds.subchapter.fields.content_helper') }}</span>
                                                </div>
                                            </div>
                                            <button type="submit" class="btn btn-primary mt-2">Submit Reply</button>
                                        </form>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>



                    <!-- Quiz Questions Section -->
                    <div class="quiz-questions mb-5">
                        <!-- <h4>Quiz Questions</h4> -->
                        <ul class="list-group">
                            {{--@foreach($quizQuestions as $question)
                                <li class="list-group-item">
                                    <p><strong>{{ $question->question_text }}</strong></p>
                                    <ul>
                                        @foreach($question->options as $option)
                                            <li>{{ $option }}</li>
                                        @endforeach
                                    </ul>
                                </li>
                            @endforeach--}}
                        </ul>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .container {
        margin-top: 30px;
    }
    .card {
        border: none;
        border-radius: 10px;
    }
    .card-header {
        border-radius: 10px 10px 0 0;
    }
</style>
@endsection

@section('scripts')
<script>
    document.querySelector('.btn-success').addEventListener('click', function() {
        alert('Quiz submitted!');
    });

    // show/hidden reply button
    function toggleReplyForm(answerId) {
        const replyForm = document.getElementById(`replyForm_${answerId}`);
        if (replyForm.style.display === "none" || replyForm.style.display === "") {
            replyForm.style.display = "block"; // Show the form
        } else {
            replyForm.style.display = "none"; // Hide the form
        }
    }

</script>
@endsection

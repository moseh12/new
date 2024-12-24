<form action="{{ route('quiz-questions.update',$question->id) }}" method="POST" class="form">@csrf
    @method('PUT')
    <div class="row gx-20 mb-4">
        <input type="hidden" value="{{ $quiz->id }}" name="quiz_id">
        <input type="hidden" value="0" class="is_modal">
        <div class="col-12">
            <div class="mb-4">
                <label for="question_type" class="form-label">{{__('question_type')}}</label>
                <div class="select-type-v2">
                    <select id="question_type" name="question_type"
                            class="form-select form-select-lg mb-3 without_search">
                        <option value="default" {{ $question->question_type == 'default' ? 'selected' : '' }}>{{__('default_question')}}
                        </option>
                        <option value="mcq" {{ $question->question_type == 'mcq' ? 'selected' : '' }}>{{__('multiple_choice_question')}}
                        </option>
                    </select>
                    <div class="nk-block-des text-danger">
                        <p class="question_type_error error"></p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12">
            <div class="mb-4">
                <label for="question" class="form-label">{{__('question')}} <span
                        class="text-danger">*</span></label>
                <input type="text" name="question" class="form-control rounded-2" id="question"
                       placeholder="Enter Question" value="{{ $question->question }}">
                <div class="nk-block-des text-danger">
                    <p class="question_error error"></p>
                </div>
            </div>
        </div>
        <div class="col-12 question_div">
            <div class="">
                <label for="#" class="form-label">{{__('quiz_answers')}}</label>
                <div
                    class="moveable-lit p-30 default_question_div {{ $question->question_type == 'mcq' ? 'd-none' : '' }} rounded-2 border">
                    <div class="answer_area" id="answerAreaMoved">
                        @foreach($question->answers as $key=> $answer)
                            <div class="input-group mb-20">
                                <input type="text" class="form-control" name="answers[]"
                                       placeholder="Answer" value="{{ $answer['answer'] }}">
                                <div class="custom-radio">
                                    <label>
                                        <input type="radio" name="correct_answer"
                                               value="{{ ++$key }}" {{ $answer['is_correct'] == 1 ? 'checked' : 0 }}>
                                        <span class=""></span>
                                    </label>
                                </div>
                                <span class="input-group-text"><i class="las la-trash-alt ml-3"></i></span>
                                <span class="input-group-text ansMove"><i class="las la-arrows-alt"></i></span>
                            </div>
                        @endforeach
                    </div>
                    <div class="nk-block-des text-danger">
                        <p class="answers_error error"></p>
                    </div>
                    <div class="nk-block-des text-danger">
                        <p class="correct_answer_error error"></p>
                    </div>
                    <div class="d-flex justify-content-start align-items-center mt-30">
                        <button type="button" class="btn sg-btn-outline-primary add_answer">Add Answer
                        </button>
                    </div>
                </div>
                <div
                    class="moveable-lit p-30 mcq_div {{ $question->question_type == 'mcq' ? '' : 'd-none' }} rounded-2 border">
                    <div class="answer_area">
                        @foreach($question->answers as $key=> $answer)
                            <div class="input-group mb-20">
                                <input type="hidden" name="mcq_correct_answer[]" class="mcq_correct_answer" value="{{ $answer['is_correct'] }}">
                                <input type="text" class="form-control" name="mcq_answers[]"
                                       placeholder="Answer" value="{{ $answer['answer'] }}">
                                <div class="custom-checkbox">
                                    <label>
                                        <input type="checkbox" value="1" {{ $answer['is_correct'] == 1 ? 'checked' : '' }}>
                                        <span class="ms-12"></span>
                                    </label>
                                    <span class="icon delete_icon"><i class="las la-trash-alt"></i></span>
                                </div>
                                <span class="input-group-text"><i class="las la-arrows-alt"></i></span>
                            </div>
                        @endforeach
                    </div>
                    <div class="nk-block-des text-danger">
                        <p class="mcq_answers_error error"></p>
                    </div>
                    <div class="nk-block-des text-danger">
                        <p class="mcq_correct_answer_error error"></p>
                    </div>
                    <div class="d-flex justify-content-start align-items-center mt-30">
                        <button type="button" class="btn sg-btn-outline-primary add_answer">{{__('add_answer')}}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12">
        <div class="mb-4">
            <label for="description" class="form-label">{{__('description')}}</label>
            <textarea name="description" class="form-control rounded-2" id="description" placeholder="Enter Description">{{  $question->description }}</textarea>
            <div class="nk-block-des text-danger">
                <p class="description_error error"></p>
            </div>
        </div>
    </div>
    <div class="d-flex justify-content-between align-items-center mt-30">
        <button type="button" class="btn sg-btn-outline-primary" data-bs-dismiss="modal"
                aria-label="Close">{{__('cancel')}}
        </button>
        <button type="submit" class="btn sg-btn-primary">{{__('submit')}}</button>
        @include('backend.common.loading-btn',['class' => 'btn sg-btn-primary'])
    </div>
</form>

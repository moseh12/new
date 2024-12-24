<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\ExamScriptResource;
use App\Http\Resources\Api\MeetingResource;
use App\Models\Quiz;
use App\Models\QuizAnswer;
use App\Models\QuizQuestion;
use App\Models\Result;
use App\Repositories\CourseRepository;
use App\Repositories\QuizRepository;
use App\Repositories\SectionRepository;
use App\Traits\ApiReturnFormatTrait;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class QuizeController extends Controller
{
    use ApiReturnFormatTrait;

    protected $courseRepository;

    protected $quiz;

    protected $section;

    public function __construct(CourseRepository $courseRepository, QuizRepository $quiz, SectionRepository $section)
    {
        $this->courseRepository = $courseRepository;
        $this->quiz             = $quiz;
        $this->section          = $section;
    }

    public function courseSections(Request $request): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'course_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $course   = $this->courseRepository->find($request->course_id);
            $sections = $course->sections;
            $data     = [
                'sections' => $sections,
            ];

            return $this->responseWithSuccess(__('quizzes_retrieved_successfully'), $data);
        } catch (\Exception $e) {
            return $this->responseWithError($e->getMessage());
        }
    }

    public function courseQuiz(Request $request): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'section_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $id      = $request->section_id;
            $section = $this->section->find($id);
            $quizzes = $section->quizzes;
            $data    = [
                'quizzes' => $quizzes,
            ];

            return $this->responseWithSuccess(__('questions_retrieved_successfully'), $data);
        } catch (\Exception $e) {
            return $this->responseWithError($e->getMessage());
        }
    }

    public function quizQuestions(Request $request): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'quiz_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $id        = $request->quiz_id;
            $quiz      = $this->quiz->find($id);
            $questions = QuizQuestion::whereHas('quiz', function ($q) use ($id) {
                $q->where('status', 1)->where('quiz_id', $id);
            })->get();
            $response  = [];
            foreach ($questions as $question) {
                $result['question']      = $question->question;
                $result['question_type'] = $question->question_type;
                $result['option']        = $question->answers;
                $answer                  = [];
                foreach ($question->answers as $key => $options_answer) {
                    if ($options_answer['is_correct'] == 1) {
                        array_push($answer, $key);
                    }
                    $result['correct_answer_index'] = $answer;
                }
                array_push($response, $result);
            }

            $data      = [
                'quiz'      => $quiz,
                'questions' => $response,
            ];

            return $this->responseWithSuccess(__('questions_retrieved_successfully'), $data);
        } catch (\Exception $e) {
            return $this->responseWithError($e->getMessage());
        }
    }

    public function meeting(Request $request)
    {
        try {
            $courses = $this->courseRepository->activeCourses([
                'my_course' => 1,
                'liveClass' => 1,
                'user_id'   => jwtUser()->id,
                'paginate'  => setting('paginate'),
            ], ['enrolls', 'liveClass']);

            if ($courses->previousPageUrl()) {
                if ($courses->onLastPage()) {
                    $total_results = $courses->total();
                } else {
                    $total_results = $request->page * $courses->perPage();
                }
            }

            if ($courses->onFirstPage()) {
                $total_results = $courses->count();
            }

            if (request()->ajax()) {
                $course_view = '';
                foreach ($courses as $key => $course) {
                    $vars = [
                        'course' => $course,
                        'key'    => $key,
                    ];
                    $course_view .= view('frontend.profile.components.live_class', $vars)->render();
                }

                return response()->json([
                    'success'       => true,
                    'html'          => $course_view,
                    'next_page'     => $courses->nextPageUrl(),
                    'total_results' => $total_results ?? 0,
                    'total_courses' => $courses->total(),
                ]);
            }

            $data    = [
                'courses'       => MeetingResource::collection($courses),
                'total_courses' => $courses->total(),
                'total_results' => $total_results ?? 0,
            ];

            return $this->responseWithSuccess(__('meeting_retrieved_successfully'), $data);
        } catch (\Exception $e) {
            return $this->responseWithError($e->getMessage());
        }
    }

    public function showQuizAnswer($slug, QuizRepository $quizRepository)
    {
        try {
            $quiz = $quizRepository->findBySlug($slug);
            $data = [
                'quiz' => new ExamScriptResource($quiz),
            ];

            return $this->responseWithSuccess(__('quiz_retrieved_successfully'), $data);
        } catch (\Exception $e) {
            return $this->responseWithError($e->getMessage());
        }
    }

    public function quizAnswerSubmit(Request $request)
    {
        try {
            DB::beginTransaction();
            if ($this->checkQuizSubmit($request->quiz_id)) {
                $quiz                = Quiz::with('questions', 'section.course')->findOrFail($request->quiz_id);
                foreach ($request['question_id'] as $key => $question_id) {
                    $data                     = [];
                    $answer                   = 'answers_'."$question_id";
                    $student_answer           = 'student_answer_'."$question_id";
                    $correct_answer           = 'correct_answer_'."$question_id";
                    $student_ans_var          = $request->$student_answer;
                    $student_ans              = str_replace(['"', '[', ']'], ['', '', ''], $student_ans_var);
                    $final_student_ans        = explode(',', $student_ans);
                    $var                      = $request->$correct_answer;
                    $var                      = str_replace(['"', '[', ']'], ['', '', ''], $var);
                    $final_correct_ans        = explode(',', $var);
                    $data['user_id']          = jwtUser()->id;
                    $data['quiz_question_id'] = $question_id;
                    $data['quiz_id']          = $request->quiz_id;
                    $data['answers']          = $final_student_ans;
                    $data['correct_answer']   = $final_correct_ans;
                    $data['is_correct']       = ($data['answers'] === $data['correct_answer']) ? 1 : 0;

                    $question_type            = 'question_type_'."$question_id";
                    if ($request->$question_type == 'short_question') {
                        $data['correct_answer'] = $request->$student_answer;
                    }
                    QuizAnswer::create($data);
                }
                $correct_answer      = QuizAnswer::where([
                    ['user_id', jwtUser()->id],
                    ['quiz_id', $request->quiz_id],
                ])->whereRaw(DB::raw('answers = correct_answer'))->where('answers', '!=', null)->count();

                $total_questions     = $quiz->questions->count();
                $question_mark       = $quiz->total_marks / $total_questions;
                $found_mark          = $correct_answer * $question_mark;

                $result              = new Result;
                $result->quiz_id     = $quiz->id;
                $result->slug        = $quiz->slug;
                $result->course_id   = $quiz->section->course->id;
                $result->user_id     = jwtUser()->id;
                $result->total_marks = $quiz->total_marks;
                $result->pass_marks  = $quiz->pass_marks;
                $result->your_marks  = $found_mark;
                $result->is_passed   = $found_mark >= $quiz->pass_marks ? 1 : 0;
                $result->save();
                DB::commit();

            } else {
                DB::rollBack();
                Toastr::warning(__('already_submitted'));
            }

            return $this->responseWithSuccess(__('quiz_submit_successfully'));
        } catch (\Exception $e) {
            return $this->responseWithError($e->getMessage());
        }
    }

    public function checkQuizSubmit($quize_id)
    {
        $answer = QuizAnswer::where([
            ['user_id', jwtUser()->id],
            ['quiz_id', $quize_id],
        ])->count();
        if ($answer > 0) {
            return false;
        } else {
            return true;
        }
    }

    public function myQuiz($slug, QuizRepository $quizRepository)
    {
        $quiz              = $quizRepository->findBySlug($slug);
        $quiz['questions'] = $quiz->questions;
        if (! $this->checkQuizSubmit($quiz->id)) {
            return redirect()->route('quiz-answer.show', encrypt($quiz->id));
        }
        try {
            $data = [
                'user_id' => jwtUser()->id,
                'quiz'    => $quiz,
            ];

            return $this->responseWithSuccess(__('quiz_retrieved_successfully'), $data);
        } catch (\Exception $e) {
            return $this->responseWithError($e->getMessage());
        }
    }
}

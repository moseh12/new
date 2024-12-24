<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\AssignmentCourseDataTable;
use App\DataTables\AssignmentListDataTable;
use App\DataTables\AssignmentSubmitListDataTable;
use App\Http\Controllers\Controller;
use App\Repositories\AssignmentRepository;
use App\Repositories\CategoryRepository;
use App\Repositories\OrganizationRepository;
use App\Repositories\SubmitedAssignmentRepository;
use App\Repositories\UserRepository;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AssignmentController extends Controller
{
    protected $assignment;

    protected $organization;

    protected $user;

    protected $category;

    protected $submittedAssignmentRepo;

    public function __construct(OrganizationRepository $organization, CategoryRepository $category, UserRepository $user, AssignmentRepository $assignment, SubmitedAssignmentRepository $submittedAssignmentRepo)
    {
        $this->assignment              = $assignment;
        $this->organization            = $organization;
        $this->user                    = $user;
        $this->category                = $category;
        $this->submittedAssignmentRepo = $submittedAssignmentRepo;
    }

    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'title'         => ['required', Rule::unique('assignments')->where(function ($query) use ($request) {
                $query->where('course_id', $request->course_id);
            })->ignore('id')],
            'deadline'      => 'required',
            'instructor_id' => 'required',
            'total_marks'   => 'required',
            'pass_marks'    => 'required',
        ]);
        if (config('app.demo_mode')) {
            $data = [
                'status' => 'danger',
                'error'  => __('this_function_is_disabled_in_demo_server'),
                'title'  => 'error',
            ];

            return response()->json($data);
        }

        try {
            $this->assignment->store($request->all());
            Toastr::success(__('create_successful'));

            return response()->json([
                'success' => __('create_successful'),
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
    }

    public function edit($id): \Illuminate\Http\JsonResponse
    {
        try {
            $user       = new UserRepository();
            $assignment = $this->assignment->find($id);
            $course     = $assignment->course;

            $data       = [
                'assignment'  => $assignment,
                'sections'    => $course->sections,
                'lesson'      => $assignment->lesson,
                'instructors' => $user->findUsers([
                    'organization_id' => $course->organization_id,
                ]),
            ];

            $data       = [
                'html' => view('backend.admin.course.assignment.edit', $data)->render(),
            ];

            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'title'         => ['required', Rule::unique('assignments')->where(function ($query) use ($request, $id) {
                $query->where('course_id', $request->course_id)->where('id', '!=', $id);
            })->ignore('id')],
            'deadline'      => 'required',
            'instructor_id' => 'required',
            'total_marks'   => 'required',
            'pass_marks'    => 'required',
        ]);
        if (config('app.demo_mode')) {
            $data = [
                'status' => 'danger',
                'error'  => __('this_function_is_disabled_in_demo_server'),
                'title'  => 'error',
            ];

            return response()->json($data);
        }

        try {
            $this->assignment->update($request->all(), $id);

            Toastr::success(__('update_successful'));

            return response()->json([
                'success' => __('update_successful'),
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
    }

    public function destroy($id): \Illuminate\Http\JsonResponse
    {
        if (config('app.demo_mode')) {
            $data = [
                'status'  => 'danger',
                'message' => __('this_function_is_disabled_in_demo_server'),
                'title'   => 'error',
            ];

            return response()->json($data);
        }
        try {
            $this->assignment->destroy($id);

            Toastr::success(__('delete_successful'));
            $data = [
                'status'  => 'success',
                'message' => __('delete_successful'),
                'title'   => __('success'),
            ];

            return response()->json($data);
        } catch (\Exception $e) {
            $data = [
                'status'  => 'danger',
                'message' => $e->getMessage(),
                'title'   => __('error'),
            ];

            return response()->json($data);
        }
    }

    public function assignmentsCourses(AssignmentCourseDataTable $dataTable, Request $request, $org_id = null)
    {
        try {

            $organization  = $this->organization->find($org_id ?? $request->organization_id);

            $instructor    = $request->organization_id ? $this->user->findUsers([
                'organization_id' => $request->organization_id,
            ]) : [];

            $categories    = $request->category_ids ? $this->category->activeCategories([
                'ids'  => $request->category_ids,
                'type' => 'course',
            ]) : [];

            $data          = [
                'organization'    => $organization,
                'instructors'     => $instructor,
                'categories'      => $categories,
                'status'          => $request->status,
                'organization_id' => $request->organization_id,
                'instructor_ids'  => $request->instructor_ids,
            ];

            $filtered_data = [
                'instructor_ids' => $request->instructor_ids,
                'category_ids'   => $request->category_ids,
                'org_id'         => $org_id ?? $request->organization_id,
                'status'         => $request->status,
            ];

            return $dataTable->with($filtered_data)->render('backend.admin.course.assignment.course_list', $data);
        } catch (\Exception $e) {
            Toastr::error($e->getMessage());

            return back()->withInput();
        }
    }

    public function assignmentsList($id, AssignmentListDataTable $dataTable)
    {
        try {
            return $dataTable->with('course_id', $id)->render('backend.admin.course.assignment.assignment_list');
        } catch (\Exception $e) {
            Toastr::error($e->getMessage());

            return back();
        }
    }

    public function studentList($id, AssignmentSubmitListDataTable $dataTable)
    {
        try {
            return $dataTable->with('assignment_id', $id)->render('backend.admin.course.assignment.submitted_assignment.index');
        } catch (\Exception $e) {
            Toastr::error($e->getMessage());

            return back();
        }
    }

    public function assignmentMarks(Request $request, $id): \Illuminate\Http\JsonResponse
    {
        if (config('app.demo_mode')) {
            $data = [
                'status' => 'danger',
                'error'  => __('this_function_is_disabled_in_demo_server'),
                'title'  => 'error',
            ];

            return response()->json($data);
        }
        $request->validate([
            'marks' => 'required|numeric|min:1|max:100',
        ]);

        try {
            $this->submittedAssignmentRepo->marksUpdate($request->all(), $id);

            Toastr::success(__('update_successful'));

            return response()->json([
                'success' => __('update_successful'),
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
    }
}

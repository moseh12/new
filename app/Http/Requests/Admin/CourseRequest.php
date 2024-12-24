<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class CourseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        $rules = [
            'title'           => 'required',
            'category_id'     => 'required',
            'language_id'     => 'required',
            'level_id'        => 'required',
            'organization_id' => 'required',
            'instructor_ids'  => 'required',
            'duration'        => 'required',
            'price'           => 'required_without:is_free',
            'discount_type'   => 'required_with:is_discountable',
            'discount'        => 'required_with:is_discountable',
            'discount_period' => 'required_with:is_discountable',
            'renew_after'     => 'required_with:is_renewable',
            'meta_image'      => 'nullable|integer',
        ];

        if ($this->isMethod('put')) {
            $rules['meeting_title']    = 'required_if:course_type,==,live_class';
            $rules['meeting_method']   = 'required_if:course_type,==,live_class';
            $rules['meeting_type']     = 'required_if:course_type,==,live_class';
            $rules['meeting_interval'] = 'nullable|in:weekly,daily|required_if:meeting_type,==,recurring';
            $rules['days']             = 'nullable|required_if:meeting_interval,==,weekly';
            $rules['start_time']       = 'required_if:course_type,==,live_class';
            $rules['end_time']         = 'nullable|required_if:meeting_type,==,recurring';
        }
        if ($this->isMethod('post')) {
            $rules['video'] = 'required_if:video_source,upload';
        }

        return $rules;
    }
}

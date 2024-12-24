<ul class="d-flex gap-30 justify-content-end align-items-center">
    @if(hasPermission('courses.edit'))
        <li>
            <a href="{{ route('courses.edit', $course->id) }}"><i class="las la-edit"></i></a>
        </li>
    @endif
    @if(hasPermission('course.destroy'))
        <li>
            <a href="javascript:void(0)"
               onclick="delete_row('{{ route('courses.destroy', $course->id) }}')"
               data-toggle="tooltip"
               data-original-title="{{ __('delete') }}"><i class="las la-trash-alt"></i></a>
        </li>
    @endif


    <div class="dropdown">
        <a class="dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="las la-ellipsis-v"></i>
        </a>
        <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="{{route('course.details', $course->slug)}}">{{ __('visit_course') }}</a>
            </li>
            @if(hasPermission('course.students'))
                <li><a class="dropdown-item"
                       href="{{ route('course.students',$course->id) }}">{{ __('manage_student') }}</a></li>
            @endif
            @if(hasPermission('courses.edit'))
                <li><a class="dropdown-item"
                       href="{{ route('courses.edit',[$course->id,'tab'=> 'curriculum']) }}">{{ __('curriculum') }}</a>
                </li>
                <li><a class="dropdown-item"
                       href="{{ route('courses.edit',[$course->id,'tab'=>'assignment']) }}">{{ __('assignment') }}</a>
                </li>
                <li><a class="dropdown-item"
                       href="{{ route('courses.edit',[$course->id,'tab'=>'faq']) }}">{{ __('faq') }}</a></li>
            @endif
        </ul>
    </div>
</ul>

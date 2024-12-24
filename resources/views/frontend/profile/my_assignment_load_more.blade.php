@php
    $submit_info = App\Models\SubmitedAssignment::where('user_id', auth()->user()->id)->where('assignment_id', $assignment->id)->first();
@endphp
<tr>
    <td>
        <div class="assignment-item">
            <h6>{{__($assignment->title)}}</h6>
            <p>{{__($assignment->instructor->first_name)}} {{__($assignment->instructor->last_name)}}</p>
        </div>
    </td>
    <td>{{ date('d M Y', strtotime($assignment->created_at)) }}</td>
    <td>{{ date('d M Y H:i A', strtotime($assignment->deadline)) }}</td>
    @if($submit_info)
        @if($submit_info->marks >0)
            <td>{{ $submit_info->marks }}</td>
        @else
            <td> {{__('pending')}}  </td>
        @endif
    @else
        <td><span class="color-supernova">{{__('not_submitted')}}</span></td>
    @endif

    @if($submit_info)
        @if($submit_info->status == 0)
            <td> {{__('pending')}}  </td>
        @elseif($submit_info->status == 1)
            <td><span class="color-secondary">{{__('pass')}}</span></td>
        @elseif($submit_info->status == 2)
            <td><span class="text-danger">{{__('fail')}}</span></td>
        @endif

    @else
        <td><span class="color-supernova">{{__('not_submitted')}}</span></td>
    @endif
    @if($submit_info)
        <td>
            <a href="{{route('assignment.details', $assignment->slug)}}" class="template-btn">{{__('view')}}</a>
        </td>
    @else
        <td>
            @if (strtotime($assignment->deadline) > strtotime(now()))
                <a href="{{ route('assignment.details', $assignment->slug) }}"
                   class="template-btn">{{ __('submit_now') }}</a>
            @else
                <p class="text-danger">{{ __('declined') }}</p>
            @endif

        </td>
    @endif

</tr>


<ul class="d-flex gap-30 justify-content-end">
    @if(hasPermission('subjects.edit'))
        <li>
            <a href="{{ route('subjects.edit',$subject->id) }}"><i
                    class="las la-edit"></i></a>
        </li>
    @endif
    @if(hasPermission('subjects.destroy'))
        <li>
            <a href="javascript:void(0)"
               onclick="delete_row('{{ route('subjects.destroy', $subject->id) }}')"
               data-toggle="tooltip"
               data-original-title="{{ __('delete') }}"><i class="las la-trash-alt"></i></a>
        </li>
    @endif
</ul>

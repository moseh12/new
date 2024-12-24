<ul class="d-flex gap-30 justify-content-end align-items-center">

    @if (hasPermission('staffs.edit'))
        <li>
            <a class="edit_modal"
               href="{{ route('organizations.staff.edit', ['org_id' => request()->route('org_id'), 'id' => $user->id]) }}"><i
                    class="las la-edit"></i></a>
        </li>
    @endif
    @if (hasPermission('staffs.destroy'))
        <li>
            <a href="javascript:void(0)"
               onclick="delete_row('{{ route('organizations.staff.delete', ['org_id' => request()->route('org_id'), 'id' => $user->id])}}')"
               data-toggle="tooltip"
               data-original-title="{{ __('delete') }}"><i class="las la-trash-alt"></i></a>
        </li>
    @endif


    <div class="dropdown">

        <a class="dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="las la-ellipsis-v"></i>
        </a>

        <ul class="dropdown-menu">
            @if (!empty($user->email_verified_at))
                <li><a class="dropdown-item"
                       href="{{ route('users.verified', $user->id) }}">{{ __('unverified_staff') }}</a>
                </li>
            @else
                <li><a class="dropdown-item"
                       href="{{ route('users.verified', $user->id) }}">{{ __('verified_staff') }}</a>
                </li>
            @endif
            @if ($user->is_user_banned == 0)
                <li><a class="dropdown-item"
                       href="{{ route('users.ban', $user->id) }}">{{ __('ban_this_staff') }}</a>
                </li>
            @else
                <li><a class="dropdown-item"
                       href="{{ route('users.ban', $user->id) }}">{{ __('active_this_staff') }}</a>
                </li>
            @endif
        </ul>
    </div>
</ul>

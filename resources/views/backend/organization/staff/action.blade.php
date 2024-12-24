<ul class="d-flex gap-30 justify-content-center align-items-center">
    @if(hasPermission('manage_staff'))
        <li>
            <a class="edit_modal" href="{{ route('organization.staff.edit', $user->id) }}"><i
                    class="las la-edit"></i></a>
        </li>
    @endif
    @if(hasPermission('manage_staff'))
        @if(auth()->user()->user_type == 'admin')
            <div class="dropdown">
                <a class="dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="las la-ellipsis-v"></i>
                </a>
                <ul class="dropdown-menu">
                    @if (!empty($user->email_verified_at))
                        <li><a class="dropdown-item"
                               href="{{ route('organization.users.verified', $user->id) }}">{{ __('unverified_staff') }}</a>
                        </li>
                    @else
                        <li><a class="dropdown-item"
                               href="{{ route('organization.users.verified', $user->id) }}">{{ __('verified_staff') }}</a>
                        </li>
                    @endif
                    @if ($user->is_user_banned == 0)
                        <li><a class="dropdown-item"
                               href="{{ route('organization.users.ban', $user->id) }}">{{ __('ban_this_staff') }}</a>
                        </li>
                    @else
                        <li><a class="dropdown-item"
                               href="{{ route('organization.users.ban', $user->id) }}">{{ __('active_this_staff') }}</a>
                        </li>
                    @endif
                </ul>
            </div>
        @endif
    @endif
</ul>

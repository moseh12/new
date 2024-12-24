@extends('frontend.layouts.master')
@section('title', __('meeting_details'))
@section('content')
    <!--====== Start Purchase Courses Section ======-->
    <section class="purchase-courses-section p-t-50 p-b-50 p-b-md-20 p-t-sm-30">
        <div class="container container-1278">
            <div class="row">
                @include('frontend.profile.sidebar')
                <div class="col-md-8">
                    <div class="purchase-courses-wrapper">
                        <div class="row">
                            <div class="col-12">
                                <div class="section-title-v3 color-dark m-b-40 m-b-sm-20">
                                    <h3>{{__('meeting_details') }}</h3>
                                    <p>{{__('Showing') }} <span
                                            class="total_results">{{ $total_results }}</span> {{__('of')  }} <span
                                            class="total_courses">{{ $total_courses }}</span> {{__('Results') }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="course-items-wrap">
                            <div class="row course-items-v3 course_section_wrap">
                                @foreach($courses as $key=> $course)
                                    @if($course->course_type == 'live_class')
                                        @include('frontend.profile.components.live_class')
                                    @endif
                                @endforeach
                            </div>
                            @if($courses->nextPageUrl())
                                <div class="course-pagination text-align-center">
                                    <a data-page="{{ $courses->currentPage() }}"
                                       data-url="{{ route('meetings') }}" href="javascript:void(0)"
                                       class="template-btn load_more">{{__('more_course') }}
                                        <i class="fas fa-long-arrow-right"></i></a>
                                    @include('components.frontend_loading_btn', ['class' => 'template-btn see-more'])
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
@push('js')
    <script>
        let page = 2;
        $(document).ready(function () {
            $(document).on('click', '.load_more', function () {
                let that = this;
                let url = $(this).data('url');
                let selector = $(this).closest('.course-pagination');
                $(that).addClass('d-none');
                $(selector).find('.loading_button').removeClass('d-none');
                $.ajax({
                    url: url,
                    type: "GET",
                    data: {
                        page: page,
                    },
                    success: function (data) {
                        $(selector).find('.loading_button').addClass('d-none');

                        if (data.success) {
                            if (data.next_page) {
                                $(that).removeClass('d-none');
                            }
                            page++;
                            $('.course_section_wrap').append(data.html);
                            $('.total_results').text(data.total_results);
                            $('.total_courses').text(data.total_courses);
                        } else {
                            $(that).removeClass('d-none');
                            $(selector).find('.loading_button').addClass('d-none');
                            toastr.error(data.error);
                        }
                    }
                });
            });
        });
    </script>
@endpush

<?php

namespace App\Repositories;

use App\Models\Category;
use App\Models\Course;
use App\Models\CourseProgress;
use App\Models\Enroll;
use App\Models\LiveClass;
use App\Traits\ApiReturnFormatTrait;
use App\Traits\ImageTrait;
use Carbon\Carbon;
use Illuminate\Support\Str;

class CourseRepository
{
    use ApiReturnFormatTrait;
    use ImageTrait;

    public function all()
    {
        return Course::orderByDesc('id')->paginate(setting('paginate'));
    }

    public function store($request)
    {
        if (arrayCheck('image_media_id', $request)) {
            $request['image'] = $this->getImageWithRecommendedSize($request['image_media_id'], '402', '248', true);
        }

        if (arrayCheck('meta_image', $request)) {
            $request['meta_image'] = $this->getImageWithRecommendedSize($request['meta_image'], '1200', '630', true);
        } else {
            $request['meta_image'] = getArrayValue('image', $request);
        }

        if (! arrayCheck('meta_title', $request)) {
            $request['meta_title'] = $request['title'];
        }

        if (! arrayCheck('meta_keywords', $request)) {
            $request['meta_keywords'] = Str::slug($request['title']);
        }

        if (! arrayCheck('meta_description', $request)) {
            $request['meta_description'] = arrayCheck('short_description', $request) ? $request['short_description'] : $request['title'];
        }

        if (arrayCheck('video_source', $request) && arrayCheck('video', $request) && $request['video_source'] == 'upload') {
            $request['video'] = $this->saveFile($request['video'], 'pos_file', false);
        }
        if (arrayCheck('video_source', $request) && $request['video_source'] != 'upload') {
            $request['video'] = $request['video_link'];
        }

        if (arrayCheck('discount_period', $request)) {
            $dates                        = explode(' - ', $request['discount_period']);
            $request['discount_start_at'] = Carbon::parse($dates[0])->startOfDay();
            $request['discount_end_at']   = Carbon::parse($dates[1])->endOfDay();
        }

        $request['user_id'] = auth()->id();

        $request['slug']    = getSlug('courses', $request['title']);

        if (arrayCheck('is_free', $request) && $request['is_free'] == 1) {
            $request['price'] = 0;
        } else {
            $request['price'] = priceFormatUpdate($request['price'], setting('default_currency'));
        }

        $course             = Course::create($request);
        $course->users()->sync($request['instructor_ids']);

        $course->category->increment('total_courses');

        return $course;
    }

    public function update($request, $id)
    {
        $increment                  = false;
        $course                     = Course::findOrfail($id);
        if (arrayCheck('category_id', $request) && $course->category_id != $request['category_id']) {
            $increment = true;
            $course->category->decrement('total_courses');
        }

        if (arrayCheck('image_media_id', $request)) {
            $request['image'] = $this->getImageWithRecommendedSize($request['image_media_id'], '402', '248', true);
        }

        if (! arrayCheck('meta_title', $request) && arrayCheck('title', $request)) {
            $request['meta_title'] = $course->meta_title ?: $request['title'];
        }

        if (! arrayCheck('meta_keywords', $request) && arrayCheck('title', $request)) {
            $request['meta_keywords'] = $course->meta_keywords ?: Str::slug($request['title']);
        }

        if (! arrayCheck('meta_description', $request) && arrayCheck('title', $request)) {
            $request['meta_description'] = $course->meta_description ?: (arrayCheck('short_description', $request) ? $request['short_description'] : $request['title']);
        }

        if (arrayCheck('meta_image', $request)) {
            $request['meta_image'] = $this->getImageWithRecommendedSize($request['meta_image'], '1200', '630', true);
        } else {
            $request['meta_image'] = getArrayValue('image', $request);
        }

        if (arrayCheck('video_source', $request) && arrayCheck('video', $request) && $request['video_source'] == 'upload') {
            $request['video'] = $this->saveFile($request['video'], 'pos_file', false);
        }
        if (arrayCheck('video_source', $request) && $request['video_source'] != 'upload') {
            $request['video'] = $request['video_link'];
        }

        if (arrayCheck('discount_period', $request)) {
            $dates                        = explode(' - ', $request['discount_period']);
            $request['discount_start_at'] = Carbon::parse($dates[0])->startOfDay();
            $request['discount_end_at']   = Carbon::parse($dates[1])->endOfDay();
        }
        if (arrayCheck('is_free', $request) && $request['is_free'] == 1) {
            $request['price'] = 0;
        } else {
            $request['price'] = priceFormatUpdate($request['price'], setting('default_currency'));
        }

        if (arrayCheck('title', $request)) {
            $request['slug'] = getSlug('courses', $request['title'], 'slug', $course->id);
        }

        if (isset($request['save_and_published'])) {
            $request['is_published'] = 1;
        }

        $course->users()->sync($request['instructor_ids']);

        $request['is_discountable'] = arrayCheck('is_discountable', $request) ? $request['is_discountable'] : '0';
        $request['is_free']         = arrayCheck('is_free', $request) ? $request['is_free'] : '0';
        $request['is_renewable']    = arrayCheck('is_renewable', $request) ? $request['is_renewable'] : '0';
        $request['is_private']      = arrayCheck('is_private', $request) ? $request['is_private'] : '0';
        if ($request['course_type'] === 'live_class') {
            if (setting('zoom_client_id') && setting('zoom_secret_key')) {
                $this->createMeetings($request, $course);
            } else {
                throw new \Exception('You need Zoom settings configured.');
            }
        }
        $course->update($request);

        if ($increment) {
            $category = Category::findOrfail($request['category_id']);
            $category->increment('total_courses');
        }

        return $course;
    }

    protected function generateZoomToken(): string
    {
        try {
            //client_id:secret_id
            //            $base64_string = base64_encode('6aG6TykjQqyOt8sVDEOPSQ:ftJQE2s8tNILIYffEkGr8RWp0r5sBRX6');
            //            $account_id    = 's4iTDKUrR0mXX3MvZt05Pw';
            $zoom_id       = setting('zoom_client_id').':'.setting('zoom_secret_key');
            $base64_string = base64_encode($zoom_id);
            $account_id    = setting('zoom_account_id');
            $headers       = [
                'Content-Type'  => 'application/x-www-form-urlencoded',
                'Authorization' => "Basic $base64_string",
            ];

            $response      = httpRequest("https://zoom.us/oauth/token?grant_type=account_credentials&account_id=$account_id", [], $headers);

            return $response['access_token'];

        } catch (\Throwable $th) {
            throw $th;
        }
    }

    //    protected function createMeetings($data, $course)
    //    {
    //        $meeting_link    = $response_data = [];
    //        $start_time      = Carbon::parse($data['start_time']);
    //        $live_class      = LiveClass::where('course_id', $course->id)->first();
    //        if ($live_class && $start_time == $live_class->start_time) {
    //            if ($live_class->meeting_method == 'zoom') {
    //                $is_zoom_update        = false;
    //                $meeting_link['zoom']  = $live_class->meeting_link['zoom'];
    //                $response_data['zoom'] = $live_class->data['zoom'];
    //            } else {
    //                $is_zoom_update = $data['meeting_method'] == 'zoom';
    //            }
    //            if ($live_class->meeting_method == 'google_meet') {
    //                $is_google_update             = false;
    //                $meeting_link['google_meet']  = $live_class->meeting_link['google_meet'];
    //                $response_data['google_meet'] = $live_class->data['google_meet'];
    //            } else {
    //                $is_google_update = $data['meeting_method'] == 'google_meet';
    //            }
    //        } else {
    //            $is_zoom_update   = $data['meeting_method'] == 'zoom';
    //            $is_google_update = $data['meeting_method'] == 'google_meet';
    //        }
    //        if ($data['meeting_method'] == 'zoom' && $is_zoom_update) {
    //            $token                 = $this->generateZoomToken();
    //            $headers               = [
    //                'Authorization' => 'Bearer '.$token,
    //                'Content-Type'  => 'application/json',
    //            ];
    //            //2017-11-25T12:00:00Z
    //            $fields                = [
    //                'topic'      => $data['meeting_title'],
    //                'type'       => 2,
    //                'start_time' => Carbon::parse($data['start_time'])->toIso8601String(),
    //                'duration'   => $data['meeting_duration'],
    //            ];
    //            if ($data['meeting_type'] == 'recurring') {
    //                $fields['recurrence']['end_date_time'] = Carbon::parse($data['end_time'])->toIso8601String();
    //                if ($data['meeting_interval'] == 'daily') {
    //                    $fields['recurrence']['type'] = 1;
    //                } elseif ($data['meeting_interval'] == 'weekly') {
    //                    $fields['recurrence']['type']        = 2;
    //                    $fields['recurrence']['weekly_days'] = implode(',', $data['days']);
    //                }
    //
    //            }
    //            $response              = httpRequest('https://api.zoom.us/v2/users/me/meetings', $fields, $headers);
    //            $meeting_link          = [
    //                'zoom' => [
    //                    'start_url' => $response['start_url'],
    //                    'join_url'  => $response['join_url'],
    //                ],
    //            ];
    //            $response_data['zoom'] = $response;
    //        } elseif ($data['meeting_method'] == 'google_meet' && $is_google_update) {
    //            $client               = new Client;
    //            $client->setAuthConfig(config('google-calendar.auth_profiles.oauth.credentials_json'));
    //            $client->setAccessToken(json_decode(file_get_contents(config('google-calendar.auth_profiles.oauth.token_json')), true));
    //
    //            $calendarService      = new Calendar($client);
    //            $until_date           = Carbon::parse($data['end_time'])->format('Ymd\THis\Z');
    //            // Create a new event
    //            $event_data           = [
    //                'summary'        => $data['meeting_title'],
    //                'start'          => new EventDateTime([
    //                    'dateTime' => $start_time->format('Y-m-d\TH:i:s'),
    //                    'timeZone' => config('app.timezone'),
    //                ]),
    //                'end'            => new EventDateTime([
    //                    'dateTime' => $start_time->addMinutes($data['meeting_duration'])->format('Y-m-d\TH:i:s'),
    //                    'timeZone' => config('app.timezone'),
    //                ]),
    //                'conferenceData' => [
    //                    'createRequest' => [
    //                        'requestId'             => uniqid(),
    //                        'conferenceSolutionKey' => [
    //                            'type' => 'hangoutsMeet',
    //                        ],
    //                    ],
    //                ],
    //                'reminders'      => new EventReminders([
    //                    'useDefault' => true,
    //                ]),
    //            ];
    //            if ($data['meeting_type'] == 'recurring') {
    //                if ($data['meeting_interval'] == 'daily') {
    //                    $event_data['recurrence'] = ["RRULE:FREQ=DAILY;UNTIL=$until_date"];
    //                } elseif ($data['meeting_interval'] == 'weekly') {
    //                    $days                     = [];
    //                    foreach ($data['days'] as $day) {
    //                        if ($day == 1) {
    //                            $days[] = 'SU';
    //                        } elseif ($day == 2) {
    //                            $days[] = 'MO';
    //                        } elseif ($day == 3) {
    //                            $days[] = 'TU';
    //                        } elseif ($day == 4) {
    //                            $days[] = 'WE';
    //                        } elseif ($day == 5) {
    //                            $days[] = 'TH';
    //                        } elseif ($day == 6) {
    //                            $days[] = 'FR';
    //                        } elseif ($day == 7) {
    //                            $days[] = 'SA';
    //                        }
    //                    }
    //                    $days                     = implode(',', $days);
    //                    $event_data['recurrence'] = ["RRULE:FREQ=WEEKLY;BYDAY=$days;UNTIL=$until_date"];
    //                }
    //            }
    //            $event                = new Event($event_data);
    //
    //            $event                = $calendarService->events->insert('primary', $event, ['conferenceDataVersion' => 1]);
    //            $response             = $event->toSimpleObject();
    //            $event['google_meet'] = $response;
    //            $meeting_link         = [
    //                'google_meet' => [
    //                    'join_url' => $response->hangoutLink,
    //                ],
    //            ];
    //        }
    //
    //        $live_class_data = [
    //            'user_id'          => auth()->id(),
    //            'course_id'        => $course->id,
    //            'title'            => $data['meeting_title'],
    //            'slug'             => getSlug('live_classes', $data['meeting_title']),
    //            'meeting_method'   => $data['meeting_method'],
    //            'start_time'       => Carbon::parse($data['start_time'])->format('Y-m-d H:i:s'),
    //            'end_time'         => $data['meeting_type']     == 'recurring' ? Carbon::parse($data['start_time'])->format('Y-m-d H:i:s') : null,
    //            'duration'         => $data['meeting_duration'],
    //            'meeting_link'     => $meeting_link,
    //            'data'             => $response_data,
    //            'meeting_type'     => $data['meeting_type'],
    //            'meeting_interval' => $data['meeting_interval'],
    //            'days'             => $data['meeting_interval'] == 'weekly' ? $data['days'] : [],
    //            'status'           => 1,
    //        ];
    //        if ($live_class) {
    //            $live_class->update($live_class_data);
    //        } else {
    //            LiveClass::create($live_class_data);
    //        }
    //    }

    protected function createMeetings($data, $course)
    {
        $meeting_link    = $response_data = [];
        $start_time      = Carbon::parse($data['start_time']);
        $live_class      = LiveClass::where('course_id', $course->id)->first();

        if ($live_class && $start_time == $live_class->start_time) {
            $is_zoom_update        = $live_class->meeting_method == 'zoom';
            $meeting_link['zoom']  = $live_class->meeting_link['zoom'];
            $response_data['zoom'] = $live_class->data['zoom'];
        } else {
            $is_zoom_update = $data['meeting_method'] == 'zoom';
        }

        if ($data['meeting_method'] == 'zoom' && $is_zoom_update) {
            $token                 = $this->generateZoomToken();
            $headers               = [
                'Authorization' => 'Bearer '.$token,
                'Content-Type'  => 'application/json',
            ];
            $fields                = [
                'topic'      => $data['meeting_title'],
                'type'       => 2,
                'start_time' => Carbon::parse($data['start_time'])->toIso8601String(),
                'duration'   => $data['meeting_duration'],
            ];
            if ($data['meeting_type'] == 'recurring') {
                $fields['recurrence']['end_date_time'] = Carbon::parse($data['end_time'])->toIso8601String();
                if ($data['meeting_interval'] == 'daily') {
                    $fields['recurrence']['type'] = 1;
                } elseif ($data['meeting_interval'] == 'weekly') {
                    $fields['recurrence']['type']        = 2;
                    $fields['recurrence']['weekly_days'] = implode(',', $data['days']);
                }
            }

            $response              = httpRequest('https://api.zoom.us/v2/users/me/meetings', $fields, $headers);
            $meeting_link          = [
                'zoom' => [
                    'start_url' => $response['start_url'],
                    'join_url'  => $response['join_url'],
                ],
            ];
            $response_data['zoom'] = $response;
        }

        $live_class_data = [
            'user_id'          => auth()->id(),
            'course_id'        => $course->id,
            'title'            => $data['meeting_title'],
            'slug'             => getSlug('live_classes', $data['meeting_title']),
            'meeting_method'   => $data['meeting_method'],
            'start_time'       => Carbon::parse($data['start_time'])->format('Y-m-d H:i:s'),
            'end_time'         => $data['meeting_type']     == 'recurring' ? Carbon::parse($data['start_time'])->format('Y-m-d H:i:s') : null,
            'duration'         => $data['meeting_duration'],
            'meeting_link'     => $meeting_link,
            'data'             => $response_data,
            'meeting_type'     => $data['meeting_type'],
            'meeting_interval' => $data['meeting_interval'],
            'days'             => $data['meeting_interval'] == 'weekly' ? $data['days'] : [],
            'status'           => 1,
        ];

        if ($live_class) {
            $live_class->update($live_class_data);
        } else {
            LiveClass::create($live_class_data);
        }
    }

    public function find($id)
    {
        return Course::withAvg('reviews', 'rating')->withCount('reviews')->withCount('enrolls')->find($id);
    }

    public function CourseFindBySlug($slug)
    {
        return Course::with('sections.quizzes.quizAnswer')->withCount('enrolls')->where('slug', $slug)->first();
    }

    public function findBySlug($slug = '', $is_my_course = false)
    {
        return Course::with('wishlists')->where('slug', $slug)->when($is_my_course, function ($query) {
            $query->whereHas('enrolls.checkout', function ($query) {
                $query->where('user_id', auth()->id());
            });
        })->withCount('enrolls')->first();
    }

    public function destroy($id)
    {
        $course = Course::findOrFail($id);
        if ($course->category) {
            $course->category->decrement('total_courses');
        }

        if ($course->video_source == 'upload' && $course->video) {
            $this->deleteFile($course->video);
        }

        return $course->delete();
    }

    public function updateProgress($request): bool
    {
        $progress            = CourseProgress::where('user_id', authUser()->id)
            ->where('course_id', $request['course_id'])->where('section_id', $request['section_id'])
            ->where('lesson_id', $request['lesson_id'])->first();

        if ($progress) {
            if ($request['total_spent_time'] == 0 || $request['total_duration'] == 0) {
                $progress_in_percentage = 0;
            } else {
                $progress_in_percentage = ($request['total_spent_time'] / $request['total_duration']) * 100;
            }

            if ($request['total_spent_time'] >= $progress->total_spent_time) {
                $progress->update([
                    'total_duration'   => $request['total_duration'],
                    'total_spent_time' => $request['total_spent_time'],
                    'progress'         => round($progress_in_percentage, 2),
                    'status'           => $progress_in_percentage >= 60 ? 1 : 0,
                ]);
            }
        } else {
            $progress = CourseProgress::create([
                'user_id'          => authUser()->id,
                'course_id'        => $request['course_id'],
                'section_id'       => $request['section_id'],
                'lesson_id'        => $request['lesson_id'],
                'total_duration'   => $request['total_duration'],
                'total_spent_time' => $request['total_spent_time'],
                'progress'         => 0,
            ]);
        }

        $progress_percentage = CourseProgress::where('user_id', authUser()->id)
            ->where('course_id', $request['course_id'])
            ->sum('progress');

        $enroll              = Enroll::whereHas('checkout', function ($query) {
            $query->where('user_id', authUser()->id);
        })->where('enrollable_id', $request['course_id'])->first();

        if ($enroll) {
            $enroll->update([
                'complete_count' => $progress_percentage / count($progress->course->lessons),
            ]);
        }

        return true;
    }

    public function findCourses($ids, $with = [])
    {
        return Course::with($with)->withAvg('reviews', 'rating')->withCount('reviews')->whereIn('id', $ids)->get();
    }

    public function studentWiseCourse($student_id)
    {
        return Course::whereHas('enrolls.checkout', function ($query) use ($student_id) {
            $query->where('user_id', $student_id);
        })->paginate();
    }

    public function activeCourses($data, $relation = [])
    {

        return Course::with($relation)->withCount('lessons')->withCount('enrolls')->withAvg('reviews', 'rating')
            ->where('is_private', 0)
            ->when(arrayCheck('suggested_course', $data), function ($query) {
                $query->orderBy('enrolls_count', 'desc');
            })->when(arrayCheck('is_featured', $data), function ($query) use ($data) {
                $query->where('is_featured', $data['is_featured']);
            })->when(arrayCheck('is_free', $data), function ($query) use ($data) {
                $query->where('is_free', $data['is_free']);
            })->when(arrayCheck('offered', $data), function ($query) {
                $query->where('is_free', 0)->where('discount', '>', 0)->where('discount_start_at', '<', now())->where('discount_end_at', '>=', now());
            })->when(arrayCheck('instructor_course', $data), function ($query) use ($data) {
                $query->whereHas('users', function ($query) use ($data) {
                    $query->where('users.id', $data['user_id']);
                });
            })->when(arrayCheck('organization_course', $data), function ($query) use ($data) {
                $query->where('organization_id', $data['organization_course']);

            })->when(arrayCheck('my_course', $data), function ($query) use ($data) {
                $query->withSum('progresses', 'progress')->where(function ($query) use ($data) {
                    $query->whereHas('enrolls.checkout', function ($query) use ($data) {
                        $query->where('status', 1)->where('user_id', $data['user_id'])->when(arrayCheck('course_view', $data), function ($query) use ($data) {
                            $query->where('complete_count', '>=', $data['course_view']);
                        });
                    })->orWhereHas('exams', function ($query) {
                        $query->where('is_passed', 1)
                            ->whereHas('quiz', function ($query) {
                                $query->where('certificate_included', 1);
                            });
                    });
                });
            })->when(arrayCheck('purchase_course', $data), function ($query) use ($data) {
                $query->whereHas('enrolls.checkout', function ($query) use ($data) {
                    $query->where('courses.id', $data['purchase_course'])->where('user_id', $data['user_id']);
                });
            })->when(arrayCheck('wishlist', $data), function ($query) use ($data) {
                $query->whereHas('wishlists', function ($query) use ($data) {
                    $query->where('user_id', $data['user_id']);
                });
            })->when(arrayCheck('category_id', $data), function ($query) use ($data) {
                $query->where('category_id', $data['category_id'])->when(arrayCheck('with_child_category', $data), function ($query) use ($data) {
                    $query->orWhereHas('category', function ($query) use ($data) {
                        $query->where('parent_id', $data['category_id']);
                    });
                });
            })->when(arrayCheck('related', $data) && arrayCheck('id', $data) && arrayCheck('category_id', $data), function ($query) use ($data) {
                $query->where('id', '!=', $data['id'])->where('category_id', $data['category_id']);
            })->when(arrayCheck('search', $data), function ($query) use ($data) {
                $query->where('title', 'like', '%'.$data['search'].'%')
                    ->orWhereHas('category', function ($query) use ($data) {
                        $query->where('title', 'like', '%'.$data['search'].'%');
                    })->orWhereHas('category.languages', function ($query) use ($data) {
                        $query->where('title', 'like', '%'.$data['search'].'%');
                    })->orWhereHas('organization', function ($query) use ($data) {
                        $query->where('org_name', 'like', '%'.$data['search'].'%');
                    })->orWhereHas('language', function ($query) use ($data) {
                        $query->where('name', 'like', '%'.$data['search'].'%')
                            ->orWhere('locale', 'like', '%'.$data['search'].'%');
                    })->orWhereHas('level', function ($query) use ($data) {
                        $query->where('title', 'like', '%'.$data['search'].'%');
                    })->orWhereHas('subject', function ($query) use ($data) {
                        $query->where('title', 'like', '%'.$data['search'].'%');
                    })->orWhereHas('subject.languages', function ($query) use ($data) {
                        $query->where('title', 'like', '%'.$data['search'].'%');
                    });
            })->when(arrayCheck('q', $data), function ($query) use ($data) {
                $query->where('title', 'like', '%'.$data['q'].'%');
            })->when(arrayCheck('filter', $data), function ($query) use ($data) {
                $filter = $data['filter'];
                $query->$filter();
            })->when(arrayCheck('category_ids', $data) && count($data['category_ids']) > 0, function ($query) use ($data) {
                $query->whereIn('category_id', $data['category_ids'])->when(arrayCheck('with_child_category', $data), function ($query) use ($data) {
                    $query->orWhereHas('category', function ($query) use ($data) {
                        $query->whereIn('parent_id', $data['category_ids']);
                    });
                });
            })->when(arrayCheck('level_ids', $data) && count($data['level_ids']) > 0, function ($query) use ($data) {
                $query->whereIn('level_id', $data['level_ids']);
            })->when(arrayCheck('subject_ids', $data) && count($data['subject_ids']) > 0, function ($query) use ($data) {
                $query->whereIn('subject_id', $data['subject_ids']);
            })->when(arrayCheck('rating', $data) && count($data['rating']) > 0, function ($query) use ($data) {
                $min_rating = $data['rating'][0];
                $max_rating = end($data['rating']);
                $query->whereBetween('total_rating', [$min_rating, $max_rating]);
            })->when(arrayCheck('price', $data) && count($data['price']) > 0, function ($query) use ($data) {
                if (in_array('paid', $data['price']) && in_array('free', $data['price'])) {
                    $query->whereIn('is_free', [1, 0]);
                } else {
                    if (in_array('paid', $data['price'])) {
                        $query->where('is_free', 0);
                    } elseif (in_array('free', $data['price'])) {
                        $query->where('is_free', 1);
                    }
                }
            })->when(arrayCheck('take', $data), function ($query) use ($data) {
                $query->take($data['take']);
            })->when(arrayCheck('skip', $data), function ($query) use ($data) {
                $query->skip($data['skip']);
            })->when(arrayCheck('sorting', $data), function ($query) use ($data) {
                if ($data['sorting'] == 'oldest') {
                    $query->orderBy('id', 'asc');
                } elseif ($data['sorting'] == 'top_rated') {
                    $query->orderBy('total_rating', 'desc');
                } else {
                    $query->orderBy('id', 'desc');
                }
            })->when(! arrayCheck('sorting', $data), function ($query) {
                $query->orderBy('id', 'desc');
            })->when(arrayCheck('ids', $data), function ($query) use ($data) {
                $query->whereIn('id', $data['ids']);
            })->active()
            ->whereNull('deleted_at')
            ->paginate($data['paginate']);
    }

    public function courseFilter($data, $relation = [])
    {
        return Course::with($relation)->withCount('lessons')->withCount('enrolls')->withAvg('reviews', 'rating')
            ->when(arrayCheck('category_id', $data), function ($query) use ($data) {
                $query->whereIn('category_id', $data['category_id']);
            })->when(arrayCheck('is_free', $data), function ($query) use ($data) {
                $query->orWhereIn('is_free', $data['is_free']);
            })->when(arrayCheck('level', $data), function ($query) use ($data) {
                $query->orWhereIn('level_id', $data['level']);
            })->when(arrayCheck('ratings_filter', $data), function ($query) use ($data) {
                $query->orWhereIn('reviews.rating', $data['ratings_filter']);
            })->latest()->active()->paginate($data['paginate']);
    }

    public function cartCourse()
    {
        return Course::whereHas('carts', function ($query) {
            $query->where('user_id', auth()->id());
        })->active()->get();
    }

    public function published($id)
    {

        $course = Course::find($id);

        $status = $course->is_published == 1 ? 0 : 1;

        $course->update([
            'status' => 'approved',
        ]);

        return $course->update([
            'is_published' => $status,
        ]);
    }

    public function activeCoursesIDs($data, $relation = [])
    {
        $courseIds = Course::with($relation)
            ->withCount('lessons')
            ->withCount('enrolls')
            ->withAvg('reviews', 'rating')
            ->where('is_private', 0)
            ->when(arrayCheck('my_course', $data), function ($query) use ($data) {
                $query->whereHas('enrolls.checkout', function ($query) use ($data) {
                    $query->where('user_id', $data['user_id'])
                        ->when(arrayCheck('course_view', $data), function ($query) use ($data) {
                            $query->where('complete_count', '>=', $data['course_view']);
                        });
                });
            })
            ->whereNull('deleted_at')
            ->paginate($data['paginate'])
            ->pluck('id');

        return $courseIds;
    }

    public function findInstructorCourse($id, $user)
    {
        return Course::withAvg('reviews', 'rating')->withCount('reviews')->withCount('enrolls')->whereHas('users', function ($query) use ($user) {
            $query->where('users.id', $user->id);
        })->find($id);
    }

    public function changeStatus($id, $user)
    {
        $course = $this->findInstructorCourse($id, $user);

        $status = $course->is_published == 1 ? 0 : 1;

        return $course->update([
            'is_published' => $status,
        ]);
    }
}

@extends('frontend.layouts.master')
@section('title', __('download'))
@section('content')
    

<style>
        .certificate {
            /* width: 730px; */
            /* height: 516px; */
            height: 626px;
            position: relative;
            width: 100%;
            background: #F1F2EB;
        }

        .certificate_img img {
            height: 100%;
            width: 100%;
        }
        .certificate_info_img img {
            width: auto !important;
            height: 105px;
            margin: 0 auto;
        }
        .certificate_content h2 {
            font-size: 26px;
            display: block;
            margin-bottom: 10px;
            line-height: 34px;
        }
        .certificate_content p {
            font-size: 15px;
            width: 520px;
            line-height: 22px;
        }
        /* .org_logo img {
            width: 85px;
            height: 85px;
            border-radius: 50%;
        } */

        .org_logo {
            width: 85px;
            height: 85px;
            border-radius: 50%;
            background: #eaebe8;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .org_logo img {
            width: auto;
            height: 50px;
            border-radius: 0;
        }
        /* .certificate_content_wrap {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
        } */
        .certificate_content_wrap {
            position: relative;
            top: 0;
            left: 0;
            transform: initial;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: space-between;
            height: 100%;
            max-width: 80%;
            margin: auto;
            padding: 60px 0;
            gap: 6px;
            text-align: center;
        }

        .certificate_content_wrap p {
            width: 100%;
        }

        .certificate_img {
            position: absolute;
            width: 100%;
            height: 100%;
        }


        .certificate_sign_info {
          margin-top: 10px !important;
          justify-content: space-between;
          width: 100%;
          gap: 20px !important;
          display: flex;
          align-items: center;
      }
              
        .fleft {
            float: left;
        }

        .fleft:not(:last-child) {
            margin-right: 46px;
        }

        .signature.fleft {
            margin-top: 20px;
        }
        .signature h6 {
            font-size: 16px;
            border-top: 1px solid #666;
            padding-top: 6px;
            color: #333;
            /* white-space: nowrap; */
            text-align: center;
        }
        .signature img {
            width: auto;
            height: 45px;
            padding-bottom: 4px;
            border-radius: 0;
            margin: auto;
            /* object-fit: contain; */
        }
        .certificate_content {
            margin-top: 0px;
        }
        .registration-number {
            position: absolute;
            top: 70px;
            left: 70px;
        }
        .registration-number span {
            color: #556068;
            font-size: 12px;
            line-height: 28px;
            font-weight: 400;
        }

        @media screen and (Max-width: 991px) {
            .certificate-respon .certificate_info_img img {
                height: 88px;
            }
            .certificate {
                height: initial;
            }
        }
        @media screen and (min-width: 1600px) and (max-width: 1799px) {
            .certificate-scrollable .certificate {
                width: 100% !important;
                height: initial;
            }
        }
        @media screen and (min-width: 1200px) and (max-width: 1599px) {
            .certificate.certificate-respon {
                height: inherit;
                width: 100% !important;
            }
            .certificate-respon .certificate_info_img img {
                height: 88px;
            }
        }
    </style>

    <!--====== Start Download Certificate Section ======-->
    <section class="download-certificate-section p-t-50 p-t-sm-30 p-b-80 p-b-sm-100">
        <div class="container container-1278">
            <div class="row">

                @include('frontend.profile.sidebar')
                <div class="col-md-8">
                    <div class="download-certificate-wrapper">
                        <div class="section-title-v3 color-dark m-b-40 m-b-sm-15">
                            <h3><i class="fal m-r-10 fa-long-arrow-left"></i>{{__('download_certificate')}}</h3>
                        </div>
                        <div class="download-certificate m-b-50" data-aos="zoom-in">
                            <div class="certificate-scrollable">
                                <div class="certificate certificate-respon">
                                <div class="certificate_img">
                                    <img src="{{ static_asset('frontend/certificate/certificate-border.png') }}" alt="Certificate">
                                </div>
                                <div class="certificate_content_wrap">
                                    <div class="certificate_info_img">
                                        <img src="{{ static_asset('frontend/certificate/certificate-top.png') }}" alt="">
                                    </div>
                                    <div class="certificate_content">
                                        <h2>{{ $course->title }}</h2>
                                        <p>{{ $course->certificate ? $course->certificate->body: '' }}</p>
                                    </div>
                                    <b>{{Auth::user()->first_name. ' ' . Auth::user()->last_name }}</b>
                                    <div class="certificate_sign_info">
                                    <div class="signature">
                                        <img src="{{ getFileLink('170x74', $course->certificate ? $course->certificate->instructor_signature: '') }}" alt="{{__('instructor_signature')}}">
                                        <h6>{{__('instructor_signature') }}</h6>
                                    </div>
                                    <div class="org_logo">
                                        <img src="{{ getFileLink('84x85', $course->certificate ? $course->certificate->background_image: '') }}" alt="{{__('background_image')}}">
                                    </div>
                                    <div class="signature">
                                        <img src="{{ getFileLink('170x74', $course->certificate ? $course->certificate->administrator_signature: '') }}" alt="{{__('administrator_signature')}}">
                                        <h6>{{__('administrator_signature') }}</h6>
                                    </div>
                                    </div>
                                </div>
                                </div>
                            </div>

                        </div>
                        <div class="text-center" data-aos="fade-up">
                            <a href="{{ route('course.certificate-download', $course->id) }}" class="template-btn template-btn-secondary">{{__('download_certificate')}}</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--====== End Download Certificate Section ======-->
@endsection

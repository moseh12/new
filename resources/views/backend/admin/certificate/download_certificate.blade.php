<html>

<head>
    <meta charset="utf-8">
    <title></title>
    <style>
        .certificate {
            width: 730px;
            height: 530px;
            position: relative;
            background: #F1F2EB;
            margin: 0 auto !important;
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
            margin-bottom: 0px;
            line-height: 28px;
        }

        .certificate_content h4 {
            line-height: 20px;
            margin-top: 5px;
        }

        .certificate_content p {
            font-size: 15px;
            width: 520px;
            line-height: 22px;
            margin-top: -15px;
        }

        .org_logo {
            width: 85px;
            height: 85px;
            border-radius: 50%;
            background: #eaebe8;
            line-height: 85px;
            vertical-align: middle;
        }

        .org_logo img {
            width: auto;
            height: 40px;
            margin-top: 22px;
            display: block;
        }

        .certificate_content_wrap {
            position: absolute;
            top: 45px;
            left: 50%;
            transform: translateX(-50%);
            text-align: center;
        }

        .certificate_sign_info {
            margin-top: 10px !important;
            width: 100%;
            gap: 20px !important;
            display: flex;
            align-items: center;
            flex-wrap: nowrap;
            justify-content: space-between;
        }

        .fleft {
            float: left;
            margin-right: 60px;
        }

        .fleft:last-child {
            margin-right: 0;
        }

        .signature h6 {
            font-size: 16px;
            border-top: 1px solid #666;
            padding-top: 6px;
            color: #333;
            text-transform: capitalize;
        }

        .signature img {
            width: auto;
            height: 45px;
            padding-bottom: 4px;
            border-radius: 0;
            margin: auto;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .certificate_content {
            margin-top: -10px;
        }

        .registration-number span {
            color: #556068;
            font-size: 12px;
            line-height: 28px;
            font-weight: 400;
        }

        .signature.fleft h6 {
            margin-top: 10px;
        }
    </style>
</head>
<body>
<div class="col-xl-6 col-lg-12 col-md-6">
    <div class="student-certificate">
        <div class="certificate">
            <div class="certificate_img">
                <img src="{{ static_asset('admin/certificate/certificate-border.png') }}" alt="Certificate">
            </div>
            <div class="certificate_content_wrap">
                <div class="certificate_info_img">
                    <img src="{{ static_asset('admin/certificate/certificate-top.png') }}" alt="">
                </div>
                <div class="certificate_content">
                    <h2>{{ $certificate->title }}</h2>
                    <h4>{{Auth::user()->first_name.' '.Auth::user()->last_name }}</h4>
                    <p>{{ $certificate->body }}</p>
                </div>
                <div class=" certificate_sign_info">
                    <div class=" signature fleft">
                        <img src="{{ getFileLink('170x74', $certificate->instructor_signature) }}"
                             alt="{{ __('instructor_signature') }}">
                        <h6>{{ __('instructor_signature') }}</h6>
                    </div>
                    <div class="org_logo fleft">
                        <img src="{{ getFileLink('84x85', $certificate->background_image) }}"
                             alt="{{ __('background_image') }}">
                    </div>
                    <div class="signature fleft">
                        <img src="{{ getFileLink('170x74', $certificate->administrator_signature) }}"
                             alt="{{ __('administrator_signature') }}">
                        <h6>{{ __('administrator_signature') }}</h6>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>

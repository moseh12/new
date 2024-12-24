@extends('frontend.layouts.master')
@section('title', __('home'))
@section('content')
    <section class="edit-profile-section p-t-50 p-t-sm-30 p-b-md-50 p-b-80">
        <div class="container container-1278">
            <div class="row">
                @include('frontend.profile.sidebar')
                <div class="col-md-8">
                    <div class="edit-profile-wrapper">
                        <div class="row">
                            <div class="col-12">
                                <div class="section-title-v3 color-dark m-b-40 m-b-sm-15">
                                    <h3>
                                        <i class="fal m-r-10 fa-long-arrow-left d-none d-sm-inline-block"></i>{{__('edit_profile') }}
                                    </h3>
                                </div>
                            </div>
                        </div>
                        <form method="post" action="{{ route('profile-update') }}" class="user-form p-0"
                              enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" value="{{ Auth()->user()->id }}" name="user_id">
                            <div class="row">

                                <div class="col-sm-6">
                                    <label for="first_name">{{__('first_name') }}</label>
                                    <input type="text" class="form-control" name="first_name" id="first_name"
                                           placeholder="{{__('first_name') }}"
                                           value="{{ old('first_name', Auth()->user()->first_name) }}">
                                    @if ($errors->has('first_name'))
                                        <div class="nk-block-des text-danger">
                                            <p>{{ $errors->first('first_name') }}</p>
                                        </div>
                                    @endif
                                </div>

                                <div class="col-sm-6">
                                    <label for="last_name">{{__('last_name') }}</label>
                                    <input type="text" class="form-control" name="last_name" id="last_name"
                                           placeholder="{{__('last_name') }}"
                                           value="{{ old('last_name', Auth()->user()->last_name) }}">
                                    @if ($errors->has('last_name'))
                                        <div class="nk-block-des text-danger">
                                            <p>{{ $errors->first('last_name') }}</p>
                                        </div>
                                    @endif
                                </div>
                                <div class="col-sm-6">
                                    <label for="phone">{{__('phone') }}</label>
                                    <input type="tel" class="form-control" name="phone" id="phone"
                                           placeholder="{{__('phone') }}"
                                           value="{{ old('phone', Auth()->user()->phone) }}">
                                    @if ($errors->has('phone'))
                                        <div class="nk-block-des text-danger">
                                            <p>{{ $errors->first('phone') }}</p>
                                        </div>
                                    @endif
                                </div>
                                <div class="col-sm-6">
                                    <label for="email">{{__('email') }}</label>
                                    <input type="email" class="form-control" name="email" id="email"
                                           placeholder="{{__('email') }}"
                                           value="{{ old('email', Auth()->user()->email) }}">
                                    @if ($errors->has('email'))
                                        <div class="nk-block-des text-danger">
                                            <p>{{ $errors->first('email') }}</p>
                                        </div>
                                    @endif
                                </div>
                                <div class="col-sm-6">
                                    <label for="address">{{__('address') }}</label>
                                    <input type="text" class="form-control" name="address" id="address"
                                           placeholder="{{__('address') }}"
                                           value="{{ old('address', Auth()->user()->address) }}">
                                    @if ($errors->has('address'))
                                        <div class="nk-block-des text-danger">
                                            <p>{{ $errors->first('address') }}</p>
                                        </div>
                                    @endif
                                </div>
                                <div class="col-sm-6">
                                    <label>{{__('image') }}</label>
                                    <div>
                                        <div class="input__file">
                                            <input class="form-control" type="file" id="image" name="image">
                                        </div>
                                        <div class="nk-block-des text-danger">
                                            <p class="image_error error"></p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 m-t-10 text-align-center text-align-md-end">
                                    <button class="template-btn w-auto d-inline-block"
                                            type="submit">{{__('update_profile') }}</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--====== End Edit Profile Section ======-->
@endsection

@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Register') }}</div>

                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success">
                            {{ session('success.message') }}
                        </div>
                    @endif

                    <form method="post" action="{{ url('users/store') }}">
                        @csrf

                        <div class="row mb-3">
                            <label for="name" class="col-md-4 col-form-label text-md-end">{{ __('Name') }}</label>

                            <div class="col-md-6">
                                <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') ? explode('/', old('name'))[0] : '' }}" required autofocus>

                                @error('name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="email" class="col-md-4 col-form-label text-md-end">{{ __('Email Address') }}</label>

                            <div class="col-md-6">
                                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required>

                                @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="date_of_enrollment" class="col-md-4 col-form-label text-md-end">{{ __('Date of Enrollment') }}</label>

                            <div class="col-md-6">
                                <input id="date_of_enrollment" type="date" class="form-control @error('date_of_enrollment') is-invalid @enderror" name="date_of_enrollment" value="{{ old('date_of_enrollment') }}" required>

                                @error('date_of_enrollment')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" class="btn btn-primary register-page" onmouseover="this.style.backgroundColor='rgba(0, 171, 174, 0.8)'; this.style.color='#fff';" onmouseout="this.style.backgroundColor='#00abae';">
                                    {{ __('Register') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            @if (session('success'))
                <div class="card">
                    <div class="card-body">
                        <textarea class="form-control" rows="19" readonly style="overflow: hidden;">
                            **【テックアイエス】パスワード設定のお願い**
                            **TECH I.S.** <support@techis.jp> ({{ now() }})

                            {{ session('success.name') }} 様 <{{ session('success.email') }}>

                            ご利用ありがとうございます。テックアイエスです。
                            カリキュラムについてのご案内です。

                            下記URLからカリキュラムパスワードの設定をお願いいたします。
                            パスワード設定URL：
                            {{ session('success.url') }}

                            ※こちらのメールは送信専用です。返信は無効となりますので、ご了承ください。

                            **━━━━━━━━━━━━━━━**
                            **■テックアイエス**
                            [https://techis.jp/](https://techis.jp/)
                            **■テックアイエス カリキュラム**
                            [https://www.techis-learning.jp/](https://www.techis-learning.jp/)</textarea>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

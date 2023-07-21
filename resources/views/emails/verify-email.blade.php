@component('siteboss::emails.components.template', ['title' => config('app.name')])
    @component('siteboss::emails.components.header')
        {{ __('siteboss::auth.verify_email_header') }} {{ config('app.name') }}
    @endcomponent
    @component('siteboss::emails.components.paragraph')
        {{ __('siteboss::auth.verify_email_link') }}
    @endcomponent

    @component('siteboss::emails.components.button', [
        'title' => __('siteboss::auth.verify_email_button'),
        'url' => $url,
    ])
    @endcomponent

    @component('siteboss::emails.components.paragraph')
        Thanks,<br>
        {{ config('app.name') }}
    @endcomponent

    @component('siteboss::emails.components.paragraph')
        @component('siteboss::emails.components.link', [
            'title' => __('siteboss::auth.verify_wrong_email'),
            'url' => $blockUrl,
        ])
        @endcomponent
    @endcomponent
@endcomponent

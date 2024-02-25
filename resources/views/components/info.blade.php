<!DOCTYPE
    html
    PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"
>
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <title>{{ $title }}</title>
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >
    <meta
        http-equiv="Content-Type"
        content="text/html; charset=UTF-8"
    >
    <meta
        name="color-scheme"
        content="light"
    >
    <meta
        name="supported-color-schemes"
        content="light dark"
    >
    <link
        href="/assets/static/siteboss.css"
        rel="stylesheet"
    />

</head>

<body
    style="box-sizing: border-box; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol'; position: relative; -webkit-text-size-adjust: none; background-color: #ffffff; color: #718096; height: 100%; line-height: 1.4; margin: 0; padding: 0; width: 100% !important;"
>
    <header>
        <img
            src="{{ config('app.url') }}/siteboss/images/sb-mail-logo.png"
            alt="SiteBoss"
            width="207"
            height="41"
        >
    </header>
    <main>
        <h1>{{ $title }}</h1>
        {{ $slot }}
    </main>

</body>

</html>

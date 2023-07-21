@props([
    'title' => '',
    'url' => '',
])
<a
    href="{{ $url }}"
    style="box-sizing: border-box; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol'; position: relative; color: #3869d4;"
>{{ $title }}</a>

<x-siteboss::info :title="$title">
    <p>
        {{ $message }}
    </p>

    <a
        href="{{ $link }}"
        class="button"
    >{{ $buttonText }}</a>
</x-siteboss::info>

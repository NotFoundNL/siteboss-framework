<div class="form-group {{ $colClasses }}">
    @if ($property('subtitle'))
        <h2> {{ $label }} </h2>
    @else
        <h1> {{ $label }} </h1>
    @endif
</div>

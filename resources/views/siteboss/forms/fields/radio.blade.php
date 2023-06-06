<div class="form-group {{ $colClasses }}">
    <label for="input">{{ $label }}</label>

    <label
        for="{{ $id }}[]"
        generated="true"
        class="error invalid-feedback"
        style="display: none;">
    </label>

    <div>
        @foreach ($optionList as $option)
            @php($optionString = $getByLanguage($option))
            <div class="form-check form-check-inline">
                <input class="form-check-input"
                    type="radio"
                    name="{{ $id }}[]"
                    id="radio{{ $id }}$optionString"
                    value="{{ $option->id }}"
                    {{$required}}
                >
                <label class="form-check-label" for="radio{{ $id }}{{$optionString}}">
                    {{$optionString}}
                </label>
            </div>
        @endforeach
    </div>
</div>

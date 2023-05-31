<div class="form-group {{ $colClasses }}">
    <label for="input">{{ $label }}</label>

    <input
        class="form-control"
        type="text"
        name="{{ $id }}"
        id="{{ $id }}"
        aria-describedby="{{ $id }}"
        placeholder="{{ $placeholder }}"
        {{ $required }}
    >
    <label for="{{ $id }}" generated="true" class="error invalid-feedback" style="display: none;"></label>
</div>

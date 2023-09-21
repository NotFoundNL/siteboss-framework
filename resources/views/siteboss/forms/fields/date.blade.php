<div class="form-group {{ $colClasses }}">
    <label for="input">{{ $label }}</label>
    <input
        type="date"
        class="form-control"
        name="{{ $id }}"
        id="{{ $id }}"
        placeholder="{{ $placeholder }}"
        {{ $required() }}
    >
    <label
        for="{{ $id }}"
        generated="true"
        class="error invalid-feedback"
        style="display: none;"
    ></label>
</div>

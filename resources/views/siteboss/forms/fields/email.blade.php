<div class="form-group {{ $colClasses }}">
    <label for="input">{{ $label }}</label>
    <input
        class="form-control"
        type="email"
        name="{{ $id }}"
        id="{{ $id }}"
        aria-describedby="{{ $id }}"
        {{ $required() }}
        placeholder="{{ $placeholder }}"
    >
    <label
        for="{{ $id }}"
        generated="true"
        class="error invalid-feedback"
        style="display: none;"
    ></label>
</div>

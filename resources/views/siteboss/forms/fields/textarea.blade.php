<div class="form-group {{ $colClasses }}">
    <label for="input">{{ $label }}</label>

    <textarea
        placeholder="{{ $placeholder }}"
        name="{{ $id }}"
        class="form-control"
        id="{{ $id }}"
        rows="3"
        {{ $required }}
    ></textarea>

    <label
        for="{{ $id }}"
        generated="true"
        class="error invalid-feedback"
        style="display: none;"
    ></label>
</div>

<div class="form-group {{ $colClasses }}">
    <label for="input">{{ $label }}</label>
    <div class="custom-file">
        <input
            type="file"
            class="custom-file-input"
            name="{{ $id }}[]"
            id="{{ $id }}"
            {{ $filetypes }}
            {{ $required }}
            {{ $multiple }}
        >
        <label
            class="custom-file-label"
            for="{{ $id }}"
        > </label>
    </div>
    <label
        for="{{ $id }}"
        generated="true"
        class="error invalid-feedback"
        style="display: none;"
    ></label>
</div>

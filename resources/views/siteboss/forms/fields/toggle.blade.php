<div class="form-group {{ $colClasses }}">
  <label class="checkbox">
    <input
      class="form-check-input"
      type="checkbox"
      name="{{ $id }}[]"
      id="toggle{{$id}}"
      value="1"
      {{ $required }}
    />
    {{ $label }}
  </label>
</div>

<div class="form-group {{ $colClasses }}">
  <label for="select">{{ $label }}</label>
    <select class="form-control"  name="{{ $id }}"
        {{ $required() }}
    >

    <option value="" selected>Kies..</option>

    @foreach ($optionList as $option)
        @php($optionString = $getByLanguage($option))
        <option value="{{ $option->id }}">{{ $optionString }}</option>
    @endforeach
  </select>
  <label for="{{ $id }}" generated="true" class="error invalid-feedback" style="display: none;"></label>
</div>

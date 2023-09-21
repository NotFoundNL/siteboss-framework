<div class="form-group {{ $colClasses }}">
    <label for="input">{{ attribute(properties . label, lang) }}</label>
    <select
        class="form-control"
        name="{{ $id }}"
        id="{{ $id }}"
        {{ $required() }}
    >
        {% for option in options %}
        <option> {{ option . value }}</option>
        {% endfor %}
    </select>
</div>

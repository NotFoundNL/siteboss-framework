<div class="row">
    @foreach ($field->GetChildrenOfCombination($field->form_id, $field->id) as $childfield)
        <div>
            <x-dynamic-component
                :component="$childfield->getBladeComponent()"
                :field="$childfield"
            />
        </div>
    @endforeach
</div>

<form
    id="form_body_{{ $form->id }}"
    method="POST"
    action=""{{ $submitUrl }}"
    enctype="multipart/form-data"
>

    <div class="row">
        @foreach ($form->fields as $item)
            <div>
                <x-dynamic-component
                    :component="$item->getBladeComponent()"
                    :field="$item"
                />
            </div>
        @endforeach
    </div>
    @include('siteboss::siteboss.forms.submit')
</form>

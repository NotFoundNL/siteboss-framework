<div class="col-sm-12">
    <div class="row">
        {% for item in item.fields %}
            {{ include(item.type|lower ~ '.html', {label: item.label, properties: item.properties, id: item.id }) }}
        {% endfor %}
    </div>
</div>

{% if item.triggerId != '' %}
<script>
    $(document).ready(function() {
        $("[name='{{ item.triggerId }}']" ).change(function (e){
            thisCombo = $("#{{ item.id }}");
            if(e.target.value == '{{ item.triggerValue }}') {
                thisCombo.show();
            } else {
                thisCombo.hide();
                //thisCombo.find('input').val('');
                //thisCombo.find('textarea').val('');
                //thisCombo.find("input:checked[type='radio']").prop('checked', false);
                //thisCombo.find("input:checked[type='checkbox']").prop('checked', false);
            }
        });
    })
</script>
{% endif %}

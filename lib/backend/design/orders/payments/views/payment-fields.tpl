{if is_array($fields)}

{use class="common\helpers\Html"}
{use class="yii\helpers\Inflector"}
    {foreach $fields as $field}
        <label>{ucfirst(strtolower(Inflector::humanize($field['name'])))}
        {if isset($field['validators']) && in_array('required', $field['validators'])}
            <span class="required">*</span>
        {/if}    
        </label>
        {assign var="validator" value = ""}
        {if is_array($field['validators'])}
            {$validator = implode(" ", $field['validators'])}
        {/if}
        {Html::textInput($field['name'], '', array_merge(['class' => 'form-control '|cat:$validator], array_combine(array_values($field['validators']), $field['validators'])))}
    {/foreach}
{/if}
<script>
$(document).ready(function(){
    $.each( $('.date, .datetime'), function(i, e){
        $(e).datepicker({
            dateFormat: '{$smarty.const.DATE_FORMAT_DATEPICKER}',//$(e).data('format'),
        })
    } )
})
</script>
{use class="\yii\helpers\Inflector"}
<tr>
    <td align="left" colspan="2"><label>{$elements['name']}</label></td>
</tr>
<tr>
    <td align="left" colspan="2">&nbsp;</td>
</tr>
{if is_array($elements['fields'])}
    {foreach $elements['fields'] as $_key => $field}
        <tr>
            <td align="left" width="30%"><label>{Inflector::humanize(Inflector::id2camel($field['name']))}:{if isset($field['comment'])} {$field['comment']}{/if}</label></td>
            <td>
                {if $field['type'] == 'textarea'}
                    <textarea name="fields[{$_key}][{$field['name']}]" class="form-control">{\common\helpers\Output::output_string($field['value'])}</textarea>
                {else}
                    <input type="{$field['type']}" name="fields[{$_key}][{$field['name']}]" value="{\common\helpers\Output::output_string($field['value'])}"  {if $field['type'] == 'checkbox' && $field['value']} checked{/if} {if $field['type'] == 'text'}class="form-control"{/if}>
                {/if}
            </td>
        </tr>
    {/foreach}
{/if}
{if is_array($elements['type'])}
    {$selected = ''}
    {foreach $elements['type'] as $type => $value}
        {if $type == 'selected'}
            {$selected = $value}
        {else}
            <tr>
                <td align="left"><label for="{$type}">{$value}:</label></td>
                <td><input type="radio" name="type" value="{$type}" id="{$type}" {if $type == $selected} checked {/if}></td>
            </tr>
        {/if}
    {/foreach}
{/if}
{if is_array($elements['pages'])}
    <tr>
        <td align="left" colspan="2">&nbsp;</td>
    </tr>
    <tr>
        <td align="left"><label>Pages:</label></td>
        <td valign="top">
            {\yii\helpers\Html::checkboxList('pages', $elements['pages'], $controllers, ['class' => 'page-selector', 'multiple' => 'multiple', 'data-role' => 'multiselect'])}
        </td>
    </tr>
{/if}
{if $example}
    <tr>
        <td align="left" ><label>Example:</label></td>
        <td align="left" >{$example}</td>
    </tr>
{/if}
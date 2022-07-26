{foreach $app->controller->view->properties_tree as $property}
    <option value="{$property['id']}" {if $property['type'] == 'category'} style="font-weight:bold;color:#000" disabled {else} {if isset($properties_id) && $property['id'] == $properties_id} selected {/if} {/if} >{$property['text']}</option>
{/foreach}
<script type="text/javascript">
$(document).ready(function() {
    selectProperty();
});
</script>

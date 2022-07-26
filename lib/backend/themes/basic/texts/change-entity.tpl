<div class="or_box_head">{$smarty.const.TEXT_CHANGE_ENTITY}</div>
<form name="texts" action="" method="post" id="texts_change_entity" onSubmit="return changesApply();">
    <div class="col_desc">{$smarty.const.TABLE_HEADING_LANGUAGE_ENTITY}</div>        
    <div class="col_desc">{tep_draw_input_field('new_entity', '')}</div>
    <div class="btn-toolbar btn-toolbar-order">
        <button class="btn btn-no-margin">{$smarty.const.IMAGE_UPDATE}</button><button class="btn btn-cancel" onClick="return resetStatement()">{$smarty.const.IMAGE_CANCEL}</button>
        <input type="hidden" name="translation_key" value="{$translation_key}">
        <input type="hidden" name="translation_entity" value="{$translation_entity}">
    </div>
</form>
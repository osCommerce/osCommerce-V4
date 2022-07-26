{use class="yii\helpers\Html"}
<div class="widget-content2 widget-content-bord-bot">
  <div class="widget box box-no-shadow" style="margin-bottom: 0;">
    <div class="widget-header">
      <h4>{$property['properties_name']}</h4>
    </div>
    <div class="widget-content1 property-extra-value">
    {if {$property['multi_choice'] == 0}}
      {foreach $values as $value}
        <label>{tep_draw_radio_field('values[]', $value['values_id'])} {$value['values']}</label><br>
      {/foreach}
    {else}
      {foreach $values as $value}
        <label>{tep_draw_checkbox_field('values[]', $value['values_id'])} {$value['values']}{if $property['extra_values'] == 1}{$value['values_prefix']}{Html::input('text', 'extra_values[]', '', ['class' => 'small-control form-control', 'maxlength' => '4'])}{$value['values_postfix']}{/if}</label><br>
      {/foreach}
    {/if}
      <br><br>
    </div>
    {if {$property['properties_type'] != 'flag'}}
    <div class="w-btn-list w-btn-add-prop">
      <a href="{Yii::$app->urlManager->createUrl(['properties/edit', 'pID' => $property['properties_id']])}" class="add_property_value btn" title="{$smarty.const.TEXT_ADD_EDIT_PROP_VALUE}">{$smarty.const.TEXT_ADD_EDIT_PROP_VALUE}</a>
    </div>
    {/if}
  </div>
</div>
<script type="text/javascript">
$(document).ready(function() {

  $('.add_property_value').popUp({
    box: "<div class='popup-box-wrap'><div class='around-pop-up'></div><div class='popup-box popup-properties'><div class='pop-up-close pop-up-close-alert'></div><div class='popup-heading cat-head'>{$smarty.const.TEXT_ADD_EDIT_PROP_VALUE}</div><div class='pop-up-content'><div class='preloader'></div></div></div></div>"
  });

});
</script>

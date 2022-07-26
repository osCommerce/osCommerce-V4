{*
This file is part of osCommerce ecommerce platform.
 * osCommerce the ecommerce

@link https://www.oscommerce.com
@copyright Copyright (c) 2005 Holbi Group Ltd

Released under the GNU General Public License
 * For the full copyright and license information, please view the LICENSE.TXT file that was distributed with this source code.
*}
<div class="or_box_head">{$smarty.const.TEXT_INFO_HEADING_EDIT_CURRENCY}</div>
<div class="col_desc">{$smarty.const.TEXT_INFO_RECALCULATE_INTRO}</div>
<div class="col_desc"><div class="main_title">{$cInfo->title} {$cInfo->code}</div></div>
<div class="col_desc"><div class="main_title">{$smarty.const.TEXT_INFO_CURRENCY_VALUE} {$cInfo->value}</div></div>

<form onsubmit="return currencyRecalculateConfirm('{$cInfo->code}');" action="{Yii::$app->urlManager->createUrl('currencies/recalculate')}?currencyCode={$cInfo->code}" method="post" id="currencies" name="currencies">
    {if $cInfo->code != $smarty.const.DEFAULT_CURRENCY }
    <div class="row_fields"><div class="row_fields_text">{$smarty.const.TEXT_ROUND_TYPE}</div><div class="row_fields_value">
        <select name="roundType" class="form-control">
          <option value="round">{$smarty.const.TEXT_ROUND}</option>
          <option value="ceil">{$smarty.const.TEXT_CEIL}</option>
          <option value="floor">{$smarty.const.TEXT_FLOOR}</option>
        </select>
    </div></div>
    <div class="row_fields"><div class="row_fields_text">{$smarty.const.TEXT_ROUND_TO}</div><div class="row_fields_value">
        <select name="roundTo" class="form-control">
          <option value="">--</option>
          <option value=".99">0.99 {$smarty.const.TEXT_ROUND_DOWN_TO}</option>
          <option value=".95">0.95 {$smarty.const.TEXT_ROUND_DOWN_TO}</option>
          <option value=".90">0.90 {$smarty.const.TEXT_ROUND_DOWN_TO}</option>
          <option value="0">0.00</option>
          <option value="0.05">0.05</option>
          <option value="0.10">0.10</option>
          <option value="0.50">0.50</option>
          <option value="10">10</option>
          <option value="100">100</option>
          <option value="1000">1000</option>
        </select>
    </div></div>

    <div class="row_fields"><div class="row_fields_text">{$smarty.const.TEXT_QTY_DISCOUNT}</div><div class="row_fields_value">
        <select name="qtyDiscount" class="form-control">
          <option value="update">{$smarty.const.TEXT_QTY_DISCOUNT_UPDATE}</option>
          <option value="donotchange">{$smarty.const.TEXT_QTY_DISCOUNT_LEAVE}</option>
          <option value="reset">{$smarty.const.TEXT_QTY_DISCOUNT_RESET}</option>
        </select>
    </div></div>

    <div class="row_fields">
      <div class="row_fields_text"><label>{$smarty.const.TEXT_ROUND_GROSS} <input type="checkbox" class="uniform" onclick="$('#grossChecker').toggle();$('input#grossTax').val('');"></label></div>
      <div class="row_fields_value" id="grossChecker" style="display:none">
        <div class="check_linear"><label class="radio_label">{$smarty.const.TEXT_ROUND_GROSS_TAX}<input type="text" name="grossTax" id="grossTax" value="0" class="uniform process-tables"></label></div>
      </div>
    </div>

    <div class="row_fields">
      <div class="row_fields_text"><label>{$smarty.const.TEXT_ALL_TABLES} <input type="checkbox" class="uniform" checked onclick="$('#tablesChecker').toggle();$('input.process-tables').prop('checked',false);"></label></div>
      <div class="row_fields_value" id="tablesChecker" style="display:none">
        {foreach $tables as $table}
        <div class="check_linear"><label class="radio_label"><input type="checkbox" name="processTables[]" value="{$table}" class="uniform process-tables">{$table}</label></div>
        {/foreach}
      </div>
    </div>

    {else}
      <div class="check_linear"><label for="switchOffMarketing" class="radio_label"><input type="checkbox" name="switchOffMarketing" value="1" class="uniform" id="switchOffMarketing">{$smarty.const.TEXT_SWITCH_OFF_MARKETING}</label></div>
      {if $fixMarketingSwitch}
      <div class="check_linear"><label for="switchOnMarketing" class="radio_label"><input type="checkbox" name="switchOnMarketing" value="1" class="uniform" checked id="switchOnMarketing">{$smarty.const.TEXT_SWITCH_ON_MARKETING}</label></div>
      {/if}
    {/if}


		<div class="btn-toolbar btn-toolbar-order">
      <input type="button" value="{$smarty.const.IMAGE_UPDATE}" class="btn btn-no-margin" onclick="return currencyRecalculateConfirm('{$cInfo->code}');"><input type="button" value="{$smarty.const.IMAGE_CANCEL}" class="btn btn-cancel" onclick="return resetStatement();">
		</div>
</form>
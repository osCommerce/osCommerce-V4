{use class="common\helpers\Html"}
{use class="common\helpers\Date"}
{use class="common\helpers\Specials"}

<div id="sales_popup" class="popupSales">
  <div class="popup-heading">{$smarty.const.BOX_CATALOG_SPECIALS}</div>
  <div class="sales-popup-content" style="padding:10px">
    <div class="widget after" >
      <div class='col-md-8'>
        <label>{if $smarty.const.PRICE_WITH_BACK_TAX == 'True'}{$smarty.const.TEXT_GROSS_PRICE}{else}{$smarty.const.TEXT_NET_PRICE}{/if}</label> {$price}
        <div {if $smarty.const.PRICE_WITH_BACK_TAX == 'True'}style="display: none;"{/if}>
          <label>{$smarty.const.TEXT_GROSS_PRICE}</label> {$priceGross}
        </div>
      </div>
      <div class='col-md-4'>
        <a href="{Yii::$app->urlManager->createUrl(['specials/specialedit', 'popup'=>1, 'products_id' => $prid])}" class="right-link btn btn-add">{$smarty.const.IMAGE_ADD}</a>
      </div>
    </div>
    <!--===specials list===-->
    <div class="row widget-content">
      <div class="col-md-12">
        <div id="specials_list_data">
          <div class="">
          <table class="table table-striped table-bordered table-hover table-responsive table-ordering table-checkable double-grid table-specials"
                 checkable_list="{$app->controller->view->sortColumns}"
                       order_list="{$app->controller->view->sortNow}"
                       order_by="{$app->controller->view->sortNowDir}"{*data_ajax="{Yii::$app->urlManager->createUrl(['specials/list', 'popup' => 1, 'prid' => $prid])}"*}>
            <thead>
              <tr>
                {foreach $app->controller->view->specialsTable as $tableItem}
                  <th class="{if isset($tableItem['not_important']) && $tableItem['not_important'] == 2} checkbox-column sorting_disabled"{/if}{if isset($tableItem['not_important']) && $tableItem['not_important'] == 1} hidden-xs{/if}">{$tableItem['title']}</th>
                  {/foreach}
              </tr>
            </thead>
            <tbody>
              {foreach $items as $special}
                {$allGross=''}{$allNet=''}

                {*if (!\common\helpers\Extensions::isCustomerGroupsAllowed() && $smarty.const.USE_MARKET_PRICES != 'True') }
                    {$special['prices'][0] = $special}
                    {$special['prices'][0]['groups_id'] = 0}
                    {$special['prices'][0]['currencies_id'] = 0}
                {/if}

                {$prices=Specials::calculatePrices($special['prices'], $tax)*}
                {$prices=Specials::getPrices($special, $tax)}
                {if is_array($prices)}
                  {foreach $prices as $cur}
                    {foreach $cur as $p}
                      {$allGross=$allGross|cat:$p['group_name']|cat:': '|cat:$p['text_inc']|cat:" \n"}
                      {$allNet=$allNet|cat:$p['group_name']|cat:': '|cat:$p['text']|cat:" \n"}
                    {/foreach}
                  {/foreach}
                {/if}
                {if ($special['start_date'] > '1980-01-01' && $special['start_date'] > date("Y-m-d H:i:s") && !$special['status'] ) }
                  {$scheduled = true}
                {else}
                  {$scheduled = false}
                {/if}
                {if ($special['expires_date'] > '1980-01-01'  && $special['expires_date'] < date("Y-m-d H:i:s")) }
                  {$expired = true}
                {else}
                  {$expired = false}
                {/if}
                {if empty($prices[0][0]['text'])}
                  {if !\common\helpers\Extensions::isCustomerGroupsAllowed()}
                    {$prices[0][0]['text'] = $smarty.const.TEXT_DISABLED}
                  {else}
                    {$prices[0][0]['text'] = sprintf($smarty.const.TEXT_PRICE_SWITCH_DISABLE, $smarty.const.TEXT_MAIN)}
                  {/if}
                  {$prices[0][0]['text_inc']=$prices[0][0]['text']}
                {/if}
                <tr class="{if $special['status']<1}dis_module{/if}">
                  <td>
                    <span style='display:none'>{$special['specials_date_added']}</span>
                    {Date::date_short($special['specials_date_added'])}</td>
                  <td title='{$allNet|escape}'><span class='sale-info'>{$prices[0][0]['text']}</span></td>
                  <td title='{$allGross|escape}'><span class='sale-info'>{$prices[0][0]['text_inc']}</span></td>
                  <td>{Date::datetime_short($special['start_date'])}</td>
                  <td>{Date::datetime_short($special['expires_date'])}</td>
                  
                    {if !empty($special['total_qty'])}
                    {$sold=Specials::getSoldOnlyQty(['specials_id' => $special['specials_id']])}
                    {/if}
                  <td class="sold {if !empty($special['total_qty']) && $special['total_qty']<=$sold}sold-out red{/if}">{if !empty($special['total_qty']) || !empty($special['max_per_order'])}
                      {$special['total_qty']} <span title="{$smarty.const.TABLE_HEADING_PRODUCTS_SOLD|escape}" style="cursor:pointer" class="_right-link sold {if $special['total_qty']<=$sold}sold-out red{/if}">({$sold})</span> / {$special['max_per_order']}
                      
                      {/if}
                  </td>
                  <td>{Specials::statusDescriptionText($special['specials_enabled'], $special['specials_disabled'], $expired, $scheduled)}</td>
                  <td>
                    <a href="{Yii::$app->urlManager->createUrl(['specials/specialedit', 'popup'=>1, '_hash_' => $hash, 'products_id' => $prid, 'id' => $special['specials_id']])}" class="right-link edit">{$smarty.const.IMAGE_EDIT}</a><br>
                    {if \common\helpers\Acl::checkExtensionAllowed('ReportOrderedProducts')}
                    <a class="right-link report" href="{Yii::$app->urlManager->createUrl(['ordered-products-report', 'specials_id' => $special['specials_id'], 'start_date' => Date::formatCalendarDate($special['specials_date_added']) ])}" target="_blank">{$smarty.const.IMAGE_REPORT}</a>
                    {/if}
                  </td>
                </tr>
              {/foreach}
            </tbody>
          </table>
          </div>
        </div>

      </div>
    </div>
    <!--===/specials list===-->
  </div>
</div>
<script>
  var table;
  (function($){

    table = $('.table-specials');
    var options = {
        'pageLength': 10,
    }
    if (table.hasClass('table-ordering')) {
        var data_order_list = table.attr('order_list');//data
        var data_order_by = table.attr('order_by');//data
        var column_index_list = data_order_list.split(',');
        var column_index_by = data_order_by.split(',');
        var aoColumnDefs = [];
        for(var column_key in column_index_list) {
            aoColumnDefs.push([parseInt(column_index_list[column_key],10), column_index_by[column_key]]);
        }
console.log(aoColumnDefs);
        $.extend(true, options, {
            'order': aoColumnDefs
        });
    }

    table = $('.table-specials').dataTable( options );
    var oSettings = table.fnSettings();
    oSettings._iDisplayStart = 0;
    table.fnDraw();

    $('.right-link.edit').popUp({ 'box_class':'popupSales' });
    $('.right-link.btn').popUp({ 'box_class':'popupSales' });

  })(jQuery)
</script>

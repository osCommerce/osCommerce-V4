{use class="\yii\helpers\Url"}
{use class="\yii\helpers\Html"}
{use class="\common\helpers\OrderProduct"}
<div class="popup-heading">{$product['products_quantity']} x {$product['products_name']}</div>
<form id="products_status" action="{Yii::$app->urlManager->createUrl('orders/products-status-update')}" method="post">
{tep_draw_hidden_field('opID', $product['orders_products_id'])}

<div class="creditHistoryPopup">
  <table class="table table-striped table-bordered table-hover table-responsive table-ordering stock-history-datatable double-grid">
    <thead>
      <tr>
        <th>&nbsp;</th>
        <th data-orderable="false">{$smarty.const.TABLE_HEADING_DATE_ADDED}</th>
        <th data-orderable="false">{$smarty.const.TABLE_HEADING_STATUS}</th>
        <th data-orderable="false">{$smarty.const.TABLE_HEADING_STATUS_MANUAL}</th>
        <th data-orderable="false">{$smarty.const.TABLE_HEADING_COMMENTS}</th>
        <th data-orderable="false">{$smarty.const.TABLE_HEADING_PROCESSED_BY}</th>
      </tr>
    </thead>
    <tbody>
{foreach $history as $Item}
      <tr>
        <td>{$Item['id']}</td>
        <td>{$Item['date']}</td>
        <td>{$Item['status']}</td>
        <td>{$Item['status_manual']}</td>
        <td>{$Item['comments']}</td>
        <td>{$Item['admin']}</td>
      </tr>
{/foreach}
    </tbody>
  </table>

  <div class="widget box box-wrapp-blue filter-wrapp">
    <div class="widget-header upd-sc-title">
      <h4>{$smarty.const.TABLE_HEADING_COMMENTS_STATUS}</h4>
    </div>
    <div class="widget-content usc-box usc-box2">
      <div class="f_tab">
        <div class="f_row">
          <div class="f_td">
            <label>{$smarty.const.TABLE_HEADING_STATUS_MANUAL}</label>
          </div>
          <div class="f_td">
            {tep_draw_pull_down_menu('status_manual', $statuses_manual_array, $product['orders_products_status_manual'], 'class="form-control"')}
          </div>
        </div>
        <div class="f_row">
          <div class="f_td">
            <label>{$smarty.const.TABLE_HEADING_COMMENTS}:</label>
          </div>
          <div class="f_td">
            {tep_draw_textarea_field('comments', 'soft', '60', '5', '', 'class="form-control"', false)}
          </div>
        </div>
        {if count($statuses_array) > 0}
        <div class="f_row">
          <div class="f_td">
            <label>{$smarty.const.ENTRY_STATUS}</label>
          </div>
          <div class="f_td">
            {tep_draw_pull_down_menu('status', $statuses_array, $product['orders_products_status'], 'class="form-control" onChange="return doCheckOrderProductStatus();"')}
          </div>
        </div>
        {/if}
        {foreach $orderProductArray as $opsId => $warehouseArray}
            {foreach $warehouseArray as $warehouseId => $supplierArray}
                {foreach $supplierArray as $supplierId => $locationArray}
                    {foreach $locationArray as $locationId => $layersArray}
                        {foreach $layersArray as $layersId => $batchArray}
                            {foreach $batchArray as $batchId => $opArray}
                            <div class="f_row update_order_product_holder_{$opsId}">
                                <div class="f_td">
                                    {if !isset($opArray['html'])}
                                        <div class="amount">{Html::input('text', ('update_order_product_'|cat:$opsId|cat:'['|cat:$warehouseId|cat:']['|cat:$supplierId|cat:']['|cat:$locationId|cat:']['|cat:$layersId|cat:']['|cat:$batchId|cat:']'), $opArray['value'], ['class'=>'form-control form-control-small-qty', 'opsid' => {$opsId}])}</div>
                                    {/if}
                                </div>
                                <div class="f_td">
                                    {if !isset($opArray['html'])}
                                        {if isset($opArray['warehouseName'])}
                                            {$opArray['warehouseName']}, {$opArray['supplierName']}, {$opArray['locationName']}, {$opArray['layersName']}, {$opArray['batchName']}
                                        {/if}
                                        <div id="update_order_product_{$opsId}[{$warehouseId}][{$supplierId}][{$locationId}][{$layersId}][{$batchId}]"></div>
                                        <div class="warning" id="warning_update_order_product_{$opsId}[{$warehouseId}][{$supplierId}][{$locationId}][{$layersId}][{$batchId}]"></div>
                                    {else}
                                        {$opArray['html']}
                                    {/if}
                                </div>
                            </div>
                            {/foreach}
                        {/foreach}
                    {/foreach}
                {/foreach}
            {/foreach}
        {/foreach}
      </div>
    </div>
  </div>
</div>

<div class="mail-sending noti-btn">
  <div><span class="btn btn-cancel">{$smarty.const.IMAGE_CANCEL}</span></div>
  <div><input type="submit" class="btn btn-confirm" value="{$smarty.const.IMAGE_UPDATE}"></div>
</div>

</form>

<script>
  var table;
  (function($){
    table = $('.stock-history-datatable').dataTable( {
        'pageLength': 5,
        'order': [[ 0, 'desc' ]],
        'columnDefs': [ { 'visible': false, 'targets': 0 } ],
    } );
    var oSettings = table.fnSettings();
    oSettings._iDisplayStart = 0;
    table.fnDraw();

    doCheckOrderProductStatus();
  })(jQuery)

    $('#products_status').submit(function(e) {
        e.preventDefault();
        $.post('{Yii::$app->urlManager->createUrl('orders/products-status-update')}', $(this).serializeArray(), function(response, status) {
            if (status == 'success') {
                if (response.status == 'ok') {
                    $.each(response.op, function(opId) {
                        $('#products-status-' + opId).css('color', this.ops.colour).text(this.ops.status);
                        $('#products-status-manual-' + opId).css('color', this.opsm.colour).text(this.opsm.status);
                        $('span#products-qty-dfct-' + opId).text(this.qty_dfct);
                        $('span#products-qty-cnld-' + opId).text(this.qty_cnld);
                        $('span#products-qty-rcvd-' + opId).text(this.qty_rcvd - this.qty_dspd);
                        $('span#products-qty-dspd-' + opId).text(this.qty_dspd - this.qty_dlvd);
                        $('span#products-qty-dlvd-' + opId).text(this.qty_dlvd);
                    });
                    if (response.os.status > 0) {
                        $('#order-status').val(response.os.status).change();
                    }
                    $('#products_status .btn-cancel').trigger('click');
                }
            }
        }, 'json');
    });

    function doCheckOrderProductStatus() {
        let opStatus = $('form#products_status select[name="status"]').val();
        $('form#products_status div[class*="update_order_product_holder_"]').hide();
        $('form#products_status input[name^="update_order_product_"]').each(function() {
            if (typeof($(this).attr('default')) == 'undefined') {
                $(this).attr('default', $(this).val());
            }
            $(this).val($(this).attr('default')).change();
        });
        $('form#products_status div.update_order_product_holder_' + opStatus).show();
        return true;
    }
    {foreach $orderProductArray as $opsId => $warehouseArray}
        {foreach $warehouseArray as $warehouseId => $supplierArray}
            {foreach $supplierArray as $supplierId => $locationArray}
                {foreach $locationArray as $locationId => $layersArray}
                    {foreach $layersArray as $layersId => $batchArray}
                        {foreach $batchArray as $batchId => $opArray}
                            {if !isset($opArray['html'])}
                            $('form#products_status div[id="update_order_product_{$opsId}[{$warehouseId}][{$supplierId}][{$locationId}][{$layersId}][{$batchId}]"]').slider({
                                range: 'min',
                                value: {$opArray['value']},
                                min: {$opArray['min']},
                                max: {$opArray['max']},
                                slide: function(event, ui) {
                                    let isError = false;
                                    let input = $('form#products_status input[name="' + $(this).attr('id') + '"]');
                                    if (input.length > 0) {
                                        let value = ui.value;
                                        if (value < {$opArray['min']}) {
                                            value = {$opArray['min']};
                                        }
                                        if (value > {$opArray['max']}) {
                                            value = {$opArray['max']};
                                        }
                                        value = parseInt(value);
                                        {if isset($opArray['awaiting'])}
                                            let quantity = 0;
                                            let inputValue = parseInt(input.val());
                                            $('form#products_status input[name^="update_order_product_' + input.attr('opsid') + '["]').each(function() {
                                                quantity += parseInt($(this).val());
                                            });
                                            if ((quantity - inputValue + value) > {$opArray['awaiting']}) {
                                                value = ({$opArray['awaiting']} - quantity + inputValue);
                                                isError = true;
                                            }
                                        {/if}
                                        input.removeClass('warning');
                                        let warningMessage = '';
                                        {foreach $opArray['warning'] as $operand => $warningData}
                                            if (value {$operand} {$warningData['value']}) {
                                                input.addClass('warning');
                                                let calculate = {if $warningData['calculate']|count_characters > 0}({$warningData['calculate']}){else}''{/if};
                                                warningMessage += '<div>' + calculate + '{$warningData['calculateAfter']}{$warningData['message']|replace:'\'':'\\\''}</div>';
                                            }
                                        {/foreach}
                                        $('form#products_status div.warning[id="warning_' + $(this).attr('id') + '"]').html(warningMessage);
                                        $(this).slider('value', value);
                                        input.val(value);
                                        if (isError == false) {
                                            return true;
                                        }
                                    }
                                    return false;
                                }
                            });
                            {/if}
                        {/foreach}
                    {/foreach}
                {/foreach}
            {/foreach}
        {/foreach}
    {/foreach}
    $('form#products_status input[name^="update_order_product_"]').unbind('change').bind('change', function() {
        let slider = $('form#products_status div[id="' + $(this).attr('name') + '"]');
        if (slider.length > 0) {
            slider.slider('option', 'slide').call(slider, null, { value: $(this).val() });
        }
    }).unbind('keypress').bind('keypress', function(event) {
        var keycode = (event.keyCode ? event.keyCode : event.which);
        if (keycode == '13') {
            event.preventDefault();
            return $(this).change();
        }
        return true;
    });
</script>
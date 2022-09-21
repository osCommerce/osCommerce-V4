{use class="\yii\helpers\Html"}
<div id="product_reallocate_popup">
    <div class="popup-heading">{$smarty.const.TEXT_ORDER_RELOCATE}</div>
    {Html::beginForm(Yii::$app->urlManager->createUrl('categories/order-reallocate'), 'post', ['id' => 'order_reallocate_form', 'onSubmit' => 'return false;'])}
    {tep_draw_hidden_field('prid', $prid)}
    <div class="popup-content">
{foreach $orderProductArray as $orderProductId => $orderProductData}
    {if $isParent == true}
<div class="widget box box-no-shadow">
    <div class="widget-header">
        <table width="100%" style="float: left;"><tr>
            <td width="25%"><h4>{$smarty.const.TEXT_OPR_ORDER} <a target="_blank" href="{Yii::$app->urlManager->createUrl(['orders/process-order', 'orders_id' => $orderProductData['orderId']])}">#{$orderProductData['orderId']}</a></h4></td>
            <td width="25%"><label class="control-label" style="font-weight: 700;">{$smarty.const.TEXT_OPR_MODEL} {$orderProductData['model']}</label></td>
            <td width="25%"><label class="control-label">{$smarty.const.TEXT_OPR_QUANTITY} {$orderProductData['quantity']}</label></td>
            <td><label class="control-label">{$smarty.const.TEXT_OPR_AWAITING} <span>{$orderProductData['allocated_parent']}</span></label></td>
        </tr></table>
    </div>
</div>
    {else}
<div class="widget box box-no-shadow widget-closed">
    <div class="widget-header">
        <table width="100%" style="float: left;"><tr>
                <td width="25%"><h4>{$smarty.const.TEXT_OPR_ORDER} <a target="_blank" href="{Yii::$app->urlManager->createUrl(['orders/process-order', 'orders_id' => $orderProductData['orderId']])}">#{$orderProductData['orderId']}</a></h4></td>
            <td width="25%"><label class="control-label" style="font-weight: 700;">{$smarty.const.TEXT_OPR_MODEL} {$orderProductData['model']}</label></td>
            <td width="25%"><label class="control-label">{$smarty.const.TEXT_OPR_QUANTITY} {$orderProductData['quantity']}</label></td>
            <td><label class="control-label">{$smarty.const.TEXT_OPR_AWAITING} <span class="product_awaiting_{$orderProductId}">0</span></label></td>
        </tr></table>
        <div class="toolbar no-padding">
            <div class="btn-group">
                <span class="btn btn-xs widget-collapse"><i class="icon-angle-up"></i></span>
            </div>
        </div>
    </div>
    <div class="widget-content" style="display: none;">
        <table class="table table-striped table-bordered table-hover table-responsive table-ordering warehouses-stock-datatable double-grid">
            <tbody>
                {foreach $orderProductData['allocatedArray'] as $warehouseId => $supplierArray}
                    {foreach $supplierArray as $supplierId => $locationArray}
                        {foreach $locationArray as $locationId => $layersArray}
                            {foreach $layersArray as $layersId => $batchArray}
                                {foreach $batchArray as $batchId => $allocatedData}
                            <tr>
                                <td><label class="control-label">{$allocatedData['warehouseName']}</label></td>
                                <td><label class="control-label">{$allocatedData['supplierName']}</label></td>
                                <td><label class="control-label">{$allocatedData['locationName']}</label></td>
                                <td width="10%"><label class="control-label"><span class="location_awaiting_{$warehouseId}_{$supplierId}_{$locationId}_{$layersId}_{$batchId}" opid="{$orderProductId}">0</span></label></td>
                                <td width="10%"><div class="amount">{Html::input('text', 'allocated_update['|cat:$orderProductId|cat:']['|cat:$warehouseId|cat:']['|cat:$supplierId|cat:']['|cat:$locationId|cat:']['|cat:$layersId|cat:']['|cat:$batchId|cat:']', $allocatedData['allocated_update'], ['class'=>'form-control form-control-small-qty', 'onchange' => 'allocatedUpdate('|cat:$orderProductId|cat:', '|cat:$warehouseId|cat:', '|cat:$supplierId|cat:', '|cat:$locationId|cat:', '|cat:$layersId|cat:', '|cat:$batchId|cat:');'])}</div></td>
                                <td width="40%"><div id="allocated_update_{$orderProductId}_{$warehouseId}_{$supplierId}_{$locationId}_{$layersId}_{$batchId}"></div></td>
                            </tr>
                                {/foreach}
                            {/foreach}
                        {/foreach}
                    {/foreach}
                {/foreach}
            </tbody>
        </table>
    </div>
</div>
    {/if}
{/foreach}
    </div>
    <div class="noti-btn" style="clear: both">
        <div><span class="btn btn-cancel">{$smarty.const.IMAGE_CANCEL}</span></div>
        {if $isParent != true}
        <div><input class="btn btn-primary" type="button" onClick="doProductReallocateSubmit();" value="{$smarty.const.IMAGE_UPDATE}"></div>
        {/if}
    </div>
    {Html::endForm()}
</div>
{if $isParent != true}
<script>
    var warehouseProductArray = jQuery.parseJSON('{$warehouseProductArray|@json_encode}');
    var orderProductArray = jQuery.parseJSON('{$orderProductArray|@json_encode}');
    {foreach $orderProductArray as $orderProductId => $orderProductData}
        {foreach $orderProductData['allocatedArray'] as $warehouseId => $supplierArray}
            {foreach $supplierArray as $supplierId => $locationArray}
                {foreach $locationArray as $locationId => $layersArray}
                    {foreach $layersArray as $layersId => $batchArray}
                        {foreach $batchArray as $batchId => $allocatedData}
                    $('#allocated_update_{$orderProductId}_{$warehouseId}_{$supplierId}_{$locationId}_{$layersId}_{$batchId}').slider({
                        range: 'min',
                        value: {$allocatedData['allocated_update']},
                        min: 0,
                        max: {if $orderProductData['quantity'] < $allocatedData['allocated_update']}{$allocatedData['allocated_update']}{else}{$orderProductData['quantity']}{/if},
                        slide: function(event, ui) {
                            if (isSet(orderProductArray, [{$orderProductId}, 'allocatedArray', {$warehouseId}, {$supplierId}, {$locationId}, {$layersId}, {$batchId}])
                                && isSet(warehouseProductArray, [{$warehouseId}, {$supplierId}, {$locationId}, {$layersId}, {$batchId}])
                            ) {
                                let value = ui.value;
                                if (value < 0) {
                                    value = 0;
                                }
                                let orderProductData = orderProductArray[{$orderProductId}];
                                let orderProductAllocatedData = orderProductData['allocatedArray'][{$warehouseId}][{$supplierId}][{$locationId}][{$layersId}][{$batchId}];
                                let valueDelta = (parseInt(orderProductAllocatedData['allocated_update']) - value);
                                let checkProductAwaiting = (getProductAwaiting({$orderProductId}) + valueDelta);
                                if ((checkProductAwaiting < 0) && (valueDelta < 0)) {
                                    return false;
                                }
                                let warehouseProductAllocatedData = warehouseProductArray[{$warehouseId}][{$supplierId}][{$locationId}][{$layersId}][{$batchId}];
                                let checkWarehouseProductAwaiting = ((warehouseProductAllocatedData['quantity'] - warehouseProductAllocatedData['allocated_update']) + valueDelta);
                                if ((checkWarehouseProductAwaiting < 0) && (value > orderProductAllocatedData['allocated_real']) && (valueDelta < 0)) {
                                    return false;
                                }
                                warehouseProductAllocatedData['allocated_update'] -= valueDelta;
                                orderProductAllocatedData['allocated_update'] = value;
                                $('input[name="allocated_update[{$orderProductId}][{$warehouseId}][{$supplierId}][{$locationId}][{$layersId}][{$batchId}]"]').val(value);
                                $(this).slider('value', value);
                                calculateAwaiting();
                                return true;
                            }
                            return false;
                        }
                    });
                        {/foreach}
                    {/foreach}
                {/foreach}
            {/foreach}
        {/foreach}
    {/foreach}
    function calculateAwaiting() {
        $.each(orderProductArray, function(orderProductId) {
            let awaiting = getProductAwaiting(orderProductId);
            $('span.product_awaiting_' + orderProductId).html(awaiting);
            $('span.product_awaiting_' + orderProductId).parent().css('color', '');
            if (awaiting < 0) {
                $('span.product_awaiting_' + orderProductId).parent().css('color', 'red');
            }
        });
        $.each(warehouseProductArray, function(warehouseId, warehouseArray) {
            $.each(warehouseArray, function(supplierId, locationArray) {
                $.each(locationArray, function(locationId, layersArray) {
                    $.each(layersArray, function(layersId, batchArray) {
                        $.each(batchArray, function(batchId, allocatedData) {
                    let awaiting = (parseInt(allocatedData['quantity']) - parseInt(allocatedData['allocated_update']));
                    $('span.location_awaiting_' + warehouseId + '_' + supplierId + '_' + locationId + '_' + layersId + '_' + batchId).html(awaiting);
                    $('span.location_awaiting_' + warehouseId + '_' + supplierId + '_' + locationId + '_' + layersId + '_' + batchId).css('color', '');
                    if (awaiting < 0) {
                        $('span.location_awaiting_' + warehouseId + '_' + supplierId + '_' + locationId + '_' + layersId + '_' + batchId).css('color', 'red');
                    }
                        });
                    });
                });
            });
        });
        $.each(orderProductArray, function(orderProductId, orderProductData) {
            $.each(orderProductData['allocatedArray'], function(warehouseId, warehouseArray) {
                $.each(warehouseArray, function(supplierId, locationArray) {
                    $.each(locationArray, function(locationId, layersArray) {
                        $.each(layersArray, function(layersId, batchArray) {
                            $.each(batchArray, function(batchId, allocatedData) {
                        if (allocatedData['allocated_update'] == 0) {
                            $('span.location_awaiting_' + warehouseId + '_' + supplierId + '_' + locationId + '_' + layersId + '_' + batchId + '[opid="' + orderProductId + '"]').css('color', '');
                        }
                        $('input[name="allocated_update[' + orderProductId + '][' + warehouseId + '][' + supplierId + '][' + locationId + '][' + layersId + '][' + batchId + ']"]').css(
                            'color', $('span.location_awaiting_' + warehouseId + '_' + supplierId + '_' + locationId + '_' + layersId + '_' + batchId + '[opid="' + orderProductId + '"]').css('color')
                        );
                            });
                        });
                    });
                });
            });
        });
        return true;
    }
    function getProductAwaiting(orderProductId) {
        orderProductId = (orderProductId || 0);
        let awaiting = 0;
        if (isSet(orderProductArray, [orderProductId])) {
            let allocated = 0;
            $.each(orderProductArray[orderProductId]['allocatedArray'], function(warehouseId, supplierArray) {
                $.each(supplierArray, function(supplierId, locationArray) {
                    $.each(locationArray, function(locationId, layersArray) {
                        $.each(layersArray, function(layersId, batchArray) {
                            $.each(batchArray, function(batchId, allocatedData) {
                        allocated += parseInt(allocatedData['allocated_update']);
                            });
                        });
                    });
                });
            });
            orderProductArray[orderProductId]['allocated_update'] = allocated;
            awaiting = (parseInt(orderProductArray[orderProductId]['quantity']) - allocated);
        }
        return awaiting;
    }
    function allocatedUpdate(orderProductId, warehouseId, supplierId, locationId, layersId, batchId) {
        orderProductId = (orderProductId || 0);
        warehouseId = (warehouseId || 0);
        supplierId = (supplierId || 0);
        locationId = (locationId || 0);
        layersId = (layersId || 0);
        batchId = (batchId || 0);
        $.each($('input[name="allocated_update[' + orderProductId + '][' + warehouseId + '][' + supplierId + '][' + locationId + '][' + layersId + '][' + batchId + ']"]'), function() {
            let value = $(this).val();
            $(this).val(0);
            if (isSet(orderProductArray, [orderProductId, 'allocatedArray', warehouseId, supplierId, locationId, layersId, batchId])) {
                $(this).val(orderProductArray[orderProductId]['allocatedArray'][warehouseId][supplierId][locationId][layersId][batchId]['allocated_update']);
            }
            let slider = $('#allocated_update_' + orderProductId + '_' + warehouseId + '_' + supplierId + '_' + locationId + '_' + layersId + '_' + batchId);
            if (slider.length > 0) {
                slider.slider('option', 'slide').call(slider, null, { value: value });
            }
        });
        calculateAwaiting();
    }
    function doProductReallocateSubmit() {
        let isError = false;
        $.each(orderProductArray, function(orderProductId) {
            if (getProductAwaiting(orderProductId) < 0) {
                isError = true;
                return false;
            }
        });
        $.each(warehouseProductArray, function(warehouseId, warehouseArray) {
            $.each(warehouseArray, function(supplierId, locationArray) {
                $.each(locationArray, function(locationId, layersArray) {
                    $.each(layersArray, function(layersId, batchArray) {
                        $.each(batchArray, function(batchId, warehouseProductRecord) {
                    if (warehouseProductRecord['allocated_update'] > 0 && warehouseProductRecord['quantity'] < warehouseProductRecord['allocated_update']) {
                        $.each(orderProductArray, function(orderProductId, orderProductData) {
                            if (isSet(orderProductData, ['allocatedArray', warehouseId, supplierId, locationId, layersId, batchId])) {
                                if (orderProductData['allocatedArray'][warehouseId][supplierId][locationId][layersId][batchId]['allocated_update'] > orderProductData['allocatedArray'][warehouseId][supplierId][locationId][layersId][batchId]['allocated_real']) {
                                    isError = true;
                                    return false;
                                }
                            }
                        })
                    }
                        });
                    });
                });
            });
        });
        if (isError == true) {
            alert('{$smarty.const.TEXT_OPR_ERROR_INVALID|replace:'\'':'\\\''}');
            return false;
        }
        $.post("{Yii::$app->urlManager->createUrl('categories/order-reallocate')}", $('form#order_reallocate_form').find('input').serialize(), function(response, status) {
            if (status == 'success') {
                if (response.message != '') {
                    alert(response.message);
                }
                if (response.status == 'ok') {
                    $('#product_reallocate_popup span.btn-cancel').click();
                }
                if (typeof(response.deficit) != 'undefined') {
                    $('#deficit_quantity_info').html(response.deficit);
                }
                if (typeof(response.allocated) != 'undefined') {
                    $('#allocated_quantity_info').html(response.allocated);
                }
                if (typeof(response.allocated_temporary) != 'undefined') {
                    $('#allocated_temporary_quantity_info').html(response.allocated_temporary);
                }
                if (typeof(response.available) != 'undefined') {
                    if (response.available > 0) {
                        $('#products_quantity_info').html(response.available);
                        $('#overallocated_quantity_info_holder').hide();
                        $('#overallocated_quantity_info').html(0);
                    } else {
                        $('#products_quantity_info').html(0);
                        $('#overallocated_quantity_info').html(Math.abs(response.available));
                        $('#overallocated_quantity_info_holder').show();
                    }
                }
{$freezeExt = \common\helpers\Acl::checkExtensionAllowed('ReportFreezeStock')}
{if $freezeExt && $freezeExt::isFreezed()}
                resetStatement();
{/if}
            }
        }, 'json');
        return false;
    }
    function isSet(checkArray, keyArray) {
        checkArray = (checkArray || []);
        keyArray = (keyArray || []);
        if (typeof(checkArray) == 'object' && typeof(keyArray) == 'object') {
            let key = keyArray.shift();
            if (typeof(key) != 'undefined' && key in checkArray) {
                if (keyArray.length == 0) {
                    return true;
                } else {
                    return isSet(checkArray[key], keyArray);
                }
            }
        }
        return false;
    }
    calculateAwaiting();
    $('#product_reallocate_popup div.widget.box.box-no-shadow div.widget-header').unbind('click').bind('click', function() {
        var widget = $(this).parents(".widget");
        var widgetContent = widget.children(".widget-content");
        if (widget.hasClass('widget-closed')) {
            // Open Widget
            $(this).find('span.btn.btn-xs.widget-collapse > i').removeClass('icon-angle-up').addClass('icon-angle-down');
            widgetContent.slideDown(200, function() {
                widget.removeClass('widget-closed');
            });
        } else {
            // Close Widget
            $(this).find('span.btn.btn-xs.widget-collapse > i').removeClass('icon-angle-down').addClass('icon-angle-up');
            widgetContent.slideUp(200, function() {
                widget.addClass('widget-closed');
            });
        }
    });
</script>
{/if}
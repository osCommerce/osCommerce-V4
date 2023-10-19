{use class="common\helpers\Html"}
{use class="common\helpers\Suppliers"}
<div class="row product-main-detail-top-switchers">
    <div class="status-left">
        <span>{$smarty.const.TEXT_STATUS}</span>
        <input type="checkbox" value="1" name="products_status"
               class="check_bot_switch_on_off"{if $pInfo->products_status == 1} checked="checked"{/if} />
    </div>
    {if \common\helpers\Acl::checkExtensionAllowed('AutomaticallyStatus', 'allowed')}
        {\common\extensions\AutomaticallyStatus\AutomaticallyStatus::viewProductEdit($pInfo)}
    {/if}
</div>
<div class="create-or-wrap after mn-tab">
    <div class="cbox-left">
        <div class="widget box box-no-shadow">
            <div class="widget-header">
                <h4>{$smarty.const.TEXT_LABEL_BRAND}</h4>
            </div>
            <div class="widget-content">
                <div class="edp-line">
                    <label>{$smarty.const.TEXT_MANUFACTURERS_NAME}</label>
                    <div class="f_td_group f_td_group-pr">
                        <input id="selectBrand" name="brand" type="text" class="form-control form-control-small"
                               readonly="readonly"
                               value="{$pInfo->manufacturers_name}" autocomplete="off">
                        {Html::hiddenInput('manufacturers_id', $pInfo->manufacturers_id, ['class'=>'js-product-manufacturer'])}
                    </div>
                </div>
            </div>
        </div>


        <div class="widget box box-no-shadow">
            <div class="widget-header">
                <h4>{$smarty.const.TEXT_STOCK}</h4>
                <div class="edp-line">
                    <span class="edp-qty-t" style="display:none;">{$smarty.const.TEXT_APPLICABLE}</b></span>
                </div>
            </div>
            <div class="widget-content">

                <div class="stock-block">
                    <div class="available-stock">
                        <div>{$smarty.const.TEXT_STOCK_QUANTITY_INFO}</div>
                        <div class="val" id="products_quantity_info">{if ($pInfo->products_quantity > 0)}{$pInfo->products_quantity}{else}0{/if}</div>
                        <input type="hidden" name="products_quantity" value="{$pInfo->products_quantity}">
                    </div>

                    <div class="temporary">
                        <div>
                        {if ($pInfo->products_id > 0)}
                            <a href="{Yii::$app->urlManager->createUrl(['categories/temporary-stock', 'prid' => $pInfo->products_id])}" class="right-link-upd">{$smarty.const.TEXT_STOCK_TEMPORARY_QUANTITY}</a>
                        {else}
                            {$smarty.const.TEXT_STOCK_TEMPORARY_QUANTITY}
                        {/if}
                        </div>
                        <div class="val" id="temporary_quantity_info">{$pInfo->temporary_quantity}</div>
                        <input type="hidden" name="temporary_quantity" value="{$pInfo->temporary_quantity}">
                    </div>

                    <div class="total-allocated">
                        <div>
                        {if ($pInfo->products_id > 0)}
                            <a href="{Yii::$app->urlManager->createUrl(['categories/orders-products-stock', 'prid' => $pInfo->products_id])}" class="right-link-upd">{$smarty.const.TEXT_STOCK_ALLOCATED_QUANTITY}</a>
                        {else}
                            {$smarty.const.TEXT_STOCK_ALLOCATED_QUANTITY}
                        {/if}
                        </div>
                        <div class="val" id="allocated_quantity_info">{$pInfo->allocated_quantity}</div>
                        <input type="hidden" name="allocated_quantity" value="{$pInfo->allocated_quantity}">

                    </div>

                    <div class="allocated-temporary">
                        <div>
                            {if ($pInfo->products_id > 0)}
                                <a href="{Yii::$app->urlManager->createUrl(['categories/orders-products-temporary-stock', 'prid' => $pInfo->products_id])}" class="right-link-upd">{$smarty.const.TEXT_STOCK_TEMPORARY_ALLOCATED}</a>
                            {else}
                                {$smarty.const.TEXT_STOCK_TEMPORARY_ALLOCATED}
                            {/if}
                        </div>
                        <div class="val" id="allocated_temporary_quantity_info">{$pInfo->allocated_temporary_quantity}</div>
                    </div>

                    <div class="real-stock-total">
                        <div>{$smarty.const.TEXT_STOCK_WAREHOUSE_QUANTITY}</div>
                        <div class="val" id="warehouse_quantity_info">{$pInfo->warehouse_quantity}</div>
                        <input type="hidden" name="warehouse_quantity" value="{$pInfo->warehouse_quantity}">
                    </div>

                    <div class="deficit-quantity">
                        <div>
                            {if ($pInfo->products_id > 0)}
                                <a href="{Yii::$app->urlManager->createUrl(['categories/orders-products-deficit', 'prid' => $pInfo->products_id])}" class="right-link-upd">{$smarty.const.TEXT_STOCK_DEFICIT_QUANTITY}</a>
                            {else}
                                {$smarty.const.TEXT_STOCK_DEFICIT_QUANTITY}
                            {/if}
                        </div>
                        <div class="val" id="deficit_quantity_info">{$pInfo->deficit_quantity}</div>
                        <input type="hidden" name="deficit_quantity" value="{\common\helpers\Product::getStockDeficit($pInfo->products_id)}">
                    </div>

                    <div class="overallocated-quantity-holder"></div>
                    <div class="overallocated-quantity" id="overallocated_quantity_info_holder" style="{if ($pInfo->products_quantity < 0)}{else}display: none;{/if}">
                        <div>{$smarty.const.TEXT_STOCK_OVERALLOCATED_QUANTITY}</div>
                        <div class="val" style="color: red;" id="overallocated_quantity_info">{abs($pInfo->products_quantity)}</div>
                    </div>

                    <div class="ordered-stock-holder"></div>
                    {if \common\helpers\Acl::checkExtensionAllowed('PurchaseOrders')}
                    <div class="ordered-stock">
                        <div>
                            {if ($pInfo->products_id > 0)}
                                <a href="{Yii::$app->urlManager->createUrl(['purchase-orders/list-pending', 'prid' => $pInfo->products_id])}" class="right-link-upd">{$smarty.const.TEXT_STOCK_ORDERED_QUANTITY}</a>
                            {else}
                                {$smarty.const.TEXT_STOCK_ORDERED_QUANTITY}
                            {/if}
                        </div>
                        <div class="val" id="ordered_quantity_info">{$pInfo->ordered_quantity}</div>
                        <input type="hidden" name="ordered_quantity" value="{\common\helpers\Product::getStockOrdered($pInfo->products_id)}">
                    </div>
                    {/if}

                    <div class="available">
                        <div class="p-r-1">{$smarty.const.TEXT_STOCK_SUPPLIERS_QUANTITY}</div>
                        <div class="val" id="suppliers_quantity_info">{$pInfo->suppliers_quantity}</div>
                        <input type="hidden" name="suppliers_quantity" value="{$pInfo->suppliers_quantity}">
                    </div>

                    <div class="buttons">
                    </div>

                    <div class="stock-availability">
                        <label>{$smarty.const.TEXT_STOCK_INDICATION}</label>
                        {tep_draw_pull_down_menu('stock_indication_id', \common\classes\StockIndication::get_variants(), $pInfo->stock_indication_id, 'readonly="readonly" disabled="disabled" class="form-control form-control-small stock-indication-id" id="product_stock_indication_id"')}
                    </div>

                    <div class="delivery-terms">
                        <div class="delivery-term-section" id='product-delivery-term-section' {if $pInfo->is_virtual}style="display:none;"{/if}>
                            <label>{$smarty.const.TEXT_STOCK_DELIVERY_TERMS}</label>
                            {tep_draw_pull_down_menu('stock_delivery_terms_id', \common\classes\StockIndication::get_delivery_terms(), $pInfo->stock_delivery_terms_id, 'readonly="readonly" disabled="disabled" class="form-control form-control-small"')}
                        </div>
                    </div>

                    <div class="stock-reorder stock-reorder-level"></div>

                    <div class="stock-reorder stock-reorder-quantity"></div>

                    <div class="links">
                        {if $pInfo->products_id_stock > 0}
                            {if \common\helpers\Acl::checkExtensionAllowed('ProductStockHistory', 'allowed')}
                                {\common\extensions\ProductStockHistory\ProductStockHistory::productBlock($pInfo->products_id_stock)}
                            {else}
                                <span class="right-link dis_module">{$smarty.const.TEXT_STOCK_HISTORY}</span>
                            {/if}
                        {/if}
                    </div>


                    <div class="actions">
                        <h4>Actions</h4>

                        <div class="actions-content">
                            {* add block with product behavior *}
                            {* add to cart *}
                            <div class="edp-line-heig">
                                <label>{$smarty.const.TEXT_CART_BTN}:</label>
                                <input type="checkbox" name="cart_button" value="1" readonly="readonly" disabled="disabled" class="check_bot_switch_on_off"{if $pInfo->cart_button == 1} checked{/if} />
                            </div>
                            {* /add block with product behavior/ *}

                            {foreach \common\helpers\Hooks::getList('categories/productedit', 'details-actions-subproduct') as $filename}
                                {include file=$filename}
                            {/foreach}
                        </div>
                    </div>
                    {foreach \common\helpers\Hooks::getList('categories/productedit', 'details-after-actions-subproduct') as $filename}
                        {include file=$filename}
                    {/foreach}

                </div>



                {if $pInfo->products_id_stock > 0}
<script type="text/javascript">
    function products_quantity_update(uprid) {
        var params = [];
        params.push({ name: 'uprid', value: uprid});
        params.push({ name: 'products_quantity_update', value: $('[name="products_quantity_update"]').val()});
        params.push({ name: 'products_quantity_update_prefix', value: $('[name="products_quantity_update_prefix"]:checked').val()});
        params.push({ name: 'warehouse_id', value: $('[name="warehouse_id"]').val()});
        params.push({ name: 'w_suppliers_id', value: $('[name="w_suppliers_id"]').val()});
        params.push({ name: 'stock_comments', value: $('[name="stock_comments"]').val()});
        params.push({ name: 'is_autoallocate', value: $('[name="is_autoallocate"]:checked').val()});

        var loc = [];
        $('[name="box_location[]"]').each(function() {
            loc.push($(this).val());
        });
        params.push({ name: 'box_location', value: loc });

        $('#location').find('input').each(function() {
            params.push({ name: $(this).attr('name'), value: $(this).val() });
        });

        $.post("{Yii::$app->urlManager->createUrl('categories/product-quantity-update')}", $.param(params), function (data, status) {
            if (status == "success") {
                if (data.products_quantity != undefined) {
                    //$('[name="products_quantity_update"]').val('');
                    $('[name="products_quantity"]').val(data.products_quantity);
                    if (data.products_quantity >= 0) {
                        $('#products_quantity_info').html(data.products_quantity);
                        $('#overallocated_quantity_info_holder').hide();
                        $('#overallocated_quantity_info').html(0);
                    } else {
                        $('#products_quantity_info').html(0);
                        $('#overallocated_quantity_info').html(Math.abs(data.products_quantity));
                        $('#overallocated_quantity_info_holder').show();
                    }
                }
                if (data.allocated_temporary_quantity != undefined) {
                    $('#allocated_temporary_quantity_info').html(data.allocated_temporary_quantity);
                }
                if (data.deficit_quantity != undefined) {
                    $('[name="deficit_quantity"]').val(data.deficit_quantity);
                    $('#deficit_quantity_info').html(data.deficit_quantity);
                }
                if (data.allocated_quantity != undefined) {
                    $('[name="allocated_quantity"]').val(data.allocated_quantity);
                    $('#allocated_quantity_info').html(data.allocated_quantity);
                }
                if (data.temporary_quantity != undefined) {
                    $('[name="temporary_quantity"]').val(data.temporary_quantity);
                    $('#temporary_quantity_info').html(data.temporary_quantity);
                }
                if (data.warehouse_quantity != undefined) {
                    $('[name="warehouse_quantity"]').val(data.warehouse_quantity);
                    $('#warehouse_quantity_info').html(data.warehouse_quantity);
                }
                if (data.ordered_quantity != undefined) {
                    $('[name="ordered_quantity"]').val(data.ordered_quantity);
                    $('#ordered_quantity_info').html(data.ordered_quantity);
                }
                if (data.suppliers_quantity != undefined) {
                    $('[name="suppliers_quantity"]').val(data.suppliers_quantity);
                    $('#suppliers_quantity_info').html(data.suppliers_quantity);
                }
                $('.popup-box-wrap:last').remove();
            } else {
                alert("Request error.");
            }
        }, "json");
    }

    $('.right-link').popUp({ 'box_class': 'popupCredithistory'});
</script>
                {/if}




            </div>
        </div>

    </div>
    {foreach \common\helpers\Hooks::getList('categories/productedit', 'details-left-column-subproduct') as $filename}
        {include file=$filename}
    {/foreach}
    <div class="cbox-right">
        <div class="widget box box-no-shadow" style="background: #fff;">
            <div class="widget-header">
                <h4>{$smarty.const.TEXT_PRODUCT_IDENTIFIERS}</h4>
            </div>
            <div class="widget-content widget-content-center">
                <div class="edp-line">
                    <label>{$smarty.const.TEXT_MODEL_SKU}</label>
                    {tep_draw_input_field('products_model', $pInfo->products_model, 'class="form-control form-control-small"')}
                </div>
                {if $smarty.const.SHOW_EAN=='True'}
                <div class="edp-line">
                    <label>{$smarty.const.TEXT_EAN}</label>
                    {tep_draw_input_field('products_ean', $pInfo->products_ean, 'class="form-control form-control-small"')}
                </div>
                {/if}
                {if $smarty.const.SHOW_ASIN=='True'}
                <div class="edp-line">
                    <label>{$smarty.const.TEXT_ASIN}</label>
                    {tep_draw_input_field('products_asin', $pInfo->products_asin, 'class="form-control form-control-small"')}
                </div>
                {/if}
                {if $smarty.const.SHOW_ISBN=='True'}
                <div class="edp-line">
                    <label>{$smarty.const.TEXT_ISBN}</label>
                    {tep_draw_input_field('products_isbn', $pInfo->products_isbn, 'class="form-control form-control-small"')}
                </div>
                {/if}
                {if $smarty.const.SHOW_UPC=='True'}
                <div class="edp-line">
                    <label>{$smarty.const.TEXT_UPC}</label>
                    {tep_draw_input_field('products_upc', $pInfo->products_upc, 'class="form-control form-control-small"')}
                </div>
                {/if}
            </div>
        </div>
        <div class="widget box box-no-shadow" style="margin-bottom: 5px;">
            <div class="widget-header">
                <h4>{$smarty.const.IMAGE_DETAILS}</h4>
            </div>
            <div class="widget-content">

                <div class="edp-line">
                    <label>{$smarty.const.TEXT_DATE_AVAILABLE}</label>
                    {tep_draw_input_field('products_date_available', $pInfo->products_date_available, 'class="datepicker form-control form-control-small"' )}
                </div>

                <div class="edp-line edp-line-heig">
                    <label>{$smarty.const.TEXT_FEATURED_PRODUCT}</label>
                    <input type="checkbox" name="featured" value="1"
                           class="check_feat_prod"{if $app->controller->view->featured == 1} checked{/if} />
                    <span class="edp-ex edp-ex-sp featured_expires_date"{if $app->controller->view->featured == 0} style="display: none;"{/if}><label>{$smarty.const.TEXT_EXPIRY_DATE}</label>
                                    <input type="text" name="featured_expires_date"
                                           value="{$app->controller->view->featured_expires_date}"
                                           class="datepicker form-control form-control-small"></span>
                </div>

                {if ($ext = \common\helpers\Acl::checkExtensionAllowed('ProductConfigurator'))}
                    <div class="edp-line">
                        <label>{$smarty.const.TEXT_PRODUCTS_PCTEMPLATE}</label>
                        {tep_draw_pull_down_menu('products_pctemplates_id', \common\extensions\ProductConfigurator\helpers\Configurator::get_pctemplates(), $pInfo->products_pctemplates_id, 'readonly="readonly" disabled="disabled" class="form-control"')}
                    </div>
                {/if}
            </div>
        </div>

        {if \common\helpers\Acl::checkExtensionAllowed('ProductTemplates', 'allowed')}
            {\common\extensions\ProductTemplates\ProductTemplates::productBlock()}
        {/if}

        {* Implements Product Design constructor *}
        {if \common\helpers\Acl::checkExtensionAllowed('ProductDesigner', 'allowed')}
            {\common\extensions\ProductDesigner\ProductDesigner::designBlock($Key, $pInfo)}
        {/if}
        {foreach \common\helpers\Hooks::getList('categories/productedit', 'details-right-column-subproduct') as $filename}
            {include file=$filename}
        {/foreach}
    </div>
</div>
<script type="text/javascript">
var total_stock = {$pInfo->products_quantity};
var allocated_stock = [];

    (function ($) {

        $('.platform-to-qty').quantity({ 'min' : 0});

        $(function () {
            $(window).on('platform_changed', function (e, ob, st) {
                if (ob.currentTarget.name == 'platform[]') {
                    if (st == true) {
                        $('.frontend-' + ob.currentTarget.value).removeClass('disable');
                    } else {
                        $('.frontend-' + ob.currentTarget.value).addClass('disable');
                    }
                    if ($('.product-frontend:not(.disable) label:nth-child(2)').length > 0) {
                        $('.product-frontend-box').show();
                    } else {
                        $('.product-frontend-box').hide();
                    }
                }
            });
            {if $pInfo->is_virtual}
                $('.stock-indication-p').hide();
            {else}
                $('.stock-indication-v').hide();
            {/if}

            $('.stock-options').on('click',function() {
                var option = $(this).val();
                if (option == '0') {
                    $('#stock_by_platforms').hide();
                    $('#platform_to_warehouse').hide();
                }
                if (option == '1') {
                    $('#stock_by_platforms').show();
                    $('#platform_to_warehouse').hide();
                }
                if (option == '2') {
                    $('#stock_by_platforms').hide();
                    $('#platform_to_warehouse').show();
                }
            });

            {foreach $pInfo->platformStockList as $platform}
            allocated_stock[{$platform.id}] = {$platform.qty};
            $( '#slider-range-{$platform.id}' ).slider({
                    range: 'min',
                    value: {$platform.qty},
                    min: 0,
                    max: {$pInfo->products_quantity},
                    slide: function( event, ui ) {
                        allocated_stock[{$platform.id}] = ui.value;
                        //$( '#slider-range-qty-{$platform.id}' ).text( ui.value );
                        updateSliderTotal();
                        $('input[name="platform_to_qty_{$platform.id}"]').val(ui.value);
                    }
            });
            //$('#slider-range-qty-{$platform.id}').text($('#slider-range-{$platform.id}').slider('value'));
            updateSliderTotal();
            {/foreach}

        });
        checkWarehouseLocation();

        $('div.stock-reorder input:checkbox')
            .off()
            .on('change', function() {
                $(this).closest('div').find('input:text.form-control').attr('disabled', 'disabled');
                if ($(this).prop('checked') == true) {
                    $(this).closest('div').find('input:text.form-control').removeAttr('disabled');
                }
            })
            .change();
    })(jQuery);
function updateSlider(id) {
    var val = $('input[name="platform_to_qty_'+id+'"]').val();
    $('#slider-range-'+id).slider('value', val);
    allocated_stock[id] = parseInt(val);
    updateSliderTotal();
}
function updateSliderTotal() {
    var total_allocated_stock = 0;
    for (var i in allocated_stock) {
        total_allocated_stock += allocated_stock[i];
    }
    $('#slider-range-qty-total').text(total_allocated_stock + ' from ' + total_stock);
    if ( (total_stock - total_allocated_stock) < 0 ) {
        $('#slider-range-qty-total').css('color', 'red');
    } else {
        $('#slider-range-qty-total').css('color', 'green');
    }
}
function checkWarehouseLocation() {
    var warehouse_id = $('[name="warehouse_id"]').val();
    var suppliers_id = $('[name="w_suppliers_id"]').val();
    var products_id = $('[name="update_stock_id"]').val();
    var prefix = $('[name="products_quantity_update_prefix"]:checked').val();
    if (prefix == '-') {
        $('.amount').hide();
    } else {
        $('.amount').show();
    }
    $.post("categories/warehouse-location", { 'warehouse_id' : warehouse_id, 'suppliers_id' : suppliers_id, 'prefix' : prefix, 'products_id' : products_id }, function(data, status) {
        if (status == "success") {
            $('#location').html(data);
        }
    },"html");
    return false;
}
function checkLocationChild(obj, warehouse_id) {
    var location_id = $(obj).val();
    $.post("categories/warehouse-location-child", { 'location_id' : location_id, 'warehouse_id' : warehouse_id }, function(data, status) {
        if (status == "success") {
            $(obj).parent('div').parent('div').children('div.sublocation').html(data);
        }
    },"html");
    return false;
}
</script>
{use class="common\helpers\Html"}
{use class="common\helpers\Product"}
{use class="common\helpers\Suppliers"}
<div class="product-main-detail-top-switchers">
    <div class="status-left">
        <span>{$smarty.const.TEXT_STATUS}</span>
        <input type="checkbox" value="1" name="products_status"
               class="check_bot_switch_on_off"{if $pInfo->products_status == 1} checked="checked"{/if} />
    </div>
    {if \common\helpers\Acl::checkExtensionAllowed('AutomaticallyStatus', 'allowed')}
        {\common\extensions\AutomaticallyStatus\AutomaticallyStatus::viewProductEdit($pInfo)}
    {/if}
{if $isBundle != true}
    <div class="status-left">
        <span>{$smarty.const.TEXT_MANUAL_STOCK_UNLIMITED}:</span>
        <input type="checkbox" value="1" name="manual_stock_unlimited"
               class=""{if $pInfo->manual_stock_unlimited == 1} checked="checked"{/if} />
    </div>
{/if}
    <div class="status-left">
        <span>{$smarty.const.TEXT_DEMO_PRODUCT}:</span>
        <input type="checkbox" value="1" name="is_demo" class="check_bot_switch_on_off"{if $pInfo->is_demo == 1} checked="checked"{/if} />
    </div>
    {if $smarty.const.LISTING_SUB_PRODUCT=='True'}
    <div class="status-right">
        <span>{$smarty.const.TEXT_LISTING_PRODUCT}:</span>
        <input type="checkbox" value="1" name="is_listing_product"
               class="check_bot_switch_on_off"{if !!$pInfo->is_listing_product} checked="checked"{/if} />
        <input type="hidden" name="listing_switch_present" value="1">
    </div>
    {/if}
</div>
<div class="create-or-wrap after mn-tab">
    <div class="cbox-left">
        <div class="widget box box-no-shadow">
            <div class="widget-header">
                <h4>{$smarty.const.TEXT_LABEL_BRAND}</h4>
            </div>
            <div class="widget-content">
                <div class="edp-line brandLine inlineMobileField">
                    <label>{$smarty.const.TEXT_MANUFACTURERS_NAME}</label>
                    <div class="f_td_group f_td_group-pr">
                        <input id="selectBrand" name="brand" type="text" class="form-control form-control-small"
                               value="{if isset($pInfo->manufacturers_name)}{$pInfo->manufacturers_name}{/if}" autocomplete="off">
                        {Html::hiddenInput('manufacturers_id', $pInfo->manufacturers_id, ['class'=>'js-product-manufacturer'])}
                        {if \common\helpers\Acl::rule(['TEXT_LABEL_BRAND', 'IMAGE_EDIT'])}<a href="{Yii::$app->urlManager->createUrl('categories/brandedit')}"
                           class="btn btn-add-brand edit_brand"
                           title="{$smarty.const.TEXT_ADD_NEW_BRAND}">{$smarty.const.TEXT_ADD_NEW_BRAND}</a>{/if}
                    </div>

                    <script type="text/javascript">
                        $(document).ready(function () {
                            $('#selectBrand').autocomplete({
                                source: "{Yii::$app->urlManager->createUrl(['categories/brands','with'=>'id'])}",
                                minLength: 0,
                                autoFocus: true,
                                delay: 0,
                                appendTo: '.f_td_group',
                                select: function( event, ui ) {
                                    event.preventDefault();
                                    $('.js-product-manufacturer').val(ui.item.id);
                                    $('.js-product-manufacturer').trigger('change');
                                    $('#selectBrand').val(ui.item.value);
                                    $('#selectBrand').trigger('blur');
                                }
                            }).focus(function () {
                                $(this).autocomplete("search");
                            });
                            $('#selectBrand').autocomplete().data( "ui-autocomplete" )._renderItem = function( ul, item ) {
                                if ( this.term && this.term!='>' ) {
                                    item.text = item.text.replace(new RegExp('(' + $.ui.autocomplete.escapeRegex(this.term) + ')', 'gi'), '<b>$1</b>');
                                }
                                return $( "<li>" )
                                    .data("item.autocomplete", item)
                                    .append( "<a>" + item.text + "</a>" )
                                    .appendTo( ul );
                            };

                            $('.edit_brand').popUp({
                                box: "<div class='popup-box-wrap'><div class='around-pop-up'></div><div class='popup-box popupEditCat'><div class='popup-heading cat-head'>{$smarty.const.TEXT_ADD_NEW_BRAND}</div><div class='pop-up-content'><div class='preloader'></div></div></div></div>"
                            });

                            $('.edit_docs').popUp({
                                box: "<div class='popup-box-wrap'><div class='around-pop-up'></div><div class='popup-box popupEditCat'><div class='pop-up-close'></div><div class='popup-heading cat-head'>{$smarty.const.CHOOSE_FILE}</div><div class='pop-up-content'><div class='preloader'></div></div></div></div>"
                            });

                        });
                    </script>
                </div>
            </div>
        </div>


        {$freezeExt = \common\helpers\Acl::checkExtensionAllowed('ReportFreezeStock')}
        <div class="widget box box-no-shadow">
            <div class="widget-header">
                <h4>{$smarty.const.TEXT_STOCK}</h4>
                <div class="edp-line">
                    <span class="edp-qty-t" {if !$app->controller->view->showInventory }style="display:none;"{/if}>{$smarty.const.TEXT_APPLICABLE}</span>
                </div>
                <span class="stock-info-reload" style="padding-left:1em; float:right"><a href="#" class="icon-refresh" data-uprid="{$pInfo->products_id}" title="{$smarty.const.TEXT_RELOAD|escape}"></a></span>
            </div>
            <div class="widget-content">

                <div class="stock-block">
                    <div class="available-stock">
                        <div>
                            <a href="{Yii::$app->urlManager->createUrl(['categories/stock-info', 'prid' => $pInfo->products_id])}" class="right-link">{$smarty.const.TEXT_STOCK_QUANTITY_INFO}</a>

                        </div>
                        <div class="val" id="products_quantity_info">{Product::getVirtualItemQuantity($pInfo->products_id, (($pInfo->products_quantity > 0) ? $pInfo->products_quantity : 0))}</div>
                        <input type="hidden" name="products_quantity" value="{$pInfo->products_quantity}">
                    </div>

                    <div class="temporary">
                        <div>
                        {if (($isBundle == false) AND ($pInfo->products_id > 0))}
                            <a href="{Yii::$app->urlManager->createUrl(['categories/temporary-stock', 'prid' => $pInfo->products_id])}" class="right-link-upd">{$smarty.const.TEXT_STOCK_TEMPORARY_QUANTITY}</a>
                        {else}
                            {$smarty.const.TEXT_STOCK_TEMPORARY_QUANTITY}
                        {/if}
                        </div>
                        <div class="val" id="temporary_quantity_info">{Product::getVirtualItemQuantity($pInfo->products_id, $pInfo->temporary_quantity)}</div>
                        <input type="hidden" name="temporary_quantity" value="{$pInfo->temporary_quantity}">
                    </div>

                    <div class="total-allocated">
                        <div>
                        {if (($isBundle == false) AND ($pInfo->products_id > 0))}
                            <a href="{Yii::$app->urlManager->createUrl(['categories/orders-products-stock', 'prid' => $pInfo->products_id])}" class="right-link-upd">{$smarty.const.TEXT_STOCK_ALLOCATED_QUANTITY}</a>
                        {else}
                            {$smarty.const.TEXT_STOCK_ALLOCATED_QUANTITY}
                        {/if}
                        </div>
                        <div class="val" id="allocated_quantity_info">{Product::getVirtualItemQuantity($pInfo->products_id, $pInfo->allocated_quantity)}</div>
                        <input type="hidden" name="allocated_quantity" value="{$pInfo->allocated_quantity}">

                    </div>

                    <div class="allocated-temporary">
                        <div>
                            {if (($isBundle == false) AND ($pInfo->products_id > 0))}
                                <a href="{Yii::$app->urlManager->createUrl(['categories/orders-products-temporary-stock', 'prid' => $pInfo->products_id])}" class="right-link-upd">{$smarty.const.TEXT_STOCK_TEMPORARY_ALLOCATED}</a>
                            {else}
                                {$smarty.const.TEXT_STOCK_TEMPORARY_ALLOCATED}
                            {/if}
                        </div>
                        <div class="val" id="allocated_temporary_quantity_info">{Product::getVirtualItemQuantity($pInfo->products_id, $pInfo->allocated_temporary_quantity)}</div>
                    </div>

                    <div class="real-stock-total">

                        {if !$freezeExt || !$freezeExt::isFreezed()}
                            {if $pInfo->products_id > 0}
                                {if ($isBundle == false AND (int)$pInfo->manual_stock_unlimited == 0)}
                                    {assign var="SuppliersQty" value=count(Suppliers::getSuppliersList($pInfo->products_id))}
                                    {assign var="WarehousesQty" value=\common\helpers\Warehouses::get_warehouses_count()}
                                    {if !($SuppliersQty > 1 && $WarehousesQty > 1)}
                                        {$warehousesUrl = Yii::$app->urlManager->createUrl(['categories/stock', 'prid' => $pInfo->products_id])}
                                    {else}
                                        {if $WarehousesQty > 1}
                                            {$warehousesUrl = Yii::$app->urlManager->createUrl(['categories/warehouses-stock', 'prid' => $pInfo->products_id])}
                                        {/if}
                                    {/if}
                                {/if}
                            {/if}
                        {/if}
                        {if $warehousesUrl}
                            <div><a href="{$warehousesUrl}" class="popup">{$smarty.const.TEXT_STOCK_WAREHOUSE_QUANTITY}</a></div>
                        {else}
                            <div>{$smarty.const.TEXT_STOCK_WAREHOUSE_QUANTITY}</div>
                        {/if}
                        <div class="val" id="warehouse_quantity_info">{Product::getVirtualItemQuantity($pInfo->products_id, $pInfo->warehouse_quantity)}</div>
                        <input type="hidden" name="warehouse_quantity" value="{$pInfo->warehouse_quantity}">
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
                            {if (($isBundle == false) AND ($pInfo->products_id > 0))}
                                <a href="{Yii::$app->urlManager->createUrl(['purchase-orders/list-pending', 'prid' => $pInfo->products_id])}" class="right-link-upd">{$smarty.const.TEXT_STOCK_ORDERED_QUANTITY}</a>
                            {else}
                                {$smarty.const.TEXT_STOCK_ORDERED_QUANTITY}
                            {/if}
                        </div>
                        <div class="val" id="ordered_quantity_info">{Product::getVirtualItemQuantity($pInfo->products_id, $pInfo->ordered_quantity)}</div>
                        <input type="hidden" name="ordered_quantity" value="{\common\helpers\Product::getStockOrdered($pInfo->products_id)}">
                    </div>
                    {/if}

                    <div class="available">
                        <div class="p-r-1">{$smarty.const.TEXT_STOCK_SUPPLIERS_QUANTITY} </div>
                        <div class="val" id="suppliers_quantity_info">{Product::getVirtualItemQuantity($pInfo->products_id, $pInfo->suppliers_quantity)}</div>
                        <input type="hidden" name="suppliers_quantity" value="{$pInfo->suppliers_quantity}">
                    </div>

                    <div class="deficit-quantity">
                        <div>
                            {if (($isBundle == false) AND ($pInfo->products_id > 0))}
                                <a href="{Yii::$app->urlManager->createUrl(['categories/orders-products-deficit', 'prid' => $pInfo->products_id])}" class="right-link-upd">{$smarty.const.TEXT_STOCK_DEFICIT_QUANTITY}</a>
                            {else}
                                {$smarty.const.TEXT_STOCK_DEFICIT_QUANTITY}
                            {/if}
                        </div>
                        <div class="val" id="deficit_quantity_info">{Product::getVirtualItemQuantity($pInfo->products_id, $pInfo->deficit_quantity)}</div>
                        <input type="hidden" name="deficit_quantity" value="{\common\helpers\Product::getStockDeficit($pInfo->products_id)}">
                    </div>

                    <div class="buttons">

{if $freezeExt && $freezeExt::isFreezed()}
    <a href="{$freezeExt::getUrl()}" target="_blank">{$smarty.const.BOX_FREEZE_STOCK} {$smarty.const.TEXT_ENABLED}</a>
{else}

    {if $pInfo->products_id > 0}
        {if \common\helpers\Acl::checkExtensionAllowed('ProductStockHistory', 'allowed')}
            {\common\extensions\ProductStockHistory\ProductStockHistory::productBlock($pInfo->products_id)}
        {else}
            <span class="btn right-link dis_module">{$smarty.const.TEXT_STOCK_HISTORY}</span>
        {/if}
    {/if}

                        {if ($isBundle == false AND (int)$pInfo->manual_stock_unlimited == 0)}
                            {if $pInfo->products_id > 0}
                                <a href="{Yii::$app->urlManager->createUrl(['categories/update-stock', 'products_id' => $pInfo->products_id])}" class="btn right-link edp-qty-update" data-class="update-stock-popup">{$smarty.const.TEXT_UPDATE_STOCK}</a>
                            {/if}
                        {/if}
                        {if $pInfo->products_id > 0}
                            {if \common\helpers\Warehouses::isRelocationPossible() && count(Suppliers::getSuppliersList($pInfo->products_id)) > 0}
                                <a href="{Yii::$app->urlManager->createUrl(['categories/order-reallocate', 'prid' => $pInfo->products_id])}" class="btn right-link edp-qty-update" data-class="product-relocate-popup">{$smarty.const.TEXT_ORDER_RELOCATE}</a>
                                {if ($isBundle == false AND (int)$pInfo->manual_stock_unlimited == 0)}
                                    <a href="{Yii::$app->urlManager->createUrl(['categories/warehouses-relocate', 'prid' => $pInfo->products_id])}" class="btn right-link edp-qty-update" data-class="relocate-stock-popup">{$smarty.const.TEXT_WAREHOUSES_RELOCATE}</a>
                                {/if}
                            {/if}
                        {/if}
{/if}
                    </div>

                    {if $freezeExt && $freezeExt::isFreezed()}
                    {else}
                        {*if $pInfo->products_id > 0}
                            {if ($isBundle == false AND (int)$pInfo->manual_stock_unlimited == 0)}
                                {assign var="SuppliersQty" value=count(Suppliers::getSuppliersList($pInfo->products_id))}
                                {assign var="WarehousesQty" value=\common\helpers\Warehouses::get_warehouses_count()}
                                {if $SuppliersQty > 1 && $WarehousesQty > 1}
                                {else}
                                    {if $SuppliersQty > 1}
                                        <a href="{Yii::$app->urlManager->createUrl(['categories/suppliers-stock', 'prid' => $pInfo->products_id])}" class="right-link edp-qty-update">{$smarty.const.TEXT_SUPPLIERS_STOCK}</a>
                                    {/if}
                                {/if}
                            {/if}
                        {/if*}

                        {if $pInfo->products_id > 0 && \common\helpers\Acl::checkExtensionAllowed('ProductAssets', 'allowed')}
                            <div class="links">
                                <a href="{Yii::$app->urlManager->createUrl(['categories/product-assets', 'prid' => $pInfo->products_id])}" class="right-link edp-qty-update">{$smarty.const.TEXT_PRODUCT_ASSETS}</a>
                            </div>
                        {/if}
                    {/if}
                </div>

                <div class="stock-data">
                    <div class="stock-availability">
                        <label>{$smarty.const.TEXT_STOCK_INDICATION}</label>
                        {tep_draw_pull_down_menu('stock_indication_id', \common\classes\StockIndication::get_variants(), $pInfo->stock_indication_id, 'class="form-control form-control-small stock-indication-id" id="product_stock_indication_id"')}
                    </div>

                    <div class="delivery-terms">
                        <div class="delivery-term-section" id='product-delivery-term-section' {if $pInfo->is_virtual}style="display:none;"{/if}>
                            <label>{$smarty.const.TEXT_STOCK_DELIVERY_TERMS}</label>
                            {tep_draw_pull_down_menu('stock_delivery_terms_id', \common\classes\StockIndication::get_delivery_terms(), $pInfo->stock_delivery_terms_id, 'class="form-control form-control-small"')}
                        </div>
                    </div>

                    <div class="stock-reorder stock-limit">
                        {if ($isBundle == false AND (int)$pInfo->manual_stock_unlimited == 0)}
                            <label>{$smarty.const.TEXT_STOCK_LIMIT}&nbsp;<input type="checkbox" {if isset($pInfo->stock_limit_on) && $pInfo->stock_limit_on}checked {/if}/></label>
                            {Html::input('text', 'stock_limit', $pInfo->stock_limit, ['class'=>'form-control form-control-small-qty'])}
                        {/if}
                    </div>

                </div>

                <div class="actions">
                    <h4>Actions</h4>

                    <div class="actions-content">
                        {* add block with product behavior *}
                        {* add to cart *}
                        <div class="edp-line-heig">
                            <label>{$smarty.const.TEXT_CART_BTN}:</label>
                            <input type="checkbox" name="cart_button" value="1" class="check_bot_switch_on_off"{if $pInfo->cart_button == 1} checked{/if} />
                        </div>
                        {foreach \common\helpers\Hooks::getList('categories/productedit', 'details-actions') as $filename}
                            {include file=$filename}
                        {/foreach}
                        {* /add block with product behavior/ *}

                        <div class="out-stock-action">
                            <label>{$smarty.const.TEXT_ACTION_ON_OUT_OF_STOCK}:</label>
                            <div class="">
                                <select name="out_stock_action" class="form-control">
                                    <option value="0"{if $pInfo->out_stock_action == 0} selected{/if}>{$smarty.const.TEXT_DEFAULT}</option>
                                    <option value="1"{if $pInfo->out_stock_action == 1} checked{/if}>{$smarty.const.TEXT_NOTIFY_BACK_IN_STOCK}</option>
                                    <option value="2"{if $pInfo->out_stock_action == 2} checked{/if}>{$smarty.const.TEXT_CONTACT_FORM}</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                {foreach \common\helpers\Hooks::getList('categories/productedit', 'details-after-actions') as $filename}
                    {include file=$filename}
                {/foreach}



                {if $pInfo->products_id > 0}
<script type="text/javascript">
    function products_quantity_update(uprid) {
        var params = [];
        params.push({ name: 'uprid', value: uprid});
        params.push({ name: 'products_quantity_update', value: $('[name="products_quantity_update"]').val()});
        params.push({ name: 'products_quantity_update_prefix', value: $('[name="products_quantity_update_prefix"]:checked').val()});
        params.push({ name: 'expiry_date', value: $('[name="expiry_date"]').val()});
        params.push({ name: 'batch_name', value: $('[name="batch_name"]').val()});
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

    var isReload = false;
    $('.right-link-upd').popUp({
        'box_class': 'popupCredithistory',
        close: function() {
            $('.pop-up-close').click(function() {
                if (isReload == true) {
                    document.location.reload(true);
                } else {
                    $('.popup-box:last').trigger('popup.close');
                    $('.popup-box-wrap:last').remove();
                }
                return false;
            });
            $('.popup-box').on('click', '.btn-cancel', function() {
                if (isReload == true) {
                    document.location.reload(true);
                } else {
                    $('.popup-box:last').trigger('popup.close');
                    $('.popup-box-wrap:last').remove();
                }
                return false;
            });
        }
    });
    $('.right-link, .popup').popUp({ 'box_class': 'popupCredithistory'});

    $('.stock-info-reload a').on('click', function(){
        if ($(this).attr('data-uprid')) {
            reloadStockBlockData($(this).attr('data-uprid'), $(this).parents('.widget')); //function defined in  categories/productedit/details.tpl
        }
        return false;
    });
    {if $smarty.const.STOCK_ADMIN_AUTO_REFRESH >= 5}
        var stockReloadTimer = setInterval(function(){
            $('.stock-info-reload a:visible').each(function(){
                if ($(this).attr('data-uprid')) {
                    reloadStockBlockData($(this).attr('data-uprid'), $(this).parents('.widget'));
                }
            });

        }, {$smarty.const.STOCK_ADMIN_AUTO_REFRESH}*1000);
    {/if}
    {if $smarty.const.STOCK_ADMIN_REFRESH_ON_CLICK == 'True'}
            $('.stock-block a.right-link-upd.edp-qty-update').on('click', function(){
                $('.stock-info-reload a:visible').click();
            });

    {/if}
</script>
                {/if}




            </div>
        </div>

    {if $extScl = \common\helpers\Acl::checkExtensionAllowed('StockControl', 'allowed')}
        {$extScl::viewProductEdit($pInfo)}
    {/if}

    {foreach \common\helpers\Hooks::getList('categories/productedit', 'details-left-column') as $filename}
        {include file=$filename}
    {/foreach}
    </div>
    <div class="cbox-right">
        <div class="widget box box-no-shadow" id="product-identifiers-box">
            <div class="widget-header">
                <h4>{$smarty.const.TEXT_PRODUCT_IDENTIFIERS}</h4>
            </div>
            <div class="widget-content widget-content-center">
                <div class="row align-items-center m-b-2">
                    <div class="col-3 align-right p-r-0">
                        <label>{$smarty.const.TEXT_MODEL_SKU}<span class="colon">:</span></label>
                    </div>
                    <div class="col-3">
                        {tep_draw_input_field('products_model', $pInfo->products_model, 'class="form-control form-control-small"')}
                    </div>
                    {if $smarty.const.SHOW_EAN=='True'}
                    <div class="col-3 align-right">
                        <label>{$smarty.const.TEXT_EAN}<span class="colon">:</span></label>
                    </div>
                    <div class="col-3 p-l-0">
                        {tep_draw_input_field('products_ean', $pInfo->products_ean, 'class="form-control form-control-small"')}
                    </div>
                    {/if}
                </div>
                <div class="row align-items-center m-b-2">
                    {if $smarty.const.SHOW_ASIN=='True'}
                    <div class="col-3 align-right p-r-0">
                        <label>{$smarty.const.TEXT_ASIN}<span class="colon">:</span></label>
                    </div>
                    <div class="col-3">
                        {tep_draw_input_field('products_asin', $pInfo->products_asin, 'class="form-control form-control-small"')}
                    </div>
                    {/if}
                    {if $smarty.const.SHOW_UPC == "True"}
                    <div class="col-3 align-right">
                        <label>{$smarty.const.TEXT_UPC}<span class="colon">:</span></label>
                    </div>
                    <div class="col-3 p-l-0">
                        {tep_draw_input_field('products_upc', $pInfo->products_upc, 'class="form-control form-control-small"')}
                    </div>
                    {/if}
                </div>
                {foreach \common\helpers\Hooks::getList('categories/productedit', 'product-identifiers') as $filename}
                    {include file=$filename}
                {/foreach}
                {if $smarty.const.SHOW_ISBN == "True"}
                <div class="row align-items-center">
                    <div class="col-3 align-right p-r-0">
                        <label>{$smarty.const.TEXT_ISBN}<span class="colon">:</span></label>
                    </div>
                    <div class="col-3">
                        {tep_draw_input_field('products_isbn', $pInfo->products_isbn, 'class="form-control form-control-small"')}
                    </div>
                </div>
                {/if}
            </div>
        </div>
        <div class="widget box box-no-shadow" id="product-details-box">
            <div class="widget-header">
                <h4>{$smarty.const.IMAGE_DETAILS}</h4>
                <div class="toolbar no-padding">
                    <div class="btn-group">
                        <span class="btn btn-xs widget-collapse"><i class="icon-angle-down"></i></span>
                    </div>
                </div>
            </div>
            <div class="widget-content">
                <div class="t-row">
                    <div class="t-col-2">
                        <div class="edp-line">
                            <label>{$smarty.const.TEXT_PRODUCT_UNIT_LABEL}:</label>
                            {\yii\helpers\Html::dropDownList('product_unit_label', $pInfo->product_unit_label, (['' => ''] + \common\helpers\Product::getUnitLabelList()), ['class' => 'form-control'])}
                        </div>
                    </div>
                </div>
                <div class="t-row">
                  <div class="t-col-2 ">
                    <div class="edp-line">
                        <label>{$smarty.const.TEXT_DATE_AVAILABLE}:</label>
                        {tep_draw_input_field('products_date_available', $pInfo->products_date_available, 'class="datepicker form-control form-control-small"' )}
                    </div>
                  </div>
                  <div class="t-col-2 ">
                    <div class="edp-line">
                        <label>{$smarty.const.TEXT_NEW_UNTIL}:</label>
                        {tep_draw_input_field('products_new_until', $pInfo->products_new_until, 'class="datepicker form-control form-control-small"' )}
                    </div>
                  </div>
                </div>

                <div class="t-row">
                    <div class="t-col-2 ">
                        <div class="edp-line edp-line-heig">
                            <label>{$smarty.const.TEXT_FEATURED_PRODUCT}:</label>
                            <input type="checkbox" name="featured" value="1"
                                   class="check_feat_prod"{if $app->controller->view->featured == 1} checked{/if} />

                        </div>
                    </div>
                    <div class="t-col-2">
                        <div class="edp-line featured_expires_date"{if $app->controller->view->featured == 0} style="display: none;"{/if}>
                            <label>{$smarty.const.TEXT_EXPIRY_DATE}:</label>
                                        <input type="text" name="featured_expires_date"
                                               value="{$app->controller->view->featured_expires_date}"
                                               class="datepicker form-control form-control-small">
                        </div>
                    </div>
                </div>
                <div class="t-row">
                    <div class="t-col-2 ">
                        <div class="edp-line">
                            <label>{$smarty.const.TEXT_JSONLD_PRODUCT_TYPE}:</label>
                            {\yii\helpers\Html::dropDownList('jsonld_product_type', $pInfo->jsonld_product_type, [$smarty.const.TEXT_DEFAULT, $smarty.const.TEXT_JSONLD_PRODUCT, $smarty.const.TEXT_JSONLD_SERVICE], ['class' => "form-control"])}
                        </div>
                    </div>
                </div>

                {if ($ext = \common\helpers\Acl::checkExtensionAllowed('ProductConfigurator'))}
                    <div class="t-row">
                        <div class="t-col-2 ">
                            <div class="edp-line">
                                <label>{$smarty.const.TEXT_PRODUCTS_PCTEMPLATE}</label>
                                {tep_draw_pull_down_menu('products_pctemplates_id', \common\extensions\ProductConfigurator\helpers\Configurator::get_pctemplates(), $pInfo->products_pctemplates_id, 'class="form-control"')}
                            </div>
                        </div>
                    </div>
                {/if}
            </div>
        </div>
    {foreach \common\helpers\Hooks::getList('categories/productedit', 'details-right-column') as $filename}
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
                    //stock by platforms

                    if (st == true) {
                        $('.platform-stock-' +  ob.currentTarget.value).show();
                        $('.platform-stock-' +  ob.currentTarget.value).removeClass('dis_module');
                    } else {
                        if (parseInt($('#platformToQty' +  ob.currentTarget.value).val())>0) {

                            if ($('.stock-options:checked').val()=='1') {
                                bootbox.alert('{$smarty.const.WARNING_STOCK_ASSIGNED|escape:"javascript"} ');
                            }
                            $('.platform-stock-' +  ob.currentTarget.value).addClass('dis_module');
                        } else {
                            $('.platform-stock-' +  ob.currentTarget.value).hide();
                        }
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

function reloadStockBlockData(uprid, container) {
    var inProgress = false;
    try {
        if (stockReloadTimer && inProgress) {
console.log('inProgress');
            return false;
            //clearInterval(stockReloadTimer);
        }
    } catch ( e ) { }

    $.post("{Yii::$app->urlManager->createUrl('categories/product-stock-details')}", 'uprid=' + uprid, function (data, status) {
        inProgress = true;
            if (status == "success") {

                if (data.products_quantity != undefined) {
                    if (data.products_quantity >= 0) {
                        $('#overallocated_quantity_info_holder').hide();
                        $('#overallocated_quantity_info').html(0);
                    } else {
                        $('#overallocated_quantity_info').html(Math.abs(data.products_quantity));
                        $('#overallocated_quantity_info_holder').show();
                        data.products_quantity = 0;
                    }
                }

                var  fields = {
                    deficit_quantity:'',
                    allocated_temporary_quantity:'',
                    products_quantity:'inventoryqty_' + uprid,
                    allocated_quantity:'allocated_quantity_' + uprid,
                    temporary_quantity:'temporary_quantity_' + uprid,
                    warehouse_quantity:'warehouse_quantity_' + uprid,
                    ordered_quantity:'ordered_quantity_' + uprid,
                    suppliers_quantity:'suppliers_quantity_' + uprid
                };

                for (fld in fields) {
                    if (data[fld] != undefined) {
                        if ($('#' + fld + '_info', $(container))) {
                            $('#' + fld + '_info', $(container)).html(data[fld]);
                        }

                        if ($('[name="' + fld + '"]', $(container))) {
                            $('[name="' + fld + '"]', $(container)).val(data[fld]);
                        }
                        // inventory
                        if (fields[fld] != '' && $('div[name="' + fields[fld] + '_info"]', $(container))) {
                            $('div[name="' + fields[fld] + '_info"]', $(container)).html(data[fld]);
                        }
                        if (fields[fld]!='' && $('[name="' + fields[fld] + '"]', $(container))) {
                            $('[name="' + fields[fld] + '"]', $(container)).val(data[fld]);
                        }
                    }
                }
                inProgress = false;

            } else {
                alert("Request error.");
                inProgress = false;
            }
        }, "json");


}

$(function () {
    if ($('input[name="manual_stock_unlimited"]').prop('checked')) {
        $('.stock-block').addClass('stock-block-unlimited')
    }
    $('input[name="manual_stock_unlimited"]').tlSwitch({
        onText: "{$smarty.const.SW_ON}",
        offText: "{$smarty.const.SW_OFF}",
        handleWidth: '20px',
        labelWidth: '24px',
        onSwitchChange: function(e, status){
            if (status) {
                $('.stock-block').addClass('stock-block-unlimited')
            } else {
                $('.stock-block').removeClass('stock-block-unlimited')
            }
        }
    });
})
</script>

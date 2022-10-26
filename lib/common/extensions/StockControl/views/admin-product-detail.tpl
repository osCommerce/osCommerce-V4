{use class="common\helpers\Html"}
{if (((int)$pInfo->is_bundle == 0) AND ((int)$pInfo->manual_stock_unlimited == 0))}
    <div class="widget box box-no-shadow edp-qty-update" style="background: #fff;">
        <div class="widget-header">
            <h4>{$smarty.const.TEXT_STOCK_SPLIT}</h4>
        </div>
        <div class="widget-content widget-content-center">
            <div class="t-row our-pr-line stock-splitting-row">
                <label for="stock_control_s2"><input type="radio" name="stock_control" class="stock-options" id="stock_control_s2" value="0" {if $pInfo->stock_control=='0'}checked{/if}/>{$smarty.const.TEXT_STOCK_OVERALL}</label>
                <label for="stock_control_s1"><input type="radio" name="stock_control" class="stock-options" id="stock_control_s1" value="1" {if $pInfo->stock_control=='1'}checked{/if}/>{$smarty.const.TEXT_STOCK_SPLIT_PLATFORMS}</label>
                <label for="stock_control_s0"><input type="radio" name="stock_control" class="stock-options" id="stock_control_s0" value="2" {if $pInfo->stock_control=='2'}checked{/if}/>{$smarty.const.TEXT_STOCK_PLATFORMS_TO_WAREHOUSE}</label>
            </div>
            <div class="t-row" id="stock_by_platforms"{if $pInfo->stock_control!='1'} style="display: none;"{/if}>
                {foreach $pInfo->platformStockList as $platform}
                <div class="stock-row platform-stock-{$platform.id}{if $platform.qty>0 && !isset($app->controller->view->platform_assigned[$platform.id])} dis_module{/if}" {if empty($platform.qty) && !isset($app->controller->view->platform_assigned[$platform.id])} style="display:none"{/if}>
                    <label class="">{$platform.name}:</label>
                    <div class="slider-controls slider-value-top stock-row-qty">
                            {Html::input('text', 'platform_to_qty_'|cat:$platform.id, $platform.qty, ['class'=>'form-control form-control-small-qty platform-to-qty', 'onchange' => 'updateSlider('|cat:$platform.id|cat:');'])}
                    </div>
                    <div id="slider-range-{$platform.id}"></div>
                </div>
                {/foreach}
                <div class="stock-summary">
                    <label class="">Summary:</label>
                    <div class="">
                        <span id="slider-range-qty-total">0 from {$pInfo->products_quantity}</span>
                    </div>
                </div>
            </div>
            <div class="t-row" id="platform_to_warehouse"{if $pInfo->stock_control!='2'} style="display: none;"{/if}>
                {foreach $pInfo->platformWarehouseList as $platform}
                    <div class="platform-row">
                        <label>{$platform.name}:</label>
                        <div class="arrow"></div>
                        <div class="">
                            {tep_draw_pull_down_menu('platform_to_warehouse_'|cat:$platform.id, \common\helpers\Warehouses::get_warehouses(), $platform.warehouse, 'class="form-control form-control-small"')}
                        </div>
                    </div>
                {/foreach}
            </div>
        </div>
    </div>
{/if}
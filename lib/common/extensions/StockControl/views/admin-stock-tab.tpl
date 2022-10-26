{use class="common\helpers\Html"}
{if ($isStockUnlimited != true)}
    <input type="hidden" name="inventory_control_present" value="1">
    <div class="t-row our-pr-line">
        <label for="inventory_control_s2"><input type="radio" name="inventory_control_{$inventory['uprid']}" data-ikey="{$ikey}" class="inventory-stock-options" id="inventory_control_s2" value="0" {if $inventory.stock_control=='0'}checked{/if}/>{$smarty.const.TEXT_STOCK_OVERALL}</label>
        <label for="inventory_control_s1"><input type="radio" name="inventory_control_{$inventory['uprid']}" data-ikey="{$ikey}" class="inventory-stock-options" id="inventory_control_s1" value="1" {if $inventory.stock_control=='1'}checked{/if}/>{$smarty.const.TEXT_STOCK_SPLIT_PLATFORMS}</label>
        <label for="inventory_control_s0"><input type="radio" name="inventory_control_{$inventory['uprid']}" data-ikey="{$ikey}" class="inventory-stock-options" id="inventory_control_s0" value="2" {if $inventory.stock_control=='2'}checked{/if}/>{$smarty.const.TEXT_STOCK_PLATFORMS_TO_WAREHOUSE}</label>
    </div>
    <div class="t-row" id="inventory_stock_by_platforms_{$ikey}"{if $inventory.stock_control!='1'} style="display: none;"{/if}>
        {foreach $inventory.platformStockList as $platform}
            <div class="form-group">
                <label class="col-md-2 control-label">{$platform.name}:</label>
                <div class="col-md-10">
                    <div class="slider-controls slider-value-top">
                        {$smarty.const.TEXT_OPR_QUANTITY} <span id="slider-range-qty-{$ikey}-{$platform.id}"></span>
                    </div>
                    <div id="slider-range-{$ikey}-{$platform.id}" data-ikey="{$ikey}" data-platform-id="{$platform.id}" data-total-stock="{$inventory['products_quantity']}" data-uprid="{$inventory['uprid']}"></div>
                </div>
            </div>
            {Html::hiddenInput('platform_to_qty_'|cat:$inventory['uprid']|cat:'_'|cat:$platform.id, $platform.qty)}
        {/foreach}
        <div class="form-group">
            <label class="col-md-2 control-label">{$smarty.const.TEXT_SUMMARY}:</label>
            <div class="slider-controls slider-value-top">
                <span id="slider-range-qty-total-{$ikey}">0 from {$inventory['products_quantity']}</span>
            </div>
        </div>
    </div>
    <div class="t-row" id="inventory_platform_to_warehouse_{$ikey}"{if $inventory.stock_control!='2'} style="display: none;"{/if}>
        {foreach $inventory.platforWarehouseList as $platform}
            <div class="t-row">
                <div class="t-col-1">
                    <div class="edp-line">
                        <label>{$platform.name}:</label>
                        {tep_draw_pull_down_menu('inventory_platform_to_warehouse_'|cat:$inventory['uprid'], \common\helpers\Warehouses::get_warehouses(), \common\helpers\Warehouses::get_default_warehouse(), 'class="form-control form-control-small"')}
                    </div>
                </div>
            </div>
        {/foreach}
    </div>
{/if}
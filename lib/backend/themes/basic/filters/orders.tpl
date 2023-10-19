{use class="yii\helpers\Html"}
<div class="wrap_filters after {if $isMultiPlatform}wrap_filters_4{/if}">
    <div class="item_filter item_filter_1 choose_platform">
        {if $departments}
        <div class="f_row_pl_cus">
            <div class="f_td">
                <label>{$smarty.const.TEXT_COMMON_DEPARTMENTS_FILTER}</label>
            </div>
            <div class="f_td f_td_radio ftd_block">
                <div><label class="radio_label"><input type="checkbox" name="departments[]" class="js_department_checkboxes" value=""> {$smarty.const.TEXT_COMMON_PLATFORM_FILTER_ALL}</label></div>
                {foreach $departments as $department}
                    <div><label class="radio_label"><input type="checkbox" name="departments[]" class="js_department_checkboxes" value="{$department['id']}" {if in_array($department['id'], $app->controller->view->filters->departments)} checked="checked"{/if}> {$department['text']}</label></div>
                {/foreach}
            </div>
        </div>
        {/if}
        {if $isMultiPlatform}
            <div class="tl_filters_title">{$smarty.const.TEXT_COMMON_PLATFORM_FILTER}</div>
            <div class="f_td wl-td ftd_block tl_fron_or">
                {$count_platform = $platforms|default:array()|@count}
                {if $count_platform < 3}
                    {foreach $platforms as $platform}
                        <div><label class="radio_label"><input type="checkbox" name="platform[]" class="js_platform_checkboxes" value="{$platform['id']}" {if in_array($platform['id'], $app->controller->view->filters->platform)} checked="checked"{/if}> {$platform['text']}</label></div>
                            {/foreach}
                        {else}
                    <div>
                        <select class="form-select" name="platform[]" multiple="multiple" data-role="multiselect">
                            {foreach $platforms as $platform}
                                <option value="{$platform['id']}"{if in_array($platform['id'], $app->controller->view->filters->platform)} selected{/if}>{$platform['text']}</option>
                            {/foreach}
                        </select>
                    </div>
                {/if}
            </div>
        {/if}
        <div class="flag-marker-row flag-flag-row">
            {if is_array($app->controller->view->flags) && count($app->controller->view->flags) > 0}
                <div class="tl_filters_title">{$smarty.const.TEXT_FLAG}</div>
                <div class="wl-td w-tdc">
                  {\yii\helpers\Html::dropDownList('flag', $app->controller->view->filters->flag, yii\helpers\ArrayHelper::map($app->controller->view->flags, 'id', 'text'), ['encode'=> false, 'data-role' => 'multiselect-radio', 'options' =>  $app->controller->view->flags , 'class' => 'form-control'])}
                </div>
            {/if}
        </div>
        <div class="flag-marker-row">
            {if is_array($app->controller->view->markers) && count($app->controller->view->markers) > 0}
                <div class="tl_filters_title">{$smarty.const.TEXT_MARKER}</div>
                <div class="wl-td w-tdc">
                 {\yii\helpers\Html::dropDownList('marker', $app->controller->view->filters->marker, yii\helpers\ArrayHelper::map($app->controller->view->markers, 'id', 'text'), ['encode'=> false, 'data-role' => 'multiselect-radio', 'options' =>  $app->controller->view->markers, 'class' => 'form-control' ])}
                </div>
            {/if}
        </div>
{foreach \common\helpers\Hooks::getList('filters/orders', 'filter-1-column') as $filename}
    {include file=$filename}
{/foreach}
    </div>
    <div class="item_filter item_filter_2">
        <div class="tl_filters_title">{$smarty.const.TABLE_HEADING_STATUS}/{$smarty.const.TEXT_STOCK}</div>
        <div class="wl-td">
            <label>{$smarty.const.TEXT_STATUS}</label>
            {Html::dropDownList('status[]', $app->controller->view->filters->status_selected, $app->controller->view->filters->status, ['class' => 'form-control', 'multiple' => 'multiple', 'data-role' => 'multiselect', 'id' => 'orderStatuses'])}
        </div>
        {if empty($tmpOrderController)}
        <div class="wl-td">
            <label>{$smarty.const.TEXT_STOCK_DEFICIT_QUANTITY}</label>
            {Html::checkbox('deficit_only', $app->controller->view->filters->deficit_only, ['class' => 'form-check-input'])}
        </div>
        {/if}
        <div class="tl_filters_title tl_filters_title_border">{$smarty.const.TEXT_ORDER_PLACED}</div>
        <div class="wl-td w-tdc">
             <label class="radio_label"><input type="radio" name="date" value="presel" id="presel" {if $app->controller->view->filters->presel}checked{/if} /> {$smarty.const.TEXT_PRE_SELECTED}</label>
             <select name="interval" class="form-select" {if $app->controller->view->filters->exact}disabled{/if}>
                    {foreach $app->controller->view->filters->interval as $Item}
                        <option {$Item['selected']} value="{$Item['value']}">{$Item['name']}</option>
                    {/foreach}
                </select>
        </div>
        <div class="wl-td wl-td-from w-tdc">
            <label class="radio_label"><input type="radio" name="date" value="exact" id="exact" {if $app->controller->view->filters->exact}checked{/if} /> {$smarty.const.TEXT_EXACT_DATES}</label><table width="100%" cellpadding="0" cellspacing="0"><tr><td><span>{$smarty.const.TEXT_FROM}</span><input id="from_date" type="text" value="{$app->controller->view->filters->from}" autocomplete="off" name="from" class="datepicker form-control form-control-small" {if $app->controller->view->filters->presel}disabled{/if} /></td><td><span class="sp_marg">{$smarty.const.TEXT_TO}</span><input id="to_date" type="text" value="{$app->controller->view->filters->to}" autocomplete="off" name="to" class="datepicker form-control form-control-small" {if $app->controller->view->filters->presel}disabled{/if} /></td></tr></table>
        </div>
        {if $app->controller->view->filters->admin}
        <div class="wl-td w-tdc">
             <label>{$smarty.const.TEXT_WALKIN_ORDER}</label>
             {\yii\helpers\Html::dropDownList('walkin[]', $app->controller->view->filters->walkin, $app->controller->view->filters->admin, ['class' => 'form-control', 'multiple' => 'multiple', 'data-role' => 'multiselect'])}
        </div>
        {/if}
    </div>
    <div class="item_filter item_filter_3">
        <div class="tl_filters_title">{$smarty.const.TEXT_BY_DELIVERY}</div>
        <div class="wl-td f_td_country">
            <label>{$smarty.const.ENTRY_COUNTRY}:</label>
            <input name="delivery_country" value="{$app->controller->view->filters->delivery_country}" id="selectCountry" type="text" class="form-control" placeholder="{$smarty.const.TEXT_TYPE_COUNTRY}" />
        </div>
        {if $app->controller->view->showState == true}
        <div class="wl-td f_td_state">
            <label>{$smarty.const.ENTRY_STATE}:</label>
           <input name="delivery_state" value="{$app->controller->view->filters->delivery_state}" id="selectState" type="text" class="form-control" placeholder="{$smarty.const.TEXT_TYPE_COUNTY}" {if $app->controller->view->filters->delivery_country == ''}disabled{/if} />
        </div>
        {/if}
        {if !empty($app->controller->view->filters->fCoupons)}
        <div class="tl_filters_title tl_filters_title_border">{$smarty.const.TEXT_USED_COUPON}</div>
        <div class="wl-td w-tdc">
            <label class="radio_label">
                <span> {$smarty.const.TEXT_FROM_LIST}</span>
            </label>
            {Html::dropDownList('fc_id[]', $app->controller->view->filters->fc_id, $app->controller->view->filters->fCoupons, ['class' => 'form-control', 'multiple' => 'multiple', 'data-role' => 'multiselect'])}
        </div>
        {/if}
    </div>
    <div class="item_filter item_filter_4">
        <div class="tl_filters_title {*tl_filters_title_border*}">{$smarty.const.TEXT_PAYMENT_SHIPPING}</div>
        <div class="wl-td">
            <label>{$smarty.const.ENTRY_PAYMENT_METHOD}</label>
            {Html::dropDownList('payments[]', $app->controller->view->filters->payments_selected, $app->controller->view->filters->payments, ['class' => 'form-control', 'multiple' => 'multiple', 'data-role' => 'multiselect'])}
        </div>
        <div class="wl-td">
            <label>{$smarty.const.TEXT_CHOOSE_SHIPPING_METHOD}:</label>
            {Html::dropDownList('shipping[]', $app->controller->view->filters->shipping_selected, $app->controller->view->filters->shipping, ['class' => 'form-control', 'multiple' => 'multiple', 'data-role' => 'multiselect'])}
        </div>
        <div class="tl_filters_title tl_filters_title_border">{$smarty.const.TEXT_TOTALS}</div>
        <div class="wl-tdr wl-td-from " style="padding-bottom:10px;">
           <table cellspacing="0" cellpadding="0" width="100%">
             <tr>
               <td><label class="radio_label"><input type="checkbox" class="form-check-input" id="fpFrom" {if isset($app->controller->view->filters->fpFrom) && $app->controller->view->filters->fpFrom}checked{/if} /><span> {$smarty.const.TEXT_FROM}</span></label><input name="fp_from" value="{if isset($app->controller->view->filters->fp_from)}{$app->controller->view->filters->fp_from}{/if}" id="fpFromSumm" type="text" class="form-control-small form-control"  {if !(isset($app->controller->view->filters->fpFrom) && $app->controller->view->filters->fpFrom)}disabled{/if} /></td>
               <td><label class="radio_label"><input type="checkbox" class="form-check-input" id="fpTo" {if isset($app->controller->view->filters->fpTo) && $app->controller->view->filters->fpTo}checked{/if} /><span> {$smarty.const.TEXT_TO}</span></label><input name="fp_to" value="{if isset($app->controller->view->filters->fp_to)}{$app->controller->view->filters->fp_to}{/if}" id="fpToSumm" type="text" class="form-control-small form-control"  {if !(isset($app->controller->view->filters->fpTo) && $app->controller->view->filters->fpTo)}disabled{/if} /></td>
             </tr>
           </table>
        </div>
        <div class="wl-td">
          <label> {$smarty.const.TEXT_TOTAL_LINE}</label>
             <select name="fp_class" id="fpClass" class="form-select" {if !(isset($app->controller->view->filters->fpFrom) && $app->controller->view->filters->fpFrom)  && !(isset($app->controller->view->filters->fpTo) && $app->controller->view->filters->fpTo)}disabled{/if}>
                    {foreach $app->controller->view->filters->fpClass as $Item}
                        <option {$Item['selected']} value="{$Item['value']}">{$Item['name']}</option>
                    {/foreach}
                </select>
        </div>
    </div>
</div>
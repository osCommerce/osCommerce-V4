{use class="common\helpers\Html"}
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
                        <select class="form-control" name="platform[]" multiple="multiple" data-role="multiselect">
                            {foreach $platforms as $platform}
                                <option value="{$platform['id']}"{if in_array($platform['id'], $app->controller->view->filters->platform)} selected{/if}>{$platform['text']}</option>
                            {/foreach}
                        </select>
                    </div>
                {/if}
            </div>
        {/if}
    </div>
    <div class="item_filter item_filter_2">
        <div class="tl_filters_title">{$smarty.const.TABLE_HEADING_STATUS}/{$smarty.const.TEXT_STOCK}</div>
        <div class="wl-td">
            <label>{$smarty.const.TEXT_STATUS}</label>
            {Html::dropDownList('status[]', $app->controller->view->filters->status_selected, $app->controller->view->filters->status, ['class' => 'form-control', 'multiple' => 'multiple', 'data-role' => 'multiselect'])}
        </div>
        <div class="wl-td">
            <label>{$smarty.const.TEXT_NOT_SENT_ONLY}</label>
            {Html::checkbox('not_sent', $app->controller->view->filters->not_sent, ['class' => 'form-control', 'value' => '1'])}
        </div>
        {if $app->controller->view->filters->admin}
        <div class="wl-td w-tdc">
             <label>{$smarty.const.TEXT_WALKIN_ORDER}</label>
             {\yii\helpers\Html::dropDownList('walkin[]', $app->controller->view->filters->walkin, $app->controller->view->filters->admin, ['class' => 'form-control', 'multiple' => 'multiple', 'data-role' => 'multiselect'])}
        </div>
        {/if}
    </div>
    <div class="item_filter item_filter_3">
        <div class="tl_filters_title tl_filters_title_border">{$smarty.const.TEXT_ORDER_PLACED}</div>
        <div class="wl-td w-tdc">
             <label class="radio_label"><input type="radio" name="date" value="presel" id="presel" {if $app->controller->view->filters->presel}checked{/if} /> {$smarty.const.TEXT_PRE_SELECTED}</label>
             <select name="interval" class="form-control" {if $app->controller->view->filters->exact}disabled{/if}>
                    {foreach $app->controller->view->filters->interval as $Item}
                        <option {$Item['selected']} value="{$Item['value']}">{$Item['name']}</option>
                    {/foreach}
                </select>
        </div>
        <div class="wl-td wl-td-from w-tdc">
            <label class="radio_label"><input type="radio" name="date" value="exact" id="exact" {if $app->controller->view->filters->exact}checked{/if} /> {$smarty.const.TEXT_EXACT_DATES}</label><table width="100%" cellpadding="0" cellspacing="0"><tr><td><span>{$smarty.const.TEXT_FROM}</span><input id="from_date" type="text" value="{$app->controller->view->filters->from}" autocomplete="off" name="from" class="datepicker form-control form-control-small" {if $app->controller->view->filters->presel}disabled{/if} /></td><td><span class="sp_marg">{$smarty.const.TEXT_TO}</span><input id="to_date" type="text" value="{$app->controller->view->filters->to}" autocomplete="off" name="to" class="datepicker form-control form-control-small" {if $app->controller->view->filters->presel}disabled{/if} /></td></tr></table>
        </div>
    </div>
    <div class="item_filter item_filter_4">
        {*<div class="tl_filters_title ">{$smarty.const.TEXT_PAYMENT_SHIPPING}</div>
        <div class="wl-td">
            <label>{$smarty.const.ENTRY_PAYMENT_METHOD}</label>
            {Html::dropDownList('payments[]', $app->controller->view->filters->payments_selected, $app->controller->view->filters->payments, ['class' => 'form-control', 'multiple' => 'multiple', 'data-role' => 'multiselect'])}
        </div>
        <div class="wl-td">
            <label>{$smarty.const.TEXT_CHOOSE_SHIPPING_METHOD}:</label>
            {Html::dropDownList('shipping[]', $app->controller->view->filters->shipping_selected, $app->controller->view->filters->shipping, ['class' => 'form-control', 'multiple' => 'multiple', 'data-role' => 'multiselect'])}
        </div>*}
        <div class="tl_filters_title tl_filters_title_border">{$smarty.const.TEXT_TOTALS}</div>
        <div class="wl-tdr wl-td-from " style="padding-bottom:10px;">
           <table cellspacing="0" cellpadding="0" width="100%">
             <tr>
               <td><label class="radio_label"><input type="checkbox" id="fpFrom" {if $app->controller->view->filters->fpFrom }checked{/if} /><span> {$smarty.const.TEXT_FROM}</span></label><input name="fp_from" value="{$app->controller->view->filters->fp_from}" id="fpFromSumm" type="text" class="form-control-small form-control"  {if !$app->controller->view->filters->fpFrom}disabled{/if} /></td>
               <td><label class="radio_label"><input type="checkbox" id="fpTo" {if $app->controller->view->filters->fpTo }checked{/if} /><span> {$smarty.const.TEXT_TO}</span></label><input name="fp_to" value="{$app->controller->view->filters->fp_to}" id="fpToSumm" type="text" class="form-control-small form-control"  {if !$app->controller->view->filters->fpTo}disabled{/if} /></td>
             </tr>
           </table>
        </div>
             {Html::hiddenInput('fp_class', 'ot_total')}
             {*
        <div class="wl-td">
          <label> {$smarty.const.TEXT_TOTAL_LINE}</label>
             <select name="fp_class" id="fpClass" class="form-control" {if !$app->controller->view->filters->fpFrom  && !$app->controller->view->filters->fpTo}disabled{/if}>
                    {foreach $app->controller->view->filters->fpClass as $Item}
                        <option {$Item['selected']} value="{$Item['value']}">{$Item['name']}</option>
                    {/foreach}
                </select>
        </div>
        *}
    </div>
</div>
{use class="yii\helpers\Html"}
<div class="w-line-row curr-block js-currency-table">
    <table class="tl-grid currency-table">
        <tr>
            <th>{$smarty.const.BOX_LOCALIZATION_CURRENCIES}</th>
            <th>{$smarty.const.TEXT_DEFAULT}</th>
            <th>{$smarty.const.TEXT_PLATFORM_CURRENCY_RATE}</th>
            <th>{$smarty.const.TEXT_CURRENCY_RATE_MARGIN}</th>
        </tr>
        {foreach $currencies->currencies as $code => $_curr}
            <tr class="act {if in_array($code, $platform_currencies)}active_row{else}hide_row{/if} currency_{$code}">
                <td>{$_curr['title']}</td>
                <td class="currency_default">{if $pInfo->default_currency|default:null == $code}<span class="check"></span>{/if}</td>
                <td class="form-group-sm currency_value">
                    <label>{if $currencies->margin_array[$_curr['id']]['custom_currency_value'] != $_curr['_value'] && $currencies->margin_array[$_curr['id']]['use_custom_currency_value']}{$currencies->margin_array[$_curr['id']]['custom_currency_value']}{else}  {sprintf($smarty.const.TEXT_USE_DEFAULT_CURRENCY,$_curr['_value'])}{/if}</label>
                </td>
                <td class="form-group-sm currency_margin">{$currencies->margin_array[$_curr['id']]['margin_value_show']}{$currencies->margin_array[$_curr['id']]['margin_type']}</td>
            </tr>
        {foreachelse}
            <tr><td colspan="4">{$smarty.const.TEXT_NOT_CHOOSEN}</td></tr>
            {/foreach}
    </table>
    <div class="countries_popup popup-box-wrap-page hide_popup" id="currency-table">
        <div class="around-pop-up-page"></div>
        <div class="popup-box-page">
            <div class="pop-up-close-page"></div>
            <div class="pop-up-content-page">
                <div class="popup-heading">{$smarty.const.TEXT_SET_UP} {$smarty.const.BOX_LOCALIZATION_CURRENCIES}</div>
                <div class="popup-content">
                    <table class="tl-grid currency-table">
                        <tr>
                            <th>{$smarty.const.BOX_LOCALIZATION_CURRENCIES}</th>
                            <th>{$smarty.const.TABLE_HEADING_STATUS}</th>
                            <th>{$smarty.const.TEXT_DEFAULT}</th>
                            <th>{$smarty.const.TEXT_PLATFORM_CURRENCY_RATE}</th>
                            <th>{$smarty.const.TEXT_CURRENCY_RATE_MARGIN}</th>
                        </tr>
                        {foreach $currencies->currencies as $code => $_curr}
                            <tr class="act popup_cur_{$code}">
                                <td>{$_curr['title']}</td>
                                <td>{Html::checkbox('pcurrencies[]', in_array($code, $platform_currencies) , ['value' => $code, 'class' => 'p_currencies']) }</td>
                                <td>{Html::radio('default_currency', ($pInfo->default_currency|default:null == $code), ['value'=> $code, 'class' => 'd_currencies', 'title' => {$smarty.const.TEXT_DEFAULT}])}</td>
                                <td class="form-group-sm">
                                    <label>{Html::checkbox('currency_margin['|cat:$_curr['id']|cat:'][use_default]',!$currencies->margin_array[$_curr['id']]['use_custom_currency_value'],['class'=>'form-control js-use_default','style'=>'display:inline-block;width:30px','value'=>'1', 'data-code'=>$code])} {sprintf($smarty.const.TEXT_USE_DEFAULT_CURRENCY,$_curr['_value'])}</label>
                                    {Html::textInput('currency_margin['|cat:$_curr['id']|cat:'][currency_value]',$currencies->margin_array[$_curr['id']]['custom_currency_value'],['class'=>'form-control js-custom-rate','data-default-value'=>$_curr['_value'], 'data-code'=>$code])}
                                </td>
                                <td class="form-group-sm">{Html::textInput('currency_margin['|cat:$_curr['id']|cat:'][value]',$currencies->margin_array[$_curr['id']]['margin_value_show'],['class'=>'form-control js-rate-margin','style'=>'display:inline-block;width:90px','title'=>$_curr['_value'], 'data-code'=>$code])}{Html::dropDownList('currency_margin['|cat:$_curr['id']|cat:'][type]',$currencies->margin_array[$_curr['id']]['margin_type'],['%'=>'%','+'=>'+'],['class'=>'form-control margin_type','style'=>'display:inline-block;width:50px'])}</td>
                            </tr>
                        {foreachelse}
                            <tr><td>{$smarty.const.TEXT_NOT_CHOOSEN}</td><td></td><td></td></tr>
                        {/foreach}
                    </table>
                    <div class="btn-bar">
                        <div class="btn-left"><a href="#" class="btn btn-cancel-foot cancel-popup">{$smarty.const.IMAGE_CANCEL}</a></div>
                        <div class="btn-right"><a href="#" class="btn apply-popup">{$smarty.const.IMAGE_APPLY}</a></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="btn-small-bar after">
        <div class="btn-right"><a class="btn popup_lang" href="#currency-table" data-class="currency-table">{$smarty.const.BUTTON_ADD_MORE_NEW}</a></div>
    </div>
</div> 
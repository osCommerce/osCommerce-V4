{use class="common\helpers\Html"}
{Html::beginForm($actionUrl, 'post', ['id'=>'stock-indication-form'])}

<div class="container form-container">
    {if $languages|count > 1}
    <div class="tabbable-custom" style="margin-top: 20px">
        <ul class="nav nav-tabs ">
            {foreach $languages as $lKey => $lItem}
                <li{if $lKey == 0} class="active"{/if} data-bs-toggle="tab" data-bs-target="#tab_{$lItem['code']}"><a>{$lItem['logo']}<span>{$lItem['name']}</span></a></li>
            {/foreach}
        </ul>
        <div class="tab-content  ">
            {/if}
            {foreach $languages as $lKey => $lItem}
                {if $languages|count > 1}<div class="tab-pane{if $lKey == 0} active{/if}" id="tab_{$lItem['code']}">{/if}
                <div class="row">
                    <div class="col-md-2"><label>{$smarty.const.TEXT_INFO_STOCK_INDICATOR_TEXT}</label></div>
                    <div class="col-md-4">
                        {$stock_indication_text_inputs[$lItem.id]}
                    </div>
                </div>
                {if $languages|count > 1}</div>{/if}
            {/foreach}
            {if $languages|count > 1}
        </div>
    </div>
    {/if}


    <div class="row">
        <div class="col-md-2"><label>{$smarty.const.TEXT_STOCK_CODE_TYPE}</label></div>
        <div class="col-md-4">{tep_draw_pull_down_menu('stock_code', $stockCodeVariants, $oInfo->stock_code, 'class="form-control"')}
        </div>
    </div>

    <div class="row">
        <div class="col-md-2"><label>{$smarty.const.TEXT_ALLOW_OUT_OF_STOCK_CHECKOUT}</label></div>
        <div class="col-md-4">
            {tep_draw_checkbox_field('allow_out_of_stock_checkout',1, !!$oInfo->allow_out_of_stock_checkout)}
        </div>
    </div>
    <div class="row">
        <div class="col-md-2"><label>{$smarty.const.TEXT_ALLOW_OUT_OF_STOCK_ADD_TO_CART}</label></div>
        <div class="col-md-4">
            {tep_draw_checkbox_field('allow_out_of_stock_add_to_cart',1, !!$oInfo->allow_out_of_stock_add_to_cart)}
        </div>
    </div>
    <div class="row">
        <div class="col-md-2"><label>{$smarty.const.TEXT_ALLOW_IN_STOCK_NOTIFY}</label></div>
        <div class="col-md-4">
            {tep_draw_checkbox_field('allow_in_stock_notify',1, !!$oInfo->allow_in_stock_notify)}
        </div>
    </div>
    {if \common\helpers\Extensions::isAllowed('Quotations')}
    <div class="row">
        <div class="col-md-2"><label>{$smarty.const.TEXT_REQUEST_FOR_QUOTE}</label></div>
        <div class="col-md-4">
            {tep_draw_checkbox_field('request_for_quote',1, !!$oInfo->request_for_quote)}
        </div>
    </div>
    {/if}
    <div class="row">
        <div class="col-md-2"><label>{$smarty.const.TEXT_IS_HIDDEN}</label></div>
        <div class="col-md-4">
            {tep_draw_checkbox_field('is_hidden',1, !!$oInfo->is_hidden)}
        </div>
    </div>
    <div class="row">
        <div class="col-md-2"><label>{$smarty.const.TEXT_DISABLE_PRODUCT_ON_OOS}</label></div>
        <div class="col-md-4">
            {tep_draw_checkbox_field('disable_product_on_oos',1, !!$oInfo->disable_product_on_oos)}
        </div>
    </div>
    <div class="row">
        <div class="col-md-2"><label>{$smarty.const.TEXT_LIMIT_CART_QTY_BY_STOCK}</label></div>
        <div class="col-md-4">
            {tep_draw_checkbox_field('limit_cart_qty_by_stock',1, !!$oInfo->limit_cart_qty_by_stock)}
        </div>
    </div>
    <div class="row">
        <div class="col-md-2"><label>{$smarty.const.TEXT_RESET_STATUS_ON_OOS}</label></div>
        <div class="col-md-4">
            {tep_draw_checkbox_field('reset_status_on_oos',1, !!$oInfo->reset_status_on_oos)}
        </div>
    </div>
    {if $extClass = \common\helpers\Acl::checkExtensionAllowed('ObsoleteProducts', 'allowed')}
        <div class="row">
            <div class="col-md-2"><label>{$smarty.const.TEXT_IS_OBSOLETE}</label></div>
            <div class="col-md-4">
                {tep_draw_checkbox_field('is_obsolete',1, !!$oInfo->is_obsolete)}
            </div>
        </div>
    {/if}

    <div class="row">
        <div class="col-md-2"><label>{$smarty.const.TEXT_STOCK_INTICATION_PRICE_ON}</label></div>
        <div class="col-md-4">
            {tep_draw_radio_field('display_price_options',0, ($oInfo->display_price_options==0))}
        </div>
    </div>
    <div class="row">
        <div class="col-md-2"><label>{$smarty.const.TEXT_STOCK_INTICATION_PRICE_OFF}</label></div>
        <div class="col-md-4">
            {tep_draw_radio_field('display_price_options',1, ($oInfo->display_price_options==1))}
        </div>
    </div>
    <div class="row">
        <div class="col-md-2"><label>{$smarty.const.TEXT_STOCK_INTICATION_PRICE_ZERO}</label></div>
        <div class="col-md-4">
            {tep_draw_radio_field('display_price_options',2, ($oInfo->display_price_options==2))}
        </div>
    </div>

    <div class="row">
        <div class="col-md-2"><label>{$smarty.const.TEXT_STOCK_INTICATION_VIRTUAL_INC}</label></div>
        <div class="col-md-4">
            {tep_draw_radio_field('display_virtual_options',0, ($oInfo->display_virtual_options==0))}
        </div>
    </div>
    <div class="row">
        <div class="col-md-2"><label>{$smarty.const.TEXT_STOCK_INTICATION_VIRTUAL_ON}</label></div>
        <div class="col-md-4">
            {tep_draw_radio_field('display_virtual_options',1, ($oInfo->display_virtual_options==1))}
        </div>
    </div>
    <div class="row">
        <div class="col-md-2"><label>{$smarty.const.TEXT_STOCK_INTICATION_VIRTUAL_OFF}</label></div>
        <div class="col-md-4">
            {tep_draw_radio_field('display_virtual_options',2, ($oInfo->display_virtual_options==2))}
        </div>
    </div>
    {if (!$oInfo->is_default)}
        <div class="row">
            <div class="col-md-2"><label>{$smarty.const.TEXT_SET_DEFAULT}</label></div>
            <div class="col-md-4">
                {tep_draw_checkbox_field('is_default',1)}
            </div>
        </div>
    {/if}

    <div class="row">
        <div class="col-md-2"><label>{$smarty.const.TEXT_INFO_STOCK_INDICATOR_LINK_TERMS}</label></div>
        <div class="col-md-4">
            {foreach \common\classes\StockIndication::get_delivery_terms() as $term}
                <div class="js-link-term-row">
                    {Html::checkbox('linked_stock_terms[]', isset($oInfo->linked_stock_terms[(int)$term['id']]), ['class' => "js-logic", 'id' => 'linked_stock_term_'|cat:$term['id'], 'value' => (int)$term['id']]) }
                    {tep_draw_radio_field('is_default_term', $term['id'], (isset($oInfo->linked_stock_terms[$term['id']]) && $oInfo->linked_stock_terms[$term['id']]['is_default_term']))}
                    <label for="linked_stock_term_{$term['id']}"><span>{$term['text']}</span></label>
                </div>
            {/foreach}
        </div>
    </div>
{*
    echo '<div class="btn-toolbar btn-toolbar-order">';
        echo
        '<input type="button" value="' . IMAGE_UPDATE . '" class="btn btn-no-margin" onclick="itemSave('.($oInfo->stock_indication_id?$oInfo->stock_indication_id:0).')">'.
        '<input type="button" value="' . IMAGE_CANCEL . '" class="btn btn-cancel" onclick="resetStatement()">';
        echo '</div>';*}
</div>

<div class="btn-bar" style="padding: 0;">
    <div class="btn-left">
        <a href="{$cancelUrl}" class="btn btn-cancel">{$smarty.const.IMAGE_CANCEL}</a>
    </div>
    <div class="btn-right">
        <button type="submit" class="btn btn-confirm">{$smarty.const.IMAGE_UPDATE}</button>
    </div>
</div>

{Html::endForm()}

<script type="text/javascript">
    $(function(){
        $('input[type="checkbox"]').not('.js-logic').bootstrapSwitch( {
            onText: "{$smarty.const.SW_ON}",
            offText: "{$smarty.const.SW_OFF}",
            handleWidth: '20px',
            labelWidth: '24px'
        } );

        $('.btn-confirm').on('click', function(e){
            e.preventDefault();

            var form = $('#stock-indication-form');
            var data = form.serializeArray();
            var action = form.attr('action');
            $.post(action, data, function (respons) {
                alertMessage('<div class="popup-content pop-mess-cont">' + respons.message + '</div>');

                if (respons.added) {
                    url = window.location.href.replace('stock_indication_id=', '_a=');
                    if (url.search('\\?') === -1) {
                        url = url +'?';
                    } else {
                        url = url +'&';
                    }
                    window.location.href = url + "stock_indication_id=" + respons.added;
                }

                setTimeout(function(){
                    $('.popup-box-wrap:last').remove();
                }, 1000)
            }, 'json');

            return false;
        })

        $('.js-link-term-row input').on('click', function(event){
            var $input = $(event.currentTarget);
            var $row = $input.parents('.js-link-term-row');
            if ( $input.attr('type')=='checkbox' ){
                var $radioList = $('.js-link-term-row input[type="radio"]');
                if ( $input[0].checked ){
                    if ($radioList.filter(':checked').length==0){
                        $radioList.filter('[value="'+$input.val()+'"]').get(0).checked = true;
                    }
                }else{
                    $radioList.filter('[value="'+$input.val()+'"]').get(0).checked = false;
                    $radioList.filter('[value="'+$('.js-link-term-row input[type="checkbox"]:checked').first().val()+'"]').get(0).checked = true;
                }
            }else if ( $input.attr('type')=='radio' && $input[0].checked ){
                $row.find('input[type="checkbox"]').not(':checked').each(function(){
                    this.checked = true;
                })
            }
        });
    });
</script>

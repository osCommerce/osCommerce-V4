{use class="common\helpers\Html"}
{Html::beginForm($actionUrl)}

<div class="container form-container">
    <div class="row">
        <div class="col-md-2"><label>{$smarty.const.TEXT_ORDERS_STATUS_GROUP}</label></div>
        <div class="col-md-4">
            {\yii\helpers\Html::dropDownList('orders_status_groups_id', $oInfo->orders_status_groups_id, \common\helpers\Status::getStatusGroupsList(false, $typeId), ['class' => 'form-control'])}
        </div>
    </div>
    {if is_array($osOesList)}
    <div class="row">
        <div class="col-md-2"><label>{$smarty.const.TEXT_INFO_ORDERS_STATUS_ORDER_EVALUATION_STATE}</label></div>
        <div class="col-md-4">
            {\yii\helpers\Html::dropDownList('order_evaluation_state_id', $oInfo->order_evaluation_state_id, $osOesList, ['class' => 'form-control'])}
        </div>
    </div>
    <div class="row">
        <div class="col-md-2"><label>{$smarty.const.TEXT_INFO_ORDERS_STATUS_ORDER_EVALUATION_STATE_DEFAULT}</label></div>
        <div class="col-md-4">
            {tep_draw_checkbox_field('order_evaluation_state_default', '1', $oInfo->order_evaluation_state_default|default:null)}
        </div>
    </div>
    {/if}
    <div class="row">
        <div class="col-md-2"><label>{$smarty.const.TEXT_ORDERS_STATUS_TEMPLATE}</label></div>
        <div class="col-md-4">
            {Html::dropDownList('orders_status_template', $oInfo->orders_status_template|default:null, $orders_status_template, ['class'=>'form-control'])}
        </div>
    </div>
    <div class="row">
        <div class="col-md-2"><label>{$smarty.const.TEXT_ORDERS_STATUS_TEMPLATE_CONFIRM}</label></div>
        <div class="col-md-4">
            {Html::dropDownList('orders_status_template_confirm', $oInfo->orders_status_template_confirm|default:null, $orders_status_template, ['class'=>'form-control'])}
        </div>
    </div>
    <div class="row">
        <div class="col-md-2"><label>{$smarty.const.TEXT_ORDERS_STATUS_TEMPLATE_SMS}</label></div>
        <div class="col-md-4">
            {tep_draw_pull_down_menu('orders_status_template_sms', $orders_status_template_sms, $oInfo->orders_status_template_sms|default:null, 'class="form-control"')}
        </div>
    </div>
    <div class="row">
        <div class="col-md-2"><label>{$smarty.const.TEXT_COMMENT_TEMPLATE}</label></div>
        <div class="col-md-4">
            {Html::dropDownList('comment_template_id', $comment_templates['selected'], $comment_templates['items'], array_merge($comment_templates['options'],['class'=>'form-control']))}
        </div>
    </div>

    {if DEFAULT_ORDERS_STATUS_ID != $oInfo->orders_status_id}
        <div class="row">
            <div class="col-md-2"><label>{$smarty.const.TEXT_SET_DEFAULT}</label></div>
            <div class="col-md-4">
                {tep_draw_checkbox_field('default')}
            </div>
        </div>
    {/if}
    {if defined('DEFAULT_ONLINE_PAYMENT_ORDERS_STATUS_ID') && $smarty.const.DEFAULT_ONLINE_PAYMENT_ORDERS_STATUS_ID != $oInfo->orders_status_id}
        <div class="row">
            <div class="col-md-2"><label for='defaultOnlinePaymentStatus'>{$smarty.const.DEFAULT_ONLINE_PAYMENT_ORDERS_STATUS}</label></div>
            <div class="col-md-4">
                {Html::checkbox('defaultOnlinePaymentStatus', false, ['class'=> 'check_on_off'])}
            </div>
        </div>
    {/if}
    {if defined('DEFAULT_ONLINE_PAYMENT_SUCCESS_ORDERS_STATUS_ID') && $smarty.const.DEFAULT_ONLINE_PAYMENT_SUCCESS_ORDERS_STATUS_ID != $oInfo->orders_status_id}
        <div class="row">
            <div class="col-md-2"><label for='defaultOnlinePaymentSuccessStatus'>{$smarty.const.TEXT_DEFAULT_ONLINE_PAYMENT_SUCCESS_ORDERS_STATUS}</label></div>
            <div class="col-md-4">
                {Html::checkbox('defaultOnlinePaymentSuccessStatus', false, ['class'=> 'check_on_off'])}
            </div>
        </div>
    {/if}
    <div class="row">
        <div class="col-md-2"><label>{$smarty.const.TEXT_AUTOMATED}</label></div>
        <div class="col-md-4">
            {tep_draw_checkbox_field('automated', '1', $oInfo->automated|default:null)}
        </div>
    </div>
    {if isset($oInfo->orders_status_allocate_allow)}
    <div class="row">
        <div class="col-md-2"><label>{$smarty.const.TEXT_ORDERS_STATUS_ALLOCATE_ALLOW}</label></div>
        <div class="col-md-4">
            {tep_draw_checkbox_field('orders_status_allocate_allow', '1', $oInfo->orders_status_allocate_allow)}
        </div>
    </div>
    {/if}
    {if isset($oInfo->orders_status_release_deferred)}
    <div class="row">
        <div class="col-md-2"><label>{$smarty.const.TEXT_ORDERS_STATUS_RELEASE_DEFERRED}</label></div>
        <div class="col-md-4">
            {tep_draw_checkbox_field('orders_status_release_deferred', '1', $oInfo->orders_status_release_deferred)}
        </div>
    </div>
    {/if}
    <div class="row">
        <div class="col-md-2"><label>{$smarty.const.TEXT_INFO_ORDERS_STATUS_GROUPS_SEND_GA}</label></div>
        <div class="col-md-4">
            {tep_draw_checkbox_field('orders_status_send_ga', '1', $oInfo->orders_status_send_ga|default:null, '', 'disabled')}
        </div>
    </div>
    <div class="row">
        <div class="col-md-2"><label for='hidden'>{$smarty.const.TEXT_INACTIVE}</label></div>
        <div class="col-md-4">
            {Html::checkbox('hidden', $oInfo->hidden|default:null, ['class'=> 'check_on_off'])}
        </div>
    </div>
</div>

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
                <div class="col-md-2"><label>{$smarty.const.TEXT_INFO_ORDERS_STATUS_NAME}</label></div>
                <div class="col-md-4">
                    {$orders_status_inputs_string[$lItem.id]}
                </div>
            </div>
            {if $languages|count > 1}</div>{/if}
        {/foreach}
        {if $languages|count > 1}
    </div>
</div>
{/if}


{if $platforms|count > 1}
<div class="tabbable-custom" style="margin-top: 20px">
    <ul class="nav nav-tabs ">
        {foreach $platforms as $pKey => $pItem}
            <li{if $pKey == 0} class="active"{/if} data-bs-toggle="tab" data-bs-target="#platform_{$pItem['id']}"><a><span>{$pItem['text']}</span></a></li>
        {/foreach}
    </ul>
    <div class="tab-content  ">
        {/if}
        {foreach $platforms as $pKey => $pItem}
            {if $platforms|count > 1}<div class="tab-pane{if $pKey == 0} active{/if}" id="platform_{$pItem['id']}">{/if}
            <div class="row">
                <div class="col-md-2"><label>{$smarty.const.DESIGN_TEMPLATE}</label></div>
                <div class="col-md-4">
                    {\yii\helpers\Html::dropDownList('designTemplates['|cat:$pItem['id']|cat:']', $emailDesignTemplate[$pItem['id']], $designTemplates[$pItem['id']], ['class' => 'form-control'])}
                </div>
            </div>
            {if $platforms|count > 1}</div>{/if}
        {/foreach}
        {if $platforms|count > 1}
    </div>
</div>
{/if}

{if $ext = \common\helpers\Extensions::isAllowed('OrderStatusRules')}
    {$ext::orderStatusEditBlock($oInfo)}
{/if}

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
        $('input[type="checkbox"]').bootstrapSwitch( {
            onText: "{$smarty.const.SW_ON}",
            offText: "{$smarty.const.SW_OFF}",
            handleWidth: '20px',
            labelWidth: '24px'
        } );

        $('.btn-confirm').on('click', function(e){
            e.preventDefault();

            var form = $('.content-container > form');
            var data = form.serializeArray();
            var action = form.attr('action');
            $.post(action, data, function (respons) {
                alertMessage('<div class="popup-content pop-mess-cont">' + respons.message + '</div>');

                if (respons.added) {
                  url = window.location.href.replace('orders_status_id=', '_a=');
                  if (url.search('\\?') === -1) {
                    url = url +'?';
                  } else {
                    url = url +'&';
                  }
                  window.location.href = url + "orders_status_id=" + respons.added;
                  //window.location.href = "orders_status/edit?orders_status_id=" + respons.added;
                }

                setTimeout(function(){
                    $('.popup-box-wrap:last').remove();
                }, 1000)
            }, 'json');

            return false;
        })
    });
</script>

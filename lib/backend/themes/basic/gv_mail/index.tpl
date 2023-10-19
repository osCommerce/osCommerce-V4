{use class="yii\helpers\Html"}
<!--=== Page Header ===-->
<div class="page-header">
    <div class="page-title">
        <h3>{$app->controller->view->headingTitle}</h3>
    </div>
</div>
<!-- /Page Header -->

<div>
<div class="popup-heading popup-heading-coup">{$smarty.const.IMAGE_SEND_EMAIL}</div>
    <div class="col-md-12" style="padding:10px;">
        <div class="widget-content ">
            {if {$messages|default:array()|@count} > 0}
			   {foreach $messages as $type => $message}
              <div class="alert alert-{$type} fade in">
                  <i data-dismiss="alert" class="icon-remove close"></i>
                  <span id="message_plce">{$message}</span>
              </div>
			   {/foreach}
			{/if} 
            <div align = "left">
                {tep_draw_form('mail', 'gv_mail/sendemailtouser', '', 'post', 'onSubmit="return false;"')}
                {if (strpos($app->request->referrer, 'customers'))}
                    {tep_draw_hidden_field('referrer', $app->request->getReferrer())}                    
                {/if}
                    <table cellspacing="0" cellpadding="0" width="100%">
                        {if $cid}
                        <tr>
                            <td class="label_name">{$smarty.const.COUPON_CODE}</td>
                            <td>{tep_draw_hidden_field('coupon_id', $cInfo->coupon_id)} {$cInfo->coupon_code}&nbsp;({$amount}) {if !$cInfo->not_expired}&nbsp;<span style="color:#ff0000">{$smarty.const.TEXT_EXPIRED}</span>{/if}</td>
                        </tr>
                        {/if}
                        <tr>
                            <td class="label_name">{$smarty.const.TEXT_CUSTOMER}</td>
                            <td class="f_td_company">
                                <div class="prods-wrap auto-wrapp" style="width:100%;">
                                {Html::textInput('customers_email_address_field', $customers_email_address, ['class' => 'form-control button-search', 'id' => 'searchCustomers', 'autocomplete'=> 'nope'])}
                                {Html::hiddenInput('customers_email_address', $customers_email_address, ['id' => 'searchCustomersResult'])}
                                 </div>
                            </td>
                        </tr>
                        <tr>
                            <td class="label_name">{$smarty.const.TEXT_SUBJECT}</td>
                            <td>{tep_draw_input_field('subject', '', 'class="form-control"')}</td>
                        </tr>
                          
                        {if $list|count>0 && $type == 'C'}
                        <tr>
                            <td class="label_name">{$smarty.const.TEXT_COUPONS}</td>
                            <td>{\yii\helpers\Html::dropDownList('coupon_id', '', $list, ['class' =>'form-control', prompt => PULL_DOWN_DEFAULT])}</td>
                        </tr>
                        {elseif !($cid && $cInfo->not_expired )}
                        <tr>
                            <td class="label_name">{$smarty.const.TEXT_AMOUNT}</td>
                            <td>{tep_draw_input_field('amount','', 'class="form-control"')}</td>
                          </tr>
                        {/if}
                          
                        <tr>
                            <td class="label_name">{$smarty.const.TEXT_MESSAGE}</td>
                            <td width="100%">{tep_draw_textarea_field('message', 'soft', '60', '15', '', "id='editor' class='ckeditor form-control' style='width: 100%;'")}</td>
                        </tr>
                        </table>
                        <br/>
                        <div class="btn-bar" style="padding: 0;">
                            {if $app->request->isAjax}
                            <div class="btn-left">
                                <a href="javascript:void(0);" onclick="return backStatement();" class="btn btn-cancel">{$smarty.const.IMAGE_CANCEL}</a>
                            </div>
                            {/if}
                            <div class="btn-right">
                                <button class="btn btn-confirm" type="submit">{$smarty.const.IMAGE_SEND_EMAIL}</button>
                            </div>
                        </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
{if (strpos($app->request->referrer, 'customers'))}
           $('form[name=mail]').submit(function(){
            if (checkForm(1)){
              $.post($(this).attr('action'),
                $('form[name=mail]').serialize(),
                function(data){
                    $('.pop-up-content .widget-content').before('<div class="alert alert-'+data.messageType+' fade in"><i data-dismiss="alert" class="icon-remove close"></i><span id="message_plce">'+data.message+'</span></div>');
                    setTimeout(function(){  $('.pop-up-close').trigger('click');},2000);
              }, 'json');
            }
            return false;
           });
{else}
        $('form[name=mail]').submit(function(){
            var _form = $(this);
            if (checkForm(1)){
                bootbox.confirm("The email will be sent to <b>"+$('#searchCustomers').val()+"</b>. Please confirm.", function(confirmed){
                    if ( !confirmed ) return;
                    _form.get(0).submit();
                });
            }
            return false;
        });
{/if}

    function backStatement(){
        {if $app->request->isAjax}
            $('.pop-up-close').trigger('click');
        {else}
            return;
        {/if}
    }

    $('#searchCustomers').autocomplete({
        source: "{$app->urlManager->createUrl('gv_mail/customer-search')}",
        minLength: 0,
        autoFocus: true,
        delay: 0,
        appendTo: '.prods-wrap.auto-wrapp',
        open: function (e, ui) {
            if ($(this).val().length > 0) {
                var acData = $(this).data('ui-autocomplete');
                acData.menu.element.find('a').each(function () {
                    var me = $(this);
                    var keywords = acData.term.split(' ').join('|');
                    me.html(me.text().replace(new RegExp("(" + keywords + ")", "gi"), '<b>$1</b>'));
                });
            }
        },
        select: function(event, ui) {
            $("#searchCustomersResult").val(ui.item.id);
        },
    });

    function wrapMessage(str){
        alertMessage('<div class="widget box"><div class="widget-content">'+str+'</div><div class="noti-btn"><div><span class="btn btn-cancel">{$smarty.const.TEXT_OK}</span></div></div></div>');
    }
    
    function checkForm(mes){
        var noerror = true;
        if (($('input[name=customers_email_address]').is('input') && !$('input[name=customers_email_address]').val())){
            if ($('input[name=customers_email_address_field]').is('input') && $('input[name=customers_email_address_field]').val()) {
                $('input[name=customers_email_address]').val($('input[name=customers_email_address_field]').val());
            } else {
                bootbox.alert("{$smarty.const.TEXT_NO_SELECTED_CUSTOMERS|escape:'javascript'}");
                noerror = false;
            }
        }
        if (($('select[name=coupon_id]').length > 0 && !$('select[name=coupon_id]').val())
            ||
            ($('input[name=coupon_id]').length > 0 && !$('input[name=coupon_id]').val())
        ){
            if (mes != 1) bootbox.alert('please select coupon');
            noerror = false;
        }
        return noerror;
    }
    </script>


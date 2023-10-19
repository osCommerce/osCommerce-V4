{use class="\common\helpers\Html"}
<style>
.buttons_hours { border-top:none; }
{if $popup == 1}
.popup-box { width:850px; }
{/if}
</style>
    {Html::beginForm(\yii\helpers\Url::to(['submit', 'page' => $page, 'gID' => $mInfo->groups_id, 'action' => 'save']), 'post', ['id' => 'save_item_form', 'enctype' => 'multipart/form-data', 'onSubmit'=> "return saveItem();" ])}
    {if $popup == 1}
        <div class="popup-content popup-content-cgr">    
    {/if}

{if $ext = \common\helpers\Acl::checkExtensionAllowed('UserGroupsExtraDiscounts', 'allowed')}
<div class="tabbable tabbable-custom">
  <ul class="nav nav-tabs">
    <li class="active" data-bs-toggle="tab" data-bs-target="#tab_0"><a class="flag-span"><span>{$smarty.const.TEXT_MAIN}</span></a></li>
    {$ext::showTabHeading()}
  </ul>
  <div class="tab-content">
    <div class="tab-pane active" id="tab_0">
{/if}

    <div class="row">
        <div class="col-md-6">
            <div class="widget box box-no-shadow">
                <div class="widget-header">
                    <h4>{$smarty.const.TEXT_EDIT_INTRO}</h4>
                </div>
                <div class="widget-content">
                    <div class="row_fields">
                        <div class="row_fields_text">{$smarty.const.TEXT_GROUPS_NAME}</div>
                        <div class="row_fields_value">{tep_draw_input_field('groups_name', $mInfo->groups_name, 'class="form-control"')}</div>
                    </div>
                    <div class="row_fields">
                        <div class="row_fields_text">{$smarty.const.DEFAULT_HOME_PAGE}<span class="colon">:</span></div>
                        <div class="row_fields_value">{Html::textInput('default_landing_page', $mInfo->default_landing_page)}</div>
                    </div>
{if $showOtherGroups == 0}
                    <div class="row_fields">
                        <div class="row_fields_text">{$smarty.const.TEXT_GROUPS_DISCOUNT}&nbsp;%</div>
                        <div class="row_fields_value">{tep_draw_input_field('groups_discount', $mInfo->groups_discount, 'size="5" class="form-control"')}</div>
                    </div>
                    <div class="row_fields">
                        <div class="row_fields_text">{$smarty.const.TEXT_GROUPS_COMMISSION}:&nbsp;%</div>
                        <div class="row_fields_value">{tep_draw_input_field('groups_commission', $mInfo->groups_commission, 'size="5" class="form-control"')}</div>
                    </div>
                    <div class="row_fields">
                      {Html::checkbox('per_product_price', $mInfo->per_product_price, ['value' => 1])}<label for="perProductPrice">{$smarty.const.TEXT_PER_PRODUCT_PRICE}</label>
                    </div>
                    <div class="row_fields">
                      {Html::checkbox('apply_groups_discount_to_specials', $mInfo->apply_groups_discount_to_specials, ['value' => 1])}<label for="applyGroupsDiscountToSpecials">{$smarty.const.TEXT_GROUPS_APPLY_DISCOUNT_TO_SPECIALS}</label>
                    </div>
                    {if $ext = \common\helpers\Acl::checkExtensionAllowed('BusinessToBusiness', 'allowed')}
                        {$ext::showGroupFields($mInfo)}
                    {else}
                        <div class="row_fields dis_module"><input disabled class="uniform" type="checkbox"><span>{$smarty.const.TEXT_GROUPS_IS_TAX_APPLICABLE}</span></div>
                        <div class="row_fields dis_module"><input disabled class="uniform" type="checkbox"><span>{$smarty.const.TEXT_GROUPS_IS_SHOW_PRICE}</span></div>
                        <div class="row_fields dis_module"><input disabled class="uniform" type="checkbox"><span>{$smarty.const.TEXT_GROUPS_DISABLE_CART}</span></div>
                        <div class="row_fields dis_module"><input disabled class="uniform" type="checkbox"><span>{$smarty.const.TEXT_GROUPS_DISABLE_CHECKOUT}</span></div>
                    {/if}
                    <div class="row_fields">
                      {Html::checkbox('new_approve', $mInfo->new_approve, ['value' => 1])}<label for="newApprove">{$smarty.const.TEXT_GROUPS_NEW_APPROVE}</label>
                    </div>
                    <div class="row_fields">
                      {Html::checkbox('groups_is_reseller', $mInfo->groups_is_reseller, ['value' => 1])}<label for="groupsIsReseller">{$smarty.const.TEXT_GROUPS_IS_RESELLER}</label>
                    </div>
                    
                    <div class="row_fields">
                      {Html::checkbox('disable_watermark', $mInfo->disable_watermark, ['value' => 1])}<label for="disableWatermark">{$smarty.const.TEXT_NO_WATERMARK}</label>
                    </div>
                    <div class="row_fields">
                      {Html::checkbox('cart_for_logged_only', $mInfo->cart_for_logged_only, ['value' => 1])}<label for="cartForLoggedOnly">{$smarty.const.TEXT_CART_FOR_LOGGED}</label>
                    </div>
                    
                    {if $popup eq 0}
                        <div class="row_fields">
                            <div class="row_fields_text">{$smarty.const.TEXT_ACTIVE_IMAGE}</div>
                            <div class="row_fields_value">{tep_draw_file_field('image_active')}</div>
                            {if $active neq ''}
                                <div class="row_img"><img src="{$active}" border="0" width="24" height="24"></div>
                            {/if}
                        </div>
                        <div class="row_fields">
                            <div class="row_fields_text">{$smarty.const.TEXT_INACTIVE_IMAGE}</div>
                            <div class="row_fields_value">{tep_draw_file_field('image_inactive')}</div>
                            {if $inactive neq ''}
                                <div class="row_img"><img src="{$inactive}" border="0" width="24" height="24"></div>
                            {/if}
                        </div>
                    {/if}
{/if}
                </div>
            </div>
        </div>

{if $showOtherGroups == 0}
        <div class="col-md-6">
            <div class="widget box box-no-shadow">
                <div class="widget-header">
                    <h4>{$smarty.const.TEXT_CUMULATIVE_DISCOUNTS}&nbsp;{Html::checkbox('groups_use_more_discount', $mInfo->groups_use_more_discount, ['class' => 'check_on_off', 'label' => ''])}</h4>
                </div>
                <div class="widget-content">
                    <div class="additional-discount-holder" {if !$mInfo->groups_use_more_discount}style="display:none"{/if}>
                        <div class="row_fields row">
                            <div class="row_fields_text col-md-8">{$smarty.const.TEXT_SUPERSUM}</div>
                            <div class="row_fields_value col-md-2">{tep_draw_input_field('superdiscount_summ', $mInfo->superdiscount_summ, 'size=8 class="form-control"')}</div>
                        </div>
                        <div class="d-list">
                        {if is_array($mInfo->additionalDiscounts)}
                            {foreach $mInfo->additionalDiscounts as $aDiscount}
                                <div class="row_fields row">
                                    <div class="row_fields_text col-md-2">{$smarty.const.TEXT_AMOUNT}</div>
                                    <div class="row_fields_value col-md-3">{tep_draw_input_field('groups_discounts_amount[]',$aDiscount->groups_discounts_amount, 'size=8 class="form-control"')}</div>
                                    <div class="row_fields_text col-md-2">{$smarty.const.TEXT_DISCOUNT}</div>
                                    <div class="row_fields_value col-md-3">
                                    {tep_draw_input_field('groups_discounts_value[]', $aDiscount->groups_discounts_value, 'size=3 class="form-control"')}
                                    </div>
                                    <div class="row_fields_value col-md-2">
                                     {Html::checkbox('check_supersum[]', $aDiscount->check_supersum, ['class' => 'uniform'])}
                                    </div>
                                </div>
                            {/foreach}
                        {/if}
                        </div>
                        <div class="hid" style="display:none;">
                            <div class="row_fields row">
                                    <div class="row_fields_text col-md-2">{$smarty.const.TEXT_AMOUNT}</div>
                                    <div class="row_fields_value col-md-3">{tep_draw_input_field('groups_discounts_amount[]', '', 'size=8 class="form-control"')}</div>
                                    <div class="row_fields_text col-md-2">{$smarty.const.TEXT_DISCOUNT}</div>
                                    <div class="row_fields_value col-md-3">
                                    {tep_draw_input_field('groups_discounts_value[]', '', 'size=3 class="form-control"')}
                                    </div>
                                    <div class="row_fields_value col-md-2">
                                    {Html::checkbox('check_supersum[]', false, [])}
                                    </div>
                                </div>
                        </div>
                        <div class="buttons_hours">
                            <a href="javascript:void(0)" class="btn" id="more">{$smarty.const.TEXT_ADD_MORE}</a>
                        </div>
                    </div>
                </div>
            </div>
        {foreach \common\helpers\Hooks::getList('customergroups/groupedit', 'right-column') as $filename}
            {include file=$filename}
        {/foreach}
        </div>
{/if}

    </div>

{if $ext = \common\helpers\Acl::checkExtensionAllowed('UserGroupsExtraDiscounts', 'allowed')}
    </div>

    {$ext::showTabContent($mInfo)}

  </div>
</div>
{/if}

    {if $popup == 1}
        </div>
    {/if}

    <div class="noti-btn">
        <div><input class="btn btn-cancel" type="button" onclick="return backStatement()" value="{$smarty.const.IMAGE_CANCEL}"></div>
        <div><input class="btn btn-primary btn-no-margin" type="submit" value="{$smarty.const.IMAGE_SAVE}"></div>
    </div>
    {tep_draw_hidden_field('row_id', $row_id)}
    {tep_draw_hidden_field('item_id', $item_id)}
    {tep_draw_hidden_field('popup', $popup)}
    {if $ext = \common\helpers\Acl::checkExtensionAllowed('ExtraGroups', 'allowed')}
        {if $showOtherGroups}
          {$ext::typeIdHidden( $item_id )}
        {/if}
    {/if}
</form>
<script>
    function backStatement() {
        {if $popup}
            $('.pop-up-close').trigger('click');
        {else}
            window.history.back();
        {/if}
        return false;
    }
    
    function switchStatement(show){
        if (show){
            $('.additional-discount-holder').show();
            
        } else {
            $('.additional-discount-holder').hide();
        }
    }
    
    function saveItem(){
    
        $.each($('[name="check_supersum[]"].uniform'), function(i, e){
            $('#save_item_form').append('<input type ="hidden" name="check_supersum_hidden[]" value="'+($(e).parent().hasClass('checked')?1:0)+'">');
        })
        {if $popup}
            $.post($('#save_item_form').attr('action'), $('#save_item_form').serialize(), function(data){
                
            });
            if ( $('.pop-up-content input[name=item_id]').val() == 0 ){
                window.location.reload();
            }
            $('.pop-up-close').trigger('click');
            return false;
        {else}            
            return true;
        {/if}
        
    }
        
    $(".uniform").uniform();
    
    $(".check_on_off").bootstrapSwitch(
        {
            onSwitchChange: function (element, arguments) {
                    switchStatement(arguments);
                    return true;  
            },
            onText: "{$smarty.const.SW_ON}",
            offText: "{$smarty.const.SW_OFF}",
            handleWidth: '20px',
            labelWidth: '24px'
        }
    );
    
    var max = 0;
    if ($('input[name=groups_use_more_discount]').prop('checked')){
          $('.more_discount').show();
    }
    $('input[name=groups_use_more_discount]').change(function(){
        if ($(this).prop('checked')){
          $('.more_discount').show();
        } else {
          $('.more_discount').hide();
        }
     })
     
      $('#more').click(function(){
        $('.d-list').append($('.hid').html());
        $('.d-list :checkbox:last').addClass('uniform');
        $(".uniform").uniform();
      })
      
        
</script>

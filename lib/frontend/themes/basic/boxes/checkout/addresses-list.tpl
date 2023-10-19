{use class="\yii\helpers\Html"}
{use class="frontend\design\Info"}

{if $mode == 'single'}
    <div class="addresses address-main {$type}-addresses address-in-{$settings[0]['address_in']}" id="{$type}-addresses">
        {if ($settings[0]['address_in'] == 'dropdown' || $settings[0]['address_in'] == 'radio_first') && $addresses}

            {if $settings[0]['address_in'] == 'dropdown'}
            <select name="{$type|cat:'_ab_id'}" id="" class="address-item-selector">
                {foreach $addresses as $address}
                    <option value="{$address['address_book_id']}"{if $selected_ab_id == $address['address_book_id']} selected{/if}>
                        {\common\helpers\Address::address_format($address['country']['address_format_id'], $address, true, '', ', ', true, false, $model->getPrefix())}
                    </option>
                {/foreach}
            </select>
            {elseif $settings[0]['address_in'] == 'radio_first'}
                {foreach $addresses as $address}
                    <label>
                       <input type="radio" name="{$type|cat:'_ab_id'}"  class="address-item-selector" value="{$address['address_book_id']}" {if $selected_ab_id == $address['address_book_id']} checked{/if}/>
                        <span></span>
                        <div class="adress-holder">
                            <div class="address">
                                {\common\helpers\Address::address_format($address['country']['address_format_id'], $address, true, '', '<br> ', true, false, $model->getPrefix())}
                            </div>
                            {if $can_change_address}
                            <span class="edit-ab" data-id="{$address['address_book_id']}">{$smarty.const.EDIT}</span>
                            {/if}
                        </div>

                    </label>
                {/foreach}
            {/if}

            <div class="buttons buttons-under-drop">
                {if $settings[0]['address_in'] == 'dropdown' && $can_change_address}
                <a href="" class="edit-ab" data-id="{$selected_ab_id}">{$smarty.const.EDIT}</a>
                {/if}

                {if $manager->isCustomerAssigned() && !$manager->getCustomersIdentity()->opc_temp_account && count($manager->getCustomersIdentity()->getAddressBooks()) < MAX_ADDRESS_BOOK_ENTRIES && $can_change_address}
                    <a class="add-ab" href="javascript:void(0);" data-id="0">{$smarty.const.IMAGE_BUTTON_ADD_ADDRESS}</a>
                {/if}
            </div>
            <script>
                tl(function(){
                    $('.{$type}-addresses .address-item-selector').change(function(){
                        let value = $(this).val();
                        checkout.change_address_list('{$type}', value, $(this).closest('.block.checkout'));
                    })

                    $('.{$type}-addresses .edit-ab, .{$type}-addresses .add-ab').click(function(e){
                        let id = $(this).data('id');
                        e.preventDefault();
                        if ($('.{$type}-addresses').closest('.box').data('address_in') == 'popup') {
                            checkout.edit_address_popup('{$type}', id);
                        } else {
                            checkout.edit_address('{$type}', id);
                        }
                    })
                })
            </script>
        {else}
        <div class="address-item">
            <label>
                {Html::hiddenInput($type|cat:'_ab_id', $selected_ab_id, ['class' => 'address-item-selector'])}
                {\common\helpers\Address::address_format($address['country']['address_format_id'], $address, true, '', '<br>', true, false, $model->getPrefix())}
{if $can_choose_address}
                <br/>
                <a href="javascript:void(0);" class="change-ab">{$smarty.const.TEXT_CHANGE}</a>
{/if}
            </label>
        </div>
        <script>        
            tl(function(){                
                $('.{$type}-addresses .change-ab').click(function(e){
                    e.preventDefault();
                    if ($('.{$type}-addresses').closest('.box').data('address_in') == 'popup') {
                        checkout.get_address_list_popup('{$type}');
                    } else {
                        checkout.get_address_list('{$type}');
                    }
                })
            })
        </script>
        {/if}
    </div>
    
{elseif $mode == 'select'}
    {if $addresses}
        {if {$wExt = \common\helpers\Acl::checkExtensionAllowed('WeddingRegistry', 'allowed')} && false && $type eq 'shipping'}
            {$wExt::renderCheckoutShippings($addresses, $selected_ab_id)}
        {else}
            <div class="addresses {$type}-addresses" id="{$type}-addresses">

                {foreach $addresses as $address}
                    <div class="address-item">
                        <label>
                            {if $address['drop_ship'] == 1}({$smarty.const.TEXT_DROP_SHIP_ADDRESS})<br>{/if}
                            {Html::radio($type|cat:'_ab_id', $selected_ab_id == $address['address_book_id'], ['value' => $address['address_book_id'], 'class' => 'address-item-selector'])}
                            {\common\helpers\Address::address_format($address['country']['address_format_id'], $address, true, '', '<br>', true, false, $model->getPrefix())}
                        </label>
                        {if $can_change_address}
                        <a href="" class="edit-ab" data-id="{$address['address_book_id']}">{$smarty.const.EDIT}</a>
                        {/if}
                    </div>
                {/foreach}


                <div class="buttons">
                    <a class="btn-cancel" href="javascript:void(0);">{$smarty.const.CANCEL}</a>
                    {if $manager->isCustomerAssigned() && !$manager->getCustomersIdentity()->opc_temp_account && $drop_ship}
                        <a class="add-ab" href="javascript:void(0);" data-id="0">{$smarty.const.TEXT_DROP_SHIP_ADDRESS}</a>
                    {/if}
                    {if $manager->isCustomerAssigned() && !$manager->getCustomersIdentity()->opc_temp_account && count($manager->getCustomersIdentity()->getAddressBooks()) < MAX_ADDRESS_BOOK_ENTRIES && $can_add_address}
                        <a class="add-ab" href="javascript:void(0);" data-id="0">{$smarty.const.IMAGE_BUTTON_ADD_ADDRESS}</a>
                    {/if}
                </div>
                {if $drop_ship}
                <div class="drop-ship-notes">
                    <p>{$smarty.const.TEXT_DROP_SHIP_NOTE1}</span>
                </div>
                {/if}
              <script>
                tl(function(){
                    $('.{$type}-addresses .address-item-selector').change(function(){
                        let value = $(this).val();
                        checkout.change_address_list('{$type}', value, $(this).closest('.block.checkout'));                        
                    })
                    $('.{$type}-addresses .edit-ab, .{$type}-addresses .add-ab').click(function(e){
                        let id = $(this).data('id');
                        e.preventDefault();
                        if ($('.{$type}-addresses').closest('.box').data('address_in') == 'popup') {
                            checkout.edit_address_popup('{$type}', id, '{$drop_ship}');
                        } else {
                            checkout.edit_address('{$type}', id, '{$drop_ship}');
                        }
                    })
                    $('.{$type}-addresses .btn-cancel').click(function(e){
                        checkout.change_address_list('{$type}', null, $(this).closest('.block.checkout'));
                    })
                })
            </script>
            </div>
        {/if}
        
    {/if}
{elseif $mode == 'edit'}
    <div class="addresses {$type}-addresses address-edit-holder" id="{$type}-addresses">
        <div class="address-edit">
        {include './address-area.tpl' model = $model}
        {Html::hiddenInput('drop_ship', $drop_ship)}
        {if ($model->address_book_id || $manager->getCustomersIdentity()->hasAddressBooks()) && ($model->customerAddressIsReady() || $model->address_book_id != $manager->get('sendto') || !$model->address_book_id) }
            <a class="btn-cancel" href="javascript:void(0);">{$smarty.const.CANCEL}</a>
        {/if}
        
        {if $manager->isCustomerAssigned()}{*&& (!$model->customerAddressIsReady() || !$model->address_book_id)*}
        <a class="btn-save btn" href="javascript:void(0);" style="float:right;">{$smarty.const.TEXT_SAVE}</a>
        {/if}
        {if $drop_ship}
            <div class="drop-ship-notes">
                <p>{$smarty.const.TEXT_DROP_SHIP_NOTE2}</p>
                <p>{$smarty.const.TEXT_DROP_SHIP_NOTE3}</p>
            </div>
        {/if}
        </div>
        <script>
            tl([
            '{Info::themeFile('/js/main.js')}',
                '{Info::themeFile('/js/select2/select2.min.js')}',
            ],function(){

                try {
                $('.select2').select2({
                    allowClear: true
                });
                } catch ( e ) {
                }

                let addresses = $('.{$type}-addresses');
                let fields = $('input, select', addresses);
                
                {if !Info::themeSetting('checkout_view')}
                fields.validate();
                {/if}

                $('.btn-save', addresses).click(function(e){
                    e.preventDefault();
                    let _invalid = false;
                    $.each($('.{$type}-addresses input, .{$type}-addresses select'), function(i, e){
                        let check = $(e).trigger('check');
                        if ($(check).hasClass('required-error')){
                            _invalid = true;
                        }
                    })
                    if (!_invalid){
                        checkout.save_address('{$type}', $(this).closest('.block.checkout'));
                        if ($('.{$type}-addresses').closest('.box').data('address_in') == 'popup') {
                            setTimeout(function(){
                                $('.popup-box-wrap').remove()
                            }, 0)
                        }
                    }
                })
                $('.btn-cancel',addresses).click(function(e){
                    checkout.change_address_list('{$type}', null, $(this).closest('.block.checkout'));                        
                });

                fields.on('change', { address_prefix: '{$type}_address', address_box:'{$type}-addresses' } , function(event){
                    checkout.copy_address(event);
                    if ( event.target.name && event.target.name.match(/({implode('|',\common\services\OrderManager::getRecalculateShippingFields())})/) ) {
                        checkout.data_changed('recalculation', [{ 'name':'checked_model', 'value':'{$model->formName()}' }]);
                    }
                })
                
            })
        </script>
    </div>
{/if}

<script>
    tl(function(){
        {if $settings[0].address_in}
            $('.{$type}-addresses').closest('.box').attr('data-address_in', '{$settings[0].address_in}')
        {/if}
    })
</script>
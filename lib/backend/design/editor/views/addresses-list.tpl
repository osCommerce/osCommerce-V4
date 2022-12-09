{use class="\yii\helpers\Html"}
{use class="frontend\design\Info"}
{if $mode == 'single'}{*not need*}
    {*<div class="addresses" id="{$type}-addresses">
        <div class="address-item">
            <label>
                {Html::hiddenInput($type|cat:'_ab_id', $selected_ab_id, ['class' => 'address-item-selector'])}
                {\common\helpers\Address::address_format($address['country']['address_format_id'], $address, true, '', '<br>')}
                <br/>
                <a href="javascript:void(0);" class="change-ab">Change</a>
            </label>
        </div>
    </div>*}
    
{else if $mode == 'select'}
    <div class="widget box box-no-shadow">
        <div class="widget-header widget-header-address"><h4><i class="icon-frontends"></i>Select Address</h4></div>
        <div class="widget-content">
        {if $addresses}
            <div class="addresses" id="{$type}-addresses">
                {foreach $addresses as $address}
                    <div class="address-item">
                        <label>
                            <div>
                            {Html::radio($type|cat:'_ab_id', $selected_ab_id == $address['address_book_id'], ['value' => $address['address_book_id'], 'class' => 'address-item-selector'])}
                            </div>
                            <div>
                            {\common\helpers\Address::address_format($address['country']['address_format_id'], $address, true, '', '<br>')}
                            </div>
                        </label>                        
                    </div>                    
                {/foreach}                
            </div>
        {/if}
        </div>
        <div class = "noti-btn">
          <div class="btn-left">
            <a class="btn btn-cancel" href="javascript:void(0);">{$smarty.const.CANCEL}</a>
          </div>
          <div class="btn-right">
            <a class="btn btn-confirm select-address" data-type="{$type}" href="javascript:void(0);">{$smarty.const.IMAGE_CONFIRM}</a>
          </div>
        </div>
        <script>
            (function($){
                $('.select-address').click(function(){                    
                    order.changeAddressList($(this).data('type'), $('.address-item-selector:checked').val());
                })
            })(jQuery)
        </script>
    </div>
{else if $mode == 'edit'}
    <div class="addresses" id="{$type}-addresses">        
        {include './address-area.tpl' model = $model holder='#'|cat:{$type}|cat:'-addresses'}       
        <script>
            (function($){
            
                let adresses = $('#{$type}-addresses');
                let fields = $('input, select', adresses);
                
                fields.validate();
                
                fields.on('change', { address_prefix: '{$type}_address', address_box:'{$type}-addresses' } , function(event){
                    if ($('input[name="ship_as_bill"]').prop('checked')){
                        order.copyAddress({ data: { address_prefix: 'shipping_address', address_box:'shipping-address-box' } }, $('#tab_contact'), '');
                    }
                    if ( event.target.name && event.target.name.match(/({implode('|',\common\services\OrderManager::getRecalculateShippingFields())})/) ) {
                        order.dataChanged($('#checkoutForm'), 'recalculation', [{ 'name':'checked_model', 'value':'{$model->formName()}' }]);
                    }
                })
                
            })(jQuery)
        </script>
    </div>
{/if}
        

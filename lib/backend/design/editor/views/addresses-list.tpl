{use class="\yii\helpers\Html"}
{use class="frontend\design\Info"}
{if $mode == 'single'}{*not need*}


    <div id="{$type}-addresses">
        {if $error}
            <div class="alert alert-danger">
                <div class="">{sprintf(ADDRESSES_HAS_ERROR, $type, Html::a($smarty.const.IMAGE_EDIT, Yii::$app->urlManager->createAbsoluteUrl(['editor/checkout', 'action' => 'get_address_list', 'type' => $type, 'currentCart' => Yii::$app->request->get('currentCart')]), ['class' => 'popup alshipping address-list']))}</div>
            </div>
        {else}
            <div class="address-item">
                <label>
                    {Html::hiddenInput($type|cat:'_ab_id', $selected_ab_id, ['class' => 'address-item-selector'])}
                    {\common\helpers\Address::address_format($address['country']['address_format_id'], $address, true, '', '<br>')}
                </label>
            </div>
            {if $manager->isCustomerAssigned()}
                <div>
                    {Html::a('Change', Yii::$app->urlManager->createAbsoluteUrl(['editor/checkout', 'action' => 'get_address_list', 'type' => $type, 'currentCart' => Yii::$app->request->get('currentCart')]), ['class' => 'popup alshipping address-list'])}
                </div>
            {/if}
        {/if}
    </div>

    
{elseif $mode == 'select'}


        <div class="popup-heading"><i class="icon-frontends"></i>Select Address</div>
        <div class="popup-content">
            <div class="addresses" id="{$type}-addresses">
                {foreach $addresses as $address}
                    <div class="address-item">
                        <label>
                            {Html::radio($type|cat:'_ab_id', $selected_ab_id == $address['address_book_id'], ['value' => $address['address_book_id'], 'class' => 'address-item-selector'])}
                            <div>
                                {\common\helpers\Address::address_format($address['country']['address_format_id'], $address, true, '', '<br>')}

                                <div class=""><a href="{Yii::$app->urlManager->createUrl(['editor/checkout', 'action' => 'edit_address', 'type' => Yii::$app->request->get('type'), 'ad_id' => $address['address_book_id'], 'currentCart' => Yii::$app->request->get('currentCart')])}" class="edit-address-link">Edit</a></div>
                            </div>
                        </label>
                    </div>
                {/foreach}
                <div class="address-item">
                    <label>
                        <div class="add-address" data-href="{Yii::$app->urlManager->createUrl(['editor/checkout', 'action' => 'edit_address', 'type' => Yii::$app->request->get('type'), 'currentCart' => Yii::$app->request->get('currentCart')])}">
                            Add address
                        </div>
                    </label>
                </div>
            </div>
        </div>
        <div class = "popup-buttons">
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
                });

                $('.edit-address-link').on('click', function (e) {
                    e.preventDefault();

                    $.get($(this).attr('href'), function (response) {
                        $('.pop-up-content:last').html(response)
                    })
                })

                $('.add-address').on('click', function (e) {
                    $.get($(this).data('href'), function (response) {
                        $('.pop-up-content:last').html(response)
                    })
                })
            })(jQuery)
        </script>


{elseif $mode == 'edit'}


    <div class="popup-heading"><i class="icon-frontends"></i>Edit Address</div>
    {Html::beginForm('editor/checkout?action=save_address', 'post', ['id' => 'save-address'])}
    <input type="hidden" name="type" value="{Yii::$app->request->get('type')}"/>
    <input type="hidden" name="action" value="save_address"/>
    <div class="popup-content">
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

                    $('.update-address')
                })(jQuery)
            </script>
        </div>
    </div>
    <div class = "popup-buttons">
        <div class="btn-right">
            <button type="submit" class="btn btn-confirm update-address">{$smarty.const.IMAGE_CONFIRM}</button>
        </div>
        <div class="btn-left">
            <span class="btn btn-back">{$smarty.const.IMAGE_BACK}</span>
        </div>
    </div>
    {Html::endForm()}
    <script type="text/javascript">
        $(function () {
            $('#save-address').on('submit', function (e) {
                e.preventDefault();
                $('#save-address input').trigger('change');
                if (!$('#save-address .required-error').length) {
                    $.post(`editor/checkout?action=save_address&currentCart={Yii::$app->request->get('currentCart')}`, $(this).serializeArray(), function (response) {
                        order.renderCheckoutDetails(response);
                        $('.popup-box-wrap:last').remove()
                    }, 'json')
                }
            });

            $('.popup-buttons .btn-back').on('click', function () {
                $.get('{Yii::$app->urlManager->createAbsoluteUrl(['editor/checkout', 'action' => 'get_address_list', 'type' => Yii::$app->request->get('type'), 'currentCart' => Yii::$app->request->get('currentCart')])}', function (response) {
                    $('.pop-up-content:last').html(response)
                })
            })
        })
    </script>


{/if}
        

{use class="yii\helpers\Html"}
{use class="common\helpers\Order"}
    <fieldset>
        <legend>{$smarty.const.TEXT_CUSTOMER_COMMENTS}</legend>
            <div class="f_row">
                <div class="f_td">
                    
                </div>
                <div class="f_td">
                    <label>{$smarty.const.ENTRY_STATUS}</label>
                    {Html::dropDownList('status', $order->info['order_status'], $ordersStatuses, ['class'=>'form-select', 'options' => $ordersStatusesOptions, 'onChange' => 'return doCheckOrderStatus();', 'id' => 'order-status'])}
                    <a id="order_status_more_options_link" href="#order_status_more_options">{$smarty.const.TEXT_MORE_OPTIONS}</a>
                </div>
            </div>
            <div id="order_status_more_options" style="display: none;">
                <div class="popup-heading">{$smarty.const.TEXT_MORE_OPTIONS}</div>
                <div class="popup-content">
                    <div class="" id="evaluation_state_force_holder" style="display: none;">
                        <div class="f_td">
                        </div>
                        <div class="f_td">
                            {Html::checkbox('evaluation_state_force', true, ['label' => TEXT_EVALUATION_STATE_FORCE, 'id' => 'evaluation_state_force'])}
                        </div>
                    </div>
                    <div class="" id="evaluation_state_restock_holder" style="display: none;">
                        <div class="f_td">
                        </div>
                        <div class="f_td">
                            {Html::checkbox('evaluation_state_restock', false, ['label' => TEXT_EVALUATION_STATE_RESTOCK, 'id' => 'evaluation_state_restock'])}
                        </div>
                    </div>
                    <div class="" id="evaluation_state_reset_cancel_holder" style="display: none;">
                        <div class="f_td">
                        </div>
                        <div class="f_td">
                            {Html::checkbox('evaluation_state_reset_cancel', false, ['label' => TEXT_EVALUATION_STATE_RESET_CANCEL, 'id' => 'evaluation_state_reset_cancel'])}
                        </div>
                    </div>
                    {$manager->render('Paying', ['manager' => $manager, 'order' => $order])}
                    <div class="btn-bar">
                        <div class="btn-left">
                            <span class="btn btn-primary btn-cancel">{$smarty.const.IMAGE_CANCEL}</span>
                        </div>
                        <div class="btn-right">
                            <a class="btn btn-primary" onclick="return order_status_more_options_save();" href="#">{$smarty.const.IMAGE_SAVE}</a>
                        </div>
                    </div>
                </div>
            </div>
            <script>
                function order_status_more_options_save() {
                    $('.popup-box').find('input, select').each(function() {
                        if ($(this).is(':checkbox')) {
                            $('div#order_status_more_options #' + $(this).attr('id')).prop('checked', $(this).prop('checked'));
                        } else {
                            $('div#order_status_more_options #' + $(this).attr('id')).val($(this).val());
                        }
                    });
                    $('.popup-box').trigger('popup.close');
                    $('.popup-box-wrap').remove();
                    return false;
                }
                (function($) {
                    $('#order_status_more_options_link').popUp({
                        box_class: 'transactions-popup-box',
                        loaded: function() {
                            $('div#order_status_more_options').find('input, select').each(function() {
                                if ($(this).is(':checkbox')) {
                                    $('.popup-box #' + $(this).attr('id')).prop('checked', $(this).prop('checked'));
                                } else {
                                    $('.popup-box #' + $(this).attr('id')).val($(this).val());
                                }
                            });
                            $('.upade_paid_on_process').trigger('change');
                        }
                    });
                }(jQuery));
                function doCheckOrderStatus() {
                    let element = $('select[name="status"]');
                    $('#evaluation_state_force_holder').hide();
                    $('#evaluation_state_restock_holder').hide();
                    $('#evaluation_state_reset_cancel_holder').hide();
                    $('#evaluation_state_force').prop('checked', true);
                    $('#evaluation_state_restock').prop('checked', false);
                    $('#evaluation_state_reset_cancel').prop('checked', false);
                    if (element.length > 0) {
                        let evaluation_state_id = $(element).find('option[value="' + $(element).val() + '"]').attr('evaluation_state_id');
                        if (evaluation_state_id == '{Order::OES_DISPATCHED}'
                            || evaluation_state_id == '{Order::OES_DELIVERED}'
                        ) {
                            $('#evaluation_state_force_holder').show();
                        } else if (evaluation_state_id == '{Order::OES_CANCELLED}') {
                            $('#evaluation_state_restock_holder').show();
                        } else if (evaluation_state_id == '{Order::OES_PENDING}') {
                            $('#evaluation_state_reset_cancel_holder').show();
                        }
                        return true;
                    }
                    return false;
                }
                $(document).ready(function() {
                    doCheckOrderStatus();
                });
            </script>
            
            <div class="f_row">
                <div class="f_td">
                    <label>{$smarty.const.TABLE_HEADING_COMMENTS}:</label>
                </div>
                <div class="f_td">
                    {Html::textArea('comments', '', ['rows' => '5', 'class' => 'form-control'])}
                </div>
            </div>
            {$manager->render('CommentTemplate', ['order' => $order])}
            {$manager->render('Trustpilot', ['order' => $order])}
            {$manager->render('SMS', ['order' => $order])}
            <div class="f_row">
                <div class="f_td"></div>
                <div class="f_td">
                    {Html::checkbox('notify', true, ['class' => 'form-check-input'])}
                    <b>{$smarty.const.ENTRY_NOTIFY_CUSTOMER}</b>
                    {Html::checkbox('notify_comments', true, ['class' => 'form-check-input'])}
                    <b>{$smarty.const.ENTRY_NOTIFY_COMMENTS}</b>                    
                    <div>
                    {*if \common\helpers\Order::is_stock_updated((int) $order->order_id)}
                        <span class="st-true st-with-text"><span>{$smarty.const.TEXT_ORDER_STOCK_UPDATED}</span></span>
                    {else}
                        <label>{Html::checkbox('update_order_stock', false, ['class' => 'uniform', 'value' => 1])}<b>{$smarty.const.TEXT_ASK_UPDATE_ORDER_STOCK}</b></label>
                    {/if*}
                    </div>
                </div>
            </div>
    </fieldset>
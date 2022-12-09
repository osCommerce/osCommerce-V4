{use class="common\helpers\Html"}
{use class="yii\helpers\Url"}
{Html::beginForm(Url::toRoute(['order/', 'action' => 'confirmed-order-reassign']),'post', ['id' => 'orders_edit', 'onSubmit' => "return confirmedReassignOrder();"])}
    <div class="or_box_head">{$smarty.const.TEXT_INFO_HEADING_REASSIGN_ORDER}</div>
    <div class="col_desc">{$smarty.const.TEXT_INFO_REASSIGN_INTRO}</div>
    <div class="row_or_wrapp">
        <div class="row_or"><div>{$smarty.const.TEXT_INFO_DELETE_DATA}:</div><div>{$oInfo->customers_name}</div></div>
        <div class="row_or"><div>{$smarty.const.TEXT_INFO_DELETE_DATA_OID}:</div><div>{$oInfo->orders_id}</div></div>
    </div>
    <div class="customer_in auto-wrapp" style="position: relative; width: 100%;">
        {Html::textInput('keywords', '', ['id' => 'selectCustomer' , 'autocomplete' => 'oxx'])}
    </div>
    <div class="btn-toolbar btn-toolbar-order">
        {Html::submitButton($smarty.const.IMAGE_REASSIGN, ['class' => 'btn btn-no-margin'])}<input type="button" class="btn btn-cancel" value="{$smarty.const.IMAGE_CANCEL}" onClick="return cancelStatement()">
        {Html::hiddenInput('orders_id', $oInfo->orders_id)}
    </div>
        {Html::hiddenInput('customers_id', 0, ['id' => 'customers_id'])}
{Html::endForm()}
    <script type="text/javascript">
        (function ($) {
            $(function () {
                $('#selectCustomer').autocomplete({
                    source: "{$app->urlManager->createUrl('orders/customer')}",
                    minLength: 0,
                    autoFocus: true,
                    delay: 0,
                    appendTo: '.auto-wrapp',
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
                    select: function (event, ui) {
                        if (ui.item.id != null) {
                            $("#customers_id").val(ui.item.id);
                        }
                    },
                }).focus(function () {
                    $(this).autocomplete("search");
                });
            })
        })(jQuery)
    </script>
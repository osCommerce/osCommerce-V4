{use class = "common\helpers\Html"}
{use class = "yii\helpers\Url"}
{\backend\assets\ProcessOrderAsset::register($this)|void}
{if is_array($messsages) && $messsages}
    {foreach $messsages as $messages_block}
        {foreach $messages_block as $message}
              <div class="alert fade in alert-{$message['type']}">
                  <i data-dismiss="alert" class="icon-remove close"></i>
                  <span id="message_plce">{$message['text']}</span>
              </div>
        {/foreach}
    {/foreach}
{/if}

{Html::beginForm(array_merge(['tmp-orders/ordersubmit'], $queryParams), 'post', ['id' => 'status_edit', 'onSubmit' => 'return check_form();'] )}
    {Html::hiddenInput('orders_id', $order->order_id)}

{if $pageName}

    <link href="{$app->view->theme->baseUrl}/css/admin-design.css" rel="stylesheet" type="text/css" />
    {\frontend\design\Block::widget(['name' => $pageName, 'params' => ['params' => ['type' => 'backendOrder', 'manager' => $manager, 'order' => $order], 'type' => 'backendOrder', 'manager' => $manager, 'order' => $order]])}
    <style type="text/css">{\frontend\design\Info::minifyCss(\frontend\design\Block::getStyles())}</style>

{else}

    {$manager->render('Notification', ['manager' => $manager])}

    {$manager->render('AddressDetails', ['manager' => $manager, 'order' => $order])}

    {$manager->render('Products', ['manager' => $manager, 'order' => $order])}

    {$manager->render('ExtraCustomData', ['manager' => $manager, 'order' => $order])}

    {$manager->render('StatusBox', ['manager' => $manager, 'order' => $order])}

{/if}

    {foreach \common\helpers\Hooks::getList('tmp-orders/process-order', 'btn-bar-bottom') as $filename}
        {include file=$filename}
    {/foreach}

{Html::endForm()}
    {$manager->render('Buttons', ['manager' => $manager, 'order' => $order, 'addedPages' => $addedPages])}
<script>
    (function($) {
        setTimeout(function(){
            $('#status_edit').on('change', function() {
                $('.btn-cancel-foot', this).html('{$smarty.const.IMAGE_CANCEL}')
            })
        }, 500)
    })($);
</script>

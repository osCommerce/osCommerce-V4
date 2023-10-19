<div><strong>{$order->info['payment_method']}</strong></div>
{if $payment_confirmation}
    {if $payment_confirmation.title}
        <div>{$payment_confirmation.title}</div>
    {/if}
    {if isset($payment_confirmation.fields) && is_array($payment_confirmation.fields)}
        <table>
            {foreach $payment_confirmation.fields as $payment_confirmation_field}
                <tr>
                    <td>{$payment_confirmation_field.title}</td><td>{$payment_confirmation_field.field}</td>
                </tr>
            {/foreach}
        </table>
    {/if}
{/if}
{\yii\helpers\Html::hiddenInput('payment', $manager->getPaymentCollection()->selected_module)}
{* VL duplicate lib/frontend/themes/basic/checkout/confirmation.tpl:103 if $payment_process_button_hidden}
    {$payment_process_button_hidden}
{/if*}
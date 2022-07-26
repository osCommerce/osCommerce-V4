{use class="\yii\helpers\Html"}
{use class="\yii\helpers\Url"}
<fieldset>
    <legend>{$smarty.const.TEXT_INVOICE_COMMENTS}</legend>
    <div class="f_row">
        <div class="f_td">
            <div class="invoice-info form-inputs w-line-row-2">
                {Html::textArea('invoice_comment', $comment, ['rows' => 12, 'class' => 'form-control order_comment'])}
            </div>
        </div>
    </div>
</fieldset>

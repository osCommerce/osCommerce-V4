{use class="\yii\helpers\Html"}
{use class="frontend\design\Info"}
{Html::activeRadioList($model, 'choice', $model->showCustomerChoices(), \frontend\design\FormElements::radioButton())}

<script>
    tl(function(){
        $('#{Html::getInputId($model, 'choice')} :radio').change(function(){
            let _choice = $(this).val();
            checkout.shipping_choice(_choice, $(this).closest('.block.checkout'));
        })
    })
</script>

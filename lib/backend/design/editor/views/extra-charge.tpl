{use class="\yii\helpers\Html"}
<div class="extra-disc-box">
    {Html::dropDownList('dis_action_percent['|cat:$product['id']|cat:']', $predefined['percent_action'], ['-' => '-', '+' => '+'], ['class' => 'form-control action-percent'])}
    {Html::textInput('dis_action_percent_value['|cat:$product['id']|cat:']', $predefined['percent_value']|cat:'%', ['class' => 'spinner-percent form-control'])}
</div>
<center class="or-after">or</center>
<div class="extra-disc-box">
    {Html::dropDownList('dis_action_fixed['|cat:$product['id']|cat:']', $predefined['fixed_action'], ['-' => '-', '+' => '+'], ['class' => 'form-control action-fixed'])}
    {Html::textInput('dis_action_fixed_value['|cat:$product['id']|cat:']', $predefined['fixed_value'], ['class' => 'spinner-fixed form-control'])}
</div>
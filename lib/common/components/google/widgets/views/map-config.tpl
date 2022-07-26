{use class="yii\helpers\Html"}

{Html::hiddenInput('provider', $owner)}
{Html::textInput($owner|cat:'[key]', $value, ['class' => 'form-control'])}

<p>
<br/>
{$description}
</p>
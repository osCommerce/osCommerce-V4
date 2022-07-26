{use class="yii\helpers\Html"}

{Html::hiddenInput('provider', $owner)}
<label>Public Key</label>
{Html::textInput($owner|cat:"[publicKey]", $publicKey, ['class' => 'form-control'])}
<label>Secret Key</label>
{Html::textInput($owner|cat:"[privateKey]", $privateKey, ['class' => 'form-control'])}
<label>reCaptcha Version</label>
{Html::radioList($owner|cat:"[version]", $version, ['v2' => 'V2', 'v3' => 'V3'])}
<p>
<br/>
{$description}
</p>
{use class="\yii\helpers\Html"}
{Html::beginForm($url, 'post', ['name' => 'pe', 'target'=> "_top"])}    
    <input type="hidden" name="email_address" value="{$login_email_address}" />
    <input type="submit" />
{Html::endForm()}

<script type="text/javascript">
document.pe.submit();
</script>
<h4>New Social Module</h4>
<form name="social_form" method="post" action="socials/save">
 {\yii\helpers\Html::dropDownList('module', $social->module, $modules, ['class' => 'form-control', 'prompt' => 'please select'])}
    <input type="hidden" name="platform_id" value="{$platform_id}">
    <input type="hidden" name="socials_id" value="0">

 <div class="btn-bar">
        <div class="btn-left"><a href="javascript:void(0)" onclick="return resetStatement();" class="btn btn-cancel-foot">{$smarty.const.IMAGE_CANCEL}</a></div>
        <div class="btn-right"><button class="btn btn-primary">{$smarty.const.IMAGE_SAVE}</button></div>
    </div>
</form>
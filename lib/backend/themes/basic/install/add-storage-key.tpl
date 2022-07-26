{use class="yii\helpers\Html"}
{Html::beginForm(['install/submit-storage-key'], 'post')}
<div class="popup-heading">{$smarty.const.TEXT_STORE_KEY}</div>
<div class="popup-content">
    {Html::input('text', 'storekey', $storageKey, ['class'=>'form-control'])}
</div>
<div class="noti-btn">
  <div><span class="btn btn-cancel">{$smarty.const.IMAGE_CANCEL}</span></div>
  <div><button class="btn btn-primary btn-save">{$smarty.const.TEXT_BTN_OK}</button></div>
</div>
{Html::endForm()}


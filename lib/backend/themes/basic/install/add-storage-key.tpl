{use class="yii\helpers\Html"}
{Html::beginForm(['install/submit-storage-key'], 'post')}
<div class="popup-heading">{$smarty.const.TEXT_STORE_KEY}</div>
<div class="popup-content">
    {Html::input('text', 'storekey', $storageKey, ['class'=>'form-control'])}
</div>
<div class="noti-btn">
  <div class="btn-left">
      <span class="btn btn-cancel">{$smarty.const.IMAGE_CANCEL}</span>
  </div>
  <div class="btn-right">
      {if \common\helpers\Acl::rule(['BOX_HEADING_INSTALL', 'TEXT_APPLY_FOR_ALL'])}<button name="button" class="btn" value="all">{$smarty.const.TEXT_APPLY_FOR_ALL}</button>&nbsp;{/if}<button name="button" class="btn btn-primary btn-save" value="me">{$smarty.const.TEXT_APPLY}</button>
  </div>
  
</div>
{Html::endForm()}


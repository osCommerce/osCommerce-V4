{use class="yii\helpers\Html"}
{Html::beginForm(['admin-menu/delete-submit'], 'post')}
{Html::input('hidden', 'id', $id)}
<div class="popup-heading">{$smarty.const.IMAGE_DELETE}</div>
<div class="popup-content">
    {$smarty.const.TEXT_DELETE_ITEM_INTRO}
</div>
<div class="noti-btn">
  <div><span class="btn btn-cancel">{$smarty.const.IMAGE_CANCEL}</span></div>
  <div><button class="btn btn-primary btn-save">{$smarty.const.TEXT_BTN_OK}</button></div>
</div>
{Html::endForm()}

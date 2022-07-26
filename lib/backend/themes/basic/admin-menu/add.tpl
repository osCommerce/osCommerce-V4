{use class="yii\helpers\Html"}
{Html::beginForm(['admin-menu/add-submit'], 'post')}
<div class="popup-heading">{$smarty.const.TEXT_ADD}</div>
<div class="popup-content">
    {$smarty.const.TEXT_TYPE}: {Html::dropDownList('box_type','1',['1'=>'Category', '0'=>'Item'],['class'=>'form-control'])}
    {$smarty.const.TEXT_CONSTANT}: {Html::input('text', 'title','',['class'=>'form-control'])}
    {$smarty.const.TEXT_PATH}: {Html::input('text', 'path','',['class'=>'form-control'])}
</div>
<div class="noti-btn">
  <div><span class="btn btn-cancel">{$smarty.const.IMAGE_CANCEL}</span></div>
  <div><button class="btn btn-primary btn-save">{$smarty.const.TEXT_BTN_OK}</button></div>
</div>
{Html::endForm()}

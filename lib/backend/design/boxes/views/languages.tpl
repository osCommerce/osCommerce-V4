{use class="Yii"}
{use class="\common\helpers\Html"}
<form action="{Yii::getAlias('@web')}/design/box-save" method="post" id="box-save">
  <input type="hidden" name="id" value="{$id}"/>
  <div class="popup-heading">
  </div>
  <div class="popup-content">



    <div class="tabbable tabbable-custom">
      <ul class="nav nav-tabs">

        <li class="active" data-bs-toggle="tab" data-bs-target="#main_settings"><a>Main</a></li>
        <li data-bs-toggle="tab" data-bs-target="#style"><a>{$smarty.const.HEADING_STYLE}</a></li>
        <li data-bs-toggle="tab" data-bs-target="#align"><a>{$smarty.const.HEADING_WIDGET_ALIGN}</a></li>
        <li data-bs-toggle="tab" data-bs-target="#visibility"><a>{$smarty.const.TEXT_VISIBILITY_ON_PAGES}</a></li>

      </ul>
      <div class="tab-content">

        <div class="tab-pane active p-3" id="main_settings">

          <div class="form-check">
            {Html::checkbox('setting[0][hide_image]', !!$settings[0].hide_image, ['id' => 'hide_image', 'class' => 'form-check-input'])}
            <label class="form-check-label" for="hide_image">
              Hide image
            </label>
          </div>
          <div class="form-check">
            {Html::checkbox('setting[0][show_title]', !!$settings[0].show_title, ['id' => 'show_title', 'class' => 'form-check-input'])}
            <label class="form-check-label" for="show_title">
              Show name
            </label>
          </div>
          <div class="form-check">
            {Html::checkbox('setting[0][show_key]', !!$settings[0].show_key, ['id' => 'show_key', 'class' => 'form-check-input'])}
            <label class="form-check-label" for="show_key">
              Sow key
            </label>
          </div>

        </div>
        <div class="tab-pane" id="style">
          {include 'include/style.tpl'}
        </div>
        <div class="tab-pane" id="align">
          {include 'include/align.tpl'}
        </div>
        <div class="tab-pane" id="visibility">
          {include 'include/visibility.tpl'}
        </div>

      </div>
    </div>




  </div>
  <div class="popup-buttons">
    <button type="submit" class="btn btn-primary btn-save">{$smarty.const.IMAGE_SAVE}</button>
    <span class="btn btn-cancel">{$smarty.const.IMAGE_CANCEL}</span>
  </div>
</form>
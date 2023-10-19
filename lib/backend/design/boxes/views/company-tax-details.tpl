{use class="Yii"}
{use class="common\helpers\Html"}
<form action="{$app->request->baseUrl}/design/box-save" method="post" id="box-save">
  <input type="hidden" name="id" value="{$id}"/>
  <div class="popup-heading">
    {$smarty.const.TEXT_CONTAINER}
  </div>
  <div class="popup-content box-img">


    <div class="tabbable tabbable-custom">
      <ul class="nav nav-tabs">

        <li class="active" data-bs-toggle="tab" data-bs-target="#upload"><a>{$smarty.const.TEXT_CONTAINER}</a></li>
        <li data-bs-toggle="tab" data-bs-target="#style"><a>{$smarty.const.HEADING_STYLE}</a></li>
        <li data-bs-toggle="tab" data-bs-target="#align"><a>{$smarty.const.HEADING_WIDGET_ALIGN}</a></li>
        <li data-bs-toggle="tab" data-bs-target="#visibility"><a>{$smarty.const.TEXT_VISIBILITY_ON_PAGES}</a></li>

      </ul>
      <div class="tab-content">


        <div class="tab-pane active" id="upload">

{for $i=0; $i<3; $i++}
          <div class="setting-row">
          <div class="">
            <label for="{'setting0'|cat:$i}">{$smarty.const.TEXT_FROM_LIST}</label>
            {Html::dropDownList('setting[0][company_]'|cat:$i, $settings[0][{'company_'|cat:$i}], ['0' => TEXT_NONE, 'name' => TEXT_COMPANY, 'address' => CATEGORY_ADDRESS, 'vat_id' => ENTRY_BUSINESS ], ['id' => 'setting0'|cat:$i])}
            </div>
            <div class="">
            <label for="{'spacer_'|cat:$i}">{$smarty.const.TEXT_SPACER}</label>
            {Html::input('text', 'setting[0][spacer_]'|cat:$i, $settings[0][{'spacer_'|cat:$i}], ['id' => 'spacer_'|cat:$i])}
            </div>
          </div>
{/for}

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
    <script type="text/javascript">
      $('.btn-cancel').on('click', function(){
        $('.popup-box-wrap').remove()
      })
    </script>

  </div>
</form>
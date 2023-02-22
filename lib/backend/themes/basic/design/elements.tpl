{use class="Yii"}
{use class="backend\assets\DesignAsset"}
{use class="frontend\design\Info"}
{DesignAsset::register($this)|void}
<script type="text/javascript" src="{$app->request->baseUrl}/plugins/html2canvas.js"></script>
<script type="text/javascript" src="{$app->view->theme->baseUrl}/js/design.js?2"></script>

<div class="page-elements">
  {include 'menu.tpl'}

  <div class="info-view-wrap"{if strpos($theme_name, '-mobile')} style="width: 500px"{/if}>
      <div class="info-view"></div>
      <div class="info-view-right-resize"></div>
  </div>

  <div class="btn-bar btn-bar-edp-page after">
    <div class="btn-left">
      <span data-href="{$link_cancel}" class="btn btn-save-boxes">{$smarty.const.IMAGE_CANCEL}</span>
    </div>
    <div class="btn-right">
      <span class="btn btn-preview">{$smarty.const.ICON_PREVIEW}</span>
      <span class="btn btn-edit" style="display: none">{$smarty.const.IMAGE_EDIT}</span>
      <span data-href="{$link_save}" class="btn btn-confirm btn-save-boxes">{$smarty.const.IMAGE_SAVE}</span>
    </div>
  </div>

</div>


<script>
    design.init()
</script>



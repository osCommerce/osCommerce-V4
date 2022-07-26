<div class="theme-page">
      <div style="text-align: center"><img src="{DIR_WS_CATALOG}themes/{$theme.theme_name}/screenshot.png" alt=""></div>
      <div class="description">{$theme.description}</div>
</div>

<div style="text-align: center; margin: 40px">
  <a href="{Yii::$app->urlManager->createUrl(['design/theme-edit', 'theme_name' => $theme.theme_name])}" class="btn btn-primary" style="margin-right: 30px">{$smarty.const.TEXT_CUSTOMIZE_THEME_STYLES}</a>
  <a href="{Yii::$app->urlManager->createUrl(['design/elements', 'theme_name' => $theme.theme_name])}" class="btn btn-primary">{$smarty.const.TEXT_CUSTOMIZE_BOXES}</a>

  <div class="buttons" style="padding-top: 10px; text-align: right">
    <a class="confirm" style="font-size: 11px">{$smarty.const.TEXT_RESTORE_DEFAULT_THEME}</a>
  </div>
</div>

<div class="btn-bar btn-bar-edp-page after">
  <div class="btn-left">
  </div>
  <div class="btn-right">
  </div>
</div>


<script type="text/javascript">
  (function($){
    $(function(){
      $('.confirm').on('click', function(){
        $.popUpConfirm('{$smarty.const.TEXT_CHANGES_WILL_BE_DESTROYED}', function(){
          $.get('{Yii::$app->urlManager->createUrl(['design/theme-restore', 'theme_name' => $theme.theme_name])}', function(){
            alertMessage('<div style="padding: 40px 20px; text-align: center">{$smarty.const.TEXT_THEME_RESTORED}</div>')
          })
        })
      })
    })
  })(jQuery)
</script>
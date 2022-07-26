<div class="theme-view">


    <a href="{Yii::$app->urlManager->createUrl(['design/elements', 'theme_name' => $theme_name])}" class="item">
      <div class="img" style="background-image: url('{DIR_WS_CATALOG}themes/{$theme_name}/screenshot.png')"></div>
      <div class="title">{$smarty.const.TEXT_DESKTOP}</div>
    </a>

    <a href="{Yii::$app->urlManager->createUrl(['design/elements', 'theme_name' => $theme_name_mobile])}" class="item">
      <div class="img" style="background-image: url('{DIR_WS_CATALOG}themes/{$theme_name_mobile}/screenshot.png')"></div>
      <div class="title">{$smarty.const.TEXT_MOBILE}</div>
    </a>

</div>

{*if is_array($platform_select) && count($platform_select)>1 }
  <div style="margin-bottom: 15px; font-size: 1.5em">
    {$smarty.const.TEXT_SELECT_PREVIEW_PLATFORM} <select id="platform_selector" >
      {foreach $platform_select as $platform_option}
        <option value="{$platform_option['id']}">{$platform_option['text']}</option>
      {/foreach}
    </select>
  </div>
{/if*}

<div class="themes-menu">
  <div class="right-area"></div>

  <a class="btn menu-themes{if $menu == 'themes'} active{/if}" href="{Yii::$app->urlManager->createUrl(['design/themes'])}" title="{$smarty.const.BACK_THEMES_LIST}"></a>
  <a class="btn menu-elements{if $menu == 'elements'} active{/if}" href="{Yii::$app->urlManager->createUrl(['design/elements', 'theme_name' => $theme_name])}">{$smarty.const.EDIT_ELEMENTS}</a>

    <a class="btn menu-theme-edit{if $menu == 'theme-edit'} active{/if}" href="{Yii::$app->urlManager->createUrl(['design/theme-edit', 'theme_name' => $theme_name])}">
      {$smarty.const.TEXT_CUSTOMIZE_THEME_STYLES}
    </a>

    <a class="btn menu-theme-edit{if $menu == 'groups'} active{/if}" href="{Yii::$app->urlManager->createUrl(['design-groups', 'theme_name' => $theme_name])}">
      {$smarty.const.TEXT_WIDGET_GROUPS}
    </a>

    {if !strpos($theme_name, '-mobile')}
      <a class="btn menu-backups{if $menu == 'backups'} active{/if}" href="{Yii::$app->urlManager->createUrl(['design/backups', 'theme_name' => $theme_name])}">{$smarty.const.TEXT_BACKUPS}</a>
    {/if}

  <a class="btn menu-settings{if $menu == 'settings'} active{/if}" href="{Yii::$app->urlManager->createUrl(['design/settings', 'theme_name' => $theme_name])}">{$smarty.const.THEME_SETTINGS}</a>

  <a class="btn menu-settings{if $menu == 'wizard'} active{/if}" href="{Yii::$app->urlManager->createUrl(['design-groups/wizard', 'theme_name' => $theme_name])}">Wizard</a>

  {if $designer_mode == 'expert'}
    <a class="btn menu-settings{if $menu == 'styles'} active{/if}" href="{Yii::$app->urlManager->createUrl(['design/styles', 'theme_name' => $theme_name])}">styles</a>
    <a class="btn menu-settings{if $menu == 'css'} active{/if}" href="{Yii::$app->urlManager->createUrl(['design/css', 'theme_name' => $theme_name])}">css</a>
    <a class="btn menu-settings{if $menu == 'js'} active{/if}" href="{Yii::$app->urlManager->createUrl(['design/js', 'theme_name' => $theme_name])}">js</a>
  {/if}

    <a class="btn menu-settings{if $menu == 'log'} active{/if}" href="{Yii::$app->urlManager->createUrl(['design/log', 'theme_name' => $theme_name])}">{$smarty.const.LOG_TEXT}</a>


  {if \backend\design\Theme::useMobileTheme($theme_name)}
    {if strpos($theme_name, '-mobile')}
      <a class="btn menu-settings" href="{Yii::$app->urlManager->createUrl(['design/'|cat:$this->context->action->id, 'theme_name' => str_replace('-mobile', '', $theme_name)])}">{$smarty.const.TEXT_DESKTOP}</a>
    {else}
      <a class="btn menu-settings" href="{Yii::$app->urlManager->createUrl(['design/'|cat:$this->context->action->id, 'theme_name' => $theme_name|cat:'-mobile'])}">{$smarty.const.TEXT_MOBILE}</a>
    {/if}
  {/if}
</div>
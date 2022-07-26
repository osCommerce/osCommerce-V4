{use class="Yii"}
{use class="frontend\design\Info"}
{use class="yii\helpers\Html"}

{\frontend\design\Info::addBoxToCss('info')}
{\frontend\design\Info::addBoxToCss('form')}
{\frontend\design\Info::addBoxToCss('social-login')}
{if $show_socials}
    <div class="social-login">
        <div class="social-login-text social-login-text-1">{$smarty.const.LOGIN_WITH_SOCIAL}</div>
        <div class="social-login-icons">{\yii\authclient\widgets\AuthChoice::widget(['baseAuthUrl'=>['account/auth'],'popupMode'=>false])}</div>
        <div class="social-login-text social-login-text-2">{$smarty.const.TEXT_OR}</div>
    </div>
{/if}
<div class="checkout-login-page">
    
    {\frontend\design\boxes\login\Returning::widget(['params' => $params, 'settings' => $settings])}
    
    {\frontend\design\boxes\login\Register::widget(['params' => $params, 'settings' => $settings])}
    
</div>

<script type="text/javascript">

    tl([
        '{Info::themeFile('/js/main.js')}',
        '{Info::themeFile('/js/jquery.tabs.js')}',
        '{Info::themeFile('/js/bootstrap-switch.js')}',
    ], function () {
     
      {Info::addBlockToWidgetsList('tabs')}
        $('.checkout-login-page').tlTabs({
            tabContainer: '.login-box',
            tabHeadingContainer: '.login-box-heading'
        });
        
    {if $create_tab_active}
        $('.checkout-login-page .tab-navigation li>span:last-child').trigger('click');
    {/if}

        $('.login-box').show();
        
        $('.tab-a.active').trigger('click');

    })

</script>
{use class="Yii"}
{use class="frontend\design\Info"}
{use class="yii\helpers\Html"}

{if $params.show_socials}
    <div class="social-login">
        <div class="social-login-text social-login-text-1">{$smarty.const.LOGIN_WITH_SOCIAL}</div>
        <div class="social-login-icons">{\yii\authclient\widgets\AuthChoice::widget(['baseAuthUrl'=>['account/auth'],'popupMode'=>false])}</div>
        <div class="social-login-text social-login-text-2">{$smarty.const.TEXT_OR}</div>
    </div>
{/if}

<div class="w-login-block{\frontend\design\Info::addBlockToWidgetsList('login-block')}">
    <div class="returning">
        <div class="heading-3">{$smarty.const.RETURNING_CUSTOMER}</div>
        
        {\frontend\design\boxes\login\Returning::widget(['params' => $params, 'settings' => $settings])}
      
        <div class="bottom">{$smarty.const.TEXT_ALREADY_HAVE_ACCOUNT}</div>

    </div>
    <div class="new">
        <div class="heading-3">{$smarty.const.NEW_CUSTOMER}</div>
        <div class="row">
          {$smarty.const.TEXT_BY_CREATING_AN_ACCOUNT}
        </div>

      {if !$create_tab_active}
          <div class="buttons">
              <span class="btn-1 btn-new-account">{$smarty.const.CONTINUE}</span>
          </div>
      {/if}
      
      <div class="new-account"{if !$create_tab_active} style="display: none;"{/if} id="box-new-account">
        {\frontend\design\boxes\login\Register::widget(['params' => $params, 'settings' => $settings, 'id' => 'new-account'])}
      </div>

    </div>
    <div class="guest" id="box-guest">
      <div class="heading-3">{$smarty.const.CONTINUE_AS_GUEST}</div>
      {$settings['tabsManually']=false}
      {$settings['hideInfo']=true}
      {\frontend\design\boxes\login\Guest::widget(['params' => $params, 'settings' => $settings, 'id' => 'guest'])}
    </div>
</div>
<script>
tl([
        '{Info::themeFile('/js/main.js')}',
        '{Info::themeFile('/js/bootstrap-switch.js')}'
    ], function () {
        $('.btn-new-account').on('click', function(){
          $('.new-account').slideDown()
          $(this).closest('.buttons').slideUp()
        });
    });
</script>
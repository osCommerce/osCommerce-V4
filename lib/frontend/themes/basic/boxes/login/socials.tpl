{\frontend\design\Info::addBoxToCss('social-login')}
{if $show_socials}
        <div class="social-login-icons">{\yii\authclient\widgets\AuthChoice::widget(['baseAuthUrl'=>['account/auth'],'popupMode'=>false])}</div>
{/if}
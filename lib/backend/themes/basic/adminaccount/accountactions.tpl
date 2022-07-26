<div class="change_avatar after">				
    <div class="avatar_col"><div class="avatar{if $avatar > 0} avatarImg{else} avatar_noimg{/if}>">{if $avatar > 0}{$image}{else}<i class="icon-user"></i>{/if}<a href="{Yii::$app->urlManager->createUrl(['adminaccount/changeavatar'])}" class="avatar_edit popup"><i class="icon-pencil"></i></a><span class="avatar_delete popup" data-admin_id="{$myAccount['admin_id']}" onclick="return deleteImage();"><i class="icon-trash"></i></span></div>
    </div>
    <div class="account_wrapper_col">
        <div class="account_wrapper_row after">
            <div class="account_col">
                <div class="account1">
                    <div class="ac_name">{TEXT_INFO_FULLNAME}</div>
                    <div class="ac_value"><a href="{Yii::$app->urlManager->createUrl(['adminaccount/nameform'])}" class="popup">{$myAccount['admin_firstname']} {$myAccount['admin_lastname']}</a></div>
                </div>    
                <div class="account3">
                    <div class="ac_name">{TEXT_INFO_PASSWORD}</div>
                    <div class="ac_value"><a class="change_pass popup" href="{Yii::$app->urlManager->createUrl(['adminaccount/getpassword'])}"><span>{TEXT_INFO_PASSWORD_HIDDEN}</span></a></div>
                </div>
            </div>
            <div class="account_col">
                <div class="account2">
                    <div class="ac_name">{TEXT_INFO_EMAIL}</div>
                    <div class="ac_value"><a href="{Yii::$app->urlManager->createUrl(['adminaccount/emailform'])}" class="popup">{$myAccount['admin_email_address']}</a></div>
                </div>
                <div class="account9">
                    <div class="ac_name">{TEXT_INFO_USERNAME}</div>
                    <div class="ac_value"><a href="{Yii::$app->urlManager->createUrl(['adminaccount/usernameform'])}" class="popup">{if $myAccount['admin_username']}{$myAccount['admin_username']}{else}{TEXT_CHANGE_USERNAME}{/if}</a></div>
                </div>
            </div>
        </div>
        <div class="account_wrapper_row after">
            <div class="account_col">
                <div class="account4">
                    <div class="ac_name">{TEXT_INFO_GROUP}</div>
                    <div class="ac_value">{$myAccount['access_levels_name']}</div>
                </div>
                <div class="account5">
                    <div class="ac_name">{TEXT_INFO_CREATED}</div>
                    <div class="ac_value">{\common\helpers\Date::date_short( $myAccount['admin_created'] )}</div>
                </div>		
                <div class="account8">
                    <div class="ac_name">{TEXT_INFO_MODIFIED}</div>
                    <div class="ac_value">{\common\helpers\Date::date_short( $myAccount['admin_modified'] )}</div>
                </div>
            </div>
            <div class="account_col">		
                <div class="account6">
                    <div class="ac_name">{TEXT_INFO_LOGNUM}</div>
                    <div class="ac_value">{$myAccount['admin_lognum']}</div>
                </div>
                <div class="account7">
                    <div class="ac_name">{TEXT_INFO_LOGDATE}</div>
                    <div class="ac_value">{\common\helpers\Date::date_short( $myAccount['admin_logdate'] )}</div>
                </div>				
            </div>
        </div>
        <div class="account_wrapper_row after">
            <h2>{$smarty.const.TEXT_POS_SECTION}</h2>
            <div class="account_col">
                <div class="account3">
                    <div class="ac_name">{TEXT_PIN}</div>
                    <div class="ac_value"><a class="change_pass popup" href="{Yii::$app->urlManager->createUrl(['adminaccount/getpin'])}"><span>{TEXT_INFO_PASSWORD_HIDDEN}</span></a></div>
                </div>
            </div>
            <div class="account_col">
                <div class="account9">
                    <div class="ac_name">{ENTRY_CUSTOMER}</div>
                    <div class="ac_value"><a href="{Yii::$app->urlManager->createUrl(['adminaccount/select-customer-form'])}" class="popup">{if $myAccount['admin_customer']|default:null}{$myAccount['admin_customer']}{else}{TEXT_CHANGE_USERNAME}{/if}</a></div>
                </div>
            </div>

            <div class="account_col">
                <div class="account8">
                    <div class="ac_name">{TABLE_HEADING_PLATFORM}</div>
                    <div class="ac_value"><a class="change_pass popup" href="{Yii::$app->urlManager->createUrl(['adminaccount/select-pos-platform-form'])}"><span>{if isset($myAccount['posPlatform']['platform_owner'])}{$myAccount['posPlatform']['platform_owner']}{else}{TEXT_CHANGE} {TABLE_HEADING_PLATFORM}{/if}</span></a></div>
                </div>
            </div>
            <div class="account_col">
                <div class="account6">
                    <div class="ac_name">{TITLE_CURRENCY}</div>
                    <div class="ac_value"><a href="{Yii::$app->urlManager->createUrl(['adminaccount/select-pos-currency-form'])}" class="popup">{if isset($myAccount['posCurrency']['title'])}{$myAccount['posCurrency']['title']}{else}{TEXT_CHANGE} {TITLE_CURRENCY}{/if}</a></div>
                </div>
            </div>

        </div>
    </div>
</div>
<!--<p class="btn-toolbar">
    <input class="btn btn-primary" type="button" onclick="return getChangeForm()" value="{TEXT_LABEL_CHANGE_ACCOUNT_DATA}">
</p>-->



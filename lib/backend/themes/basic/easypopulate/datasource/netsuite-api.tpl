{*
This file is part of osCommerce ecommerce platform.
 * osCommerce the ecommerce

@link https://www.oscommerce.com
@copyright Copyright (c) 2000-2022 osCommerce LTD

Released under the GNU General Public License
 * For the full copyright and license information, please view the LICENSE.TXT file that was distributed with this source code.
*}

{use class="common\helpers\Html"}
<div class="widget box">
    <div class="widget-header">
        <h4>API Access details</h4>
        <div class="toolbar no-padding">
          <div class="btn-group">
            <span class="btn btn-xs widget-collapse"><i class="icon-angle-down"></i></span>
          </div>
        </div>
    </div>
  {if false} {*password based auth*}
    <div class="widget-content">
      <div class="w-line-row w-line-row-1">
          <div class="wl-td">
              <label>{$smarty.const.TEXT_INFO_APP_ID}</label> {Html::textInput('datasource['|cat:$code|cat:'][client][appid]', $client['appid'], ['class' => 'form-control'])}
          </div>
      </div>
      <div class="w-line-row w-line-row-1">
          <div class="wl-td">
              <label>{$smarty.const.TEXT_INFO_USERNAME}</label> {Html::textInput('datasource['|cat:$code|cat:'][client][username]', $client['username'], ['class' => 'form-control'])}
          </div>
      </div>
      <div class="w-line-row w-line-row-1">
          <div class="wl-td">
              <label>{$smarty.const.ENTRY_PASSWORD}</label> {Html::textInput('datasource['|cat:$code|cat:'][client][password]', $client['password'], ['class' => 'form-control'])}
          </div>
      </div>
      <div class="w-line-row w-line-row-1">
          <div class="wl-td">
              <label>{$smarty.const.TEXT_ACCOUNT}</label> {Html::textInput('datasource['|cat:$code|cat:'][client][account]', $client['account'], ['class' => 'form-control'])}
          </div>
      </div>
      <div class="w-line-row w-line-row-1">
          <div class="wl-td">
              <label></label><button type="button" class="btn" onclick="return ep_command('configure_datasource_settings:update')">Update access details</button>
          </div>
      </div>
    </div>
  {else}
    <div class="widget-content">
      {foreach [
                'account' => $smarty.const.TEXT_ACCOUNT,
                'consumer_key' => $smarty.const.TEXT_CONSUMER_KEY,
                'consumer_secret' => $smarty.const.TEXT_CONSUMER_SECRET,
                'token_key' => $smarty.const.TEXT_TOKEN_KEY,
                'token_secret' => $smarty.const.TEXT_TOKEN_SECRET
               ] as $k => $v}
      <div class="w-line-row w-line-row-1">
          <div class="wl-td">
              <label>{$v}<span class="colon">:</span></label>{Html::textInput('datasource['|cat:$code|cat:'][client]['|cat:$k|cat:']', $client[$k], ['class' => ''])}
          </div>
      </div>
      {/foreach}
      <div class="w-line-row w-line-row-1">
          <div class="wl-td">
              <label></label><button type="button" class="btn" onclick="return ep_command('configure_datasource_settings:update')">Update access details</button>
          </div>
      </div>
    </div>
  {/if}
</div>

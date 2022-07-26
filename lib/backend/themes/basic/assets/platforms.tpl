{*
This file is part of osCommerce ecommerce platform.
 * osCommerce the ecommerce

@link https://www.oscommerce.com
@copyright Copyright (c) 2000-2022 osCommerce LTD

Released under the GNU General Public License
 * For the full copyright and license information, please view the LICENSE.TXT file that was distributed with this source code.
*}
{use class="\common\classes\platform"}
{use class="\common\helpers\Html"}
<div class="filter_pad">
  <table class="table tabl-res table-striped table-hover table-responsive table-bordered table-switch-on-off double-grid">
    <thead>
    <tr>
      <th>{$smarty.const.TABLE_HEAD_PLATFORM_NAME}</th>
      <th width="150">{$smarty.const.TEXT_ASSIGN}</th>
    </tr>
    </thead>
    <tbody>
    {foreach $platforms as $platform}
      <tr>
        <td>{$platform['text']}</td>
        <td>
          {Html::checkbox('platform[]', isset($app->controller->view->platform_assigned[$platform['id']]), ['value' => $platform['id'],'class'=>'check_on_off'])}
        </td>
      </tr>
    {/foreach}
    </tbody>
  </table>
</div>
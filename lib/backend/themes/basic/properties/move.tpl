{*
This file is part of osCommerce ecommerce platform.
 * osCommerce the ecommerce

@link https://www.oscommerce.com
@copyright Copyright (c) 2000-2022 osCommerce LTD

Released under the GNU General Public License
 * For the full copyright and license information, please view the LICENSE.TXT file that was distributed with this source code.
*}

<div class="or_box_head">{$pInfo->properties_name}</div>
<form name="properties" action="" method="post" id="properties_move" onSubmit="return moveProperty();">
    <input name="properties_id" type="hidden" value="{$pInfo->properties_id}">
    <div id="propertiesCatFilter">{tep_draw_pull_down_menu('parID', \common\helpers\Properties::get_properties_tree(), $parID, 'id="parID" class="form-control"')}</div>
    <div class="btn-toolbar btn-toolbar-order">
        <button class="btn btn-move btn-no-margin">{$smarty.const.IMAGE_MOVE}</button><button class="btn btn-cancel" onClick="return resetStatement()">{$smarty.const.IMAGE_CANCEL}</button>
    </div>

</form>

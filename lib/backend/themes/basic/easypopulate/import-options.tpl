{*
This file is part of osCommerce ecommerce platform.
 * osCommerce the ecommerce

@link https://www.oscommerce.com
@copyright Copyright (c) 2000-2022 osCommerce LTD

Released under the GNU General Public License
 * For the full copyright and license information, please view the LICENSE.TXT file that was distributed with this source code.
*}

{use class="yii\helpers\Html"}

<div class="widget box box-no-shadow">
    <div class="widget-header"><h4>{$smarty.const.TEXT_IMPORT_OPTIONS}</h4></div>
    {foreach $options as $option}
    <div class="widget-content">
        <div class="row form-group">
            <div class="col-md-6"><label>{$option.title}</label></div>
            <div class="col-md-6">{Html::dropDownList($option.name, $option.value, $option.values,['class'=>'form-control'])}</div>
        </div>
        {if $option.description != ''}
        <div class="row form-group">
            <div class="col-md-12 option-description">{$option.description}</div>
        </div>
        {/if}
    </div>
    {/foreach}
</div>
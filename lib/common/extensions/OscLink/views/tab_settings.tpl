{use class="yii\helpers\Html"} 
<div class="widget box"> 
    <div class="widget-content">
        <div class="alert fade in" style="display:none;">
            <i data-dismiss="alert" class="icon-remove close"></i>
            <span id="message_plce"></span>
        </div>
        <table id="{$type}_ConfigurationTable" class="table table-bordered table-hover table-responsive">
            <thead>
                <tr>
                {foreach $app->controller->view->ViewTable as $tableItem}
                    <th{if isset($tableItem['not_important']) && $tableItem['not_important'] == 1} class="hidden-xs"{/if}>{$tableItem['title']}</th>
                {/foreach}
                </tr>
            </thead>
            <tbody>
            {foreach $app->controller->view->configurationArray as $tableItem}
                {if $tableItem['cmc_type'] != $type}
                    {continue}
                {/if}
                <tr>
                    <td>{$tableItem['title']}</td>
                {if $tableItem['cmc_key'] == 'api_platform'}
                    <td>{Html::dropDownList($tableItem['cmc_key'], $tableItem['cmc_value'], $app->controller->view->platformList, ['class' => 'form-control'])}</td>
                {elseif $tableItem['cmc_key'] == 'api_measurement'}
                    <td>{Html::dropDownList($tableItem['cmc_key'], $tableItem['cmc_value'], ['english'=>'English','metric'=>'Metric'], ['class' => 'form-control'])}</td>
                {elseif $tableItem['cmc_key'] == 'api_method'}
                    <td>{Html::dropDownList($tableItem['cmc_key'], $tableItem['cmc_value'], ['bearer'=>'Bearer Token (recommended)','post'=>'POST','get'=>'GET (non secure)'], ['class' => 'form-control'])}</td>
                {elseif $tableItem['cmc_key'] == 'api_status_map'}
                    <td>
                        <table class="table table-bordered table-hover table-responsive">
                        <thead>
                            <tr>
                                <td>{$smarty.const.EXTENSION_OSCLINK_TEXT_MAPPING_ORDER_STATUS_DESC_OSC}</td>
                                <td>{$smarty.const.EXTENSION_OSCLINK_TEXT_MAPPING_ORDER_STATUS_DESC_OUR}</td>
                            <tr>
                        {assign var="statusMapCount" value="1"}
                        {foreach $tableItem['cmc_value'] as $mStatus => $tStatus}
                            <tr>
                                <td>{Html::dropDownList('api_status_map[mstatus]['|cat:$statusMapCount|cat:']', $mStatus, $app->controller->view->OscStateStatusArray, ['class' => 'form-control'])}</td>
                                <td>{Html::dropDownList('api_status_map[tstatus]['|cat:$statusMapCount|cat:']', $tStatus, $app->controller->view->orderStatusArray, ['class' => 'form-control'])}</td>
                                <td><a class="btn" onclick="return doStatusMapDelete(this);">-</a></td>
                            </tr>
                            {assign var="statusMapCount" value=($statusMapCount + 1)}
                        {/foreach}
                            <tr style="display: none;" id="StatusMapEtalon">
                                <td>{Html::dropDownList('api_status_map_mstatus', '', $app->controller->view->OscStateStatusArray, ['class' => 'form-control'])}</td>
                                <td>{Html::dropDownList('api_status_map_tstatus', '', $app->controller->view->orderStatusArray, ['class' => 'form-control'])}</td>
                                <td><a class="btn" onclick="return doStatusMapDelete(this);">-</a></td>
                            </tr>
                            <tr>
                                <td colspan="3"><a class="btn" onclick="return doStatusMapNew();">+</a></td>
                            </tr>
                        </table>
                    </td>
                {elseif $tableItem['cmc_key'] == 'api_category'}
                    <td>
                        <select name="api_category" class="col-md-12 select2 ">
                            {foreach $app->controller->view->categoryTree as $category}
                                <option {if $tableItem['cmc_value'] == $category['id']}selected{/if} value="{$category['id']}">{$category['text']}</option>
                            {/foreach}
                        </select>
                        </td>
                {elseif $tableItem['cmc_key'] == 'api_tax_map'}
                    <td>
                        <table class="table table-bordered table-hover table-responsive">
                        {assign var="taxMapCount" value="1"}
                        {foreach $tableItem['cmc_value'] as $tTax => $mTax}
                            <tr>
                                <td>{Html::dropDownList('api_tax_map[mtax]['|cat:$taxMapCount|cat:']', $mTax, $app->controller->view->linkProductTaxArray, ['class' => 'form-control', 'style'])}</td>
                                <td>{Html::dropDownList('api_tax_map[ttax]['|cat:$taxMapCount|cat:']', $tTax, $app->controller->view->tlTaxArray, ['class' => 'form-control'])}</td>
                                <td><a class="btn" onclick="return doTaxMapDelete(this);">-</a></td>
                            </tr>
                            {assign var="taxMapCount" value=($taxMapCount + 1)}
                        {/foreach}
                            <tr style="display: none;" id="TaxMapAddEtalon">
                                <td>{Html::dropDownList('api_tax_map_mtax', '', $app->controller->view->linkProductTaxArray, ['class' => 'form-control'])}</td>
                                <td>{Html::dropDownList('api_tax_map_ttax', '', $app->controller->view->tlTaxArray, ['class' => 'form-control'])}</td>
                                <td><a class="btn" onclick="return doTaxMapDelete(this);">-</a></td>
                            </tr>
                            <tr>
                                <td colspan="3"><a class="btn" onclick="return doTaxMapNew();">+</a></td>
                            </tr>
                        </table>
                    </td>
                {else}
                    <td>{Html::input('text', $tableItem['cmc_key'], $tableItem['cmc_value'], ['class' => 'form-control'])}</td>
                {/if}
                </tr>
            {/foreach}
            </tbody>
        </table>
        <p class="btn-wr">
            <a class="btn btn-primary" onclick="return saveConfiguration('{$type}');">{$smarty.const.IMAGE_SAVE}</a>
        </p>
    </div>
</div>
{if $type=='mapping'}
<script type="text/javascript">
    var statusMapCount = {$statusMapCount};
    var taxMapCount = {$taxMapCount|default:1};
</script>
{/if}

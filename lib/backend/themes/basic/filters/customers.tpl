
<div class="customers-filters">
{if $departments}
<div class="f_row f_row_pl_cus platform-filter">
    <div class="platform-filter-holder">
    <div class="f_td">
        <label>{$smarty.const.TEXT_COMMON_DEPARTMENTS_FILTER}</label>
    </div>
    <div class="f_td f_td_radio ftd_block platforms-list">
        <div><label class="radio_label"><input type="checkbox" name="departments[]" class="js_department_checkboxes" value=""> {$smarty.const.TEXT_COMMON_PLATFORM_FILTER_ALL}</label></div>
        {foreach $departments as $department}
            <div><label class="radio_label"><input type="checkbox" name="departments[]" class="js_department_checkboxes" value="{$department['id']}" {if in_array($department['id'], $app->controller->view->filters->departments)} checked="checked"{/if}> {$department['text']}</label></div>
        {/foreach}
    </div>
    </div>
</div>
{/if}
{if $isMultiPlatform}
<div class="f_row f_row_pl_cus platform-filter">
    <div class="platform-filter-holder">
    <div class="f_td">
    <label>{$smarty.const.TEXT_COMMON_PLATFORM_FILTER}</label>
    </div>
    <div class="f_td f_td_radio ftd_block platforms-list">
            <div><label class="radio_label"><input type="checkbox" name="platform[]" class="js_platform_checkboxes" value=""> {$smarty.const.TEXT_COMMON_PLATFORM_FILTER_ALL}</label></div>
      {foreach $platforms as $platform}
        <div><label class="radio_label"><input type="checkbox" name="platform[]" class="js_platform_checkboxes" value="{$platform['id']}" {if in_array($platform['id'], $app->controller->view->filters->platform)} checked="checked"{/if}> {$platform['text']}</label></div>
      {/foreach}
    </div>

        {if $cfExt = \common\helpers\Acl::checkExtensionAllowed('CustomerFlag')}
            {$cfExt::renderCustomerFilter($app->controller->view->filters)}
        {/if}

    </div>
</div>
{/if}



<div class="filter-box filter-box-cus {if $isMultiPlatform}filter-box-pl{/if}">

    <div class="row">
        <div class="col-2 align-right">
            <label>{$smarty.const.TEXT_SEARCH_BY}</label>
        </div>
        <div class="col-5">
            <select class="form-control" name="by">
                {foreach $app->controller->view->filters->by as $Item}
                    <option {$Item['selected']} value="{$Item['value']}">{$Item['name']}</option>
                {/foreach}
            </select>
        </div>
        <div class="col-5">
            <input type="text" name="search" value="{$app->controller->view->filters->search}" class="form-control" />
        </div>
    </div>

    {if $app->controller->view->filters->showGroup}
        <div class="row m-t-2">
            <div class="col-2 align-right">
                <label>{$smarty.const.TEXT_GROUP}</label>
            </div>
            <div class="col-5 f_td_group">
                <input name="group" id="selectGroup" type="text" value="{$app->controller->view->filters->group}" placeholder="{$smarty.const.TEXT_PLEASE_CHOOSE_GROUP}" class="form-control">
            </div>
        </div>
    {/if}

    <div class="row m-t-2">
        <div class="col-2 align-right">
            <label>{$smarty.const.TEXT_ADDRESS_BY}</label>
        </div>
        <div class="col-10">
            <div class="row">
                <div class="col-4 f_td_country">
                    <input name="country" id="selectCountry" type="text" value="{$app->controller->view->filters->country}" placeholder="{$smarty.const.TEXT_TYPE_COUNTRY}" class="form-control">
                </div>
                {if $app->controller->view->showState == true}
                    <div class="col-4 f_td_state"><input name="state" id="selectState" type="text" value="{$app->controller->view->filters->state}" placeholder="{$smarty.const.TEXT_TYPE_COUNTY}" class="form-control" {if $app->controller->view->filters->country == ''}disabled{/if}></div>
                {/if}
                <div class="col-4 f_td_city"><input name="city" id="selectCity" type="text" value="{$app->controller->view->filters->city}" placeholder="{$smarty.const.TEXT_TYPE_CITY}" class="form-control" {if $app->controller->view->filters->state == ''}disabled{/if}></div>
            </div>
        </div>
    </div>

    <div class="row m-t-2">
        <div class="col-2 align-right">
            <label>{$smarty.const.TEXT_COMPANY}</label>
        </div>
        <div class="col-5 f_td_company">
            <input name="company" id="selectCompany" type="text" value="{$app->controller->view->filters->company}" placeholder="{$smarty.const.TEXT_CHOOSE_COMPANY}" class="form-control">
        </div>
        <div class="col-5">
            <div class="row">
                <div class="col-3 align-right">
                    <label>{$smarty.const.TEXT_GUEST}:</label>
                </div>
                <div class="col-9">
                    <select name="guest" class="form-control small-in">
                        {foreach $app->controller->view->filters->guest as $Item}
                            <option {$Item['selected']} value="{$Item['value']}">{$Item['name']}</option>
                        {/foreach}
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="row m-t-2">
        {if !empty($app->controller->view->filters->newsletter) && is_array($app->controller->view->filters->newsletter) }
            <div class="col-2 align-right">
                <label>{$smarty.const.TEXT_NEWSLETTER}</label>
            </div>
            <div class="col-5">
                <select name="newsletter" class="form-control">
                    {foreach $app->controller->view->filters->newsletter as $Item}
                        <option {$Item['selected']} value="{$Item['value']}">{$Item['name']}</option>
                    {/foreach}
                </select>
            </div>
        {else}
            <div class="col-7">
                &nbsp;
            </div>
        {/if}
        <div class="col-5">
            <div class="row">
                <div class="col-3 align-right">
                    <label>{$smarty.const.TEXT_STATUS}</label>
                </div>
                <div class="col-9">
                    <select name="status" class="form-control small-in">
                        {foreach $app->controller->view->filters->status as $Item}
                            <option {$Item['selected']} value="{$Item['value']}">{$Item['name']}</option>
                        {/foreach}
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="row m-t-2">
        <div class="col-2 align-right">
            <label>{$smarty.const.TEXT_REGISTERED}</label>
        </div>
        <div class="col-5">
            <div class="f-row row align-items-center">
                <div class="f_td_radio col-5 p-r-0">
                    <label class="row align-items-center">
                            <div class="col-3 p-l-0 p-r-0">
                                <input type="radio" name="date" value="presel" id="presel" {if $app->controller->view->filters->presel}checked{/if} />
                            </div>
                            <div class="col-9 p-l-0 p-r-0">
                                    {$smarty.const.TEXT_PRE_SELECTED}
                            </div>
                    </label>
                </div>
                <div class="f_td2 col-7">
                    <select name="interval" class="form-control" {if $app->controller->view->filters->exact}disabled{/if}>
                        {foreach $app->controller->view->filters->interval as $Item}
                            <option {$Item['selected']} value="{$Item['value']}">{$Item['name']}</option>
                        {/foreach}
                    </select>
                </div>
            </div>
        </div>
        <div class="col-5">
            <div class="row">
                <div class="col-3 align-right">
                    <label>{$smarty.const.TEXT_TITLE}</label>
                </div>
                <div class="col-9">
                    <select name="title" class="form-control small-in">
                        {foreach $app->controller->view->filters->title as $Item}
                            <option {$Item['selected']} value="{$Item['value']}">{$Item['name']}</option>
                        {/foreach}
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="row m-t-2">
        <div class="col-2 align-right">&nbsp;</div>
        <div class="col-10">
            <div class="f-row row align-items-center">
                <div class="f_td_radio col-3">
                    <label class="row align-items-center">
                        <div class="col-3 p-l-0 p-r-0">
                            <input type="radio" name="date" value="exact" id="exact" {if $app->controller->view->filters->exact}checked{/if} />
                        </div>
                        <div class="col-9 p-l-0 p-r-0">
                            {$smarty.const.TEXT_EXACT_DATES}
                        </div>
                    </label>
                </div>
                <div class="f_td2 col-6">
                    <div class="row">
                        <div class="col-2 align-right">
                            <span>{$smarty.const.TEXT_FROM}</span>
                        </div>
                        <div class="col-4 p-l-0">
                            <input id="from_date" type="text" value="{$app->controller->view->filters->from}" autocomplete="off" name="from" class="datepicker form-control form-control-small" {if $app->controller->view->filters->presel}disabled{/if} />
                        </div>
                        <div class="col-1 align-right p-l-0">
                            <span class="sp_marg">{$smarty.const.TEXT_TO}</span>
                        </div>
                        <div class="col-4 p-l-0">
                            <input id="to_date" type="text" value="{$app->controller->view->filters->to}" autocomplete="off" name="to" class="datepicker form-control form-control-small" {if $app->controller->view->filters->presel}disabled{/if} />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<table width="100%" border="0" cellpadding="0" cellspacing="0">

    <tr>
        <td valign="top" align="right">
        </td>
        <td class="td_radio">

        </td>
        <td valign="top" class="td_small" align="right">
        </td>
    </tr>
</table>
</div>
    </div>
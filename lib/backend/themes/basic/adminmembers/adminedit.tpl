{tep_draw_form('admin', 'adminmembers/adminsubmit', \common\helpers\Output::get_all_get_params(array('action')), 'post', 'id="admin_edit"')}

{tep_draw_hidden_field('default_address_id', $mInfo->admin_email_address|default)}
{tep_draw_hidden_field('admin_id', $mInfo->admin_id|default)}

<div class="or_box_head">{$smarty.const.CATEGORY_PERSONAL}</div>

{foreach \common\helpers\Hooks::getList('adminmembers/adminedit', 'form-top') as $filename}
    {include file=$filename}
{/foreach}

<div class="main_row">
    <div class="main_title">{$smarty.const.ENTRY_FIRST_NAME}</div>
    <div class="main_value">{tep_draw_input_field('admin_firstname', $mInfo->admin_firstname|default, 'maxlength="32" class="form-control"', true)}</div>
</div>

<div class="main_row">
    <div class="main_title">{$smarty.const.ENTRY_LAST_NAME}</div>
    <div class="main_value">{tep_draw_input_field('admin_lastname', $mInfo->admin_lastname|default, 'maxlength="32" class="form-control"', false)}</div>
</div>

<div class="main_row">
    <div class="main_title">{$smarty.const.ENTRY_EMAIL_ADDRESS}</div>
    <div class="main_value">{tep_draw_input_field('admin_email_address', $mInfo->admin_email_address|default, 'maxlength="100" class="form-control"', true)}</div>
</div>

<div class="main_row">
    <div class="main_title">{$smarty.const.ENTRY_TELEPHONE_NUMBER}</div>
    <div class="main_value">{tep_draw_input_field('admin_phone_number', $mInfo->admin_phone_number|default, 'maxlength="100" class="form-control"')}</div>
</div>

{foreach \common\helpers\Hooks::getList('adminmembers/adminedit', 'form-middle') as $filename}
    {include file=$filename}
{/foreach}

<div class="main_row">
    <div class="main_title">{$smarty.const.TEXT_INFO_GROUP}</div>
    <div class="main_value">{tep_draw_pull_down_menu('access_levels_name', $access_array, ( is_object($mInfo) ) ? $mInfo->access_levels_id : 0, 'class="form-control"', false)}</div>
</div>

<div class="main_row">
    <div class="main_title">{$smarty.const.TEXT_INFO_TWO_STEP_AUTH}</div>
    <div class="main_value">{tep_draw_pull_down_menu('admin_two_step_auth', $adminTwoStepAuthArray, $mInfo->admin_two_step_auth|default, 'class="form-control"', false)}</div>
</div>

{if ($ext = \common\helpers\Acl::checkExtension('Communication', 'adminActionAdminEditRender'))}
    {$ext::adminActionAdminEditRender($mInfo->admin_id|default:null)}
{/if}

<div class="main_row">
    <div class="main_title">
        {$smarty.const.TEXT_EDIT_TRANSLATIONS_FRONTEND}
        <input type="checkbox" name="frontend_translation" class="translate-frontend" {if $mInfo->frontend_translation|default}checked{/if}/>
    </div>
</div>

{foreach \common\helpers\Hooks::getList('adminmembers/adminedit', 'form-bottom') as $filename}
    {include file=$filename}
{/foreach}

<div class="btn-toolbar btn-toolbar-order">
    {if $admin_id > 0}

        {if !common\helpers\Affiliate::isLogged()}
            <input type="submit" class="btn btn-no-margin" value="{$smarty.const.IMAGE_UPDATE}" >
        {/if}
        <input type="button" class="btn btn-cancel btn-no-margin" value="{$smarty.const.IMAGE_CANCEL}" onClick="return backStatement()">

    {else}
        <input type="submit" class="btn btn-no-margin" value="{$smarty.const.IMAGE_INSERT}" >
        <input type="button" class="btn btn-cancel btn-no-margin" value="{$smarty.const.IMAGE_CANCEL}" onClick="return backStatement()">
    {/if}
</div>
    </form>

<script type="text/javascript">
function backStatement() {
    window.history.back();
    return false;
}
    $(function(){

        $(".translate-frontend").tlSwitch({
            onText: "{$smarty.const.SW_ON}",
            offText: "{$smarty.const.SW_OFF}",
            handleWidth: '20px',
            labelWidth: '24px',
            onSwitchChange: function(e, status){
            }
        });
    })
</script>
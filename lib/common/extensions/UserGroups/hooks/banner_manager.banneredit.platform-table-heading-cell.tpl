{if $ext = \common\helpers\Acl::checkExtensionAllowed('UserGroups', 'allowed')}
    {if $ext::useWithBanners()}
        {\common\extensions\UserGroups\assets\BannerEditAsset::register($this)|void}
        <th>{$smarty.const.USER_GROUPS}</th>
    {/if}
{/if}
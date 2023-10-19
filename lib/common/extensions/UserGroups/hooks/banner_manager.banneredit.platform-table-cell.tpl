{if $ext = \common\helpers\Acl::checkExtensionAllowed('UserGroups', 'allowed')}
    {if $ext::useWithBanners()}
        {$ext::platformTableCell($banners_data, $platform['id'])}
    {/if}
{/if}
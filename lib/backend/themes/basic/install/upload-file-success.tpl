{$uploadInfo}
<p>{$message}</p>
{foreach $packagesSynergyList as $package name=Selected}
    {if $smarty.foreach.Selected.first}
        <label>Synergy packages list:</label>
    {/if}
    <p>{$package}</p>
{/foreach}
{if !empty($extClass)}
    {if $menu = \common\helpers\MenuHelper::getExtensionHtmlMenu($extClass, false, 'extension-menu-item mt-1')}
        <br><br>
        <div class="extensions-menu-title"><b>{$smarty.const.TEXT_MENU_STRUCTURE}:</b></div>
        {$menu}
    {/if}
{/if}
<script>
    $('.bootbox').on('hidden.bs.modal' , function() {
        window.location.href = window.location.href;
    });
</script>
<style>
    .extension-menu-item .btn {
        margin-top: 0 !important;
    }
</style>
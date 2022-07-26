<!-- TEMPORARY STYLES - TODO DELETE AND NEED ADD THEME DISAINER -->
<style>
    .catalog-pages-listing-ul li a{
        display: block;
    }
</style>
<div class="catalog-pages-listing">
    <div>{$smarty.const.LAST_EVENTS}</div>
    <ul class="catalog-pages-listing-ul">
        {foreach $infoPages as $page}
            <li>
                <img src="{if $page.image}{$imageInformationPath|cat:$page.image}{else}{\frontend\design\Info::themeSetting('na_product', 'hide')}{/if}" title="{$page_title}" alt="{$page_title}" />
                <div>{$page.date_added|date_format:"%d/%m/%y "}</div>
                <div>{$page.name}</div>
                <a href="{\Yii::$app->urlManager->createUrl(["info", 'info_id' => $page.information_id])}" title="{$page.page_title}">{$page.page_title}</a>
                <div>{$page.description_short}</div>
            </li>
        {/foreach}
    </ul>
</div>
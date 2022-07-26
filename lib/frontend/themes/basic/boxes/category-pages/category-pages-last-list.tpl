<!-- TEMPORARY STYLES - TODO DELETE AND NEED ADD THEME DISAINER -->
<style>
    .catalog-pages-listing-ul li a{
        display: block;
    }
</style>
<div class="catalog-pages-listing">
    {*<div>{$smarty.const.LAST_EVENTS}</div>*}
    <ul class="catalog-pages-listing-ul">
        {foreach $infoPages as $page}
            <li>
                <a href="{\Yii::$app->urlManager->createUrl(["info", 'info_id' => $page.information_id])}" title="{$page.page_title}"><img src="{if $page.image}{$imageInformationPath|cat:$page.image}{else}{\frontend\design\Info::themeSetting('na_product', 'hide')}{/if}" title="{$page_title}" alt="{$page_title}" /></a>
                <div class="date-catalog">{$page.date_added|date_format:"%d/%m/%y "}</div>
                <a class="title-catalog-page" href="{\Yii::$app->urlManager->createUrl(["info", 'info_id' => $page.information_id])}" title="{$page.page_title}">{$page.page_title}</a>
                <div class="name-category">{$page.name}</div>
                <div class="catalog-description">{$page.description_short}</div>
            </li>
        {/foreach}
    </ul>
</div>
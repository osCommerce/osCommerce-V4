<!-- TEMPORARY STYLES - TODO DELETE AND NEED ADD THEME DISAINER -->
<style>
    .catalog-pages-img{
        display: inline-block;
        width: 30%;
    }
    .catalog-pages-name{
        display: inline-block;
        width: 69%;
    }
    .catalog-pages-short_description{
        width: 100%;
    }
    .catalog-pages-listing-ul li a{
        display: block;
    }
</style>
<div class="catalog-pages-listing">
    <div class="catalog-pages-img">
        <img src="{if $infoPages.image}{$imagePageCatalogPath|cat:$infoPages.image}{else}{\frontend\design\Info::themeSetting('na_product', 'hide')}{/if}" title="{$infoPages.descriptionLanguageId.name}"  alt="{$infoPages.descriptionLanguageId.name}">
    </div>
    <div class="catalog-pages-name">
        {$infoPages.descriptionLanguageId.name}
    </div>
    <div class="catalog-pages-short_description">
        {$infoPages.descriptionLanguageId.description_short}
    </div>
    <ul class="catalog-pages-listing-ul">
        {foreach $infoPages.information  as $page}
            <li>
                <img src="{if $page.image}{$imageInformationPath|cat:$page.image}{else}{\frontend\design\Info::themeSetting('na_product', 'hide')}{/if}" title="{$page_title}" alt="{$page_title}" />
                <div>{$page.date_added|date_format:"%d/%m/%y "}</div>
                <a href="{\Yii::$app->urlManager->createUrl(["info", 'info_id' => $page.information_id])}" title="{$page.page_title}">{$page.page_title}</a>
                <div>{$page.description_short}</div>
            </li>
        {/foreach}
    </ul>
</div>
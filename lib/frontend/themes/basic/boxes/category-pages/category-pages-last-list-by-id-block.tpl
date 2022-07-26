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
        <img src="{if $catalogPage.image}{$imagePageCatalogPath|cat:$catalogPage.image}{else}{\frontend\design\Info::themeSetting('na_product', 'hide')}{/if}" title="{$catalogPage.descriptionLanguageId.name}"  alt="{$catalogPage.descriptionLanguageId.name}">
    </div>
    <div class="catalog-pages-name">
        {$catalogPage.descriptionLanguageId.name}
    </div>
    <div class="catalog-pages-short_description">
        {$catalogPage.descriptionLanguageId.description_short}
    </div>
    <div>{$smarty.const.LAST_EVENTS}</div>
    <ul class="catalog-pages-listing-ul">
        {if $infoPages}
            {foreach $infoPages as $page}
                <li>
                    <img src="{if $page.image}{$imageInformationPath|cat:$page.image}{else}{\frontend\design\Info::themeSetting('na_product', 'hide')}{/if}" title="{$page_title}" alt="{$page_title}" />
                    <div>{$page.date_added|date_format:"%d/%m/%y "}</div>
                    <a href="{\Yii::$app->urlManager->createUrl(["info", 'info_id' => $page.information_id])}" title="{$page.page_title}">{$page.page_title}</a>
                    <div>{$page.description_short}</div>
                </li>
            {/foreach}
        {/if}
    </ul>
</div>
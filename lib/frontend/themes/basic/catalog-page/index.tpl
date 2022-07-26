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
    .catalog-pages-catalog-ul li{
        padding: 10px;
        width: 45%;
        text-align: center;
        display: inline-block;
    }
    .catalog-pages-catalog-ul li img{
        display: inline-block;
    }
    .catalog-pages-catalog-ul li a{
        display: block;
    }
</style>
{if $catalogPage}
    <h1>{$catalogPage.descriptionLanguageId.name}</h1>

    {assign var="breadcumbsCount" value=($breadcumbs|count)-1 }
    {if $breadcumbsCount >= 0}
        <a href="{Yii::$app->urlManager->createUrl(['catalog-page'])}">{$smarty.const.TEXT_CATEGORY_PAGE}</a> >
    {/if}
    {foreach $breadcumbs as $key => $bread}
        {if $key !== $breadcumbsCount}
            <a href="{Yii::$app->urlManager->createUrl(['catalog-page/'|cat:$bread.slug])}" ">{$bread.name}</a> >
        {else}
            <b>{$bread.name}</b>
        {/if}
    {/foreach}

{else}
    <h1>{$smarty.const.TEXT_CATEGORY_PAGE}</h1>
{/if}
{if $catalogPagesCatalog}
    <div class="catalog-pages-catalog">
        <ul class="catalog-pages-catalog-ul">
            {foreach $catalogPagesCatalog  as $catalog}
                <li>
                    <img src="{if $catalog.image}{$imagePageCatalogPath|cat:$catalog.image}{else}/themes/theme-1/img/na.png{/if}" title="{$catalog.descriptionLanguageId.name}"  alt="{$catalog.descriptionLanguageId.name}">
                    <a href="{Yii::$app->urlManager->createUrl(['catalog-page/'|cat:$catalog.descriptionLanguageId.slug])}" title="{$catalog.descriptionLanguageId.name}">{$catalog.descriptionLanguageId.name}</a>
                    <div>{$catalog.descriptionLanguageId.description_short}</div>
                </li>
            {/foreach}
        </ul>
    </div>
{/if}
{if $catalogPage}
    <div class="catalog-pages-listing">
        <div class="catalog-pages-img">
            <img src="{if $catalogPage.image}{$imagePageCatalogPath|cat:$catalogPage.image}{else}/themes/theme-1/img/na.png{/if}" title="{$catalogPage.descriptionLanguageId.name}"  alt="{$catalogPage.descriptionLanguageId.name}">
        </div>
        <div class="catalog-pages-name">
            {$catalogPage.descriptionLanguageId.name}
        </div>
        <div class="catalog-pages-short_description">
            {$catalogPage.descriptionLanguageId.description}
        </div>
        <ul class="catalog-pages-listing-ul">
            {foreach $catalogPage.information  as $page}
                <li><a href="/{$page.seo_page_name}" title="{$page.page_title}">{$page.page_title}</a></li>
            {/foreach}
        </ul>
    </div>
{/if}
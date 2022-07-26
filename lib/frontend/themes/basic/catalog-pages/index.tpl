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
<h1>{$smarty.const.TEXT_CATEGORY_PAGE}</h1>
{if $catalogPagesCatalog}
    <div class="catalog-pages-catalog">
        <ul class="catalog-pages-catalog-ul">
            {foreach $catalogPagesCatalog  as $catalog}
                <li>
                    <img src="{if $catalog.image}{$imagePageCatalogPath|cat:$catalog.image}{else}{\frontend\design\Info::themeSetting('na_product', 'hide')}{/if}" title="{$catalog.descriptionLanguageId.name}"  alt="{$catalog.descriptionLanguageId.name}">
                    <a href="{Yii::$app->urlManager->createUrl(['catalog-pages/post','page'=>$catalog.descriptionLanguageId.slug])}" title="{$catalog.descriptionLanguageId.name}">{$catalog.descriptionLanguageId.name}</a>
                    <div>{$catalog.descriptionLanguageId.description_short}</div>
                </li>
            {/foreach}
        </ul>
    </div>
{/if}
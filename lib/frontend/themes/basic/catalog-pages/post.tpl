{use class="yii\widgets\LinkPager"}
{use class="frontend\design\Info"}
{Info::addBoxToCss('pagination')}
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
    .paging{
        margin-top: 10px;
    }
    ul.pagination li{
        display: inline-block;
        margin: 0 5px;
    }
    ul.pagination li.prev:before,ul.pagination li.next:before{
        content:none;
    }
    ul.pagination li.prev,ul.pagination li.next{
        border:none;
        padding: 0;
    }
</style>
<h1>{$catalogPage.descriptionLanguageId.name}</h1>
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
{if $catalogPage}
    <div class="catalog-pages-listing">
        <div class="catalog-pages-img">
            <img src="{if $catalogPage.image}{$imagePageCatalogPath|cat:$catalogPage.image}{else}{\frontend\design\Info::themeSetting('na_product', 'hide')}{/if}" title="{$catalogPage.descriptionLanguageId.name}"  alt="{$catalogPage.descriptionLanguageId.name}">
        </div>
        <div class="catalog-pages-name">
            {$catalogPage.descriptionLanguageId.name}
        </div>
        <div class="CATALOG-pages-short_description">
            {$catalogPage.descriptionLanguageId.description}
        </div>
        <ul class="catalog-pages-listing-ul">
            {foreach $infoPages->getModels()  as $page}
                <img src="{if $page.image}{$imageInformationPath|cat:$page.image}{else}{\frontend\design\Info::themeSetting('na_product', 'hide')}{/if}" title="{$page_title}" alt="{$page_title}" />
                <div class="date-catalog">{$page.date_added|date_format:"%d/%m/%y "}</div>
                <a href="{\Yii::$app->urlManager->createUrl(["info", 'info_id' => $page.information_id])}" title="{$page.page_title}">{$page.page_title}</a>
                <div>{$page.description_short}</div>
            {/foreach}
        </ul>
        <div class="paging">
            {LinkPager::widget(['pagination' => $infoPages->pagination, 'options'=> ['class'=>'pagination'] ])}
        </div>
    </div>
{/if}
{assign var="breadcrumbsCount" value=($breadcrumbs|count)-1 }
{if $breadcrumbsCount >= 0}
    <a href="{Yii::$app->urlManager->createUrl(['catalog-pages/','platform_id'=>$bread.platform_id, 'item_id'=> 0, 'parent_id'=> 0])}">{$smarty.const.TEXT_TOP}</a> >
{/if}
{foreach $breadcrumbs as $key => $bread}
    {if $key !== $breadcrumbsCount}
        <a href="{Yii::$app->urlManager->createUrl(['catalog-pages/','platform_id'=>$bread.platform_id, 'item_id'=> $bread.catalog_pages_id, 'parent_id'=> $bread.parent_id])}">{$bread.name}</a> >
    {else}
        <b>{$bread.name}</b>
    {/if}
{/foreach}

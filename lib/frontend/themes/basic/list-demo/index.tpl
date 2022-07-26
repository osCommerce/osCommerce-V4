


{if in_array($get['list_type'], ['type-1', 'type-1_2', 'type-1_3', 'type-1_4', 'type-2', 'type-2_2'])}
    {frontend\design\boxes\Listing::widget($params)}
{else}
    {\frontend\design\boxes\ProductListing::widget($params)}
{/if}

<style type="text/css">
    .item {
        min-width: 100% !important;
    }
</style>
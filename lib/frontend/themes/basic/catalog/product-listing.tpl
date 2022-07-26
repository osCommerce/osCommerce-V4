{use class="frontend\design\Block"}
<div class="products-listing cols-1 list-productListing w-list-productListing">
{Block::widget(['name' => $page_name, 'params' => ['type' => 'productListing']])}
</div>

<style type="text/css">
    .header, .footer, .messageBox {
        display: none;
    }
</style>
<script type="text/javascript">
    tl(function(){
        $('.header, .footer, .messageBox').remove()
    })
</script>
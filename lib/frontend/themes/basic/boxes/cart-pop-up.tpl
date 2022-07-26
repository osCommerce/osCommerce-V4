{use class="yii\helpers\Html"}
<div class="cart-content">
    {foreach $products as $item}
        <a href="{$item.link}" class="item">
            <span class="image"><img src="{$item.image}" alt="{str_replace('"', '″', $item.name)}"></span>

            <span class="name"><span class="qty">{$item.quantity_virtual}</span>{$item.name}</span>
            <span class="price">{$item.price}</span>
        </a>
    {/foreach}

    <div class="cart-total">{$smarty.const.SUB_TOTAL} {$total}</div>

    <div class="buttons">
        <div class="left-buttons"><a href="{tep_href_link('shopping-cart/index')}"
                                     class="btn">{$smarty.const.TEXT_HEADING_SHOPPING_CART}</a></div>
        <div class="right-buttons"><a href="{$checkout_link}"
                                      class="btn">{$smarty.const.HEADER_TITLE_CHECKOUT}</a></div>
    </div>
</div>
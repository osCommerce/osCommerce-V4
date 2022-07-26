<div class="w-line-row w-line-row-qtd">
    <div class="edp-line">
        <label>{$smarty.const.TEXT_QUANTITY_DISCOUNT}</label>
        <div class="quantity-discounts">
          <div class="quantity-discounts-content">
            {foreach $discounts as $key=>$discount}
            <div class="item" data-id="{$key+1}" data-min="" data-max="">
              <span class="count">{$discount.count}</span>
              <span class="price">{$discount.price}</span>
            </div>
            {/foreach}
          </div>
        </div>
    </div>
</div>
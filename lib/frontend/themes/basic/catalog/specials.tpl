{use class="frontend\design\Block"}

<div class="special-page">

  {*{Block::widget(['name' => 'products', 'params' => ['type' => 'catalog', 'params' => $params]])}*}
  {Block::widget(['name' => 'products', 'params' => $params])}
{*
  {frontend\design\boxes\ListingFunctionality::widget($params)}
  {frontend\design\boxes\PagingBar::widget($params)}
  {frontend\design\boxes\Listing::widget($params)}
  {frontend\design\boxes\PagingBar::widget($params)}
*}

</div>


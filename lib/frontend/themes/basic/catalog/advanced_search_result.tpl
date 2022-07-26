{use class="frontend\design\Block"}

<div class="advanced-search-result-page">
  <h3>{$smarty.const.HEADING_TITLE_2}</h3>

  {Block::widget(['name' => 'products', 'params' => $params])}
{*
  {frontend\design\boxes\ListingFunctionality::widget($params)}
  {frontend\design\boxes\PagingBar::widget($params)}
  {frontend\design\boxes\Listing::widget($params)}
  {frontend\design\boxes\PagingBar::widget($params)}
*}

</div>


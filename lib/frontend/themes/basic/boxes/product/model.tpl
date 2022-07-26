{if $data.model && !(isset($settings.show_model) && $settings.show_model == 'no')}
<div class="model">
  <strong>{$smarty.const.TEXT_MODEL}<span class="colon">:</span></strong> <span itemprop="model">{$data.model}</span>
</div>
{/if}
{if $data.ean && $settings.show_ean != 'no'}
  <div class="ean">
    <strong>{$smarty.const.TEXT_EAN}<span class="colon">:</span></strong> <span itemprop="gtin13">{$data.ean}</span>
  </div>
{/if}
{if $data.isbn && $settings.show_isbn != 'no'}
  <div class="isbn">
    <strong>{$smarty.const.TEXT_ISBN}<span class="colon">:</span></strong> <span>{$data.isbn}</span>
  </div>
{/if}
{if $data.asin && $settings.show_asin != 'no'}
  <div class="asin">
    <strong>{$smarty.const.TEXT_ASIN}<span class="colon">:</span></strong> <span>{$data.asin}</span>
  </div>
{/if}
{if $data.upc && $settings.show_upc != 'no'}
  <div class="upc">
    <strong>{$smarty.const.TEXT_UPC}<span class="colon">:</span></strong> <span itemprop="gtin12">{$data.upc}</span>
  </div>
{/if}
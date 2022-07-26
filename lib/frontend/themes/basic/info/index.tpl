{use class="frontend\design\Block"}
{if $page_name == '0_blank'}
  {*frontend\design\boxes\info\Content::widget()*}
  {Block::widget(['name' => 'info', 'params' => ['type' => 'inform']])}
{else}
  {Block::widget(['name' => $page_name, 'params' => ['type' => 'inform']])}
{/if}
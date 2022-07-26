{if $widgets}
    {use class="frontend\design\Block"}

    {if $forever}
        {Block::widget(['name' => 'logoff_forever', 'params' => ['type' => 'inform']])}
    {else}
        {Block::widget(['name' => 'logoff', 'params' => ['type' => 'inform']])}
    {/if}
{else}
  <h1>{$title}</h1>
    {if $forever}
      <p>{$smarty.const.TEXT_CUSTOMER_DELETED}</p>
    {else}
      <p>{$smarty.const.TEXT_MAIN}</p>
    {/if}
  <br><br>
  <div class="buttons">
    <div class="left-buttons"><a href="{$link_continue_href}" class="btn">{$smarty.const.IMAGE_BUTTON_CONTINUE}</a></div>
  </div>
{/if}

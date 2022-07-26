{use class="frontend\design\Block"}
{if $hasTemplate}
    {Block::widget(['name' => '404', 'params' => ['type' => 'inform']])}
{else}
    {$smarty.const.TEXT_PAGE_NOT_FOUND}
{/if}
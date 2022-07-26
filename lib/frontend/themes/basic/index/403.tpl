{use class="frontend\design\Block"}
{if $hasTemplate}
    {Block::widget(['name' => '403', 'params' => ['type' => 'inform']])}
{else}
    {$smarty.const.TEXT_PAGE_ACCESS_FORBIDDEN}
{/if}
{\frontend\design\Info::addBoxToCss('page-name')}

{if $smarty.const.HEAD_H1_TAG_PRODUCT_REVIEWS_INFO}    
    {if !$settings[0].show_heading}
        <div class="page-name">{$smarty.const.TEXT_PAGE_REVIEW_TITLE}</div>
        <h1>{$smarty.const.HEAD_H1_TAG_PRODUCT_REVIEWS_INFO}</h1>
    {elseif $settings[0].show_heading == 'h1_name'}
        <h1>{$smarty.const.HEAD_H1_TAG_PRODUCT_REVIEWS_INFO}</h1>
        <div class="page-name">{$smarty.const.TEXT_PAGE_REVIEW_TITLE}</div>
    {elseif $settings[0].show_heading == 'h1'}
        <h1>{$smarty.const.HEAD_H1_TAG_PRODUCT_REVIEWS_INFO}</h1>
    {elseif $settings[0].show_heading == 'name_in_div'}
        <div>{$smarty.const.TEXT_PAGE_REVIEW_TITLE}</div>
    {elseif $settings[0].show_heading == 'name_in_h1'}
        <h1>{$smarty.const.TEXT_PAGE_REVIEW_TITLE}</h1>
    {elseif $settings[0].show_heading == 'name_in_h2'}
        <h2>{$smarty.const.TEXT_PAGE_REVIEW_TITLE}</h2>
    {elseif $settings[0].show_heading == 'name_in_h3'}
        <h3>{$smarty.const.TEXT_PAGE_REVIEW_TITLE}</h3>
    {elseif $settings[0].show_heading == 'name_in_h4'}
        <h4>{$smarty.const.TEXT_PAGE_REVIEW_TITLE}</h4>
    {/if}
{else}
    <h1>{$smarty.const.TEXT_PAGE_REVIEW_TITLE}</h1>
{/if}

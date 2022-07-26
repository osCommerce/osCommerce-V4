
{if $h1}
    {\frontend\design\Info::addBoxToCss('page-name')}
    {if !$settings[0].show_heading}
        <div class="page-name">{$title}</div>
        <h1>{$h1}</h1>
    {elseif $settings[0].show_heading == 'h1_name'}
        <h1>{$h1}</h1>
        <div class="page-name">{$title}</div>
    {elseif $settings[0].show_heading == 'h1'}
        <h1>{$h1}</h1>
    {elseif $settings[0].show_heading == 'name_in_div'}
        <div>{$title}</div>
    {elseif $settings[0].show_heading == 'name_in_h1'}
        <h1>{$title}</h1>
    {elseif $settings[0].show_heading == 'name_in_h2'}
        <h2 class="heading-2">{$title}</h2>
    {elseif $settings[0].show_heading == 'name_in_h3'}
        <h3 class="heading-3">{$title}</h3>
    {elseif $settings[0].show_heading == 'name_in_h4'}
        <h4 class="heading-4">{$title}</h4>
    {/if}
{else}
    <h1>{$title}</h1>
{/if}
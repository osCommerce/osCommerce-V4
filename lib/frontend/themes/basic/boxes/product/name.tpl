{if $h1}
    {\frontend\design\Info::addBoxToCss('page-name')}
    {if !$settings[0].show_heading}
        <div class="page-name">{$name}</div>
        <h1>{$h1}</h1>
    {elseif $settings[0].show_heading == 'h1_name'}
        <h1>{$h1}</h1>
        <div class="page-name">{$name}</div>
    {elseif $settings[0].show_heading == 'h1'}
        <h1>{$h1}</h1>
    {elseif $settings[0].show_heading == 'name_in_div'}
        <div>{$name}</div>
    {elseif $settings[0].show_heading == 'name_in_h1'}
        <h1>{$name}</h1>
    {elseif $settings[0].show_heading == 'name_in_h2'}
        <h2 class="heading-2">{$name}</h2>
    {elseif $settings[0].show_heading == 'name_in_h3'}
        <h3 class="heading-3">{$name}</h3>
    {elseif $settings[0].show_heading == 'name_in_h4'}
        <h4 class="heading-4">{$name}</h4>
    {/if}
{else}
    {if $settings[0].show_heading == 'name_in_div'}
        <div>{$name}</div>
    {elseif $settings[0].show_heading == 'name_in_h1'}
        <h1>{$name}</h1>
    {elseif $settings[0].show_heading == 'name_in_h2'}
        <h2 class="heading-2">{$name}</h2>
    {elseif $settings[0].show_heading == 'name_in_h3'}
        <h3 class="heading-3">{$name}</h3>
    {elseif $settings[0].show_heading == 'name_in_h4'}
        <h4 class="heading-4">{$name}</h4>
    {else}
        <h1>{$name}</h1>
    {/if}
{/if}
{if $params.message}
    {\frontend\design\Info::addBoxToCss('info')}
    <div class="info">{$params.message}</div>
{/if}
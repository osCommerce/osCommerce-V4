{if defined('MAX_SHOW_ATTRIBUTE_LENGTH')}{$max_attribute_length=MAX_SHOW_ATTRIBUTE_LENGTH}{else}{{$max_attribute_length=20}}{/if}
{foreach $attributes as $attribute}
    {$value=htmlspecialchars($attribute['value'])}
    {if strlen($value) > $max_attribute_length}
        {$hint="title=\"`$value`\""}
        {$value=substr($value,0,$max_attribute_length)|cat:"..."}
    {/if}
    <br><nobr><small style="">&nbsp;&nbsp;<i {$hint}> - {str_replace(array('&amp;nbsp;', '&lt;b&gt;', '&lt;/b&gt;', '&lt;br&gt;'), array('&nbsp;', '<b>', '</b>', '<br>'), htmlspecialchars($attribute['option']))}
    {if $attribute['value']}: {$value}{/if}
    {if round($attribute['price'], 2) != 0}{$attribute['display_price']}{/if}
    </i></small></nobr>
{/foreach}
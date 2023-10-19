{foreach $attributes as $attribute}
    {$value=htmlspecialchars($attribute['value'])}
    {if strlen($value)> 20}
        {$hint="title=\"`$value`\""}
        {$value=substr($value,0,20)|cat:"..."}
    {/if}
    <br><nobr><small style="">&nbsp;&nbsp;<i {$hint}> - {str_replace(array('&amp;nbsp;', '&lt;b&gt;', '&lt;/b&gt;', '&lt;br&gt;'), array('&nbsp;', '<b>', '</b>', '<br>'), htmlspecialchars($attribute['option']))}
    {if $attribute['value']}: {$value}{/if}
    {$attribute['display_price']}
    </i></small></nobr>
{/foreach}
{foreach $attributes as $attribute}
    <br><nobr><small>&nbsp;&nbsp;<i> - {str_replace(array('&amp;nbsp;', '&lt;b&gt;', '&lt;/b&gt;', '&lt;br&gt;'), array('&nbsp;', '<b>', '</b>', '<br>'), htmlspecialchars($attribute['option']))}
    {if $attribute['value']}: {htmlspecialchars($attribute['value'])}{/if}
    {$attribute['display_price']}
    </i></small></nobr>
{/foreach}
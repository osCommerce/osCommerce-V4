{use class="frontend\design\Info"}
<div class="tracking-numbers{if $settings[0].short_view} short-view{/if}">
    {foreach $trackings as $tkey => $track}
        <div class="item">
            <div class="tracking-code{if is_array($track['products']) && count($track['products']) > 0 && !$settings[0].short_view} tracking-code-left{/if}">
                <a href="{$track['url']}" target="_blank">
                    <span>{$track['number']}</span>
                    <img alt="{$track['number']}" src="{$track['qr_code_url']}">
                </a>
            </div>

            {if is_array($track['products']) && count($track['products']) > 0 && !$settings[0].short_view}
                <div class="tracking-products">
                    <table border="0" class="table table-bordered">
                        <tr>
                            <th class="qty">{$smarty.const.QTY}</th>
                            <th>{$smarty.const.PRODUCTS}</th>
                            <th class="model">{$smarty.const.TEXT_MODEL}</th>
                        </tr>
                        {foreach $track['products'] as $product}
                            <tr>
                                <td class="qty">{if $product['selected_qty'] > 0}{$product['selected_qty']}{else}{$product['qty']}{/if}&nbsp;x</td>
                                <td>
                                    {htmlspecialchars($product['name'])}
                                    {if (isset($product['attributes']) && (sizeof($product['attributes']) > 0))}
                                        {for $j = 0 to (sizeof($product['attributes']) - 1)}
                                            <br><nobr><small>&nbsp;&nbsp;<i> - {str_replace(array('&amp;nbsp;', '&lt;b&gt;', '&lt;/b&gt;', '&lt;br&gt;'), array('&nbsp;', '<b>', '</b>', '<br>'), htmlspecialchars($product['attributes'][$j]['option']))}: {htmlspecialchars($product['attributes'][$j]['value'])}
                                                </i></small></nobr>
                                        {/for}
                                    {/if}
                                </td>
                                <td class="model">{$product['model']}</td>
                            </tr>
                        {/foreach}
                    </table>
                </div>
            {/if}
        </div>
        {if $settings[0].short_view}{break}{/if}
    {/foreach}
</div>

{if $trackings|count == 0 && !Info::isAdmin()}
    <script>
        tl(function(){
            {if $settings[0].hide_parents == 1}
            $('#box-{$id}').hide()
            {elseif $settings[0].hide_parents == 2}
            $('#box-{$id}').closest('.box-block').hide()
            {elseif $settings[0].hide_parents == 3}
            $('#box-{$id}').closest('.box-block').closest('.box-block').hide()
            {elseif $settings[0].hide_parents == 4}
            $('#box-{$id}').closest('.box-block').closest('.box-block').closest('.box-block').hide()
            {/if}
        })
    </script>
{/if}
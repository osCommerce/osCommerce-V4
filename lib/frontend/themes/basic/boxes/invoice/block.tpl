{use class="frontend\design\Block"}
{if $type == 1}
  {$block_1}
{else}

<table cellpadding="0" cellspacing="0" border="0" width="{$p_width|ceil}" style="width: {$p_width|ceil}px">
  <tr>
    {if $type == 2}
      <td style="width: {($p_width*0.5)|ceil}px">
        {$block_1}
      </td>
      <td style="width: {($p_width*0.5)|ceil}px">
        {$block_2}
      </td>
    {elseif $type == 3}
      <td style="width: {($p_width*0.3333)|ceil}px">
        {$block_1}
      </td>
      <td style="width: {($p_width*0.3333)|ceil}px">
        {$block_2}
      </td>
      <td style="width: {($p_width*0.3333)|ceil}px">
        {$block_3}
      </td>
    {elseif $type == 4}
      <td style="width: {($p_width*0.6666)|ceil}px">
        {$block_1}
      </td>
      <td style="width: {($p_width*0.3333)|ceil}px">
        {$block_2}
      </td>
    {elseif $type == 5}
      <td style="width: {($p_width*0.3333)|ceil}px">
        {$block_1}
      </td>
      <td style="width: {($p_width*0.6666)|ceil}px">
        {$block_2}
      </td>
    {elseif $type == 6}
      <td style="width: {($p_width*0.25)|ceil}px">
        {$block_1}
      </td>
      <td style="width: {($p_width*0.75)|ceil}px">
        {$block_2}
      </td>
    {elseif $type == 7}
      <td style="width: {($p_width*0.75)|ceil}px">
        {$block_1}
      </td>
      <td style="width: {($p_width*0.25)|ceil}px">
        {$block_2}
      </td>
    {elseif $type == 8}
      <td style="width: {($p_width*0.25)|ceil}px">
        {$block_1}
      </td>
      <td style="width: {($p_width*0.5)|ceil}px">
        $block_2}
      </td>
      <td style="width: {($p_width*0.25)|ceil}px">
        {$block_3}
      </td>
    {elseif $type == 9}
      <td style="width: {($p_width*0.2)|ceil}px">
        {$block_1}
      </td>
      <td style="width: {($p_width*0.8)|ceil}px">
        {$block_2}
      </td>
    {elseif $type == 10}
      <td style="width: {($p_width*0.8)|ceil}px">
        {$block_1}
      </td>
      <td style="width: {($p_width*0.2)|ceil}px">
        {$block_2}
      </td>
    {elseif $type == 11}
      <td style="width: {($p_width*0.4)|ceil}px">
        {$block_1}
      </td>
      <td style="width: {($p_width*0.6)|ceil}px">
        {$block_2}
      </td>
    {elseif $type == 12}
      <td style="width: {($p_width*0.6)|ceil}px">
        {$block_1}
      </td>
      <td style="width: {($p_width*0.4)|ceil}px">
        {$block_2}
      </td>
    {elseif $type == 13}
      <td style="width: {($p_width*0.2)|ceil}px">
        {$block_1}
      </td>
      <td style="width: {($p_width*0.6)|ceil}px">
        {$block_2}
      </td>
      <td style="width: {($p_width*0.2)|ceil}px">
        {$block_3}
      </td>
    {elseif $type == 14}
      <td style="width: {($p_width*0.25)|ceil}px">
        {$block_1}
      </td>
      <td style="width: {($p_width*0.25)|ceil}px">
        {$block_2}
      </td>
      <td style="width: {($p_width*0.25)|ceil}px">
        {$block_3}
      </td>
      <td style="width: {($p_width*0.25)|ceil}px">
        {$block_4}
      </td>
    {elseif $type == 15}
      <td style="width: {($p_width*0.2)|ceil}px">
        {$block_1}
      </td>
      <td style="width: {($p_width*0.2)|ceil}px">
        {$block_2}
      </td>
      <td style="width: {($p_width*0.2)|ceil}px">
        {$block_3}
      </td>
      <td style="width: {($p_width*0.2)|ceil}px">
        {$block_4}
      </td>
      <td style="width: {($p_width*0.2)|ceil}px">
        {$block_5}
      </td>
    {else}
      <td width="">
        <div class="no-block-settings"></div>
        <script type="text/javascript">
          tl(function(){
            setTimeout(function () {
              $('.no-block-settings').closest('.box-block').find('.edit-box').trigger('click')
            }, 2000)
          })
        </script>
      </td>
    {/if}
  </tr>
</table>
{/if}
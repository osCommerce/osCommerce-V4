{*use class="frontend\design\Block"}

{assign var="type" value=$settings[0].block_type}
{if $type == 2 || $type == 4 || $type == 5 || $type == 6 || $type == 7 || $type == 9 || $type == 10 || $type == 11 || $type == 12}
  {Block::widget(['name' => $block_id])}
  {Block::widget(['name' => $block_id|cat:'-2', 'params' => ['cols' => 2]])}

{elseif $type == 3 || $type == 8 || $type == 13}
  {Block::widget(['name' => $block_id])}
  {Block::widget(['name' => $block_id|cat:'-2', 'params' => ['cols' => 2]])}
  {Block::widget(['name' => $block_id|cat:'-3', 'params' => ['cols' => 3]])}

{elseif $type == 1}
  {Block::widget(['name' => $block_id])}
{elseif $type == 14}
  {Block::widget(['name' => $block_id])}
  {Block::widget(['name' => $block_id|cat:'-2', 'params' => ['cols' => 2]])}
  {Block::widget(['name' => $block_id|cat:'-3', 'params' => ['cols' => 3]])}
  {Block::widget(['name' => $block_id|cat:'-4', 'params' => ['cols' => 4]])}
{elseif $type == 15}
  {Block::widget(['name' => $block_id])}
  {Block::widget(['name' => $block_id|cat:'-2', 'params' => ['cols' => 2]])}
  {Block::widget(['name' => $block_id|cat:'-3', 'params' => ['cols' => 3]])}
  {Block::widget(['name' => $block_id|cat:'-4', 'params' => ['cols' => 4]])}
  {Block::widget(['name' => $block_id|cat:'-5', 'params' => ['cols' => 5]])}
{else}
  {Block::widget(['name' => $block_id])}
  <div class="no-block-settings"></div>
  <script type="text/javascript">
    (function($){ $(function(){ setTimeout(function(){
      $('.no-block-settings').closest('.box-block').find('.edit-box').trigger('click')
    }, 2000)  }) })(jQuery)
  </script>
{/if*}
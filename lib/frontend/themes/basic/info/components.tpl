{use class="frontend\design\Block"}
{use class="frontend\design\Info"}

{Block::widget(['name' => $page_name, 'params' => ['type' => 'components']])}

<style type="text/css">
  body > .header,
  body > .footer,
  body > .messageBox {
    visibility: hidden;
    height: 0;
    overflow: hidden;
  }
</style>
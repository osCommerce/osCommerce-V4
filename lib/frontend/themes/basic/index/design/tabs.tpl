{use class="frontend\design\Info"}
{Info::addBlockToWidgetsList('tabs')}

<div style="margin: 30px 0 30px;">

  <div class="demo-heading-3">{$smarty.const.IMAGE_PREVIEW}</div>
  <div class="box-block type- tabs w-tabs" style="font-weight:normal;" data-name="Tabs" id="box-0">
    <div class="tab-navigation">
      <div class="tab-block-0-1 tab-li">
        <span data-href="#tab-block-0-1 tab-a" class="tab-a active">tab 1</span>
      </div>
      <div class="tab-block-0-2 tab-li">
        <span data-href="#tab-block-0-2 tab-a" class="tab-a">tab 2</span>
      </div>
      <div class="tab-block-0-2 tab-li">
        <span data-href="#tab-block-0-2 tab-a" class="tab-a">tab 3</span>
      </div>
    </div>
    <div class="block" id="tab-block-0-1" style="display: block;">
      Tab content
    </div>
    <div class="block" id="tab-block-0-2" style="display: none;">
      Tabs 2 content
    </div>
    <div class="block" id="tab-block-0-3" style="display: none;">
      Tabs 3 content
    </div>
  </div>


  <div class="demo-heading-3">{$smarty.const.EDIT}</div>

  <div class="edit-tabs"{Info::dataClass('.w-tabs')}>
    <div class="edit-tab-navigation"{Info::dataClass('.w-tabs .tab-navigation')}>
      <div{Info::dataClass('.w-tabs .tab-li')}>
        <span class=""{Info::dataClass('.w-tabs .tab-a')}>tab 1</span>
      </div>
      <div{Info::dataClass('.w-tabs .tab-li')}>
        <span class=""{Info::dataClass('.w-tabs .tab-a')}>tab 2</span>
      </div>
      <div{Info::dataClass('.w-tabs .tab-li')}>
        <span class=""{Info::dataClass('.w-tabs .tab-a')}>tab 3</span>
      </div>
    </div>
    <div class="edit-block" style="display: block;"{Info::dataClass('.w-tabs > .block')}>
      Tab content
    </div>
  </div>

</div>
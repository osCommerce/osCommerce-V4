{use class="frontend\design\Info"}


<div class="frame-content-wrap">

  <div class="col-left">
    <div class="demo-heading-3">{$smarty.const.IMAGE_PREVIEW}</div>


    <div class="filter-widget">
      <div class="head-filter-widget"><span>Refine search</span><a class="mobileCollapse" href="#">&nbsp;</a></div>
      <div class="content-filter-widget">
        <div class="filter-box filter-box-ul" id="fil-keywords">
          <div class="filter-box-head"><span class=""></span>Keywords</div>
          <div class="filter-box-content" style="display: block;">
            <input type="text" value="" name="keywords">
          </div>

        </div>
        <div class="filter-box filter-box-ul" id="fil-p">
          <div class="filter-box-head"><span></span>Price</div>
          <div class="filter-box-content">
            <div id="slider-p" class="ui-slider ui-slider-horizontal ui-widget ui-widget-content ui-corner-all">
              <div class="ui-slider-range ui-widget-header ui-corner-all" style="left: 0%; width: 100%;"></div>
              <span class="ui-slider-handle ui-state-default ui-corner-all" tabindex="0" style="left: 0%;"></span>
              <span class="ui-slider-handle ui-state-default ui-corner-all" tabindex="0" style="left: 100%;"></span>
            </div>
            <div class="fsl_handle after">
              <div>
                <span class="handle_tit">From</span>
                <input type="text" name="pfrom" value="" size="5" id="min_p" placeholder="12">
              </div>
              <div>
                <span class="handle_tit">To</span>
                <input type="text" name="pto" value="" size="5" id="max_p" placeholder="1800">
              </div>
            </div>
          </div>
        </div>
        <div class="filter-box filter-box-ul" id="fil-brand">
          <div class="filter-box-head"><span class=""></span>Brand</div>
          <div class="filter-box-content" style="display: block;">
            <ul>
              <li><label><input type="checkbox" value="90" name="brand[]" checked><span></span>Cartier (3)</label></li>
              <li><label><input type="checkbox" value="87" name="brand[]"><span></span>Casio (3)</label></li>
              <li><label><input type="checkbox" value="88" name="brand[]"><span></span>Citizen (5)</label></li>
              <li class="view_items"><a class="view_more">more</a></li>
            </ul>
          </div>
        </div>
        <div class="filter-box filter-box-ul" id="fil-at7">
          <div class="filter-box-head"><span class="close"></span>Case</div>
        </div>
        <div class="filter-box filter-box-ul" id="fil-at8">
          <div class="filter-box-head"><span class="close"></span>Caseback</div>
        </div>

      </div>
    </div>
    <script type="text/javascript">
      tl(function(){
        $('head').append('<link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.min.css"/>')
      });
    </script>



  </div>
  <div class="col-right">
    <div class="demo-heading-3">{$smarty.const.EDIT}</div>



    <div class="edit-filter-widget edit-element-2"{Info::dataClass('.filter-widget')}>
      <div class="edit-head-filter-widget edit-element-2"{Info::dataClass('.head-filter-widget')}>
        <div class="edit-element-1"{Info::dataClass('.head-filter-widget span')}>Refine search</div>
        <div class="edit-mobileCollapse edit-element-1"{Info::dataClass('.mobileCollapse')}>mobileCollapse</div>
      </div>
      <div class="edit-content-filter-widget edit-element-2"{Info::dataClass('.content-filter-widget')}>
        <div class="edit-filter-box edit-element-2"{Info::dataClass('.filter-box')}>
          <div class="edit-filter-box-head edit-element-1"{Info::dataClass('.filter-box-head')}>
            <div class="edit-element-1"{Info::dataClass('.filter-box-head span')}>-</div>
            Keywords
          </div>
          <div class="edit-filter-box-content edit-element-2"{Info::dataClass('.filter-box-content')}>
            <div class="edit-element-1"{Info::dataClass('.filter-box-content input[name="keywords"]')}>
              <input type="text" value="" name="keywords">
            </div>
          </div>

        </div>
        <div class="edit-filter-box edit-element-2"{Info::dataClass('.filter-box')}>
          <div class="edit-filter-box-head edit-element-1"{Info::dataClass('.filter-box-head')}>
            <div class="edit-element-1"{Info::dataClass('.filter-box-head span')}>-</div>
            Price
          </div>
          <div class="edit-filter-box-content edit-element-2"{Info::dataClass('.filter-box-content')}>
            <div class="edit-element-1"{Info::dataClass('.filter-box-content .ui-slider')}>
              <div id="slider-p" class="ui-slider ui-slider-horizontal ui-widget ui-widget-content ui-corner-all">
                <div class="ui-slider-range ui-widget-header ui-corner-all" style="left: 0%; width: 100%;"{Info::dataClass('.filter-box-content .ui-slider .ui-slider-range')}></div>
                <div class="ui-slider-handle ui-state-default ui-corner-all" tabindex="0" style="left: 0%;"{Info::dataClass('.filter-box-content .ui-slider .ui-slider-handle')}></div>
                <div class="ui-slider-handle ui-state-default ui-corner-all" tabindex="0" style="left: 100%;"{Info::dataClass('.filter-box-content .ui-slider .ui-slider-handle')}></div>
              </div>
            </div>
            <div class="edit-fsl_handle edit-element-2"{Info::dataClass('.fsl_handle')}>
              <div class="edit-element-1"{Info::dataClass('.fsl_handle div:nth-child(1)')}>
                <div class="handle_tit edit-element-1"{Info::dataClass('.fsl_handle span ')}>From</div>
                <div class="edit-element-1"{Info::dataClass('.fsl_handle input ')}>
                  <input type="text" name="pfrom" value="" size="5" id="min_p" placeholder="12">
                </div>
              </div>
              <div class="edit-element-1"{Info::dataClass('.fsl_handle div:nth-child(2)')}>
                <div class="handle_tit edit-element-1"{Info::dataClass('.fsl_handle span ')}>To</div>
                <div class="edit-element-1"{Info::dataClass('.fsl_handle input ')}>
                  <input type="text" name="pto" value="" size="5" id="max_p" placeholder="1800">
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="edit-filter-box edit-element-2"{Info::dataClass('.filter-box')}>
          <div class="edit-filter-box-head edit-element-1"{Info::dataClass('.filter-box-head')}>
            <div class="edit-element-1"{Info::dataClass('.filter-box-head span')}>-</div>
            Brand
          </div>
          <div class="edit-filter-box-content edit-element-2"{Info::dataClass('.filter-box-content')}>
            <div class="edit-element-1"{Info::dataClass('.filter-box-content ul')}>
              <div class="edit-element-1"{Info::dataClass('.filter-box-content li')}>
                  <div class="edit-filter-checkbox edit-element-1"{Info::dataClass('.filter-box-content input[type="checkbox"]:checked + span')}>
                    <input type="checkbox" value="90" name="brand[]" checked>
                  </div>
                  Cartier (3)
              </div>
              <div class="edit-element-1"{Info::dataClass('.filter-box-content li')}>
                <div class="edit-filter-checkbox edit-element-1"{Info::dataClass('.filter-box-content input[type="checkbox"]:not(checked) + span')}>
                  <input type="checkbox" value="87" name="brand[]">
                </div>
                Casio (3)
              </div>
              <div class="edit-element-1"{Info::dataClass('.filter-box-content li')}>
                <div class="edit-filter-checkbox edit-element-1"{Info::dataClass('.filter-box-content input[type="checkbox"]:not(checked) + span')}>
                  <input type="checkbox" value="88" name="brand[]">
                </div>
                Citizen (5)
              </div>
              <div class="edit-view_items edit-element-1"{Info::dataClass('.filter-box-content li.view_items')}>
                <a class="edit-view_more edit-element-1"{Info::dataClass('.filter-box-content a.view_more')}>more</a>
              </div>
            </div>
          </div>
        </div>
        <div class="edit-filter-box edit-element-2"{Info::dataClass('.filter-box')}>
          <div class="edit-filter-box-head edit-element-1"{Info::dataClass('.filter-box-head')}>
            <div class="edit-element-1"{Info::dataClass('.filter-box-head span.close')}>+</div>
            Case
          </div>
        </div>
        <div class="edit-filter-box edit-element-2"{Info::dataClass('.filter-box')}>
          <div class="edit-filter-box-head edit-element-1"{Info::dataClass('.filter-box-head')}>
            <div class="edit-element-1"{Info::dataClass('.filter-box-head span.close')}>+</div>
            Caseback
          </div>
        </div>

      </div>
    </div>


  </div>

</div>
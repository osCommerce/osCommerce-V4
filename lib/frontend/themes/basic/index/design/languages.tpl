{use class="frontend\design\Info"}


<div class="frame-content-wrap">

  <div class="col-left">
    <div class="demo-heading-3">{$smarty.const.IMAGE_PREVIEW}</div>

    <div class="languages">
      <div class="current">
        <span><img class="language-icon" src="images/icons/en.svg" width="24" height="16" alt="English" title="English"></span>
      </div>
      <div class="select">
        <a><img class="language-icon" src="images/icons/nl.svg" width="24" height="16"></a>
        <a><img class="language-icon" src="images/icons/de.svg" width="24" height="16"></a>
        <a><img class="language-icon" src="images/icons/fr.svg" width="24" height="16"></a>
      </div>
    </div>


  </div>
  <div class="col-right">
    <div class="demo-heading-3">{$smarty.const.EDIT}</div>


    <div class="edit-languages edit-element-2"{Info::dataClass('.languages')}>
      <div class="current edit-element-1"{Info::dataClass('.languages .current')}>
        <div class="edit-element-1"{Info::dataClass('.languages .current span')}>
          <img class="language-icon" src="images/icons/en.svg" width="24" height="16" alt="English" title="English">
        </div>
      </div>
      <div class="select edit-element-1"{Info::dataClass('.languages .select')}>
        <div class="edit-element-1"{Info::dataClass('.languages .select a')}><img class="language-icon" src="images/icons/nl.svg" width="24" height="16"></div>
        <div class="edit-element-1"{Info::dataClass('.languages .select a')}><img class="language-icon" src="images/icons/de.svg" width="24" height="16"></div>
        <div class="edit-element-1"{Info::dataClass('.languages .select a')}><img class="language-icon" src="images/icons/fr.svg" width="24" height="16"></div>
      </div>
    </div>

  </div>

</div>
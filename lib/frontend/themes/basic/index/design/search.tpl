{use class="frontend\design\Info"}


<div class="frame-content-wrap">

  <div class="col-left">
    <div class="demo-heading-3">{$smarty.const.IMAGE_PREVIEW}</div>


    <div class="search">
      <input type="text" name="" placeholder="{$smarty.const.ENTER_YOUR_KEYWORDS}" value="a" autocomplete="off">
      <button type="submit"></button>

      <div class="suggest" style="display: block;">

        <strong class="items-title">{$smarty.const.BOX_HEADING_MANUFACTURERS}</strong>
        <a class="item">
          <span class="image"><img src="themes/basic/img/na.png" alt=""></span>
          <span class="name"><span class="typed">A</span>rgus</span>
        </a>
        <a class="item">
          <span class="image"><img src="themes/basic/img/na.png" alt=""></span>
          <span class="name">C<span class="typed">a</span>sio</span>
        </a>

        <strong>{$smarty.const.TEXT_INFORMATION}</strong>
        <a class="item">
          <span class="image"></span>
          <span class="name"><span class="typed">A</span>bout us test</span>
        </a>
        <a class="item">
          <span class="image"></span>
          <span class="name">Priv<span class="typed">a</span>cy Policy</span>
        </a>

        <strong class="items-title">{$smarty.const.TABLE_HEADING_PRODUCTS}</strong>
        <a class="item">
          <span class="image"><img src="themes/basic/img/na.png" alt=""></span>
          <span class="name"><span class="typed">A</span>spire Serene White Le<span class="typed">a</span>ther Div<span class="typed">a</span>n Bed Set with Orthop<span class="typed">a</span>edic M<span class="typed">a</span>ttr</span>
        </a>
        <a class="item">
          <span class="image"><img src="themes/basic/img/na.png" alt=""></span>
          <span class="name">b<span class="typed">a</span>ll</span>
        </a>

      </div>
    </div>


  </div>
  <div class="col-right">
    <div class="demo-heading-3">{$smarty.const.EDIT}</div>


    <div class="edit-search edit-element-2"{Info::dataClass('.search')}>
      <div class="edit-element-1"{Info::dataClass('.search input')}><input type="text" name="keywords" placeholder="{$smarty.const.ENTER_YOUR_KEYWORDS}" value="a" autocomplete="off" style="margin: 0"></div>
      <div class="edit-element-1"{Info::dataClass('.search button')}><button type="submit">Search</button></div>
    </div>

    <div class="edit-suggest edit-element-2"{Info::dataClass('.suggest')} style="display: block;">

      <div class="edit-element-2"{Info::dataClass('.suggest strong')}>{$smarty.const.BOX_HEADING_MANUFACTURERS}</div>
      <div class="item edit-element-2"{Info::dataClass('.suggest .item')}>
        <div class="image edit-element-1"{Info::dataClass('.suggest .image')}><img src="themes/basic/img/na.png" alt=""></div>
        <div class="name edit-element-1"{Info::dataClass('.suggest .name')}><div class="typed edit-element-1"{Info::dataClass('.suggest .typed')}>A</div>rgus</div>
      </div>
      <div class="item edit-element-2"{Info::dataClass('.suggest .item')}>
        <div class="image edit-element-1"{Info::dataClass('.suggest .image')}><img src="themes/basic/img/na.png" alt=""></div>
        <div class="name edit-element-1"{Info::dataClass('.suggest .name')}>C<div class="typed edit-element-1"{Info::dataClass('.suggest .typed')}>a</div>sio</div>
      </div>

      <div class="edit-element-2"{Info::dataClass('.suggest strong')}>{$smarty.const.TEXT_INFORMATION}</div>
      <div class="item edit-element-2"{Info::dataClass('.suggest .item')}>
        <div class="image"></div>
        <div class="name edit-element-1"{Info::dataClass('.suggest .name')}><div class="typed edit-element-1"{Info::dataClass('.suggest .typed')}>A</div>bout us test</div>
      </div>
      <div class="item edit-element-2"{Info::dataClass('.suggest .item')}>
        <div class="image1"></div>
        <div class="name edit-element-1"{Info::dataClass('.suggest .name')}>Priv<div class="typed edit-element-1"{Info::dataClass('.suggest .typed')}>a</div>cy Policy</div>
      </div>

      <div class="edit-element-2"{Info::dataClass('.suggest strong')}>{$smarty.const.TABLE_HEADING_PRODUCTS}</div>
      <div class="item edit-element-2"{Info::dataClass('.suggest .item')}>
        <div class="image edit-element-1"{Info::dataClass('.suggest .image')}><img src="themes/basic/img/na.png" alt=""></div>
        <div class="name edit-element-1"{Info::dataClass('.suggest .name')}><div class="typed edit-element-1"{Info::dataClass('.suggest .typed')}>A</div>spire Serene White Le<div class="typed edit-element-1"{Info::dataClass('.suggest .typed')}>a</div>ther Div<div class="typed edit-element-1"{Info::dataClass('.suggest .typed')}>a</div>n Bed Set with Orthop<div class="typed edit-element-1"{Info::dataClass('.suggest .typed')}>a</div>edic M<div class="typed edit-element-1"{Info::dataClass('.suggest .typed')}>a</div>ttr</div>
      </div>
      <div class="item edit-element-2"{Info::dataClass('.suggest .item')}>
        <div class="image edit-element-1"{Info::dataClass('.suggest .image')}><img src="themes/basic/img/na.png" alt=""></div>
        <div class="name edit-element-1"{Info::dataClass('.suggest .name')}>b<div class="typed edit-element-1"{Info::dataClass('.suggest .typed')}>a</div>ll</div>
      </div>

    </div>

  </div>

</div>
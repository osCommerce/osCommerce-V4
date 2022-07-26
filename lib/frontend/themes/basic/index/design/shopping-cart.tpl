{use class="frontend\design\Info"}


<div class="frame-content-wrap">
    <div class="demo-heading-3">{$smarty.const.IMAGE_PREVIEW}</div>


  <div class="cart-listing">
    <div class="headings">
      <div class="remove">{$smarty.const.REMOVE}</div>
      <div class="image">{$smarty.const.PRODUCTS}</div>
      <div class="name"></div>
      <div class="qty">{$smarty.const.QTY}</div>
      <div class="price">{$smarty.const.PRICE}</div>
    </div>

    <div class="item">
      <div class="remove"><a class="remove-btn"></a></div>
      <div class="image"><a><img src="{$app->request->baseUrl}/themes/theme-1/img/na.png" alt=""></a></div>
      <div class="name">
        <a>{$smarty.const.TEXT_PRODUCT} 1</a> <br>
        <div class="in-stock"><span class="in-stock-icon">&nbsp;</span>In stock</div>
        <div class="attributes">
          <div class="">
            <strong>{$smarty.const.TEXT_ATTRIBUTE} 1:</strong>
            <span>{$smarty.const.TEXT_VALUE}</span>
          </div>
          <div class="">
            <strong>{$smarty.const.TEXT_ATTRIBUTE} 2:</strong>
            <span>{$smarty.const.TEXT_VALUE}</span>
          </div>
          <div class="">
            <strong>{$smarty.const.TEXT_ATTRIBUTE} 3:</strong>
            <span>{$smarty.const.TEXT_VALUE}</span>
          </div>
        </div>
      </div>
      <div class="right-area">
        <div class="qty">
          <span class="qty-box"><span class="smaller disabled"></span><input type="text" value="1"><span class="bigger"></span></span>
        </div>
        <div class="price">£134.28</div>
      </div>
    </div>


    <div class="item">
      <div class="remove"><a class="remove-btn"></a></div>
      <div class="image"><a><img src="{$app->request->baseUrl}/themes/theme-1/img/na.png" alt=""></a></div>
      <div class="name">
        <a>{$smarty.const.TEXT_PRODUCT} 2</a> <br>
        <div class="in-stock"><span class="in-stock-icon">&nbsp;</span>In stock</div>
        <div class="attributes">
        </div>
      </div>
      <div class="right-area">
        <div class="qty">
          <span class="qty-box"><span class="smaller disabled"></span><input type="text" value="1"><span class="bigger"></span></span>
        </div>
        <div class="price">£629.99</div>
        <div class="gift-wrap">
          <label>{$smarty.const.BUYING_GIFT} (+£3.00)
            <div class="bootstrap-switch bootstrap-switch-wrapper bootstrap-switch-off bootstrap-switch-animate" style="width: 62px;"><div class="bootstrap-switch-container" style="width: 94px; margin-left: -32px;"><span class="bootstrap-switch-handle-on bootstrap-switch-primary" style="width: 32px;">Yes</span><span class="bootstrap-switch-label" style="width: 30px;">&nbsp;</span><span class="bootstrap-switch-handle-off bootstrap-switch-default" style="width: 32px;">No</span><input type="checkbox" name="gift_wrap[463]" class="check-on-off"></div>
            </div>
          </label>
        </div>
      </div>
    </div>

  </div>


</div>
<div class="frame-content-wrap">
    <div class="demo-heading-3">{$smarty.const.EDIT}</div>


  <div class="edit-cart-listing edit-element-2"{Info::dataClass('.cart-listing')}>
    <div class="edit-headings edit-element-1"{Info::dataClass('.headings')}>
      <div class="remove edit-element-1"{Info::dataClass('.headings .remove')}>{$smarty.const.REMOVE}</div>
      <div class="image edit-element-1"{Info::dataClass('.headings .image')}>{$smarty.const.PRODUCTS}</div>
      <div class="name edit-element-1"{Info::dataClass('.headings .name')}>&nbsp;</div>
      <div class="qty edit-element-1"{Info::dataClass('.headings .qty')}>{$smarty.const.QTY}</div>
      <div class="price edit-element-1"{Info::dataClass('.headings .price')}>{$smarty.const.PRICE}</div>
    </div>

    <div class="item edit-element-2"{Info::dataClass('.cart-listing .item')}>
      <div class="remove edit-element-1"{Info::dataClass('.cart-listing .item .remove')}>
        <div class="remove-btn edit-element-1"{Info::dataClass('.cart-listing .item .remove a')}>&times;</div>
      </div>
      <div class="image edit-element-1"{Info::dataClass('.cart-listing .item .image')}>
        <div class="edit-element-1"{Info::dataClass('.cart-listing .item .image a')}>
          <img src="{$app->request->baseUrl}/themes/theme-1/img/na.png" alt="">
        </div>
      </div>
      <div class="name edit-element-1"{Info::dataClass('.cart-listing .item .name')}>
        <div class="edit-element-1"{Info::dataClass('.cart-listing .item .name a')}>{$smarty.const.TEXT_PRODUCT} 1</div>
        <div class="in-stock"><span class="in-stock-icon">&nbsp;</span>In stock</div>
        <div class="attributes edit-element-1"{Info::dataClass('.cart-listing .item .attributes')}>
          <div class="edit-element-1"{Info::dataClass('.cart-listing .item .attributes > div')}>
            <div class="edit-element-1"{Info::dataClass('.cart-listing .item .attributes strong')}>{$smarty.const.TEXT_ATTRIBUTE} 1:</div>
            <div class="edit-element-1"{Info::dataClass('.cart-listing .item .attributes span')}>{$smarty.const.TEXT_VALUE}</div>
          </div>
          <div class="edit-element-1"{Info::dataClass('.cart-listing .item .attributes > div')}>
            <div class="edit-element-1"{Info::dataClass('.cart-listing .item .attributes strong')}>{$smarty.const.TEXT_ATTRIBUTE} 2:</div>
            <div class="edit-element-1"{Info::dataClass('.cart-listing .item .attributes span')}>{$smarty.const.TEXT_VALUE}</div>
          </div>
          <div class="edit-element-1"{Info::dataClass('.cart-listing .item .attributes > div')}>
            <div class="edit-element-1"{Info::dataClass('.cart-listing .item .attributes strong')}>{$smarty.const.TEXT_ATTRIBUTE} 3:</div>
            <div class="edit-element-1"{Info::dataClass('.cart-listing .item .attributes span')}>{$smarty.const.TEXT_VALUE}</div>
          </div>
        </div>
      </div>
      <div class="right-area edit-element-1"{Info::dataClass('.cart-listing .item .right-area')}>
        <div class="qty edit-element-1"{Info::dataClass('.cart-listing .item .qty')}>
          <span class="qty-box"><span class="smaller disabled"></span><input type="text" value="1"><span class="bigger"></span></span>
        </div>
        <div class="price edit-element-1"{Info::dataClass('.cart-listing .item .price')}>£134.28</div>
      </div>
    </div>


    <div class="item edit-element-2"{Info::dataClass('.cart-listing .item')}>
      <div class="remove edit-element-1"{Info::dataClass('.cart-listing .item .remove')}>
        <div class="remove-btn edit-element-1"{Info::dataClass('.cart-listing .item .remove a')}>&times;</div>
      </div>
      <div class="image edit-element-1"{Info::dataClass('.cart-listing .item .image')}>
        <div class="edit-element-1"{Info::dataClass('.cart-listing .item .image a')}>
          <img src="{$app->request->baseUrl}/themes/theme-1/img/na.png" alt="">
        </div>
      </div>
      <div class="name edit-element-1"{Info::dataClass('.cart-listing .item .name')}>
        <div class="edit-element-1"{Info::dataClass('.cart-listing .item .name a')}>{$smarty.const.TEXT_PRODUCT} 2</div>
        <div class="in-stock"><span class="in-stock-icon">&nbsp;</span>In stock</div>
        <div class="attributes"></div>
      </div>
      <div class="right-area edit-element-1"{Info::dataClass('.cart-listing .item .right-area')}>
        <div class="qty edit-element-1"{Info::dataClass('.cart-listing .item .qty')}>
          <span class="qty-box"><span class="smaller disabled"></span><input type="text" value="1"><span class="bigger"></span></span>
        </div>
        <div class="price edit-element-1"{Info::dataClass('.cart-listing .item .price')}>£134.28</div>
        <div class="gift-wrap edit-element-1"{Info::dataClass('.cart-listing .item .gift-wrap')}>
          <label class="edit-element-1"{Info::dataClass('.cart-listing .item .gift-wrap label')}>{$smarty.const.BUYING_GIFT} (+£3.00)
            <div class="bootstrap-switch bootstrap-switch-wrapper bootstrap-switch-off bootstrap-switch-animate" style="width: 62px;"><div class="bootstrap-switch-container" style="width: 94px; margin-left: -32px;"><span class="bootstrap-switch-handle-on bootstrap-switch-primary" style="width: 32px;">Yes</span><span class="bootstrap-switch-label" style="width: 30px;">&nbsp;</span><span class="bootstrap-switch-handle-off bootstrap-switch-default" style="width: 32px;">No</span><input type="checkbox" name="gift_wrap[463]" class="check-on-off"></div>
            </div>
          </label>
        </div>
      </div>
    </div>

  </div>


</div>
{use class="frontend\design\Info"}



<div class="frame-content-wrap">

  <div class="col-left">
    <div class="demo-heading-3">{$smarty.const.IMAGE_PREVIEW}</div>

    <div class="bestsellers">
      <div class="heading-3">Best Sellers</div>

      <div class="bestsellers-list">
        <div class="item"><span>1</span><a>Product 1</a></div>
        <div class="item"><span>2</span><a>Product 2</a></div>
        <div class="item"><span>3</span><a>Product 3</a></div>
        <div class="item"><span>4</span><a>Product 4</a></div>
        <div class="item"><span>5</span><a>Product 5</a></div>
      </div>
    </div>

  </div>
  <div class="col-right">
    <div class="demo-heading-3">{$smarty.const.EDIT}</div>


    <div class="edit-bestsellers edit-element-2"{Info::dataClass('.bestsellers')}>
      <div class="edit-element-2 heading-3"{Info::dataClass('.bestsellers .heading-3')}>Best Sellers</div>
      <div class="edit-bestsellers-list edit-element-2"{Info::dataClass('.bestsellers-list')}>
        <div class="item edit-element-1"{Info::dataClass('.bestsellers-list .item:first-child')}><div class="edit-element-1"{Info::dataClass('.bestsellers-list .item:first-child span')}>1</div><a  class="edit-element-1"{Info::dataClass('.bestsellers-list .item:first-child a')}>Product 1</a></div>
        <div class="item edit-element-1"{Info::dataClass('.bestsellers-list .item')}><div class="edit-element-1"{Info::dataClass('.bestsellers-list .item span')}>2</div><a  class="edit-element-1"{Info::dataClass('.bestsellers-list .item a')}>Product 2</a></div>
        <div class="item edit-element-1"{Info::dataClass('.bestsellers-list .item')}><div class="edit-element-1"{Info::dataClass('.bestsellers-list .item span')}>3</div><a  class="edit-element-1"{Info::dataClass('.bestsellers-list .item a')}>Product 3</a></div>
        <div class="item edit-element-1"{Info::dataClass('.bestsellers-list .item')}><div class="edit-element-1"{Info::dataClass('.bestsellers-list .item span')}>4</div><a  class="edit-element-1"{Info::dataClass('.bestsellers-list .item a')}>Product 4</a></div>
        <div class="item edit-element-1"{Info::dataClass('.bestsellers-list .item')}><div class="edit-element-1"{Info::dataClass('.bestsellers-list .item span')}>5</div><a  class="edit-element-1"{Info::dataClass('.bestsellers-list .item a')}>Product 5</a></div>
      </div>
    </div>

  </div>

</div>


<div class="frame-content-wrap">

  <div class="col-left">
    <div class="demo-heading-3">{$smarty.const.IMAGE_PREVIEW}</div>

    <div class="reviews">
      <div class="heading-3">Reviews</div>

      <div class="reviews-list">
        <div class="item">
          <div class="review">kusheriges hitse kusheriges hitse kusheriges hitse...</div>
          <div class="name">Lotte Van Dijk <span class="rating-3"></span></div>
        </div>
        <div class="item">
          <div class="review">i bought five of these saturday mourning at a church...</div>
          <div class="name">Kate Kelly <span class="rating-4"></span></div>
        </div>
        <div class="item">
          <div class="review">A previous reviewer stated that his 3 main requirements...</div>
          <div class="name">Sara Cox <span class="rating-5"></span></div>
        </div>
      </div>
    </div>

  </div>
  <div class="col-right">
    <div class="demo-heading-3">{$smarty.const.EDIT}</div>

    <div class="edit-reviews edit-element-2"{Info::dataClass('.reviews')}>
      <div class="heading-3 edit-element-2"{Info::dataClass('.reviews .heading-3')}>Reviews</div>

      <div class="edit-reviews-list edit-element-2"{Info::dataClass('.reviews-list')}>
        <div class="item edit-element-1"{Info::dataClass('.reviews-list .item')}>
          <div class="edit-review edit-element-1"{Info::dataClass('.reviews-list .review')}>kusheriges hitse kusheriges hitse kusheriges hitse...</div>
          <div class="edit-name edit-element-1"{Info::dataClass('.reviews-list .name')}>Lotte Van Dijk <div class="rating-3 edit-element-1"{Info::dataClass('.reviews-list span')}></div></div>
        </div>
        <div class="item edit-element-1"{Info::dataClass('.reviews-list .item')}>
          <div class="edit-review edit-element-1"{Info::dataClass('.reviews-list .review')}>i bought five of these saturday mourning at a church...</div>
          <div class="edit-name edit-element-1"{Info::dataClass('.reviews-list .name')}>Kate Kelly <div class="rating-4 edit-element-1"{Info::dataClass('.reviews-list span')}></div></div>
        </div>
        <div class="item edit-element-1"{Info::dataClass('.reviews-list .item')}>
          <div class="edit-review edit-element-1"{Info::dataClass('.reviews-list .review')}>A previous reviewer stated that his 3 main requirements...</div>
          <div class="edit-name edit-element-1"{Info::dataClass('.reviews-list .name')}>Sara Cox <div class="rating-5 edit-element-1"{Info::dataClass('.reviews-list span')}></div></div>
        </div>
      </div>
    </div>


  </div>

</div>


<div class="frame-content-wrap">

  <div class="col-left">
    <div class="demo-heading-3">{$smarty.const.IMAGE_PREVIEW}</div>

    <div class="cart-box">
      <a>
        <span style="overflow: visible">
          <strong>{$smarty.const.TEXT_HEADING_SHOPPING_CART}</strong>
        </span>
      </a>
      <div class="cart-content">
        <a class="item">
          <span class="image"><img src="{$app->request->baseUrl}/themes/theme-1/img/na.png" alt=""></span>

          <span class="name"><span class="qty">1</span>Product 1</span>
          <span class="price">£46.98</span>
        </a>
        <a class="item">
          <span class="image"><img src="{$app->request->baseUrl}/themes/theme-1/img/na.png" alt=""></span>
          <span class="name"><span class="qty">1</span>Product 2</span>
          <span class="price">£58.21</span>
        </a>
        <div class="cart-total">{$smarty.const.SUB_TOTAL}: £105.19</div>
        <div class="buttons">
          <div class="left-buttons"><a class="btn">{$smarty.const.TEXT_HEADING_SHOPPING_CART}</a></div>
          <div class="right-buttons"><a class="btn">{$smarty.const.HEADER_TITLE_CHECKOUT}</a></div>
        </div>
      </div>
    </div>

  </div>
  <div class="col-right">
    <div class="demo-heading-3">{$smarty.const.EDIT}</div>


    <div class="edit-cart-box edit-element-2"{Info::dataClass('.cart-box')}>
      <a{Info::dataClass('.cart-box > a')} class="edit-cart-link edit-element-2">
        <span style="overflow: visible">
          <strong{Info::dataClass('.cart-box > a > span > strong')} class=" edit-element-1">{$smarty.const.TEXT_HEADING_SHOPPING_CART}</strong>
        </span>
      </a>
      <div class="edit-cart-content edit-element-2"{Info::dataClass('.cart-content')}>
        <a class="item edit-element-1"{Info::dataClass('.cart-content a.item')}>
          <div class="edit-image edit-element-1"{Info::dataClass('.cart-content .image')}><img src="{$app->request->baseUrl}/themes/theme-1/img/na.png" alt=""></div>

          <div class="edit-name edit-element-1"{Info::dataClass('.cart-content .name')}><span class="qty">1</span>Product 1</div>
          <div class="edit-price edit-element-1"{Info::dataClass('.cart-content .price')}>£46.98</div>
        </a>
        <a class="item edit-element-1"{Info::dataClass('.cart-content a.item')}>
          <div class="edit-image edit-element-1"{Info::dataClass('.cart-content .image')}><img src="{$app->request->baseUrl}/themes/theme-1/img/na.png" alt=""></div>
          <div class="edit-name edit-element-1"{Info::dataClass('.cart-content .name')}><span class="qty">1</span>Product 2</div>
          <div class="edit-price edit-element-1"{Info::dataClass('.cart-content .price')}>£58.21</div>
        </a>
        <div class="edit-cart-total edit-element-1"{Info::dataClass('.cart-content .cart-total')}>{$smarty.const.SUB_TOTAL}: £105.19</div>
        <div class="buttons edit-element-1"{Info::dataClass('.cart-content .buttons')}>
          <div class="left-buttons"><a class="btn">{$smarty.const.TEXT_HEADING_SHOPPING_CART}</a></div>
          <div class="right-buttons"><a class="btn">{$smarty.const.HEADER_TITLE_CHECKOUT}</a></div>
        </div>
      </div>
    </div>

  </div>

</div>


<div class="frame-content-wrap" style="min-height: 300px">

  <div class="col-left">
    <div class="demo-heading-3">{$smarty.const.IMAGE_PREVIEW}</div>


    <ul class="account-top" style="float: right">
      <li>
        <a class="my-acc-link"><span class="no-text">{$smarty.const.TEXT_MY_ACCOUNT}</span></a>
        <ul class="account-dropdown account-dropdown-js2{if tep_session_is_registered('customer_id')} logged-ul{/if}">
          {if !tep_session_is_registered('customer_id')}
            <li class="acc-new">
              <div class="heading-2">{$smarty.const.NEW_CUSTOMER}</div>
              <div class="acc-text">
              {$smarty.const.TEXT_BY_CREATING_AN_ACCOUNT}
              </div>

              <div class="acc-top"><a class="btn-1" >{$smarty.const.CONTINUE}</a></div>
              <div class="acc-bottom">{$smarty.const.TEXT_CONTACT_AND_ASK}</div>

            </li>
            <li class="acc-returning">
              <div class="heading-2">{$smarty.const.RETURNING_CUSTOMER}</div>

              <div class="acc-form-item">
                <label>{field_label const="ENTRY_EMAIL_ADDRESS" required_text=""}</label>
                <input type="text" name="email_address" id="email_address" data-required="{$smarty.const.EMAIL_REQUIRED}" data-pattern="email"/>
              </div>
              <div class="acc-form-item">
                <label>{field_label const="PASSWORD" required_text=""}</label>
                <input type="password" name="password"/>
              </div>

              <div class="acc-buttons">
                <a class="f-pass">{$smarty.const.TEXT_PASSWORD_FORGOTTEN_S}</a>
                <button class="btn-1" type="submit">{$smarty.const.SIGN_IN}</button>
              </div>
              <div class="acc-bottom">{$smarty.const.TEXT_ALREADY_HAVE_ACCOUNT}</div>

            </li>
          {else}
            <li class="logged-in">
              <ul class="acc-link">
                <li><a>{$smarty.const.TEXT_MY_ACCOUNT}</a></li>
                <li><a>{$smarty.const.ENTRY_PASSWORD}</a></li>
                <li><a>{$smarty.const.TEXT_ADDRESS_BOOK}</a></li>
                <li><a>{$smarty.const.HEADER_ORDER_OVERVIEW}</a></li>
                <li><a>{$smarty.const.TEXT_LOGOFF}</a></li>
              </ul>
            </li>
          {/if}
        </ul>
      </li>
    </ul>

    <script type="text/javascript">
      tl(function(){
        $('.account-top > li').hover(function(){
          $('> a', this).addClass('active')
        }, function(){
          $('> a', this).removeClass('active')
        });

        var account_dropdown = $('.account-dropdown-js2');
        var key = true;
        var account_position = function(){
          var _this = $(this);
          if (key){
            key = false;
            setTimeout(function(){
              _this.show();
              key = true;
              if (_this.width() > $(window).width()/2){
                var w = $(window).width() /2 - 20;
                _this.css({
                  width: w + 'px'
                })
              }
              if (_this.offset().left < 0){
                var r = _this.offset().left * 1 - 15;
                _this.css({
                  right: r + 'px'
                })
              }
              _this.hide();
            }, 300)
          }
        };

        account_dropdown.each(account_position);
        $(window).on('resize', function(){
          account_dropdown.each(account_position)
        })
      })
    </script>

  </div>
  <div class="col-right">
    <div class="demo-heading-3">{$smarty.const.EDIT}</div>



    <div>
      <div class="edit-my-acc-link edit-element-1"{Info::dataClass('a.my-acc-link')}>
        <div class="edit-element-1"{Info::dataClass('a.my-acc-link > span')}>{$smarty.const.TEXT_MY_ACCOUNT}</div>
      </div>
    </div>
    <div class="edit-account-dropdown edit-element-2{if tep_session_is_registered('customer_id')} logged-ul{/if}" {Info::dataClass('.account-dropdown')}>
      {if !tep_session_is_registered('customer_id')}
        <div class="edit-acc-new edit-element-2"{Info::dataClass('.account-dropdown .acc-new')}>
          <div class="heading-2 edit-element-1"{Info::dataClass('.account-dropdown .heading-2')}>{$smarty.const.NEW_CUSTOMER}</div>
          <div class="edit-acc-text edit-element-1"{Info::dataClass('.account-dropdown .acc-text')}>
            {$smarty.const.TEXT_BY_CREATING_AN_ACCOUNT}
          </div>

          <div class="edit-acc-top edit-element-1"{Info::dataClass('.account-dropdown .acc-top')}>
            <div class=" edit-element-1" style="display: inline-block"{Info::dataClass('.account-dropdown .acc-top > a')}>
              <a class="btn-1">{$smarty.const.CONTINUE}</a>
            </div>
          </div>
          <div class="edit-acc-bottom edit-element-1"{Info::dataClass('.account-dropdown .acc-bottom')}>{$smarty.const.TEXT_CONTACT_AND_ASK}</div>

        </div>
        <div class="edit-acc-returning edit-element-2"{Info::dataClass('.account-dropdown .acc-returning')}>
          <div class="heading-2 edit-element-1"{Info::dataClass('.account-dropdown .heading-2')}>{$smarty.const.RETURNING_CUSTOMER}</div>

          <div class="edit-acc-form-item edit-element-1"{Info::dataClass('.account-dropdown .acc-form-item')}>
            <label class="edit-element-1"{Info::dataClass('.account-dropdown .acc-form-item label')}>{field_label const="ENTRY_EMAIL_ADDRESS" required_text=""}</label>
            <input type="text" name="email_address" id="email_address" data-required="{$smarty.const.EMAIL_REQUIRED}" data-pattern="email"/>
          </div>
          <div class="edit-acc-form-item edit-element-1"{Info::dataClass('.account-dropdown .acc-form-item')}>
            <label class="edit-element-1"{Info::dataClass('.account-dropdown .acc-form-item label')}>{field_label const="PASSWORD" required_text=""}</label>
            <input type="password" name="password"/>
          </div>

          <div class="edit-acc-buttons edit-element-1"{Info::dataClass('.account-dropdown .acc-buttons')}>
            <a class="edit-f-pass edit-element-1"{Info::dataClass('.account-dropdown .acc-buttons a')}>{$smarty.const.TEXT_PASSWORD_FORGOTTEN_S}</a>
            <div class=" edit-element-1" style="display: inline-block"{Info::dataClass('.account-dropdown button')}>
              <button class="btn-1" type="submit">{$smarty.const.SIGN_IN}</button>
            </div>
          </div>
          <div class="edit-acc-bottom edit-element-1"{Info::dataClass('.account-dropdown .acc-bottom')}>{$smarty.const.TEXT_ALREADY_HAVE_ACCOUNT}</div>

        </div>
      {else}
        <div class="edit-acc-link edit-element-2"{Info::dataClass('.account-dropdown .acc-link')}>
          <div class="edit-element-1"{Info::dataClass('.account-dropdown .acc-link li')}>
            <a class="edit-element-1"{Info::dataClass('.account-dropdown .acc-link li a')}>{$smarty.const.TEXT_MY_ACCOUNT}</a>
          </div>
          <div class="edit-element-1"{Info::dataClass('.account-dropdown .acc-link li')}>
            <a class="edit-element-1"{Info::dataClass('.account-dropdown .acc-link li a')}>{$smarty.const.ENTRY_PASSWORD}</a>
          </div>
          <div class="edit-element-1"{Info::dataClass('.account-dropdown .acc-link li')}>
            <a class="edit-element-1"{Info::dataClass('.account-dropdown .acc-link li a')}>{$smarty.const.TEXT_ADDRESS_BOOK}</a>
          </div>
          <div class="edit-element-1"{Info::dataClass('.account-dropdown .acc-link li')}>
            <a class="edit-element-1"{Info::dataClass('.account-dropdown .acc-link li a')}>{$smarty.const.HEADER_ORDER_OVERVIEW}</a>
          </div>
          <div class="edit-element-1"{Info::dataClass('.account-dropdown .acc-link li')}>
            <a class="edit-element-1"{Info::dataClass('.account-dropdown .acc-link li a')}>{$smarty.const.TEXT_LOGOFF}</a>
          </div>
        </div>
      {/if}
    </div>



  </div>

</div>


<div class="frame-content-wrap">

  <div class="col-left">
    <div class="demo-heading-3">{$smarty.const.IMAGE_PREVIEW}</div>


    <ul class="account-top" style="float: right">
      <li>
        <a class="my-acc-link"><span class="no-text">{$smarty.const.TEXT_MY_ACCOUNT}</span></a>
        <ul class="account-dropdown account-dropdown-js2 logged-ul">

            <li class="logged-in">
              <ul class="acc-link">
                <li><a>{$smarty.const.TEXT_MY_ACCOUNT}</a></li>
                <li><a>{$smarty.const.ENTRY_PASSWORD}</a></li>
                <li><a>{$smarty.const.TEXT_ADDRESS_BOOK}</a></li>
                <li><a>{$smarty.const.HEADER_ORDER_OVERVIEW}</a></li>
                <li><a>{$smarty.const.TEXT_LOGOFF}</a></li>
              </ul>
            </li>
        </ul>
      </li>
    </ul>


  </div>
  <div class="col-right">
    <div class="demo-heading-3">{$smarty.const.EDIT}</div>


    <div>
      <div class="edit-my-acc-link edit-element-1"{Info::dataClass('a.my-acc-link')}>
        <div class="edit-element-1"{Info::dataClass('a.my-acc-link > span')}>{$smarty.const.TEXT_MY_ACCOUNT}</div>
      </div>
    </div>
    <div class="edit-account-dropdown edit-element-2 logged-ul" style="display: block"{Info::dataClass('.account-dropdown')}>

        <div class="edit-acc-link edit-element-2"{Info::dataClass('.account-dropdown .acc-link')}>
          <div class="edit-element-1"{Info::dataClass('.account-dropdown .acc-link li')}>
            <div class="edit-element-1"{Info::dataClass('.account-dropdown .acc-link li a')}>{$smarty.const.TEXT_MY_ACCOUNT}</div>
          </div>
          <div class="edit-element-1"{Info::dataClass('.account-dropdown .acc-link li')}>
            <div class="edit-element-1"{Info::dataClass('.account-dropdown .acc-link li a')}>{$smarty.const.ENTRY_PASSWORD}</div>
          </div>
          <div class="edit-element-1"{Info::dataClass('.account-dropdown .acc-link li')}>
            <div class="edit-element-1"{Info::dataClass('.account-dropdown .acc-link li a')}>{$smarty.const.TEXT_ADDRESS_BOOK}</div>
          </div>
          <div class="edit-element-1"{Info::dataClass('.account-dropdown .acc-link li')}>
            <div class="edit-element-1"{Info::dataClass('.account-dropdown .acc-link li a')}>{$smarty.const.HEADER_ORDER_OVERVIEW}</div>
          </div>
          <div class="edit-element-1"{Info::dataClass('.account-dropdown .acc-link li')}>
            <div class="edit-element-1"{Info::dataClass('.account-dropdown .acc-link li a')}>{$smarty.const.TEXT_LOGOFF}</div>
          </div>
        </div>

    </div>



  </div>

</div>


<div class="frame-content-wrap">

  <div class="col-left">
    <div class="demo-heading-3">{$smarty.const.IMAGE_PREVIEW}</div>




  </div>
  <div class="col-right">
    <div class="demo-heading-3">{$smarty.const.EDIT}</div>




  </div>

</div>
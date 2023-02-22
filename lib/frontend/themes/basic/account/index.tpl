{use class="frontend\design\Info"}{use class = "yii\helpers\Html"}
<div class="account_page">
  <div class="buttons">
    <a class="btn" href="{$app->urlManager->createUrl('account/delete')}" onclick="return confirm('{$smarty.const.TEXT_DELETE_ACCOUNT_CONFIRM}');">{$smarty.const.TEXT_DELETE_ACCOUNT}</a>
    <div class="button1 right-buttons"><a class="btn" href="{$account_links['account_logoff']}">{$smarty.const.TEXT_LOGOFF}</a></div>
  </div>

  <div class="content-item">
    <h1>{$smarty.const.HEADING_TITLE}</h1>
    {$account_links['message']}
    <ul class="topAccount after">
      <li>
        <div class="dateLast">
          <span class="date_title">{$smarty.const.DATE_LAST_ORDERED}</span>
          <span class="date_value">{$topAcc.last_purchased} <br><strong>{if $topAcc.last_purchased_days}({$topAcc.last_purchased_days}){else}{/if}</strong></span>
        </div>
      </li>
      <li>
        <div class="order_count">
          <span class="date_title">{$smarty.const.ORDER_COUNT}</span>
          <span class="date_value">{$topAcc.total_orders}</span>
          <a href="{tep_href_link('account/download-my-orders')}" class="btn" target="_blank">{$smarty.const.TEXT_DOWNLOAD_ORDERS}</a>
        </div>
      </li>
      <li>
        <div class="total_count">
          <span class="date_title">{$smarty.const.TOTAL_ORDERED}</span>
          <span class="date_value">{$topAcc.total_sum}</span>
        </div>
      </li>
      <li>
        <div class="credit_amount_ac">
          <span class="date_title">{$smarty.const.CREDIT_AMOUNT}</span>
          <span class="date_value">{$topAcc.credit_amount}</span>
          {if $topAcc.count_credit_amount|string_format:"%d" > 0}
            <div><a href="{tep_href_link('account/credit-amount','','SSL')}" class="view_history">{$smarty.const.TEXT_VIEW_HISTORY}</a></div>
          {/if}
          <a href="{tep_href_link('account/apply-certificate')}" class="btn popup">{$smarty.const.GIFT_CERTIFICATE}</a>
        </div>
      </li>
      <li>
        <div class="points_earnt">
          <span class="date_title">{$smarty.const.TEXT_POINTS_EARNT}</span>
          <span class="date_value">{$topAcc.customer_points}</span>
          {if $topAcc.has_customer_points_history}
            <div><a href="{tep_href_link('account/credit-amount','type=bonus','SSL')}" class="view_history">{$smarty.const.TEXT_VIEW_HISTORY}</a></div>
          {/if}
        </div>
      </li>
    </ul>
    <ul class="account_info after">
      <li>
        <h4 class="icon-user"><strong>{$smarty.const.TEXT_MY_ACCOUNT}</strong><a class="edit" href="{$account_links['acount_edit']}">{$smarty.const.SMALL_IMAGE_BUTTON_EDIT}</a></h4>
        <div class="account_block">
          <div class="acount_row">
            <div class="ai_title">{output_label const="TEXT_NAME"}</div>
            <div class="ai_value">{$customers.customers_firstname} {$customers.customers_lastname}</div>
          </div>
          {if in_array(ACCOUNT_DOB, ['required', 'required_register', 'visible', 'visible_register'])}
            <div class="acount_row">
              <div class="ai_title">{output_label const="ENTRY_DATE_OF_BIRTH"}</div>
              <div class="ai_value">{\common\helpers\Date::date_long($customers['customers_dob'])}</div>
            </div>
          {/if}
          <div class="acount_row">
            <div class="ai_title">{output_label const="ENTRY_EMAIL_ADDRESS"}</div>
            <div class="ai_value">{$customers.customers_email_address}</div>
          </div>
          <div class="acount_row">
            <div class="ai_title">{output_label const="ENTRY_TELEPHONE_NUMBER"}</div>
            <div class="ai_value">{$customers.customers_telephone}</div>
          </div>
        </div>
      </li>
      <li>
        {if \common\helpers\Acl::checkExtensionAllowed('Subscribers', 'allowed') && defined('ENABLE_CUSTOMERS_NEWSLETTER') && ENABLE_CUSTOMERS_NEWSLETTER == 'true' }
          <h4 class="icon-mail">{$smarty.const.EMAIL_NOTIFICATIONS_TITLE}</h4>
          {Html::beginForm(['account/switch-newsletter'], 'post', [])}
          <div class="info">
            <span class="info_span">{$smarty.const.TEXT_KEEP_UPTODATE_VIA_EMAIL}</span>
            <input type="checkbox" name="newsletter_general" value="{$customers.customers_id}" onchange="return gdprRegularOffers(this);" class="check-on-off"{if $customers.customers_newsletter == 1} checked{/if}>
            <div class="regular_offers_box"{if !$customers.customers_newsletter} style="display: none;"{/if}>
              <label for="regular_offers">{$smarty.const.RECEIVE_REGULAR_OFFERS_PERIOD}</label>
              {Html::dropDownList('regular_offers', $regular_offers, ['12' => '12 months', '24' => '24 months', '36' => '36 months', '60' => '60 months', '0' => 'indefinitely'], ['id' => "regular_offers", 'onchange' => 'gdprRegularOffersPeriod()'])}
            </div>
          </div>
          {Html::endForm()} 
        {/if}
        <h4 class="icon-password">{$smarty.const.TEXT_MY_PASSWORD} <a class="edit" href="{$account_links['account_password']}">{$smarty.const.SMALL_IMAGE_BUTTON_EDIT}</a></h4>
        <div class="account_block">
          <strong>{output_label const="TEXT_CURRENT_PASSWORD"} *****</strong>
        </div>
      </li>
      <li class="addr_book">
        <h4 class="icon-address">{$smarty.const.TEXT_ADDRESS_BOOK}<a class="edit" href="{$account_links['address_book_edit']}">{$smarty.const.SMALL_IMAGE_BUTTON_EDIT}</a></h4>
        <div class="account_block">
          <div class="acount_row">
            <div class="ai_title">{$smarty.const.TEXT_PRIMARY}</div>
            <div class="ai_value">{$priamry_address}</div>
          </div>
        </div>
        <div class="address_book_center view-all"><a class="btn" href="{$account_links['address_book']}">{$smarty.const.TEXT_VIEW_ALL_ADDRESSES}</a></div>
      </li>
    </ul>
  </div>

  {if \common\helpers\Acl::checkExtensionAllowed('TradeForm') && \common\helpers\Php8::getConst('ENABLE_TRADE_FORM') == 'True'}
    <div class="trade-form-area">{$smarty.const.WOULD_LIKE_TRADE} <a href="{tep_href_link('trade-form')}" class="btn-1">{$smarty.const.TRADE_FORM}</a></div>
  {/if}


  <div class="content-item">
    <h4 class="order-table-title">{$smarty.const.OVERVIEW_TITLE}</h4>
    <div class="contentBoxContents mobile_scroll">
      <table cellspacing="0" cellpadding="0" width="100%" class="orders-table">
        <tr class="headings">
          <th>{$smarty.const.TEXT_ORDER_NUMBER}</th>
          <th>{$smarty.const.TEXT_ORDER_DATE}</th>
          <th>{$smarty.const.TEXT_ORDER_SHIPPED_TO}</th>
          <th>{$smarty.const.TEXT_ORDER_PRODUCTS}</th>
          <th>{$smarty.const.TEXT_ORDER_TOTAL}</th>
          <th>{$smarty.const.TEXT_ORDER_STATUS}</th>
          <th></th>
          <th></th>
        </tr>
        {foreach $account_orders as $ac_orders}
          <tr class="moduleRow item {if $ac_orders.pay_link}moduleRowDue{/if}">
            <td class="orders-id">
              <span class="hidden">{$smarty.const.TEXT_ORDER_NUMBER}</span>
              {$ac_orders.orders_id}
              {if $ac_orders.pay_link}
                <div class="not_fully_paid_td">{$smarty.const.TEXT_NOT_FULLY_PAID}</div>
              {/if}
            </td>
            <td class="date">
              <span class="hidden">{$smarty.const.TEXT_ORDER_DATE}</span>
              {$ac_orders.date}
            </td>
            <td class="name">{$ac_orders.name}</td>
            <td class="products">
              <span class="hidden">{$smarty.const.TEXT_ORDER_PRODUCTS}</span>
              {$ac_orders.products}
            </td>
            <td class="order-total price">{$ac_orders.order_total}</td>
            <td class="status-name">{$ac_orders.orders_status_name}</td>
            <td class="">
              {if $ac_orders.pay_link}
                <a class="btn-1" href="{$ac_orders.pay_link}">{$smarty.const.PAY}</a>
              {/if}
            </td>
            <td class="td-alignright">
              {if $ac_orders.pay_link == ''}
                {if $ac_orders.reorder_link}
                  <a class="view_link" {if $ac_orders.reorder_confirm}data-js-confirm="{$ac_orders.reorder_confirm|escape:'html'}"{/if} href="{$ac_orders.reorder_link}">{$smarty.const.SMALL_IMAGE_BUTTON_REORDER}</a>
                {/if}
              {/if}
              <a class="view_link" href="{$ac_orders.view}">{$smarty.const.SMALL_IMAGE_BUTTON_VIEW}</a>
            </td>
          </tr>
        {/foreach}
      </table>
    </div>
    <div class="address_book_center view-all"><a class="btn" href="{tep_href_link('account/history', '', 'SSL')}">{$smarty.const.OVERVIEW_SHOW_ALL_ORDERS}</a></div>
  </div>



  {if count($subscriptions) > 0}
    <div class="content-item">
      <h4 class="order-table-title">{$smarty.const.BOX_HEADING_SUBSCRIPTIONS}</h4>
      <div class="mobile_scroll">
        <table cellspacing="0" cellpadding="0" width="100%" class="orders-table">
          <tr>
            <th>{$smarty.const.TEXT_ORDER_NUMBER}</th>
            <th>{$smarty.const.TEXT_ORDER_DATE}</th>
            <th>{$smarty.const.TEXT_ORDER_STATUS}</th>
            <th></th>
          </tr>
          {foreach $subscriptions as $ac_subscriptions}
            <tr class="moduleRow">
              <td>{$ac_subscriptions.orders_id}</td>
              <td>{$ac_subscriptions.date}</td>
              <td>{$ac_subscriptions.orders_status_name}</td>
              <td class="td-alignright">
                <a class="view_link" href="{$ac_subscriptions.view}">{$smarty.const.SMALL_IMAGE_BUTTON_VIEW}</a>
              </td>
            </tr>
          {/foreach}
        </table>
      </div>
      <div class="address_book_center view-all"><a class="btn" href="{tep_href_link('account/subscription-history', '', 'SSL')}">{$smarty.const.OVERVIEW_SHOW_ALL_SUBSCRIPTIONS}</a></div>
    </div>
  {/if}



  {if count($quotations) > 0}
    <div class="content-item">
      <h4 class="order-table-title">{$smarty.const.BOX_HEADING_QUOTATIONS}</h4>
      <div class="mobile_scroll">
        <table cellspacing="0" cellpadding="0" width="100%" class="orders-table">
          <tr>
            <th>{$smarty.const.TEXT_ORDER_NUMBER}</th>
            <th>{$smarty.const.TEXT_ORDER_DATE}</th>
            <th>{$smarty.const.TEXT_ORDER_STATUS}</th>
            <th></th>
          </tr>
          {foreach $quotations as $ac_quotations}
            <tr class="moduleRow">
              <td>{$ac_quotations.orders_id}</td>
              <td>{$ac_quotations.date}</td>
              <td>{$ac_quotations.orders_status_name}</td>
              <td class="td-alignright">
                <a class="view_link" href="{$ac_quotations.view}">{$smarty.const.SMALL_IMAGE_BUTTON_VIEW}</a>
              </td>
            </tr>
          {/foreach}
        </table>
      </div>
      <div class="address_book_center view-all"><a class="btn" href="{tep_href_link('account/quotation-history', '', 'SSL')}">{$smarty.const.OVERVIEW_SHOW_ALL_QUOTATIONS}</a></div>
    </div>
  {/if}

  {if count($samples) > 0}
    <div class="content-item">
      <h4 class="order-table-title">{$smarty.const.BOX_HEADING_SAMPLES}</h4>
      <div class="mobile_scroll">
        <table cellspacing="0" cellpadding="0" width="100%" class="orders-table">
          <tr>
            <th>{$smarty.const.TEXT_ORDER_NUMBER}</th>
            <th>{$smarty.const.TEXT_ORDER_DATE}</th>
            <th>{$smarty.const.TEXT_ORDER_STATUS}</th>
            <th></th>
          </tr>
          {foreach $samples as $ac_samples}
            <tr class="moduleRow">
              <td>{$ac_samples.orders_id}</td>
              <td>{$ac_samples.date}</td>
              <td>{$ac_samples.orders_status_name}</td>
              <td class="td-alignright">
                <a class="view_link" href="{$ac_samples.view}">{$smarty.const.SMALL_IMAGE_BUTTON_VIEW}</a>
              </td>
            </tr>
          {/foreach}
        </table>
      </div>
      <div class="address_book_center view-all"><a class="btn" href="{tep_href_link('account/samples-history', '', 'SSL')}">{$smarty.const.OVERVIEW_SHOW_ALL_SAMPLES}</a></div>
    </div>
  {/if}  

  {if count($products_wishlist) > 0}
    <div class="content-item">
      <h4 class="order-table-title order_wishlist">{$smarty.const.BOX_HEADING_CUSTOMER_WISHLIST}</h4>
      {\frontend\design\Info::addBlockToWidgetsList('cart-listing')}
      <div class="w-cart-listing">
        <div class="headings">
          <div class="head remove">{$smarty.const.TEXT_REMOVE_CART}</div>
          <div class="head image">{$smarty.const.PRODUCTS}</div>
          <div class="head name"></div>
          <div class="head price">{$smarty.const.TEXT_PRICE}</div>
          <div class="head qty"></div>
        </div>

        {foreach $products_wishlist as $pr_wishlist}
          <div class="item">
            <div class="remove"><a href="{$pr_wishlist.remove_link}" class="remove-btn">{$smarty.const.REMOVE}</a></div>

            <div class="image">
              {if $pr_wishlist.status}
                <a href="{$pr_wishlist.link}"><img src="{$pr_wishlist.image}" alt="{$pr_wishlist.name|escape:'html'}" title="{$pr_wishlist.name|escape:'html'}"></a>
              {else}
                <img src="{$pr_wishlist.image}" alt="{$pr_wishlist.name|escape:'html'}" title="{$pr_wishlist.name|escape:'html'}">
              {/if}
            </div>

            <div class="name">
              <table class="wrapper"><tr><td>{if $pr_wishlist.status}
                <a href="{$pr_wishlist.link}">{$pr_wishlist.name}</a>
              {else}
                {$pr_wishlist.name}
              {/if}
              </td></tr></table>
              <div class="attributes">
                {foreach $pr_wishlist.attr as $attr}
                  <div class="">
                    <strong>{$attr.products_options_name}:</strong>
                    <span>{$attr.products_options_values_name}</span>
                  </div>
                {/foreach}
              </div>
              {if $pr_wishlist.is_bundle}
                {foreach $pr_wishlist.bundles_info as $bundle_product }
                  <div class="bundle_product">
                    <table class="wrapper"><tr><td>{$bundle_product.x_name}</td></tr></table>
                    {if $bundle_product.with_attr}
                      <div class="attributes">
                        {foreach $bundle_product.attr as $attr}
                          <div class="">
                            <strong>{$attr.products_options_name}:</strong>
                            <span>{$attr.products_options_values_name}</span>
                          </div>
                        {/foreach}
                      </div>
                    {/if}
                  </div>
                {/foreach}
              {/if}
            </div>
            <div class="price">
              {$pr_wishlist.final_price_formatted}
            </div>
            <div class="links">
              {if $pr_wishlist.status}
                {if $pr_wishlist.oos}
                  {$smarty.const.TEXT_PRODUCT_OUT_STOCK}
                {else}
                  <a class="view_link" href="{$pr_wishlist.move_in_cart}">{$smarty.const.BOX_WISHLIST_MOVE_TO_CART}</a>
                {/if}
              {else}
                {$smarty.const.TEXT_PRODUCT_DISABLED}
              {/if}
              <a class="view_link popup" href="{tep_href_link('reviews/write', 'products_id='|cat:$pr_wishlist.id)}">{$smarty.const.IMAGE_BUTTON_WRITE_REVIEW}</a>
            </div>
          </div>
        {/foreach}
      </div>
      <div class="address_book_center view-all"><a class="btn" href="{tep_href_link('account/wishlist', '', 'SSL')}">{$smarty.const.BOX_INFORMATION_ALLPRODS}</a></div>
    </div>
  {/if}
  
  {* Include data Bonus Program*}
  {include file='./bonus-program.tpl'}

  {if count($account_reviews)>0}
    <div class="content-item">
      <h4 class="order-table-title order_review_title">{$smarty.const.OVERVIEW_MY_REVIEW_TITLE}</h4>
      <div class="contentBoxContents">
        {if count($account_reviews)>0}
          <div class="mobile_scroll">
            <table cellspacing="0" cellpadding="0" width="100%" class="orders-table">
              <tr>
                <th>{$smarty.const.TEXT_REVIEW_COLUMN_PRODUCT_NAME}</th>
                <th>{$smarty.const.TEXT_REVIEW_COLUMN_RATED}</th>
                <th>{$smarty.const.TEXT_REVIEW_COLUMN_DATE_ADDED}</th>
                <th>{$smarty.const.TEXT_REVIEW_COLUMN_STATUS}</th>
                <th></th>
              </tr>
              {foreach $account_reviews as $_review}
                <tr class="moduleRow">
                  <td>
                    {if $_review.products_link}
                      <a href="{$_review.products_link}">{$_review.products_name}</a>
                    {else}
                      {$_review.products_name}
                    {/if}
                  </td>
                  <td><span class="rating-{$_review.reviews_rating}"></span></td>
                  <td>{$_review.date_added_str}</td>
                  <td>{$_review.status_name}</td>
                  <td class="td-alignright">
                    {if $_review.status_name == 'Approved'}
                      <a class="view_link" href="{$_review.view}">{$smarty.const.SMALL_IMAGE_BUTTON_VIEW}</a>
                    {/if}
                  </td>
                </tr>
              {/foreach}
            </table>
          </div>
          {if $account_reviews_more_link}
            <div class="view-all badc_bottom"><a href="{$account_reviews_more_link}" class="btn">{$smarty.const.LINK_ACCOUNT_REVIEW_MORE}</a></div>
          {/if}
        {else}
          {$smarty.const.OVERVIEW_MY_REVIEW_NONE}
        {/if}
      </div>
    </div>
  {/if}

</div>


<script type="text/javascript">
function gdprRegularOffers(obj) {
    if ($(obj).prop('checked') == true) {
        $('.regular_offers_box').show();
    } else {
        $('.regular_offers_box').hide();
    }
    return true;
}
function gdprRegularOffersPeriod() {
    var newsletter_general = $('input[name="newsletter_general"]').prop('checked');
    switchStatement(0, newsletter_general);
}
  tl('{Info::themeFile('/js/main.js')}', function(){

    $('a[data-js-confirm]').on('click', function () {
      if ( typeof alertMessage !== 'function' ) return;
      alertMessage('<p>'+$(this).attr('data-js-confirm')+'</p><div><a class="btn" href="'+$(this).attr('href')+'">{$smarty.const.IMAGE_BUTTON_CONTINUE}</a></div>');
      return false;
    });
    $('.popup').popUp({
      box_class: 'popup-write'
    });
    $('.view_history').popUp({
      box_class: 'popup-credit-amount'
    });
  });

  function switchStatement(id, newsletter_general) {
    $.post("account/switch-newsletter", { 
        'id': id, 
        'newsletter_general' : newsletter_general,
        'regular_offers' : $('select[name="regular_offers"]').val(),
        '_csrf' : $('input[name="_csrf"]').val()
        }, function(data, status){
      if (status == "success") {
        $('main').html(data);
      } else {
        alert("Request error.");
      }
    },"html");
  }

  tl('{Info::themeFile('/js/bootstrap-switch.js')}', function(){
    {\frontend\design\Info::addBoxToCss('switch')}
    $(".check-on-off").bootstrapSwitch({
      onSwitchChange: function (element, arguments) {
        switchStatement(element.target.value, arguments);
        if (arguments){
            {if \common\helpers\Acl::checkExtensionAllowed('BonusActions')}
            {Yii::$app->controller->promoActionsObs->renderValidPromoAction('signing_newsletter')}
            {/if}
        }
        return true;
      },
      offText: '{TEXT_NO}',
      onText: '{TEXT_YES}'
    });
  })
</script>

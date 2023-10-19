{use class="Yii"}{use class = "yii\helpers\Html"}
{use class="frontend\design\boxes\Menu"}
{use class="frontend\design\Info"}
<ul class="account-top">
  <li class="account-title">
    <a href="{tep_href_link(FILENAME_ACCOUNT, '', 'SSL')}" class="my-acc-link">
      <span class="no-text">
        {$smarty.const.TEXT_MY_ACCOUNT}
        {if $customerLogged && $settings[0].show_customers_name}<span class="">{sprintf(LOGGED_CUSTOMER_GREETING, $customerData.customers_firstname)}</span>{/if}
      </span>
    </a>
	{if isset($settings[0].link_or_dropdown) && $settings[0].link_or_dropdown}
    <ul class="account-dropdown account-dropdown-js {if $customerLogged} logged-ul{/if}">
      {if !$customerLogged}
        <li class="acc-returning">
          <div class="heading-2">{$smarty.const.RETURNING_CUSTOMER}</div>          
          
          {\frontend\design\boxes\login\Returning::widget(['params' => $params])}
          {\frontend\design\boxes\login\Socials::widget(['params' => $params])}
		  
			<div class="acc-top"><a href="{tep_href_link(FILENAME_CREATE_ACCOUNT, '', 'SSL')}">{$smarty.const.NEW_CUSTOMER}</a></div>
        </li>
      {else}
        <li class="logged-in">

          {if Info::themeSetting('customer_account') == 'new'}
              {Menu::widget(['settings' => [['params' => 'Account box']]])}
          {else}
            <ul class="acc-top-link">
              <li class="acc-top-li"><a class="account-link" href="{tep_href_link(FILENAME_ACCOUNT, '', 'SSL')}">{$smarty.const.TEXT_MY_ACCOUNT}</a></li>
              <li class="acc-top-li"><a class="account-link" href="{tep_href_link(FILENAME_ACCOUNT_PASSWORD, '', 'SSL')}">{$smarty.const.ENTRY_PASSWORD}</a></li>
              <li class="acc-top-li"><a class="account-link" href="{tep_href_link(FILENAME_ADDRESS_BOOK, '', 'SSL')}">{$smarty.const.TEXT_ADDRESS_BOOK}</a></li>
              <li class="acc-top-li"><a class="account-link" href="{tep_href_link('account/history', '', 'SSL')}">{$smarty.const.HEADER_ORDER_OVERVIEW}</a></li>
              {if $isReseller}
              <li class="acc-top-li"><a class="account-link" href="{Yii::$app->urlManager->createUrl('quick-order/')}">{$smarty.const.TEXT_WHOLESALE_ORDER_FORM}</a></li>
              {/if}
                {if \common\helpers\Acl::checkExtensionAllowed('Messages', 'allowed')}
                    {\common\extensions\Messages\Messages::menuLink()}
                {/if}
              <li class="acc-top-li"><a class="account-link" href="{tep_href_link(FILENAME_LOGOFF, '')}">{$smarty.const.TEXT_LOGOFF}</a></li>
            </ul>
          {/if}
        </li>
      {/if}
    </ul>
	{/if}
  </li>
</ul>
<script type="text/javascript">
  tl(function(){
      var focus = false;
{if isset($settings[0].link_or_dropdown) && $settings[0].link_or_dropdown}
      const $box = $('#box-{$id}');
      var account_dropdown = $('.account-dropdown-js', $box);
      $('.my-acc-link', $box).on('click', function(){
          $(this).toggleClass('active');
		  return false;
      });
	  $(window).on('click', function(){
		if(!$('.account-top', $box).is(':hover') && !focus){
			$('.my-acc-link', $box).removeClass('active');
		}
	  })
      $('input', account_dropdown).on('focus', function(){
          focus = true;
      })
      $('input', account_dropdown).on('blur', function(){
          focus = false;
      })

    var key = true;
    var account_position = function(){
      if (key){
        key = false;
        setTimeout(function(){
          account_dropdown.show();
          key = true;
          if (account_dropdown.width() > $(window).width()){
            var w = $(window).width() * 1 - 20;
            account_dropdown.css({
              width: w + 'px'
            })
          }
          if (account_dropdown.offset().left < 0){
            var r = account_dropdown.offset().left * 1 - 10;
            account_dropdown.css({
              right: r + 'px'
            })
          }
          account_dropdown.hide();
        }, 300)
      }
    };
    
    account_position();
    $(window).on('resize', account_position)
{/if}
  })
</script>

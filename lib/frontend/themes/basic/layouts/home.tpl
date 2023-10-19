{$this->beginPage()}<!DOCTYPE html>
{use class="yii\helpers\Html"}
{use class="frontend\design\IncludeTpl"}
{use class="Yii"}
{use class="frontend\design\Block"}
{use class="frontend\design\Css"}
{use class="frontend\design\Info"}
{use class="common\components\google\widgets\GoogleWidget"}
<html lang="{Yii::$app->language}">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />	
	<LINK REL="Shortcut Icon" HREF="{$smarty.const.BASE_URL}images/favicon.ico">
	<base href="{$smarty.const.BASE_URL}">
	{Html::csrfMetaTags()}
	<title>{$this->title}</title>

	{*<link rel="stylesheet" href="{Info::themeFile('/css/basic.css')}"/>
	<link rel="stylesheet" href="{Info::themeFile('/css/style.css')}"/>*}

	{$this->head()}

  <script type="text/javascript">
    var tl_js = [];
    var tl_start = false;
    var tl_include_js = [];
    var tl_include_loaded = [];
    var tl = function(a, b){
      var script = { };
      if (typeof a == 'string' && typeof b == 'function'){
        script = {
          'js': [a],
          'script': b
        }
      } else if (typeof a == 'object' && typeof b == 'function') {
        script = {
          'js': a,
          'script': b
        }
      } else if (typeof a == 'function') {
        script = {
          'script': a
        }
      }
      tl_js.push(script);

      if (tl_start){
        tl_action([script])
      }
    };
  </script>

  <style type="text/css">
    {file_get_contents(Info::themeFile('/css/base.css', 'fs'))}
    {file_get_contents(Info::themeFile('/css/style.css', 'fs'))}
  </style>
  
	{Css::widget()}
</head>

<body class="layout-main {$this->context->id}-{$this->context->action->id} context-{$this->context->id} action-{$this->context->action->id}{if Info::isAdmin()} is-admin{/if}">
{$this->beginBody()}

{if !$app->controller->view->only_content}{Block::widget(['name' => 'header', 'params' => ['type' => 'header']])}{/if}

{*<div class="{if $app->controller->view->page_layout == 'default'}main-width {/if}main-content">{$content}</div>*}
<div class="site_width">
		<div class="log_div">
        {$app->controller->view->messages_login}
            {assign var=re1 value='.{'}
            {assign var=re2 value='}'}
    <div class="tabsLogin">
        <div id="tab1" class="active">            
            {\frontend\design\boxes\login\Returning::widget(['params' => $app->controller->view->page_params])}            
        </div>
        <div id="box-tab2">
            {\frontend\design\boxes\login\Register::widget(['params' => $app->controller->view->page_params, 'id'=> 'tab2'])}
            {*<form action="{tep_href_link(FILENAME_DEFAULT, 'action=process', 'SSL')}" method="post">
            <div class="login_title">{$smarty.const.TEXT_DONT_HAVE_USERNAME}</div>
            <input type="hidden" name="account_login" value="create_account">
            <div class="login_form">
                <label for="firstname">{field_label const="ENTRY_FIRST_NAME" required_text="*"}</label>
                <input type="text" name="firstname" id="firstname" value="{$customers_first_name|escape:'html'}" data-pattern="{$re1}{$smarty.const.ENTRY_FIRST_NAME_MIN_LENGTH}{$re2}" data-required="{sprintf($smarty.const.ENTRY_FIRST_NAME_ERROR, $smarty.const.ENTRY_FIRST_NAME_MIN_LENGTH)}"/>
            </div>
            <div class="login_form">
                <label for="lastname">{field_label const="ENTRY_LAST_NAME" required_text="*"}</label>
                <input type="text" name="lastname" id="lastname" value="{$customers_last_name|escape:'html'}" data-pattern="{$re1}{$smarty.const.ENTRY_LAST_NAME_MIN_LENGTH}{$re2}" data-required="{sprintf($smarty.const.ENTRY_LAST_NAME_ERROR, $smarty.const.ENTRY_LAST_NAME_MIN_LENGTH)}"/>
            </div>
            <div class="login_form">
                <label for="email_address-2">{field_label const="ENTRY_EMAIL_ADDRESS" required_text="*"}</label>
              <input type="email" name="email_address" id="email_address-2" value="{$customers_email_address|escape:'html'}" data-required="{$smarty.const.EMAIL_REQUIRED}" data-pattern="email"/>
            </div>
            <div class="login_form">
                <label for="password" class="password-info">{field_label const="PASSWORD" required_text=""}</label>
                <input type="password" name="password" id="password" data-pattern="{$re1}{$smarty.const.ENTRY_PASSWORD_MIN_LENGTH}{$re2}" data-required="{sprintf($smarty.const.ENTRY_PASSWORD_ERROR, $smarty.const.ENTRY_PASSWORD_MIN_LENGTH)}"/>
            </div>
            <div class="login_form">
                <label for="confirmation">{field_label const="PASSWORD_CONFIRMATION" required_text="*"}</label>
                <input type="password" name="confirmation" id="confirmation" data-required="{$smarty.const.ENTRY_PASSWORD_ERROR_NOT_MATCHING}" data-confirmation="password"/>
            </div>
            <div class="login_btns after">
                <div class="colL">
                    <input type="checkbox" id="terms">
                    <label for="terms">{$smarty.const.TEXT_ACCEPT_TERMS_CONDIONS}</label>
                </div>
                <div class="colR"><button class="btn" type="submit">{$smarty.const.SUBMIT}</button></div>
            </div>
            </form>*}
        </div>
        <div id="tab3">
            <form action="{tep_href_link(FILENAME_DEFAULT, 'action=process', 'SSL')}" method="post">
            <div class="login_title">{$smarty.const.TEXT_FORGOT_PASSWORD}</div>
            <input type="hidden" name="account_login" value="password_forgotten">
            <div class="login_form">
                <label for="email_address-2">{field_label const="ENTRY_EMAIL_ADDRESS" required_text="*"}</label>
              <input type="email" name="email_address" id="email_address-3" value="{$customers_email_address|escape:'html'}" data-required="{$smarty.const.EMAIL_REQUIRED}" data-pattern="email"/>
            </div>
            <div class="login_btns after">
                <div class="colR"><button class="btn" type="submit">{$smarty.const.SUBMIT}</button></div>
            </div>
            </form>
        </div>
        <div id="tab4">
            <form action="{tep_href_link(FILENAME_DEFAULT, 'action=process', 'SSL')}" method="post">
            <div class="login_title">{$smarty.const.TEXT_ENQUIRES}</div>
            <input type="hidden" name="account_login" value="contact">

            <div class="login_form">
                <label for="">{$smarty.const.TEXT_NAME}:</label>
                <input type="text" name="name"/>
            </div>
            <div class="login_form">
                <label for="">{$smarty.const.ENTRY_COMPANY}:</label>
                <input type="text" name="company_name"/>
            </div>
            <div class="login_form">
                <label for="">{$smarty.const.TEXT_EMAIL}:</label>
                <input type="text" name="email"/>
            </div>
            <div class="login_form">
                <label for="">{$smarty.const.ENTRY_TELEPHONE_NUMBER}:</label>
                <input type="text" name="phone"/>
            </div>
            <div class="login_form">
                <label for="enquires_field">{field_label const="TEXT_ENQUIRES" required_text=""}</label>
                <textarea id="enquires_field" name="content"></textarea>
            </div>

            <div class="login_btns after">
                <div class="colR"><button class="btn" type="submit">{$smarty.const.SUBMIT}</button></div>
            </div>
            </form>
        </div>
    </div>
    <ul class="tabsRegister">
            <li><a href="#tab1">{$smarty.const.SIGN_IN}</a></li>
            <li><a href="#box-tab2">{$smarty.const.TEXT_DONT_HAVE_USERNAME}</a></li>
            <li><a href="#tab4">{$smarty.const.TEXT_ENQUIRES}</a></li>
    </ul>
    </div>
	</div>
{if !$app->controller->view->only_content}{Block::widget(['name' => 'footer', 'params' => ['type' => 'footer']])}{/if}

{GoogleWidget::widget()}

<style type="text/css">
  @font-face {
    font-family: 'FontAwesome';
    src: url('{Info::themeFile('/fonts/fontawesome-webfont.eot')}?v=3.2.1');
    src: url('{Info::themeFile('/fonts/fontawesome-webfont.eot')}?#iefix&v=3.2.1') format('embedded-opentype'),
    url('{Info::themeFile('/fonts/fontawesome-webfont.woff')}?v=3.2.1') format('woff'),
    url('{Info::themeFile('/fonts/fontawesome-webfont.ttf')}?v=3.2.1') format('truetype'),
    url('{Info::themeFile('/fonts/fontawesome-webfont.svg')}#fontawesomeregular?v=3.2.1') format('svg');
    font-weight: normal;
    font-style: normal;
  }
  @font-face {
    font-family: 'trueloaded';
    src:  url('{Info::themeFile('/fonts/trueloaded.eot')}?4rk52p');
    src:  url('{Info::themeFile('/fonts/trueloaded.eot')}?4rk52p#iefix') format('embedded-opentype'),
    url('{Info::themeFile('/fonts/trueloaded.ttf')}?4rk52p') format('truetype'),
    url('{Info::themeFile('/fonts/trueloaded.woff')}?4rk52p') format('woff'),
    url('{Info::themeFile('/fonts/trueloaded.svg')}?4rk52p#trueloaded') format('svg');
    font-weight: normal;
    font-style: normal;
  }
{*if Info::isAdmin()*}
	{Info::getStyle(THEME_NAME)}
{*else}
  {if is_file($smarty.const.DIR_FS_CATALOG|cat:'themes/'|cat:THEME_NAME|cat:'/css/custom.css')}
    {file_get_contents($smarty.const.DIR_FS_CATALOG|cat:'themes/'|cat:THEME_NAME|cat:'/css/custom.css')}
  {/if}
{/if*}
</style>
{if Info::isAdmin()}
<link rel="stylesheet" href="{Info::themeFile('/css/admin.css')}"/>
{/if}
<link href='https://fonts.googleapis.com/css?family=Hind:400,700,600,500,300' rel='stylesheet' type='text/css'>
<link href='https://fonts.googleapis.com/css?family=Varela+Round' rel='stylesheet' type='text/css'>
<link href="https://fonts.googleapis.com/css?family=Roboto+Condensed:300,300i,400,400i,700,700i" rel="stylesheet">
<link href="https://fonts.googleapis.com/css?family=Roboto:100,100i,300,300i,400,400i,500,500i,700,700i,900,900i" rel="stylesheet">
<link rel="stylesheet" href="{Info::themeFile('/css/jquery-ui.min.css')}"/>

{*<script type="text/javascript" src="{Info::themeFile('/js/jquery-ui.min.js')}"></script>*}
{*<script type="text/javascript" src="{Info::themeFile('/js/bootstrap-switch.js')}"></script>*}
{*<script type="text/javascript" src="{Info::themeFile('/js/jquery.fancybox.pack.js')}"></script>*}

<script type="text/javascript" src="{Info::themeFile('/js/jquery.min.js')}"></script>
<script type="text/javascript">
  tl(function(){
    $('body').on('reload-frame', function(d, m){
      $(this).html(m);
    });
  });

  var tl_action = function(script){
    tl_start = true;
    var action = function(block){
      var key = true;
      $.each(block.js, function(j, js){
        var include_index = tl_include_js.indexOf(js);
        if (include_index == -1 || tl_include_loaded.indexOf(js) == -1){
          key = false;
        }
      });
      if (key){
        block.script()
      }
      return key
    };
    $.each(script, function(i, block){
      if (!action(block)) {
        $.each(block.js, function (j, js) {
          var include_index = tl_include_js.indexOf(js);
          if (include_index == -1) {
            tl_include_js.push(js);
            include_index = tl_include_js.indexOf(js);
            $.ajax({
              url: js,
              success: function () {
                tl_include_loaded.push(js)
                $(window).trigger('tl_action_' + include_index);
              },
              error: function (a, b, c) {
                console.error('Error: "' + js + '" ' + c);
              }
            });
          }
          $(window).on('tl_action_' + include_index, function () {
            action(block)
          })
        })
      }
    })
  };
  tl_action(tl_js);

</script>
<div class="toTop"></div>
<script type="text/javascript">
  tl(function(){
	$(window).scroll(function() {
      if ($(this).scrollTop()) {
          $('.toTop').fadeIn();
      } else {
          $('.toTop').fadeOut();
      }
    });
    $(".toTop").click(function() {
        $("html, body").animate({
				scrollTop: 0}
				, 500);
     });
	})
   tl('{Info::themeFile('/js/main.js')}', function(){
        $('.popupLink a').popUp();        
        $('.tabsRegister a, .home_password_forgot a').click(function(){
            var href_link = $(this).attr('href');
            $('.tabsLogin > div').hide();
            $(href_link).show().addClass('active');
            return false;
        })
        //$('.login_form input').validate();
        $('.popupTerms').popUp();
   })
</script>
{$this->endBody()}
</body>
</html>
{$this->endPage()}
{use class="frontend\design\Info"}

<div class="tabsLogin">
        <div id="box-tab1" {if $params.active == 'login' || !$params.active }class="active"{/if}>
            {\frontend\design\boxes\login\Returning::widget(['params' => $app->controller->view->page_params, 'id'=> 'tab1'])}            
        </div>
        <div id="box-tab2" {if $params.active == 'registration'}class="active"{/if}>
            {\frontend\design\boxes\login\Register::widget(['params' => $app->controller->view->page_params, 'id'=> 'tab2'])}
        </div>
        <div id="box-tab4" {if $params.active == 'enquire'}class="active"{/if}>
            {\frontend\design\boxes\login\Enquire::widget(['params' => $app->controller->view->page_params, 'id'=> 'tab4'])}            
        </div>
    </div>
    <ul class="tabsRegister">
        <li><a href="#box-tab1">{$smarty.const.SIGN_IN}</a></li>
        <li><a href="#box-tab2">{$smarty.const.TEXT_DONT_HAVE_USERNAME}</a></li>
        <li><a href="#box-tab4">{$smarty.const.TEXT_ENQUIRES}</a></li>
    </ul>
<script type="text/javascript">
   tl('{Info::themeFile('/js/main.js')}', function(){
        $('.popupLink a').popUp();
        $('.tabsLogin > div:not(.active)').hide();
        $('.tabsRegister a').click(function(){
            var href_link = $(this).attr('href');
            $('.tabsLogin > div').hide();
            $(href_link).show().addClass('active');
            return false;
        })
        $('.popupTerms').popUp();
   })
</script>
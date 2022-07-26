{use class="Yii"}
{use class="frontend\design\Info"}
{use class="yii\helpers\Html"}

{\frontend\design\Info::addBoxToCss('info')}
{\frontend\design\Info::addBoxToCss('form')}

<div class="checkout-login-page">
    
    <div id="box-register">  
        {\frontend\design\boxes\login\Register::widget(['params' => $params, 'settings' => $settings, 'id' => 'register'])}
    </div>
    
</div>



<script type="text/javascript">
    
    
    tl([
        '{Info::themeFile('/js/main.js')}',
        '{Info::themeFile('/js/jquery.tabs.js')}',      
    ], function () {
      
      {Info::addBlockToWidgetsList('tabs')}
        $('.checkout-login-page').tlTabs({
            tabContainer: '.login-box',
            tabHeadingContainer: '.login-box-heading'
        });
    


        $('.pop-up-link').popUp();
    })

</script>
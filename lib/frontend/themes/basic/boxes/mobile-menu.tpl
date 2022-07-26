
<div class="menu w-menu-mobile">

    <span class="menu-ico"></span>

    <div class="dropdown">
        <table class="wrapper"><tr><td>
            {$menu_htm}
        </td></tr></table>

        {if $languages|count > 1}
            <div class="menu-languages">
                {foreach $languages as $language}
                    <a {if $language.id == $languages_id}class="active"{/if} href="{$language.link}">
                        <span class="image">{$language.image}</span>
                        <span class="name">{$language.name}</span>
                        <span class="key">{$language.key}</span>
                    </a>
                {/foreach}
            </div>
        {/if}
        {if $currenciesArray|count > 1}
            <div class="menu-languages">
                {foreach $currenciesArray as $currency}
                    <a {if $currency.id == $currency_id}class="active"{/if} href="{$currency.link}">
                        <span class="name">{$currency.title}</span>
                        <span class="key">{$currency.key}</span>
                        <span class="symbol_left">{$currency.symbol_left}</span>
                        <span class="symbol_right">{$currency.symbol_right}</span>
                    </a>
                {/foreach}
            </div>
        {/if}
    </div>
</div>
<script>
    {\frontend\design\Info::addBoxToCss('menu-slider')}
    tl('{\frontend\design\Info::themeFile('/js/menu-slider.js')}', function(){

        var box = $('#box-{$id}');

        $('.menu-ico', box).menuSlider({
            'holder': $('.dropdown', box)
        });


        $('ul', box).prev('a, .no-link').addClass('parent');

        var openClose = function(){
            if ($(this).hasClass('opened')){
                $(this).removeClass('opened');
                $(this).next('ul').slideUp();
            } else {
                $(this).addClass('opened');
                $(this).next('ul').slideDown();
            }
            return false;
        };
        $('ul', box).prev('a, .no-link').off('click', openClose).on('click', openClose);
    })
</script>
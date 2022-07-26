{use class="frontend\design\Block"}
{Block::widget(['name' => $page_name, 'params' => ['type' => 'account', 'params' => $params]])}
<script>
    tl('{frontend\design\Info::themeFile('/js/main.js')}', function(){
        $('.popup-link').popUp()
    })
</script>
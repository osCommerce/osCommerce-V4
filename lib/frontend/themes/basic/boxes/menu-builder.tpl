<div class="menu-style" {$attributes}>
    <div class="burger-icon"></div>
    <div class="menu-content">
    {$menu_htm}
    </div>
</div>

<script>
    (function(){
        const $box = document.getElementById('box-{$id}');
        const $menuStyle = $box.getElementsByClassName('menu-style')[0];
        const settings = JSON.parse('{json_encode($jsSettings['visibility'])}');

        for (let setting in settings) {
            if (setting.match(/^[0-9]+$/)) continue;

            for (let limit in settings[setting]) {
                let sizes = limit.split('w')
                if (!sizes[1]) sizes[1] = 100000;
                if (+sizes[0] < window.innerWidth && window.innerWidth < +sizes[1]){
                    $menuStyle.setAttribute('data-' + setting, settings[setting][limit]);
                }
            }
        }
    })();
</script>
{\frontend\design\Info::addBoxToCss('closable-box')}

<div class="widget box box-no-shadow">
    <div class="widget-header {$settings[0].header_class}">
        <h4>{$title}</h4>
        <div class="toolbar no-padding">
            <div class="btn-group">
                <span class="btn btn-xs widget-collapse"><i class="icon-angle-down"></i></span>
            </div>
        </div>
    </div>
    <div class="widget-content">

        {\frontend\design\Block::widget(['name' => 'block-'|cat:$id, 'params' => ['params' => $params, 'settings' => $settings]])}

    </div>
</div>
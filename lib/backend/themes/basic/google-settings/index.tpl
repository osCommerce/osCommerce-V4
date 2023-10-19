{use class="yii\helpers\Html"}
<div class="page-header">
    <div class="page-title">
        <h3>{$app->controller->view->headingTitle}</h3>
    </div>
</div>
<div class="widget-content ">
    {if {$messages|@count} > 0}
        {foreach $messages as $message}
            <div class="alert {$message['type']} fade in">
                <i data-dismiss="alert" class="icon-remove close"></i>
                <span id="message_plce">{$message['info']}</span>
            </div>
        {/foreach}
    {/if}
    <div class="tabbable tabbable-custom">
        <ul class="nav nav-tabs tab-radius-ul tab-radius-ul-white">
            {if is_array($providers)}
                {if is_array($providers['independed']) && count($providers['independed'])}
                    {foreach $providers['independed'] as $key => $provider}
                        <li {if $provider->getCode() == $providers['selected'] || (!$key && !$providers['selected'])} class="active"{/if} data-bs-toggle="tab" data-bs-target="#{$provider->getCode()}">
                            <a><span>{$provider->getName()}</span></a>
                        </li>
                    {/foreach}
                {/if}
                {if is_array($providers['platformed']) && count($providers['platformed'])}
                    {foreach $providers['platformed'] as $key => $provider}
                        <li {if $provider->getCode() == $providers['selected'] || (!$key && !$providers['selected'] && !$providers['independed'])} class="active"{/if} data-bs-toggle="tab" data-bs-target="#{$provider->getCode()}">
                            <a><span>{$provider->getName()}</span></a>
                        </li>
                    {/foreach}
                {/if}
            {else}
                {* *}
            {/if}
        </ul>
        <div class="tab-content">

            {if is_array($providers)}
                {if is_array($providers['independed']) && count($providers['independed'])}
                    {foreach $providers['independed'] as $key => $provider}
                        <div id="{$provider->getCode()}" class="tab-pane {if $provider->getCode() == $providers['selected'] || (!$key && !$providers['selected'])}active{/if}">
                            {Html::beginForm('google-settings')}
                            {$provider->drawConfigTemplate()}
                            <br/>
                            {Html::submitButton(IMAGE_SAVE, ['class' => 'btn btn-primary'])}
                            {Html::endForm()}
                        </div>
                    {/foreach}
                {/if}
                {if is_array($providers['platformed']) && count($providers['platformed'])}
                    {foreach $providers['platformed'] as $key => $provider}
                        <div id="{$provider->getCode()}" class="tab-pane {if $provider->getCode() == $providers['selected'] || (!$key && !$providers['selected'] && !$providers['independed'])}active{/if}">
                            {if $provider->platforms}
                                <div class="tabbable tabbable-custom">
                                    <ul class="nav nav-tabs tab-radius-ul tab-radius-ul-white">
                                        {foreach $provider->platforms as $pkey => $platform}
                                            {$tab = $provider->getCode()|cat:$platform['id']}
                                            <li {if $tab == $providers['selected'] || (!$pkey && !$providers['selected'])} class="active"{/if} data-bs-toggle="tab" data-bs-target="#{$tab}">
                                                <a><span>{$platform['text']}</span></a>
                                            </li>
                                        {/foreach}
                                    </ul>
                                    <div class="tab-content">
                                        {foreach $provider->platforms as $pkey => $platform}
                                            {$tab = $provider->getCode()|cat:$platform['id']}
                                            <div id="{$tab}" class="tab-pane {if $tab == $providers['selected'] || (!$pkey && !$providers['selected'])}active{/if}">
                                                {Html::beginForm('google-settings')}
                                                {Html::hiddenInput('platform_id', $platform['id'])}
                                                {$provider->drawConfigTemplate($platform['id'])}
                                                <br/>
                                                {Html::submitButton(IMAGE_SAVE, ['class' => 'btn btn-primary'])}
                                                {Html::endForm()}
                                            </div>
                                        {/foreach}
                                    </div>
                                </div>
                            {/if}
                        </div>
                    {/foreach}
                {/if}
            </div>
        {/if}
    </div>
</div>
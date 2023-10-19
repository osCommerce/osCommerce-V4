{use class="common\helpers\Html"}
{use class="yii\helpers\ArrayHelper"}
<div class="">
    <style>
        div.form-control label{ display: list-item; }
        .tab-content .payments-list label { display: list-item;list-style: none; }
    </style>
    <div class="tabbable tabbable-custom tabbable-ep">
        <ul class="nav nav-tabs">
            {foreach $platforms_list as $_platform}
                <li class="{if $_platform['is_default']} active {/if}" data-bs-toggle="tab" data-bs-target="#tab_pp_{$_platform['id']}"><a><span>{$_platform['text']}</span></a></li>
            {/foreach}            
        </ul>
        <div class="tab-content tab-content">
            {foreach $platforms_list as $_platform}
            <div class="tab-pane topTabPane tabbable-custom {if $_platform['is_default']} active {/if}" id="tab_pp_{$_platform['id']}">
                <div class="payments-list">
                {assign var=array value = $payments[$_platform['id']]}
                {if !is_array($array)}{$array = []}{/if}
                {foreach $_platform['modules'] as $class => $module}
                    <label>
                    {Html::checkbox('datasource['|cat:$code|cat:'][payments]['|cat:$_platform['id']|cat:'][]', in_array($class, $array), ['value' => $class])}
                    {$module['title']}
                    </label>
                    {if is_array($module['fields'])}
                        {assign var="attributes" value=[]}
                        {foreach $module['fields'] as $field}
                            {if $field[1] == 'datetime'}
                                {if is_array($field[0])}
                                    {$attributes = array_merge($attributes, array_values($field[0]))}
                                {else}
                                    {$attributes[] = $field[1]}
                                {/if}
                            {/if}
                        {/foreach}
                        {$attributes  = array_combine($attributes, $attributes)}
                        <div style="padding-left: 10px;">
                            <label style="display:inline-block;">
                            Start Date Field
                            {Html::dropDownList('datasource['|cat:$code|cat:'][payments]['|cat:$_platform['id']|cat:']['|cat:$class|cat:'][start_date]', $payments[$_platform['id']][$class]['start_date'], $attributes)}
                            </label>
                            <label style="display:inline-block;">
                            End Date Field
                            {Html::dropDownList('datasource['|cat:$code|cat:'][payments]['|cat:$_platform['id']|cat:']['|cat:$class|cat:'][end_date]', $payments[$_platform['id']][$class]['end_date'], $attributes)}
                            </label>
                        </div>
                    {/if}
                {/foreach}
                </div>
                {assign var=years value = range(date("Y", strtotime("-10 year")), date(Y) )}
                {Html::dropDownList('datasource['|cat:$code|cat:'][payments]['|cat:$_platform['id']|cat:'][begining]', $payments[$_platform['id']]['begining'], array_combine($years, $years))} Begining Year
            </div>
            {/foreach}
        </div>
    </div>    
</div>
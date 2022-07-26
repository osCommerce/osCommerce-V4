{use class="yii\helpers\Html"}
<div class="scroll-table-workaround">
    <div class="w-line-row w-line-row-1">
        <div class="wl-td">
            <label>Server URL:</label> {Html::textInput('datasource['|cat:$code|cat:'][client][location]', $client['location'], ['class' => 'form-control'])}
        </div>
    </div>
    <div class="w-line-row w-line-row-1">
        <div class="wl-td">
            <label>Api User:</label> {Html::textInput('datasource['|cat:$code|cat:'][client][api_user]', $client['api_user'], ['class' => 'form-control'])}
        </div>
    </div>
    <div class="w-line-row w-line-row-1">
        <div class="wl-td">
            <label>Api Key:</label> {Html::textInput('datasource['|cat:$code|cat:'][client][api_key]', $client['api_key'], ['class' => 'form-control'])}
        </div>
    </div>
    <div class="w-line-row w-line-row-1">
        <div class="wl-td">
            <label>Trunkate Categories:</label> {Html::checkbox('datasource['|cat:$code|cat:'][trunkate_categories]', $trunkate_categories)}
        </div>
    </div>
    <div class="w-line-row w-line-row-1">
        <div class="wl-td">
            <label>Trunkate Products:</label> {Html::checkbox('datasource['|cat:$code|cat:'][trunkate_products]', $trunkate_products)}
        </div>
    </div>
    <div class="w-line-row w-line-row-1">
        <div class="wl-td">
            {if empty($media['path'])}
            {assign var=mediaPath value='/media/catalog/category/'}
            {else}
            {assign var=mediaPath value=$media['path']}
            {/if}
            <label>Media Category Folder:</label> {Html::textInput('datasource['|cat:$code|cat:'][media][path]', $mediaPath, ['class' => 'form-control'])}
        </div>
    </div>
    <div class="w-line-row w-line-row-1">
        <div class="wl-td">
            <label>Apply Attributes:</label>
            <div class="atts-box">
            {if is_array($attributes) && count($attributes)}
                {foreach $attributes as $att}
                    <div><div class="f_td">{Html::textInput('datasource['|cat:$code|cat:'][attributes][]', $att, ['class' => 'form-control', 'placeholder'=> 'SetID;attribute_code'])}<div class="del-pt"></div></div></div>
                {/foreach}
            {else}
                <div><div class="f_td">{Html::textInput('datasource['|cat:$code|cat:'][attributes][]', '', ['class' => 'form-control', 'placeholder'=> 'SetID;attribute_code'])}</div></div>
            {/if}
            </div>
            <span class="btn btn-add-more">{$smarty.const.TEXT_AND_MORE}</span>
        </div>
    </div>
    <div class="w-line-row w-line-row-1">
        <div class="wl-td">
            <label>Trunkate Customers:</label> {Html::checkbox('datasource['|cat:$code|cat:'][trunkate_customers]', $trunkate_customers)}
        </div>
    </div>
    <div class="w-line-row w-line-row-1">
        <div class="wl-td">
            <label>Trunkate Orders:</label> {Html::checkbox('datasource['|cat:$code|cat:'][trunkate_orders]', $trunkate_orders)}
        </div>
    </div>
    <div class="w-line-row w-line-row-1">
        <style>
        .f_td{ display: flex; }
        </style>
        <div class="wl-td">
            {assign var=status_grouped value=\common\helpers\Order::getStatusesGrouped()}            
            {assign var=status_grouped_mapped  value=\yii\helpers\ArrayHelper::map($status_grouped, 'id', 'text')}
            <label>Predefine Order Statuses:</label> {tep_draw_pull_down_menu('statuses', $status_grouped, '', ' class="form-control"')}
            <div class="drop_zone" style="display:list-item;">
                 {assign var=predefined_status_ready value="datasource[$code][predefined_status]"}
                  {if is_array($predefined_status) && $predefined_status|count>0}
                    {foreach $predefined_status as $key=>$value}
                        <div><label>{$status_grouped_mapped[$key]}</label><div class="f_td"><input type="text" name="{$predefined_status_ready}[{$key}]" value="{$value}" class="form-control"><div class="del-pt"></div>Please fill the code of status from magento source</div></div>
                    {/foreach}
                {/if}
            </div>
        </div>
    </div>    
</div>
<script>
    $(document).ready(function(){
        $('select[name=statuses]').on('change', function(){
            $('.drop_zone').append('<div><label>' + $('option:selected', this).text()+'</label><div class="f_td"><input type="text" name="{$predefined_status_ready}['+$(this).val()+']" value="" class="form-control"><div class="del-pt"></div>Please fill the code of status from magento source</div></div>');
        });
        $('body').on('click', '.del-pt', function(){
            $(this).parent().parent().remove();
        })
        $('.btn-add-more').click(function(){
            $('.atts-box').append('<div><div class="f_td"><input type="text" name="datasource[{$code}][attributes][]" value="" class="form-control" placeholder="SetID;attribute_code"><div class="del-pt"></div></div></div>');
        })
    })
</script>
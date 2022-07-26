
<div class="integrate-holder">
    <div class="filter-row">
    {foreach $filters_array as $filter}
        {if in_array($filter.name, $added_filter_items)}
            {if $filter.type == 'input'}{/if}
            {if $filter.type == 'boxes'}
                <div class="system-1">
                    <label fpr="fil-{$filter.name}">{$filter.title}</label>
                    <select name="{$filter.name}[]" id="fil-{$filter.name}">
                        <option value=""></option>
                        {foreach $filter.values as $id => $value}
                            <option value="{$value.id|escape:'html'}"{if $value.selected} selected{/if}>{$value.text|escape:'html'} {*({$value.count})*}</option>

                        {/foreach}
                    </select>
                </div>
            {/if}
            {if $filter.type == 'extra'}{/if}
            {if $filter.type == 'slider'}{/if}
        {/if}
    {/foreach}

    <div class="integrate-button">
        <a href="{if $count == 1}{$product_url}{else}{$list_url}{/if}" class="btn-2">{$smarty.const.TEXT_OK}</a>
    </div>
    </div>
    <div class="integrate-price-box">
        <div class="integrate-price-holder">
            {if $min_price}
            <div class="start-at">{$smarty.const.TEXT_FROM}</div>
            <div class="integrate-price">{$min_price}</div>
            {*<div class="per-month">per month</div>*}
            {/if}
        </div>
    </div>
</div>

<script>
    tl(function () {
        $('.integrate-holder select').on('change', function(){
            var arr = [];

            $('.integrate-holder select').each(function(){

                var val = $(this).val();
                if (val) {
                    arr.push({
                        name: $(this).attr('name'),
                        value: val
                    });
                }
            });

            for (let item in arr) {
                if (arr.hasOwnProperty(item) && !arr[item].value) {
                    arr.splice(1, 1);
                    delete arr[item]
                }
            }

            arr.push({
                name: 'id',
                value: '{$id}'
            });
            arr.push({
                name: 'get_json',
                value: '1'
            });

            $.get("{Yii::$app->urlManager->createUrl('get-widget/one')}", arr, function(d){

                $("#box-{$id}").html(d)
            })
        })
    })
</script>
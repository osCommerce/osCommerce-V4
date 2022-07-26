
<div class="filters">

    <script type="text/javascript">
        var propertieList = [];
    </script>

    {foreach $filters_array as $item}
        <div style="display:inline-block; vertical-align: middle;"><label>{$item.title}</label>
            {if $item.id}
                <input type="text" name="pr{$item.id}[]" class="property js-filter_param"/>
                <script>
                    propertieList[{$item.id}] = [];
                </script>
                <script>
                    {foreach $item.values as $value}
                        {if $value.count > 0}
                    propertieList[{$item.id}].push({
                        'id': '{$value.id}',
                        'name': "{$value.text}"
                    });
                        {/if}
                    {/foreach}
                    tl(function () {
                        $("input[name='pr{$item.id}[]']").tokenInput(propertieList[{$item.id}]);
                    });
                </script> 
            {elseif $item.type == 'pulldown'}
                <ul class="token-input-list"><li class="token-input-input-token">
                    {$item.pulldown}
                </li></ul>
            {else}
                <ul class="token-input-list"><li class="token-input-input-token">
                    <input type="text" name="{$item.name}" class="property js-filter_param"/>
                </li></ul>
            {/if}
        </div>
    {/foreach}

    <button class="btn search-all js-search_all_products">Search</button> <span class="clear">Clear</span>


    <script>

        tl(function () {

            var collectFields = function () {
                var _arr = [];
                $.each($('.js-filter_param'), function (i, e) {
                    if (e.name.indexOf('pr') === 0) {
                        var _tmp = [];
                        if ($(e).val().length > 0) {
                            _tmp = $(e).val().split(':-:');
                            $.each(_tmp, function (ii, ee) {
                                _arr.push($(e).attr('name') + '=' + ee);
                            });
                        }
                    } else {
                        _arr.push(e.name + '=' + $(e).val());
                    }
                });

                return _arr.join('&');
            };

            $('body').on('change', 'input[name^=pr].property', function () {
                table.search(collectFields()).draw();
            });

            $('body').on('change', 'select[name=category_id]', function () {
                table.search(collectFields()).draw();
            });

            $('.js-search_all_products').click(function (e) {
                var ev = e || window.event;
                ev.preventDefault();

                table.search(collectFields()).draw();

                return false;
            });

            $('.clear').click(function (e) {
                $.each($('input[name^=pr].property'), function (i, e) {
                    $(e).tokenInput("clear");
                });
                $('input[name="keywords"]').val('');
                $('select[name="category_id"]').val('');
                table.search('').draw();
            })

        });

    </script>
</div>
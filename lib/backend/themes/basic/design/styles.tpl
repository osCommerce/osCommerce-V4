<div class="style-edit-page">
    {include 'menu.tpl'}
    {use class="backend\assets\DesignAsset"}
    {use class="backend\design\SelectStyle"}
    {DesignAsset::register($this)|void}


    <div class="row">
        <div class="col-md-12">


            <h4 class="m-b-4">Change style all the theme</h4>


            <div class="" style="max-width: 900px">
                <table style="width: 100%">

                    {$styles = ['Font' => 'color', 'Background' => 'background-color', 'Border' => 'border-color']}
                    {foreach ['Font' => $fontColors, 'Background' => $backgroundColors, 'Border' => $borderColors] as $title => $colorArray}
                        <tr class="change-styles-row" data-name="{$styles[$title]}">
                            <td>{$title} color</td>
                            <td>from</td>
                            <td style="width: 30%">
                                <div class="dropdown">
                                    <div class="dropdown-selected"></div>
                                    <div class="dropdown-content">
                                        {foreach $colorArray as $color => $items}
                                            <div data-val="{$color}">
                                                <span class="choose-color" style="background: {$color}"></span> {$color} ({$items})
                                            </div>
                                        {/foreach}
                                    </div>
                                </div>
                            </td>
                            <td>to</td>
                            <td style="width: 30%" class="style-to">{SelectStyle::widget(['type' => 'color', 'name' => 'name', 'theme_name' => $theme_name])}</td>
                            <td><span class="btn">Change</span></td>
                        </tr>
                    {/foreach}


                    <tr class="change-styles-row" data-name="font-family">
                        <td>Font family</td>
                        <td>from</td>
                        <td style="width: 30%">
                            <div class="dropdown">
                                <div class="dropdown-selected"></div>
                                <div class="dropdown-content">
                                    {foreach $fontFamily as $font => $items}
                                        <div data-val="{$font}">{$font} ({$items})</div>
                                    {/foreach}
                                </div>
                            </div>
                        </td>
                        <td>to</td>
                        <td style="width: 30%" class="style-to">{SelectStyle::widget(['type' => 'font', 'name' => 'name', 'theme_name' => $theme_name])}</td>
                        <td><span class="btn">Change</span></td>
                    </tr>
                </table>
            </div>


        </div>
    </div>


<script type="text/javascript">
    $(function () {
        $('.dropdown-content > div').on('click', function () {
            const $selected = $(this).closest('.dropdown').find('.dropdown-selected');
            $selected.html($(this).html());
            const val = $(this).data('val');
            $selected.data('val', val);

            const $row = $(this).closest('.change-styles-row');
            $(`.select-style div[data-value=""]`, $row).trigger('click');
            $(`.select-style div[data-value]`, $row).each(function () {
                if ($('.value', this).text().trim() == val) {
                    $(this).trigger('click');
                }
            });
        });

        $('.change-styles-row .btn').on('click', function () {
            const $row = $(this).closest('.change-styles-row');
            const from = $('.dropdown-selected', $row).data('val');
            const to = $('.style-to input[type="hidden"]', $row).val();
            const style = $row.data('name');
            $.get('design/styles-change', { from, to, style, theme_name: '{$theme_name}' }, function (d) {
                const $message = alertMessage(d, 'alert-message');
                setTimeout(() => $message.remove(), 1000)
            })
        });
    });
</script>
</div>
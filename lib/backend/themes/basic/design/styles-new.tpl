{include 'menu.tpl'}
{use class="backend\assets\DesignAsset"}
{DesignAsset::register($this)|void}

<div class="style-edit-page">

    <div class="row">
        <div class="col-md-6">
            <table class="table table-bordered">
                <thead>
                <tr class="">
                    <th class=""></th>
                    <th class="">{$smarty.const.STYLE_NAME}</th>
                    <th class="">{$smarty.const.STYLE_VALUE}</th>
                    <th class="">{$smarty.const.HEADING_TYPE}</th>
                    <th class="">Main styles</th>
                    <th class=""></th>
                    <th class=""></th>
                </tr>
                </thead>

                <tbody class="main-styles"></tbody>

            </table>

            <div class="row">
                <div class="col-12 align-right p-t-2">
                    <span class="btn btn-primary btn-add-style">{$smarty.const.TEXT_ADD_STYLE}</span>
                </div>
            </div>

        </div>
        <div class="col-md-6">


        </div>
    </div>
</div>
<div class="btn-bar btn-bar-edp-page after">
    <div class="btn-left">
        <span class="btn btn-export-style" data-type="font">{$smarty.const.EXPORT_FONTS}</span>
        <span class="btn btn-export-style" data-type="color">{$smarty.const.EXPORT_COLORS}</span>
    </div>
    <div class="btn-right">
        <span class="btn btn-confirm btn-save-styles">{$smarty.const.IMAGE_SAVE}</span>
    </div>
</div>

<script>
    $(function(){
        const mainStyles = JSON.parse('{json_encode($mainStyles)}');
        const $mainStyles = $('.main-styles');

        mainStyles.forEach(function(style){
            $mainStyles.append(styleRow(style))
        });
        $mainStyles.sortable({
            handle: '.sort-handle',
        })

        $('.btn-add-style').on('click', function () {
            $mainStyles.append(styleRow())
        });

        $('.btn-save-styles').on('click', function () {
            const styles = [];

            $('.main-styles tr').each(function(){
                styles.push({
                    name: $('input[name="name"]', this).val(),
                    value: $('*[name="value"]', this).val(),
                    type: $('*[name="type"]', this).val(),
                    main_style: +$('*[name="main_style"]', this).prop('checked'),
                })
            });
            $.post('design/style-main-save', { styles, theme_name: '{$theme_name}' }, function (response) {
                const $popup = alertMessage(response.text, 'alert-message')
                setTimeout(() => $popup.remove(), 2000)
            }, 'json')
        });

        $('.btn-export-style').on('click', function () {
            const type = $(this).data('type');
            const $popUp = alertMessage(`
                    <form>
                        <div class="popup-heading">{$smarty.const.EXPORT_STYLES}</div>
                        <div class="popup-content pop-mess-cont">
                            <div class="row align-items-center m-b-2 block-name">
                                <div class="col-5 align-right">
                                    <label>{$smarty.const.TEXT_NAME}<span class="colon">:</span></label>
                                </div>
                                <div class="col-7">
                                    <input name="name" type="text" class="form-control" autofocus="">
                                </div>
                            </div>
                            <div class="row align-items-center m-b-2 save-to-groups">
                                <div class="col-5 align-right">
                                    <label>{$smarty.const.SAVE_TO_THEME_WIZARD}<span class="colon">:</span></label>
                                </div>
                                <div class="col-7">
                                    <input name="save-to-groups" type="checkbox" class="form-control" checked>
                                </div>
                            </div>
                            <div class="row align-items-center m-b-2 download">
                                <div class="col-5 align-right">
                                    <label>{$smarty.const.DOWNLOAD_ON_MY_COMPUTER}<span class="colon">:</span></label>
                                </div>
                                <div class="col-7">
                                    <input name="download" type="checkbox" class="form-control" checked>
                                </div>
                            </div>
                            <div class="row m-b-2">
                                <div class="col-5 align-right">
                                    <label>{$smarty.const.TEXT_COMMENTS}<span class="colon">:</span></label>
                                </div>
                                <div class="col-7">
                                    <textarea name="comment" class="form-control"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="popup-buttons">
                            <button type="submit" class="btn btn-primary">{$smarty.const.TEXT_EXPORT}</button>
                            <span class="btn btn-cancel">{$smarty.const.IMAGE_CANCEL}</span>
                        </div>
                    </form>
            `);

            const $form = $('form', $popUp);

            $form.on('submit', function (e) {
                e.preventDefault();
                const data = $(this).serializeArray();
                data.push({ name: 'theme_name', value: '{$theme_name}'});
                data.push({ name: 'type', value: type});

                $.post('design/export-styles', data, function (response) {
                    if (response.error){
                        alertMessage(response.error, 'alert-message');
                    }
                    if (response.text){
                        const $message = alertMessage(response.text, 'alert-message');
                        setTimeout(() => $message.remove(), 2000);
                        if ($('input[name="download"]', $form).prop('checked') && response.filename) {

                            const url = new URL(window.entryData.mainUrl + '/design/download-block');
                            url.searchParams.set('filename', response.filename);
                            if ($('input[name="save-to-groups"]', $form).prop('checked') == false) {
                                url.searchParams.set('delete', 1);
                            }

                            window.location = url.toString();
                        }
                    }
                }, 'json')
            })

        })
    })

    function styleRow(data = { }){
        console.log(data);
        const $row = $(`
            <tr class="">
                <td class="sort-handle"></td>
                <td class="">
                    <input type="text" name="name" value="${ data.name || ''}" class="form-control"${ data.value && ' disabled'}/>
                </td>
                <td class="style-value">
                </td>
                <td class="">
                    <select name="type" class="form-control"${ data.value && ' disabled'}>
                        <option value="color"${ data.type == 'color' && ' selected'}>{$smarty.const.TEXT_COLOR_}</option>
                        <option value="font"${ data.type == 'font' && ' selected'}>{$smarty.const.TEXT_FONT}</option>
                    </select>
                </td>
                <td class="main-styles"><input type="checkbox" name="main_style"${ data.main_style && ' checked'} class="form-controll"></td>
                <td class="remove-style"></td>
                <td class="count">${ data.count || 0 }</td>
            </tr>
        `);

        const $name = $('input[name="name"]', $row);
        const $value = $('input[name="value"]', $row);
        const $styleValue = $('.style-value', $row);
        const $type = $('select', $row);

        const $colorPicker = colorPicker();

        const $fontFamily = $(`
                <select name="value" class="form-control">
                    <option value=""></option>
                    {foreach $fontAdded as $item}
                      <option value="{$item}">{$item}</option>
                    {/foreach}
                    <option value="Arial">Arial</option>
                    <option value="Verdana">Verdana</option>
                    <option value="Tahoma">Tahomaa</option>
                    <option value="Times">Times</option>
                    <option value="Times New Roman">Times New Roman</option>
                    <option value="Georgia">Georgia</option>
                    <option value="Trebuchet MS">Trebuchet MS</option>
                    <option value="Sans">Sans</option>
                    <option value="Comic Sans MS">Comic Sans MS</option>
                    <option value="Courier New">Courier New</option>
                    <option value="Garamond">Garamond</option>
                    <option value="Helvetica">Helvetica</option>
                </select>
        `);
        if (data.value) {
            $fontFamily.val(data.value)
        }

        $name.on('keyup', function () {
            let val = $(this).val();
            val = val.replace(/[ ]+/g, '-');
            val = val.replace('_', '-');
            val = val.replace(/[^a-zA-Z0-9\-]/g, '');
            val = val.toLowerCase();
            $(this).val(val)
        });

        $('.remove-style', $row).on('click', function () {
            const name = $name.val();
            if (name) {
                const $preloader = $(`<div class="hided-box-holder"><div class="preloader"></div></div>`)
                $('.style-edit-page').addClass('hided-box').append($preloader);
                $.get('design/styles-data', {
                    action: 'count',
                    theme_name: '{$theme_name}',
                    name
                }, function (response) {
                    $('.style-edit-page').removeClass('hided-box');
                    $preloader.remove();

                    if (response.error) {
                        alertMessage(response.error, 'alert-message');
                        return null
                    }
                    if (response.count && response.count > 0) {
                        alertMessage(response.text, 'alert-message');
                        return null
                    }
                    $row.remove()
                }, 'json')
            } else {
                $row.remove()
            }
        })

        setType();
        $type.on('change', setType)

        function setType(){
            const type = $type.val();
            if (type == 'color') {
                $styleValue.html('').append($colorPicker.create())
            } else {
                $colorPicker.destroy();
                $styleValue.html('').append($fontFamily)
            }
        }

        function colorPicker() {
            const $component = $(`
                    <div class="input-group colorpicker-component">
                        <input type="text" name="value" value="${ data.value || ''}" class="form-control" />
                        <span class="input-group-append"><span class="input-group-text colorpicker-input-addon"><i></i></span></span>
                    </div>
            `);

            return {
                create: function () {
                    $component.colorpicker({ sliders: {
                            saturation: { maxLeft: 200, maxTop: 200 },
                            hue: { maxTop: 200 },
                            alpha: { maxTop: 200 }
                        }});
                    return $component;
                },
                destroy: function () {
                    if ($component.length && $component.hasClass('colorpicker-element')) {
                        $component.colorpicker('destroy')
                    }
                }
            }
        }

        return $row;
    }
</script>
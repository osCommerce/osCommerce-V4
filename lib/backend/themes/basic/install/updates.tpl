<div class="widget box">
    <div class="widget-header">
        <h4><i class="icon-upload"></i><span id="easypopulate_upload_files_title">{$smarty.const.TEXT_SYSTEM_UPDATES}</span> <span id="types"></span> <a class="credit_amount_history" target="_blank" href="https://www.oscommerce.com/wiki/index.php?title=Change_Log">{$smarty.const.TEXT_CHANGELOG_INTRO}</a></h4>
    </div>
    <div class="widget-content" id="updates_box" style="min-height:400px">
        
    </div>
</div>
<script type="text/javascript">
    function showUpdateLog() {
        $.get("{Yii::$app->urlManager->createUrl('install/update-log')}" , function(data, status) {
            if (status == "success") {
                $('#updates_box').html(data);
            }
        },'html');
        return false;
    }

    function setTypes() {
        let div = $('span#types');
        $(div[0].children).each(function (i, e) {
            e.remove();
        });

        let types = ['default', 'success', 'info', 'warning', 'error'];
        // $('#iframe').contents().find('div.ic').each(function (index, element) {
        //     if (typeof element.className != 'undefined') {
        //         let name = element.className.replaceAll('ic ic-', '');
        //         list.push(name);
        //     }
        // });
        // let types = [new Set(list)];

        // $.each(Array.from(new Set(list)), function (i, e) {
        $.each(types, function (i, e) {
            let find = $('#iframe').contents().find('div.ic.ic-'+e)
            if (find.length) {
                let container = $('<div></div>', {
                    'class': 'form-check form-check-inline form-switch install-type-messages',
                }).appendTo(div);
                let label = $('<label></label>', {
                    'for': e + '_' + i,
                    'class': 'form-check-label',
                }).appendTo(container);
                let box = $('<input>', {
                    'class': 'form-check-input',
                    'type': 'checkbox',
                    'value': e,
                    'checked': '',
                    // 'style': 'margin-top: 7px !important;',
                    'id': e + '_' + i,
                    on: {
                        click: function (event) {
                            filtered();
                        }
                    }
                }).appendTo(container);
                label.html(e);
            }
        });

        $($('input.form-check-input')[0]).hide();
        $($('label.form-check-label')[0]).hide();

        if ($('input.form-check-input').length > 1) {
            let max_width = Math.max.apply(Math, $('div.install-type-messages').map(function(){ return $(this).width();}).get());
            $.each($($(div)[0].childNodes), function (i, e) {
                let el = $(e);
                if (el !== undefined && i > 0) {
                    el.width(max_width);
                }
            });
            div.show();
        }
    }

    function filtered() {
        let checked = [];
        $('input:checkbox:checked.form-check-input').each(function (i, e) {
            checked.push('ic-'+$(e).val());
        });
        $('#iframe').contents().find('div.ic').each(function (index, element) {
            let el = $(element);
            if (typeof (el) != 'undefined') {
                let classes = Array.from($(el)[0].classList);
                let matched = classes.filter( el => checked.indexOf( el ) > -1 );
                if (matched.length) {
                    el.show();
                } else {
                    el.hide();
                }
            }
        });
    }

    function runQuery(force) {
        var dst_file_ignore = [];
        var selected_count = 0;
        if (force == 1) {
            $('#iframe').contents().find('input:checkbox:checked.dst_file_ignore').each(function(j, cb) {
                var aaa = $(cb).val();
                if (typeof(aaa) != 'undefined') {
                    selected_count++;
                    dst_file_ignore[selected_count] = aaa;
                }
            });
            $.post("{Yii::$app->urlManager->createUrl('install/save-ignore-list')}", { "dst_file_ignore" : dst_file_ignore } , function(data) {
                $('#updates_box').html('<iframe id="iframe" src="{Yii::$app->urlManager->createUrl('install/update-now')}?force='+force+'" style="width:100%;min-height:400px;border:0px;"></iframe>');
                $('span#types').hide();
             },'json');
        } else {
            $('#updates_box').html('<iframe id="iframe" onload="parent.setTypes()" src="{Yii::$app->urlManager->createUrl('install/update-now')}?force='+force+'" style="width:100%;min-height:400px;border:0px;"></iframe>');
        }
        return false;
    }
    function checkActualStatus() {
        $.get("{Yii::$app->urlManager->createUrl('install/updates')}" , function(data, status) {
            if (status == "success") {
                $('#updates_box').html(data);
            }
        },'html');
    }
    $(document).ready(function(){
        checkActualStatus();
    });

</script>
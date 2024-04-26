{use class="Yii"}
{use class="backend\assets\DesignAsset"}
{DesignAsset::register($this)|void}
{include 'menu.tpl'}
<style type="text/css">
    .choose-visibility select {
        width: 300px;
    }
</style>


<div class="choose-visibility">
    <label>Choose widget or page css class</label>
    <select name="widgets_list" id="widgets_list" class="col-md-12 select2 select2-offscreen">
        {foreach $widgets_list as $widget}
            {if $widget == ''}
                <option value="main">main</option>
            {else}
                <option value="{$widget}">{$widget}</option>
            {/if}
        {/foreach}
        <option value="block_box">Blocks</option>
        <option value="all">All</option>
    </select>

    <label for="" style="margin-left: 50px">Add new widget</label>
    <input type="text" class="form-control add-widget" style="width: 250px; display: inline-block;"/>
    <span class="btn btn-add-widget" style="position: relative; top: -1px">Add</span>
</div>

<div class="theme-stylesheet">
    <div class="row">
        <div class="col-9">
            <textarea name="css" id="css" cols="30" rows="10">{*$css*}</textarea>
            <div id="code" style="border: 1px solid #ccc"></div>
        </div>
        <div class="col-3 add-code">
            <div class="search-style">
                <input type="search" class="form-control" placeholder="Search"/>
            </div>

            {foreach $groupStyles as $groupStyle}
                <div class="group" data-name="{$groupStyle.group_name}">
                    <h4 class="group-name" data-target="group-style-{$groupStyle.group_id}">
                        {$groupStyle.group_name}
                    </h4>
                    <div class="group-styles" id="group-style-{$groupStyle.group_id}">
                        {foreach $mainStyles as $mainStyle}
                            {if $mainStyle.group_id == $groupStyle.group_id}
                                <div class="item" data-name="${$mainStyle.name}">
                                    {if in_array($mainStyle.type, ['color', 'color-var', 'color-opacity'])}
                                        <span class="style-color" style="background: {$mainSubStyles['$'|cat:$mainStyle.name]}" title="{$mainSubStyles['$'|cat:$mainStyle.name]}"></span>
                                    {/if}
                                    <span class="name">${$mainStyle.name}</span> - <span class="value">{$mainStyle.value}</span>
                                </div>
                            {/if}
                        {/foreach}
                    </div>
                </div>
            {/foreach}
        </div>
    </div>


    <link rel="stylesheet" href="{$app->request->baseUrl}/plugins/codemirror/lib/codemirror.css">
    <link rel="stylesheet" href="{$app->request->baseUrl}/plugins/codemirror/addon/hint/show-hint.css">
    <link rel="stylesheet" href="{$app->request->baseUrl}/plugins/codemirror/addon/dialog/dialog.css">
    <script src="{$app->request->baseUrl}/plugins/codemirror/lib/codemirror.js"></script>
    <script src="{$app->request->baseUrl}/plugins/codemirror/addon/hint/show-hint.js"></script>
    <script src="{$app->request->baseUrl}/plugins/codemirror/addon/hint/xml-hint.js"></script>
    <script src="{$app->request->baseUrl}/plugins/codemirror/addon/hint/html-hint.js"></script>
    <script src="{$app->request->baseUrl}/plugins/codemirror/mode/xml/xml.js"></script>
    <script src="{$app->request->baseUrl}/plugins/codemirror/mode/javascript/javascript.js"></script>
    <script src="{$app->request->baseUrl}/plugins/codemirror/mode/css/css.js"></script>
    <script src="{$app->request->baseUrl}/plugins/codemirror/mode/htmlmixed/htmlmixed.js"></script>

    <script src="{$app->request->baseUrl}/plugins/codemirror/addon/dialog/dialog.js"></script>
    <script src="{$app->request->baseUrl}/plugins/codemirror/addon/search/searchcursor.js"></script>
    <script src="{$app->request->baseUrl}/plugins/codemirror/addon/search/search.js"></script>
    <script src="{$app->request->baseUrl}/plugins/codemirror/addon/search/annotatescrollbar.js"></script>
    <script src="{$app->request->baseUrl}/plugins/codemirror/addon/search/matchesonscrollbar.js"></script>
    <script src="{$app->request->baseUrl}/plugins/codemirror/addon/search/jump-to-line.js"></script>
    <style type="text/css">
        .highlight-colors {
            width: 15px;
        }
        .gutter-color {
            width: 10px;
            height: 10px;
            line-height: 10px;
            border: 1px solid var(--border-color-midle);
            position: relative;
            top: 4px;
            left: 3px;
        }
    </style>
    <script type="text/javascript">
        var CodeMirrorEditor;
        $(function () {
            var redo_buttons = $('.redo-buttons');
            var widgetsList = $('#widgets_list');
            var addWidgetInput = $('.add-widget');
            var addWidgetBtm = $('.btn-add-widget');
            const mainSubStyles = JSON.parse('{json_encode($mainSubStyles)}');

            CodeMirrorEditor = CodeMirror(document.getElementById("code"), {
                mode: "text/css",
                extraKeys: {
                    "Ctrl-Space": "autocomplete",
                    "Ctrl-S": cssSve
                },
                lineNumbers: true,
                gutters: ["CodeMirror-linenumbers", "highlight-colors"]
            });
            var htm = $('#css');
            CodeMirrorEditor.setValue(htm.val());
            CodeMirrorEditor.getSearchCursor('gift');
            htm.hide();

            $('.btn-save-css').on('click', cssSve);


            $('.add-code .item').on('click', function () {
                CodeMirrorEditor.replaceSelection($(this).data('name'))
            })


            redo_buttons.on('click', '.btn-undo', function () {
                var scrollInfo = CodeMirrorEditor.getScrollInfo();
                var event = $(this).data('event');
                $(redo_buttons).hide();
                $.get('design/undo', { 'theme_name': '{$theme_name}'}, function () {
                    $.get('design/get-css', { 'theme_name': '{$theme_name}'}, function ($css) {
                        CodeMirrorEditor.setValue($css);
                        CodeMirrorEditor.scrollTo(scrollInfo.left, scrollInfo.top);
                    });
                    $.get('design/redo-buttons', { 'theme_name': '{$theme_name}'}, function (data) {
                        redo_buttons.show();
                        redo_buttons.html(data)
                    });
                })
            });
            redo_buttons.on('click', '.btn-redo', function () {
                var scrollInfo = CodeMirrorEditor.getScrollInfo();
                var event = $(this).data('event');
                $(redo_buttons).hide();
                $.get('design/redo', { 'theme_name': '{$theme_name}', 'steps_id': $(this).data('id')}, function () {
                    $.get('design/get-css', { 'theme_name': '{$theme_name}'}, function ($css) {
                        CodeMirrorEditor.setValue($css);
                        CodeMirrorEditor.scrollTo(scrollInfo.left, scrollInfo.top);
                    });
                    $.get('design/redo-buttons', { 'theme_name': '{$theme_name}'}, function (data) {
                        redo_buttons.show();
                        redo_buttons.html(data)
                    });
                })
            });
            $.get('design/redo-buttons', { 'theme_name': '{$theme_name}'}, function (data) {
                redo_buttons.html(data)
            });


            widgetsList.on('change', function () {
                var widget = $(this).val();
                $.get('design/get-css', {
                    'theme_name': '{$theme_name}',
                    'widget': widget
                }, function ($css) {
                    CodeMirrorEditor.setValue($css);

                    //console.log(CodeMirrorEditor.lineInfo(4));
                    //CodeMirrorEditor.addLineClass(3, "gutter", "mark");
                })
            }).trigger('change');

            CodeMirrorEditor.on('change', function (e) {
                CodeMirrorEditor.clearGutter("highlight-colors");
                const css = CodeMirrorEditor.getValue()
                css.split("\n").forEach(function (row, i) {
                    const colorVar = row.match(/\$[a-z0-9\-]+/i);
                    if (colorVar && colorVar[0] && mainSubStyles[colorVar[0]] &&
                        colorVar[0].search(/\$font-[0-9]/) === -1 && colorVar[0].search(/\$font-icons/) === -1
                    ) {
                        CodeMirrorEditor.setGutterMarker(i, "highlight-colors", makeMarker(mainSubStyles[colorVar[0]]))
                    }
                });
            })

            function makeMarker(color) {
                var marker = document.createElement("div");
                marker.style.background = color;
                marker.style.color = color;
                marker.className = 'gutter-color';
                marker.innerHTML = "-";
                marker.title = color;
                return marker;
            }

            addWidgetBtm.on('click', addWidget);
            addWidgetInput.on('keydown', function (e) {
                if (e.which == 13) {
                    addWidget();
                }
            });


            function addWidget() {
                var newWidget = addWidgetInput.val();
                if (newWidget.substring(0, 1) !== '.') {
                    newWidget = '.' + newWidget;
                }
                widgetsList.append('<option value="' + newWidget + '">' + newWidget + '</option>');
                widgetsList.val(newWidget).trigger('change');
            }

            function cssSve() {
                if (widgetsList.val() === 'all') {
                    alertMessage('<div style="padding: 30px">All styles can\'t be saved at once</div>');
                    return false
                }

                $('body').append('<div class="css-preloader"><div class="preloader"></div></div>');
                $.post('design/css-save', {
                    theme_name: '{$theme_name}',
                    css: CodeMirrorEditor.getValue(),
                    widget: widgetsList.val()
                }, function () {
                    $('.css-preloader').remove();
                    $.get('design/redo-buttons', { 'theme_name': '{$theme_name}'}, function (data) {
                        redo_buttons.show();
                        redo_buttons.html(data)
                    });
                });
            }
        })
    </script>


    <div class="btn-bar btn-bar-edp-page after">
        <div class="btn-right">
            <span data-href="{$link_save}" class="btn btn-confirm btn-save-css">{$smarty.const.IMAGE_SAVE}</span>
        </div>
    </div>

    <div class="">
        Ctrl-F / Cmd-F : Start searching<br>
        Ctrl-G / Cmd-G : Find next<br>
        Shift-Ctrl-G / Shift-Cmd-G : Find previous<br>
        Shift-Ctrl-F / Cmd-Option-F : Replace<br>
        Shift-Ctrl-R / Shift-Cmd-Option-F : Replace all<br>
        Alt-F : Persistent search (dialog doesn't autoclose, enter to find next, Shift-Enter to find previous)<br>
        Alt-G : Jump to line<br>
    </div>

    <div class="" style="margin: 20px 0; display: none">
        <input type="checkbox" class="edit-css-in-devtools"{if $css_status} checked{/if}/> css from file
    </div>

    <script type="text/javascript">

        (function ($) {
            $(function () {
                $('.edit-css-in-devtools').on('change', function () {
                    $('body').append('<div class="css-preloader"><div class="preloader"></div></div>');
                    if ($(this).is(":checked")) {
                        var status = 1;
                    } else {
                        var status = 0;
                    }
                    $.get('design/css-status', {
                        'status': status,
                        'theme_name': '{$theme_name}'
                    }, function (data) {
                        $('.css-preloader').remove();
                        if (data === 'ok') {
                            alertMessage('<div style="padding: 30px">Changed</div>');
                        } else {
                            alertMessage('<div style="padding: 30px">Error</div>');
                        }
                    })
                });


                const styleStorage = localStorage.getItem('style-groups');
                if (styleStorage) {
                    const styleGroups = JSON.parse(styleStorage);
                    styleGroups.forEach(function (group) {
                        $('#'+group).hide();
                        $(`.group-name[data-target="${ group}"]`).addClass('closed')
                    });
                }
                $('.group-name').on('click', function () {
                    const id = $(this).data('target');
                    $('#'+id).slideToggle();
                    $(this).toggleClass('closed');

                    const styleStorage = localStorage.getItem('style-groups');
                    let styleGroups = [];
                    if (styleStorage) {
                        styleGroups = JSON.parse(styleStorage);
                    }
                    if ($(this).hasClass('closed')) {
                        styleGroups.push(id)
                    } else {
                        styleGroups = styleGroups.filter(i => i != id)
                    }
                    localStorage.setItem('style-groups', JSON.stringify(styleGroups))
                });

                $('.search-style input').on('keyup', function () {
                    const value = $(this).val();
                    $('.group-styles .item').each(function () {
                        if (value) {
                            if ($(this).text().includes(value)) {
                                $(this).show()
                            } else {
                                $(this).hide()
                            }
                            $('.group-styles').show()
                        } else {
                            $(this).show();
                            $('.group-name.closed + .group-styles').hide()
                        }
                    })
                })
            })

        })(jQuery)
    </script>

</div>





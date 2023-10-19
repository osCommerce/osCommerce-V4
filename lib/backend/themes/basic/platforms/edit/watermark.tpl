{use class="common\helpers\Html"}

<div class="widget box box-no-shadow" style="min-height:183px;">
    <div class="widget-header widget-header-theme"><h4>{$smarty.const.TEXT_WATERMARK}</h4></div>
    <div class="widget-content">
        <div class="w-line-row w-line-row-1">
            <div class="wl-td">
                <label>{$smarty.const.ENTRY_STATUS}</label>
                {Html::checkbox('watermark_status', $pInfo->watermark_status, ['value'=>'1', 'class' => 'js_check_watermark_on_off'])}
            </div>
        </div>

        <div class="can_set_watermark">
            <div class="tabbable tabbable-custom">
                <ul class="nav nav-tabs">
                    <li class="active" data-bs-toggle="tab" data-bs-target="#tab_1"><a>{$smarty.const.TEXT_BIG}</a></li>
                    <li data-bs-toggle="tab" data-bs-target="#tab_2"><a>{$smarty.const.TEXT_MEDIUM}</a></li>
                    <li data-bs-toggle="tab" data-bs-target="#tab_3"><a>{$smarty.const.TEXT_SMALL}</a></li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane active topTabPane tabbable-custom" id="tab_1">
                        <div class="wrap_watermark after">
                            <div class="top_left_watermark300{if $pInfo->top_left_watermark300 != ''} upl{/if}">
                                <img width="100" height="100"{if $pInfo->top_left_watermark300 == ''} style="display: none;"{/if} src="{DIR_WS_CATALOG}images/stamp/{$pInfo->top_left_watermark300}">
                                <a href="javascript:void(0)" class="btn-wat-up" onclick="click_watermark('top_left_watermark300')"></a>
                                <div class="upload-remove"{if $pInfo->top_left_watermark300 == ''} style="display: none;"{/if} onclick="delete_watermark('top_left_watermark300')"></div>
                                {Html::hiddenInput('top_left_watermark300', $pInfo->top_left_watermark300)}
                            </div>
                            <div class="top_watermark300{if $pInfo->top_watermark300 != ''} upl{/if}">
                                <img width="100" height="100"{if $pInfo->top_watermark300 == ''} style="display: none;"{/if} src="{DIR_WS_CATALOG}images/stamp/{$pInfo->top_watermark300}">
                                <a href="javascript:void(0)" class="btn-wat-up" onclick="click_watermark('top_watermark300')"></a>
                                <div class="upload-remove"{if $pInfo->top_watermark300 == ''} style="display: none;"{/if} onclick="delete_watermark('top_watermark300')"></div>
                                {Html::hiddenInput('top_watermark300', $pInfo->top_watermark300)}
                            </div>
                            <div class="top_right_watermark300{if $pInfo->top_right_watermark300 != ''} upl{/if}">
                                <img width="100" height="100"{if $pInfo->top_right_watermark300 == ''} style="display: none;"{/if} src="{DIR_WS_CATALOG}images/stamp/{$pInfo->top_right_watermark300}">
                                <a href="javascript:void(0)" class="btn-wat-up" onclick="click_watermark('top_right_watermark300')"></a>
                                <div class="upload-remove"{if $pInfo->top_right_watermark300 == ''} style="display: none;"{/if} onclick="delete_watermark('top_right_watermark300')"></div>
                                {Html::hiddenInput('top_right_watermark300', $pInfo->top_right_watermark300)}
                            </div>
                            <div class="left_watermark300{if $pInfo->left_watermark300 != ''} upl{/if}">
                                <img width="100" height="100"{if $pInfo->left_watermark300 == ''} style="display: none;"{/if} src="{DIR_WS_CATALOG}images/stamp/{$pInfo->left_watermark300}">
                                <a href="javascript:void(0)" class="btn-wat-up" onclick="click_watermark('left_watermark300')"></a>
                                <div class="upload-remove"{if $pInfo->left_watermark300 == ''} style="display: none;"{/if} onclick="delete_watermark('left_watermark300')"></div>
                                {Html::hiddenInput('left_watermark300', $pInfo->left_watermark300)}
                            </div>
                            <div class="watermark300{if $pInfo->watermark300 != ''} upl{/if}">
                                <img width="100" height="100"{if $pInfo->watermark300 == ''} style="display: none;"{/if} src="{DIR_WS_CATALOG}images/stamp/{$pInfo->watermark300}">
                                <a href="javascript:void(0)" class="btn-wat-up" onclick="click_watermark('watermark300')"></a>
                                <div class="upload-remove"{if $pInfo->watermark300 == ''} style="display: none;"{/if} onclick="delete_watermark('watermark300')"></div>
                                {Html::hiddenInput('watermark300', $pInfo->watermark300)}
                            </div>
                            <div class="right_watermark300{if $pInfo->right_watermark300 != ''} upl{/if}">
                                <img width="100" height="100"{if $pInfo->right_watermark300 == ''} style="display: none;"{/if} src="{DIR_WS_CATALOG}images/stamp/{$pInfo->right_watermark300}">
                                <a href="javascript:void(0)" class="btn-wat-up" onclick="click_watermark('right_watermark300')"></a>
                                <div class="upload-remove"{if $pInfo->right_watermark300 == ''} style="display: none;"{/if} onclick="delete_watermark('right_watermark300')"></div>
                                {Html::hiddenInput('right_watermark300', $pInfo->right_watermark300)}
                            </div>
                            <div class="bottom_left_watermark300{if $pInfo->bottom_left_watermark300 != ''} upl{/if}">
                                <img width="100" height="100"{if $pInfo->bottom_left_watermark300 == ''} style="display: none;"{/if} src="{DIR_WS_CATALOG}images/stamp/{$pInfo->bottom_left_watermark300}">
                                <a href="javascript:void(0)" class="btn-wat-up" onclick="click_watermark('bottom_left_watermark300')"></a>
                                <div class="upload-remove"{if $pInfo->bottom_left_watermark300 == ''} style="display: none;"{/if} onclick="delete_watermark('bottom_left_watermark300')"></div>
                                {Html::hiddenInput('bottom_left_watermark300', $pInfo->bottom_left_watermark300)}
                            </div>
                            <div class="bottom_watermark300{if $pInfo->bottom_watermark300 != ''} upl{/if}">
                                <img width="100" height="100"{if $pInfo->bottom_watermark300 == ''} style="display: none;"{/if} src="{DIR_WS_CATALOG}images/stamp/{$pInfo->bottom_watermark300}">
                                <a href="javascript:void(0)" class="btn-wat-up" onclick="click_watermark('bottom_watermark300')"></a>
                                <div class="upload-remove"{if $pInfo->bottom_watermark300 == ''} style="display: none;"{/if} onclick="delete_watermark('bottom_watermark300')"></div>
                                {Html::hiddenInput('bottom_watermark300', $pInfo->bottom_watermark300)}
                            </div>
                            <div class="bottom_right_watermark300{if $pInfo->bottom_right_watermark300 != ''} upl{/if}">
                                <img width="100" height="100"{if $pInfo->bottom_right_watermark300 == ''} style="display: none;"{/if} src="{DIR_WS_CATALOG}images/stamp/{$pInfo->bottom_right_watermark300}">
                                <a href="javascript:void(0)" class="btn-wat-up" onclick="click_watermark('bottom_right_watermark300')"></a>
                                <div class="upload-remove"{if $pInfo->bottom_right_watermark300 == ''} style="display: none;"{/if} onclick="delete_watermark('bottom_right_watermark300')"></div>
                                {Html::hiddenInput('bottom_right_watermark300', $pInfo->bottom_right_watermark300)}
                            </div>
                        </div>
                        <div class="watermark-info">
                            <h4>{$smarty.const.TEXT_MIN_WIDTH} - 300px</h4>
                            <div class="about-image-text">
                                {$smarty.const.IMAGES_BIGGER_THAN|sprintf:300}
                                <ul>
                                    {$smarty.const.IF_YOUD_LIKE_TO_UPLOAD|sprintf:300}
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane topTabPane tabbable-custom" id="tab_2">
                        <div class="wrap_watermark after wrap_watermark170">
                            <div class="top_left_watermark170{if $pInfo->top_left_watermark170 != ''} upl{/if}">
                                <img width="100" height="100"{if $pInfo->top_left_watermark170 == ''} style="display: none;"{/if} src="{DIR_WS_CATALOG}images/stamp/{$pInfo->top_left_watermark170}">
                                <a href="javascript:void(0)" class="btn-wat-up" onclick="click_watermark('top_left_watermark170')"></a>
                                <div class="upload-remove"{if $pInfo->top_left_watermark170 == ''} style="display: none;"{/if} onclick="delete_watermark('top_left_watermark170')"></div>
                                {Html::hiddenInput('top_left_watermark170', $pInfo->top_left_watermark170)}
                            </div>
                            <div class="top_watermark170{if $pInfo->top_watermark170 != ''} upl{/if}">
                                <img width="100" height="100"{if $pInfo->top_watermark170 == ''} style="display: none;"{/if} src="{DIR_WS_CATALOG}images/stamp/{$pInfo->top_watermark170}">
                                <a href="javascript:void(0)" class="btn-wat-up" onclick="click_watermark('top_watermark170')"></a>
                                <div class="upload-remove"{if $pInfo->top_watermark170 == ''} style="display: none;"{/if} onclick="delete_watermark('top_watermark170')"></div>
                                {Html::hiddenInput('top_watermark170', $pInfo->top_watermark170)}
                            </div>
                            <div class="top_right_watermark170{if $pInfo->top_right_watermark170 != ''} upl{/if}">
                                <img width="100" height="100"{if $pInfo->top_right_watermark170 == ''} style="display: none;"{/if} src="{DIR_WS_CATALOG}images/stamp/{$pInfo->top_right_watermark170}">
                                <a href="javascript:void(0)" class="btn-wat-up" onclick="click_watermark('top_right_watermark170')"></a>
                                <div class="upload-remove"{if $pInfo->top_right_watermark170 == ''} style="display: none;"{/if} onclick="delete_watermark('top_right_watermark170')"></div>
                                {Html::hiddenInput('top_right_watermark170', $pInfo->top_right_watermark170)}
                            </div>
                            <div class="left_watermark170{if $pInfo->left_watermark170 != ''} upl{/if}">
                                <img width="100" height="100"{if $pInfo->left_watermark170 == ''} style="display: none;"{/if} src="{DIR_WS_CATALOG}images/stamp/{$pInfo->left_watermark170}">
                                <a href="javascript:void(0)" class="btn-wat-up" onclick="click_watermark('left_watermark170')"></a>
                                <div class="upload-remove"{if $pInfo->left_watermark170 == ''} style="display: none;"{/if} onclick="delete_watermark('left_watermark170')"></div>
                                {Html::hiddenInput('left_watermark170', $pInfo->left_watermark170)}
                            </div>
                            <div class="watermark170{if $pInfo->watermark170 != ''} upl{/if}">
                                <img width="100" height="100"{if $pInfo->watermark170 == ''} style="display: none;"{/if} src="{DIR_WS_CATALOG}images/stamp/{$pInfo->watermark170}">
                                <a href="javascript:void(0)" class="btn-wat-up" onclick="click_watermark('watermark170')"></a>
                                <div class="upload-remove"{if $pInfo->watermark170 == ''} style="display: none;"{/if} onclick="delete_watermark('watermark170')"></div>
                                {Html::hiddenInput('watermark170', $pInfo->watermark170)}
                            </div>
                            <div class="right_watermark170{if $pInfo->right_watermark170 != ''} upl{/if}">
                                <img width="100" height="100"{if $pInfo->right_watermark170 == ''} style="display: none;"{/if} src="{DIR_WS_CATALOG}images/stamp/{$pInfo->right_watermark170}">
                                <a href="javascript:void(0)" class="btn-wat-up" onclick="click_watermark('right_watermark170')"></a>
                                <div class="upload-remove"{if $pInfo->right_watermark170 == ''} style="display: none;"{/if} onclick="delete_watermark('right_watermark170')"></div>
                                {Html::hiddenInput('right_watermark170', $pInfo->right_watermark170)}
                            </div>
                            <div class="bottom_left_watermark170{if $pInfo->bottom_left_watermark170 != ''} upl{/if}">
                                <img width="100" height="100"{if $pInfo->bottom_left_watermark170 == ''} style="display: none;"{/if} src="{DIR_WS_CATALOG}images/stamp/{$pInfo->bottom_left_watermark170}">
                                <a href="javascript:void(0)" class="btn-wat-up" onclick="click_watermark('bottom_left_watermark170')"></a>
                                <div class="upload-remove"{if $pInfo->bottom_left_watermark170 == ''} style="display: none;"{/if} onclick="delete_watermark('bottom_left_watermark170')"></div>
                                {Html::hiddenInput('bottom_left_watermark170', $pInfo->bottom_left_watermark170)}
                            </div>
                            <div class="bottom_watermark170{if $pInfo->bottom_watermark170 != ''} upl{/if}">
                                <img width="100" height="100"{if $pInfo->bottom_watermark170 == ''} style="display: none;"{/if} src="{DIR_WS_CATALOG}images/stamp/{$pInfo->bottom_watermark170}">
                                <a href="javascript:void(0)" class="btn-wat-up" onclick="click_watermark('bottom_watermark170')"></a>
                                <div class="upload-remove"{if $pInfo->bottom_watermark170 == ''} style="display: none;"{/if} onclick="delete_watermark('bottom_watermark170')"></div>
                                {Html::hiddenInput('bottom_watermark170', $pInfo->bottom_watermark170)}
                            </div>
                            <div class="bottom_right_watermark170{if $pInfo->bottom_right_watermark170 != ''} upl{/if}">
                                <img width="100" height="100"{if $pInfo->bottom_right_watermark170 == ''} style="display: none;"{/if} src="{DIR_WS_CATALOG}images/stamp/{$pInfo->bottom_right_watermark170}">
                                <a href="javascript:void(0)" class="btn-wat-up" onclick="click_watermark('bottom_right_watermark170')"></a>
                                <div class="upload-remove"{if $pInfo->bottom_right_watermark170 == ''} style="display: none;"{/if} onclick="delete_watermark('bottom_right_watermark170')"></div>
                                {Html::hiddenInput('bottom_right_watermark170', $pInfo->bottom_right_watermark170)}
                            </div>
                        </div>
                        <div class="watermark-info">
                            <h4>{$smarty.const.TEXT_MIN_WIDTH} - 170px</h4>
                            <div class="about-image-text">
                                {$smarty.const.IMAGES_BIGGER_THAN|sprintf:170}
                                <ul>
                                    {$smarty.const.IF_YOUD_LIKE_TO_UPLOAD|sprintf:170}
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane topTabPane tabbable-custom" id="tab_3">
                        <div class="wrap_watermark after wrap_watermark30">
                            <div class="top_left_watermark30{if $pInfo->top_left_watermark30 != ''} upl{/if}">
                                <img width="100" height="100"{if $pInfo->top_left_watermark30 == ''} style="display: none;"{/if} src="{DIR_WS_CATALOG}images/stamp/{$pInfo->top_left_watermark30}">
                                <a href="javascript:void(0)" class="btn-wat-up" onclick="click_watermark('top_left_watermark30')"></a>
                                <div class="upload-remove"{if $pInfo->top_left_watermark30 == ''} style="display: none;"{/if} onclick="delete_watermark('top_left_watermark30')"></div>
                                {Html::hiddenInput('top_left_watermark30', $pInfo->top_left_watermark30)}
                            </div>
                            <div class="top_watermark30{if $pInfo->top_watermark30 != ''} upl{/if}">
                                <img width="100" height="100"{if $pInfo->top_watermark30 == ''} style="display: none;"{/if} src="{DIR_WS_CATALOG}images/stamp/{$pInfo->top_watermark30}">
                                <a href="javascript:void(0)" class="btn-wat-up" onclick="click_watermark('top_watermark30')"></a>
                                <div class="upload-remove"{if $pInfo->top_watermark30 == ''} style="display: none;"{/if} onclick="delete_watermark('top_watermark30')"></div>
                                {Html::hiddenInput('top_watermark30', $pInfo->top_watermark30)}
                            </div>
                            <div class="top_right_watermark30{if $pInfo->top_right_watermark30 != ''} upl{/if}">
                                <img width="100" height="100"{if $pInfo->top_right_watermark30 == ''} style="display: none;"{/if} src="{DIR_WS_CATALOG}images/stamp/{$pInfo->top_right_watermark30}">
                                <a href="javascript:void(0)" class="btn-wat-up" onclick="click_watermark('top_right_watermark30')"></a>
                                <div class="upload-remove"{if $pInfo->top_right_watermark30 == ''} style="display: none;"{/if} onclick="delete_watermark('top_right_watermark30')"></div>
                                {Html::hiddenInput('top_right_watermark30', $pInfo->top_right_watermark30)}
                            </div>
                            <div class="left_watermark30{if $pInfo->left_watermark30 != ''} upl{/if}">
                                <img width="100" height="100"{if $pInfo->left_watermark30 == ''} style="display: none;"{/if} src="{DIR_WS_CATALOG}images/stamp/{$pInfo->left_watermark30}">
                                <a href="javascript:void(0)" class="btn-wat-up" onclick="click_watermark('left_watermark30')"></a>
                                <div class="upload-remove"{if $pInfo->left_watermark30 == ''} style="display: none;"{/if} onclick="delete_watermark('left_watermark30')"></div>
                                {Html::hiddenInput('left_watermark30', $pInfo->left_watermark30)}
                            </div>
                            <div class="watermark30{if $pInfo->watermark30 != ''} upl{/if}">
                                <img width="100" height="100"{if $pInfo->watermark30 == ''} style="display: none;"{/if} src="{DIR_WS_CATALOG}images/stamp/{$pInfo->watermark30}">
                                <a href="javascript:void(0)" class="btn-wat-up" onclick="click_watermark('watermark30')"></a>
                                <div class="upload-remove"{if $pInfo->watermark30 == ''} style="display: none;"{/if} onclick="delete_watermark('watermark30')"></div>
                                {Html::hiddenInput('watermark30', $pInfo->watermark30)}
                            </div>
                            <div class="right_watermark30{if $pInfo->right_watermark30 != ''} upl{/if}">
                                <img width="100" height="100"{if $pInfo->right_watermark30 == ''} style="display: none;"{/if} src="{DIR_WS_CATALOG}images/stamp/{$pInfo->right_watermark30}">
                                <a href="javascript:void(0)" class="btn-wat-up" onclick="click_watermark('right_watermark30')"></a>
                                <div class="upload-remove"{if $pInfo->right_watermark30 == ''} style="display: none;"{/if} onclick="delete_watermark('right_watermark30')"></div>
                                {Html::hiddenInput('right_watermark30', $pInfo->right_watermark30)}
                            </div>
                            <div class="bottom_left_watermark30{if $pInfo->bottom_left_watermark30 != ''} upl{/if}">
                                <img width="100" height="100"{if $pInfo->bottom_left_watermark30 == ''} style="display: none;"{/if} src="{DIR_WS_CATALOG}images/stamp/{$pInfo->bottom_left_watermark30}">
                                <a href="javascript:void(0)" class="btn-wat-up" onclick="click_watermark('bottom_left_watermark30')"></a>
                                <div class="upload-remove"{if $pInfo->bottom_left_watermark30 == ''} style="display: none;"{/if} onclick="delete_watermark('bottom_left_watermark30')"></div>
                                {Html::hiddenInput('bottom_left_watermark30', $pInfo->bottom_left_watermark30)}
                            </div>
                            <div class="bottom_watermark30{if $pInfo->bottom_watermark30 != ''} upl{/if}">
                                <img width="100" height="100"{if $pInfo->bottom_watermark30 == ''} style="display: none;"{/if} src="{DIR_WS_CATALOG}images/stamp/{$pInfo->bottom_watermark30}">
                                <a href="javascript:void(0)" class="btn-wat-up" onclick="click_watermark('bottom_watermark30')"></a>
                                <div class="upload-remove"{if $pInfo->bottom_watermark30 == ''} style="display: none;"{/if} onclick="delete_watermark('bottom_watermark30')"></div>
                                {Html::hiddenInput('bottom_watermark30', $pInfo->bottom_watermark30)}
                            </div>
                            <div class="bottom_right_watermark30{if $pInfo->bottom_right_watermark30 != ''} upl{/if}">
                                <img width="100" height="100"{if $pInfo->bottom_right_watermark30 == ''} style="display: none;"{/if} src="{DIR_WS_CATALOG}images/stamp/{$pInfo->bottom_right_watermark30}">
                                <a href="javascript:void(0)" class="btn-wat-up" onclick="click_watermark('bottom_right_watermark30')"></a>
                                <div class="upload-remove"{if $pInfo->bottom_right_watermark30 == ''} style="display: none;"{/if} onclick="delete_watermark('bottom_right_watermark30')"></div>
                                {Html::hiddenInput('bottom_right_watermark30', $pInfo->bottom_right_watermark30)}
                            </div>
                        </div>
                        <div class="watermark-info">
                            <h4>{$smarty.const.TEXT_MIN_WIDTH} - 30px</h4>
                            <div class="about-image-text">
                                {$smarty.const.IMAGES_BIGGER_THAN|sprintf:30}
                                <ul>
                                    {$smarty.const.IF_YOUD_LIKE_TO_UPLOAD|sprintf:30}
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript" src="{$app->request->baseUrl}/plugins/fileupload/jquery.fileupload.js"></script>
<script>
    function click_watermark (name) {
        if ( $('#fileupload_'+name).length==0 ) {
            $('body').append('<form id="fileupload_' + name + '" data-source="'+name+'" action="#" method="POST" enctype="multipart/form-data">' +
                '<input name="files" id="' + name + '" type="file">' +
                '</form>');
            $('#fileupload_'+name).fileupload({
                url: '{Yii::$app->urlManager->createUrl('platforms/file-manager-upload')}',
                maxFileSize: 2097152,
                maxNumberOfFiles: 1,
                downloadTemplateId: false,
                autoUpload: true,
                acceptFileTypes: /(\.|\/)(gif|jpe?g|png)$/i
            });
            $('#fileupload_'+name).bind('fileuploaddone', function (e, data) {
                var mainFormSource = $(e.currentTarget).data('source');
                $('input[name='+mainFormSource+']').val(data.result);
                var $mainRoot = $('div.'+mainFormSource);
                $mainRoot.addClass('upl');
                $mainRoot.find('div.upload-remove').show();
                $mainRoot.find('img').attr('src', '{DIR_WS_CATALOG}images/stamp/'+data.result).show();
            } );
        }
        $('#fileupload_'+name+' input').click();
    }
    function delete_watermark (name) {
        $('input[name='+name+']').val('');
        $('div.'+name+'').children('div.upload-remove').hide();
        $('div.'+name+'').children('img').hide();
        $('div.'+name+'').removeClass('upl');
    }
    $(document).ready(function(){
        $('.js_check_watermark_on_off').bootstrapSwitch({
            onText: "{$smarty.const.SW_ON}",
            offText: "{$smarty.const.SW_OFF}",
            handleWidth: '20px',
            labelWidth: '24px'
        });
    });
</script>
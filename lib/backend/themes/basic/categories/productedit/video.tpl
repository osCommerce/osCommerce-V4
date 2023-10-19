<div class="video-tab">
    <h4>{$smarty.const.VIDEOS_FROM}</h4>
    <div class="tabbable tabbable-custom">
        {*toDo video per platform if !(isset($is_marketplace) && $is_marketplace) }
        {$platform_id=}
        <div class="platform-lang-tabs-switcher"><label>{strtoupper($smarty.const.TEXT_ALL_LANGUAGES)}&nbsp;</label><input type="checkbox" data-id="{$platform_id}" class="pl-tabs-switcher" {if isset($app->controller->view->sphl[$platform_id]) && $app->controller->view->sphl[$platform_id]}checked{/if}></div>
        {*/if*}
        {if $languages|@count > 1}
            <ul class="nav nav-tabs">
                {foreach $languages as $lKey => $lItem}
                    <li{if $lKey == 0} class="active"{/if} data-bs-toggle="tab" data-bs-target="#tab_1_14_{$lItem['id']}"><a class="flag-span">{$lItem['image']}<span>{$lItem['name']}</span></a></li>
                {/foreach}
            </ul>
        {/if}
        <div class="tab-content {if $languages|@count < 2}tab-content-no-lang{/if}">
            {foreach $languages as $lKey => $lItem}
                {if $lItem['id']==1}
                <div class="tab-pane{if $lKey == 0} active{/if}" id="tab_1_14_{$lItem['id']}">

                    <div class="product-video" data-lng="{$lItem['id']}">
                        {*foreach $app->controller->view->videos[$lItem['id']] as $item}
                        {/foreach*}
                    </div>

                    <div>
                        <span class="btn btn-add-youtube" data-lng="{$lItem['id']}">{$smarty.const.TEXT_ADD_YOUTUBE_VIDEO}</span>
                        <span class="btn btn-add-video" data-lng="{$lItem['id']}">{$smarty.const.TEXT_UPLOAD_NEW_VIDEO}</span>
                    </div>

                </div>
                    {/if}
            {/foreach}
        </div>
    </div>


<script>
(function($){ $(function(){

    {if isset($app->controller->view->videos) && $app->controller->view->videos}
    const videos = JSON.parse('{json_encode($app->controller->view->videos)|escape:'javascript'}');
    {else}
    const videos = [];
    {/if}

    $('.product-video').each(function(){
        const lng = $(this).data('lng');
        if (videos[lng]) {
            $(this).append(videos[lng].map(item => {
                if (!item.type || item.type == '0') {
                    return youtubeVideoTemplate(lng, item.video, item.video_id)
                } else {
                    return uploadeVideoTemplate(lng, item.video, item.src, item.video_id)
                }
            }))
        }
    })

    $('.btn-add-youtube').on('click', function(){
        const $thisLng = $(this).closest('.tab-pane');
        const lngId = $(this).data('lng');

        $('.product-video', $thisLng).append(youtubeVideoTemplate(lngId));
    });

    $('.btn-add-video').on('click', function(){
        const $thisLng = $(this).closest('.tab-pane');
        const lngId = $(this).data('lng');

        $('.product-video', $thisLng).append(uploadeVideoTemplate(lngId));
    });

    $('.product-video .remove').on('click', function(){
        $(this).parent().remove()
    })

    function youtubeVideoTemplate(lngId, video = '', videoId = 0){
        const $template = $(`
        <div class="row">
            <div class="col-md-12">
            <div class="remove"></div>
                <h3>Video#${ videoId }</h3>
                <textarea name="video[${ lngId}][]" cols="30" rows="2" placeholder="{$smarty.const.PLACE_HERE_CODE}" class="form-control">${ video }</textarea>
                <input type="hidden" name="video_type[${ lngId }][]" value="0"/>
                <input type="hidden" name="video_id[${ lngId }][]" value="${ videoId }"/>
            </div>
        </div>`);

        $('.remove', $template).on('click', function () {
            $template.remove();
        });

        return $template;
    }

    function uploadeVideoTemplate(lngId, video = '', src = '', videoId = 0){
        const $template = $(`
    <div class="row">
        <div class="product-upload-video col-md-12">
        <div class="remove"></div>
            <h3>Video#${ videoId }</h3>
            <div class="upload-image">
                <div class="upload-image-left"${ src ? ' style="display: none"' : '' }>
                    <div class="upload_1"></div>
                </div>
                <div class="uploaded-wrap"${ !src ? ' style="display: none"' : '' }>
                    <div class="uploaded">
                        <video class="video-js" width="200px" height="150px" controls>
                            <source src="${ src }" class="show-image">
                        </video>
                    </div>
                </div>
            </div>
        </div>

        <input type="hidden" name="video[${ lngId }][]" value="${ video }" class="video-value"/>
        <input type="hidden" name="video_type[${ lngId }][]" value="1"/>
        <input type="hidden" name="video_id[${ lngId }][]" value="${ videoId }"/>
    </div>`);

        $('.upload_1', $template)
            .uploads({ 'acceptedFiles': 'video/mpeg,video/mp4,video/ogg,video/quicktime,video/webm,video/x-ms-wmv,video/x-flv,video/3gpp,video/3gpp2'})
            .on('upload', function(){
                $('source', $template)
                    .attr('src', '{\Yii::getAlias('@web')}/uploads/' + $('input[type="hidden"]', this).val())
                    .closest('video')
                    .trigger('load');
                $('.video-value', $template).val($('input[type="hidden"]', this).val())
                $('.uploaded-wrap', $template).show();
                $('.upload-image-left', $template).hide();
            });

        $('.remove', $template).on('click', function () {
            $template.remove();
        });

        return $template;
    }


    $('.pl-tabs-switcher').tlSwitch(
        {
            onSwitchChange: function (element, arguments) {
                window.location.replace(window.location.protocol+'//'+window.location.host+window.location.pathname+(window.location.search.length?window.location.search+'&':'')+'shpl['+$(element.target).data('id')+']='+(arguments?'1':'0')+window.location.hash);
                return true;
            },
            onText: "{$smarty.const.SW_ON}",
            offText: "{$smarty.const.SW_OFF}",
            handleWidth: '20px',
            labelWidth: '24px',
        }
    );


})})(jQuery)
</script>
</div>
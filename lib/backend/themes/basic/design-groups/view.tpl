{use class="backend\assets\BannersAsset"}
{BannersAsset::register($this)|void}
{use class="backend\assets\DesignAsset"}
{DesignAsset::register($this)|void}
{use class="backend\assets\SliderAsset"}
{SliderAsset::register($this)|void}

<form class="group-view-form">
    <input type="hidden" name="group_id" value="{$groupId}"/>
    <input type="hidden" name="group[status]" value="{$group.status}"/>

    <div class="row group-view-content">
        <div class="col-md-6 images">
            <div class="slider-for">
                {foreach $group.images as $image}
                    <div class="image">
                        <img src="../{$smarty.const.DIR_WS_IMAGES}widget-groups/{$groupId}/{$image.file}" alt="">
                    </div>
                {/foreach}
            </div>
            {if $imagesCount > 1}
            <div class="slider-nav">
                {foreach $group.images as $image}
                    <div class="image">
                        <span><img src="../{$smarty.const.DIR_WS_IMAGES}widget-groups/{$groupId}/{$image.file}" alt=""></span>
                    </div>
                {/foreach}
            </div>
            {/if}
        </div>
        <div class="col-md-6">

            <h2>{$group.languages[$languageId].title}</h2>

            <div class="m-b-4">{$group.languages[$languageId].description}</div>

            <div class="file row align-items-center m-b-2">
                <label class="col-xs-3">{$smarty.const.ICON_FILE}:</label>
                <div class="col-xs-9">
                    {$group.file}
                </div>
            </div>

            <div class="category row align-items-center m-b-2">
                <label class="col-xs-3">{$smarty.const.TEXT_CATEGORY}:</label>
                <div class="col-xs-9">
                    {$group.category}
                </div>
            </div>

            <div class="page-type row align-items-center m-b-2">
                <label class="col-xs-3">{$smarty.const.SHOW_ON_PAGE_TYPE}:</label>
                <div class="col-xs-9">
                    {$group.page_type}
                </div>
            </div>

        </div>
    </div>


    <div class="btn-bar edit-btn-bar">
        <div class="btn-left">
            <a href="{$backUrl}" class="btn btn-cancel-foot">{$smarty.const.IMAGE_BACK}</a>
        </div>
        <div class="btn-right">
            {*<button class="btn btn-confirm">{$smarty.const.IMAGE_SAVE}</button>*}
        </div>
    </div>

</form>

<script>
    $(function(){
        $('.slider-for').slick({
            slidesToShow: 1,
            slidesToScroll: 1,
            arrows: false,
            fade: true,
            asNavFor: '.slider-nav'
        });
        {if $imagesCount > 1}
        $('.slider-nav').slick({
            slidesToShow: {if $imagesCount < 5}{$imagesCount}{else}5{/if},
            slidesToScroll: 1,
            asNavFor: '.slider-for',
            dots: true,
            centerMode: true,
            focusOnSelect: true
        });
        {/if}
    })
</script>
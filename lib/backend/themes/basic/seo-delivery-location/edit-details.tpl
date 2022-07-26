<div class="row">
    <div class="col-md-6">
        <div class="widget box">
            <div class="widget-header">
                <h4>Gallery image</h4>
            </div>
            <div class="widget-content">
                <div class="about-image">
                    <div class="about-image-scheme-1">
                        <div></div><div></div><div></div><div></div><div></div><div></div>
                    </div>
                    <div class="about-image-text">
                        This image will be used on location listing page
                        <ul>
                            <li>Make sure your image is appropriately sized. It should be not too big and not too small.</li>
                            <li>Formats:  jpg, png, gif.</li>
                            <li>Color mode: RGB</li>
                        </ul>
                    </div>
                </div>
                {\backend\design\Image::widget([
                'name' => 'image_listing',
                'value' => {$location_data['image_listing_src_admin']},
                'upload' => 'image_listing_loaded',
                'delete' => 'image_headline_delete'
                ])}
            </div>
            <div class="divider"></div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="widget box">
            <div class="widget-header">
                <h4>Hero image</h4>
            </div>
            <div class="widget-content">
                <div class="about-image">
                    <div class="about-image-scheme-2">
                        <div></div><div></div><div></div><div></div>
                    </div>
                    <div class="about-image-text">
                        This image will be used on location view page
                        <ul>
                            <li>Make sure your image is appropriately sized. It should be not too small.</li>
                            <li>Formats:  jpg, png, gif.</li>
                            <li>Color mode: RGB</li>
                        </ul>
                    </div>
                </div>
                {\backend\design\Image::widget([
                'name' => 'image_headline',
                'value' => {$location_data['image_headline_src_admin']},
                'upload' => 'image_headline_loaded',
                'delete' => 'image_headline_delete'
                ])}
            </div>
            <div class="divider"></div>
        </div>
    </div>
</div>
<div class="">
    <div class="md_row after">
        <label for="status">{$smarty.const.TEXT_SHOW_ON_INDEX}</label>
        <div class="md_value"><input type="checkbox" value="1" name="show_on_index" class="check_on_off"{if $location_data['show_on_index'] == 1} checked="checked"{/if}></div>
    </div>
    <div class="md_row after">
        <label for="status">{$smarty.const.TEXT_FEATURED_LOCATION}</label>
        <div class="md_value"><input type="checkbox" value="1" name="featured" class="check_on_off"{if $location_data['featured']== 1} checked="checked"{/if}></div>
    </div>
    <div class="md_row after">
        <label for="status">{$smarty.const.TABLE_HEADING_DATE_ADDED}</label>
        <div class="md_value">
            <input type="text" value="{\common\helpers\Date::formatDateTimeJS($location_data['date_added'])}" name="date_added" class="form-control datepicker">
        </div>
    </div>
</div>

{\backend\assets\BDTPAsset::register($this)|void}
{use class="backend\assets\BannersAsset"}
{BannersAsset::register($this)|void}
<script>
    $(function(){

        $('.datepicker').datetimepicker({
            format: 'DD MMM YYYY h:mm A'
        });
    })
</script>
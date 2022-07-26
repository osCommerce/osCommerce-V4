{*
This file is part of osCommerce ecommerce platform.
 * osCommerce the ecommerce

@link https://www.oscommerce.com
@copyright Copyright (c) 2000-2022 osCommerce LTD

Released under the GNU General Public License
 * For the full copyright and license information, please view the LICENSE.TXT file that was distributed with this source code.
*}

{* variables
$idSuffix
$nameSuffix
$value[mapsTitle => '', mapsId => N, mapsImage => '']
*}


    <input type="text" class="form-control map-name" id="map_name_{$idSuffix}" value="{$value.mapsTitle}"/>
    <input type="hidden" id="map_id_{$idSuffix}" name="maps_id{$nameSuffix}" value="{$value.mapsId}"/>
    <div class="search-map" id="search_map_{$idSuffix}"></div>
    <div class="map-image-holder">
        <img src="../images/maps/{$value.mapsImage}" class="map-image" id="map_image_{$idSuffix}" alt=""
                {if !$value.mapsImage} style="display: none" {/if}>
        <div class="map-image-remove" id="map_image_remove_{$idSuffix}"
                {if !$value.mapsImage} style="display: none" {/if}></div>
    </div>

<script type="text/javascript">

        $('#map_name_{$idSuffix}').keyup(function(e){
            $.get('image-maps/search', {
                key: $(this).val()
            }, function(data){
                $('.suggest').remove();
                $('#search_map_{$idSuffix}').append('<div class="suggest">'+data+'</div>');

                $('a', $('#search_map_{$idSuffix}')).on('click', function(e){
                    e.preventDefault();

                    $('#map_id_{$idSuffix}').val($(this).data('id'));
                    $('#map_name_{$idSuffix}').val($('.td_name', this).text());
                    $('#map_image_{$idSuffix}').show().attr('src', '../images/maps/' + $(this).data('image'));
                    $('#map_image_remove_{$idSuffix}').show();

                    $('.suggest').remove();
                    return false;
                });
            });
        });

        $('#map_image_remove_{$idSuffix}').on('click', function(){
            $('#map_id_{$idSuffix}').val('');
            $('#map_name_{$idSuffix}').val('');
            $('#map_image_{$idSuffix}').show().attr('src', '');
            $(this).hide();
        });
</script>
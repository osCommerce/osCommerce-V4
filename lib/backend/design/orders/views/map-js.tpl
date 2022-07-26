<script src="https://maps.googleapis.com/maps/api/js?key={$key}&callback=initMap" async defer></script>
<script>
    $(function () {
        var click_map = false;
        $('body').on('click', function () {
            setTimeout(function () {
                if (click_map) {
                    $('.map_dashboard-hide').remove()
                } else {
                    if (!$('.map_dashboard-hide').hasClass('map_dashboard-hide')) {
                        $('.gmaps-wrap').append('<div class="map_dashboard-hide" style="position: absolute; left: 0; top: 0; right: 0; bottom: 0"></div>')
                    }
                }
                click_map = false
            }, 200)
        });
        $('.gmaps-wrap')
            .css('position', 'relative')
            .append('<div class="map_dashboard-hide" style="position: absolute; left: 0; top: 0; right: 0; bottom: 0"></div>')
            .on('click', function () {
                setTimeout(function () {
                    click_map = true
                }, 100)
            })
    });

    function initMap() {
        var geocoder = new google.maps.Geocoder();
        
        {foreach $adds as $address}
            var {$address['marker']} = new google.maps.Map(document.getElementById("{$address['marker']}"), {
                zoom: {$address['zoom']},
                center: { lat: -34.397, lng: 150.644 }
            });
            geocodeAddress(geocoder, {$address['marker']}, "{$address['add1']|escape:'javascript'}", "{$address['add2']|escape:'javascript'}");
        {/foreach}
    }

    function geocodeAddress(geocoder, resultsMap, address1, address2) {                    
        geocoder.geocode({ 'address': address1 }, function (results, status) {
            if (status === google.maps.GeocoderStatus.OK) {
                resultsMap.setCenter(results[0].geometry.location);
                var marker = new google.maps.Marker({
                    map: resultsMap,
                    position: results[0].geometry.location
                });
            } else {
                geocoder.geocode({ 'address': address2 }, function (results, status) {
                    if (status === google.maps.GeocoderStatus.OK) {
                        resultsMap.setCenter(results[0].geometry.location);
                        var marker = new google.maps.Marker({
                            map: resultsMap,
                            position: results[0].geometry.location
                        });
                    }
                });
            }
        });
    }
   
</script>
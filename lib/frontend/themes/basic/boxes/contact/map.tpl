<input id="hid-add0" type="hidden" value="{$address}">
<input id="hid-add1" type="hidden" value="{$addressnosuburb}">
<input id="hid-add2" type="hidden" value="{$addressnocode}">
<div id="gmap_markers2" class="gmaps" style="width: 100%; height:400px"></div>

<script src="https://maps.googleapis.com/maps/api/js?key={$key}&callback=initMap" async defer></script>
<script>


  function initMap() {
    var map1 = new google.maps.Map(document.getElementById('gmap_markers2'), {
      zoom: {$country_info['zoom']},
      center: { lat: {if is_numeric($country_info['latitude'])} {$country_info['latitude']} {else} 0 {/if}, lng: {if is_numeric($country_info['longitude'])} {$country_info['longitude']} {else} 0 {/if}}
    });
    var geocoder = new google.maps.Geocoder();

    geocodeAddress1(geocoder, map1);
  }
  var index = 0;
  function geocodeAddress1(geocoder, resultsMap) {
  /*
    var address1 = document.getElementById('hid-add').value;
    geocoder.geocode({ 'address': address1}, function(results, status) {
      if (status === google.maps.GeocoderStatus.OK) {

        resultsMap.setCenter(results[0].geometry.location);
        var marker = new google.maps.Marker({
          map: resultsMap,
          position: results[0].geometry.location
        });

        var panorama = new google.maps.StreetViewPanorama(
                document.getElementById('pano'), {
                  position: results[0].geometry.location,
                  pov: {
                    heading: {if isset($settings_street[0].street_heading) && $settings_street[0].street_heading}{$settings_street[0].street_heading}{else}0{/if},
                    pitch: {if isset($settings_street[0].street_pitch) && $settings_street[0].street_pitch}{$settings_street[0].street_pitch}{else}0{/if}
                  }
                });
        resultsMap.setStreetView(panorama);


      } else {
        //alert('Geocode was not successful for the following reason: ' + status);
      }
    });
*/
multisearch(geocoder, resultsMap, 0);
  }

function multisearch(geocoder, resultsMap, index){

if (index >= 3) return;

    geocoder.geocode(
      {
		address:document.getElementById('hid-add'+index).value
	  },
	  function(results, status) {
      if (status === google.maps.GeocoderStatus.OK) {
        resultsMap.setCenter(results[0].geometry.location);
        var marker = new google.maps.Marker({
          map: resultsMap,
          position: results[0].geometry.location
        });

        var panorama = new google.maps.StreetViewPanorama(
                document.getElementById('pano'), {
                  position: results[0].geometry.location,
                  pov: {
                    heading: {if isset($settings_street[0].street_heading) && $settings_street[0].street_heading}{$settings_street[0].street_heading}{else}0{/if},
                    pitch: {if isset($settings_street[0].street_pitch) && $settings_street[0].street_pitch}{$settings_street[0].street_pitch}{else}0{/if}
                  }
                });
        resultsMap.setStreetView(panorama);
      } else if(status === google.maps.GeocoderStatus.ZERO_RESULTS) {
		index = parseInt(index)+1;
		multisearch(geocoder, resultsMap, index)
	  }
    });
    return;
}

</script>
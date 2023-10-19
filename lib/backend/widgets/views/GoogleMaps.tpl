{if defined('SHOW_GOOGLE_MAPS')}
  {if SHOW_GOOGLE_MAPS == 'true'}
<div class="map_dashboard">
    <div id="gmap_markers" class="gmaps"></div>
    <script src="https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/markerclusterer.js">
    </script>    
    <script src="https://maps.googleapis.com/maps/api/js?key={$mapskey}&callback=initMap" async defer></script> 
</div>
<script type="text/javascript">
  $(function(){
    var click_map = false;
    $('body').on('click', function(){
      setTimeout(function(){
        if (click_map ) {
          $('.map_dashboard-hide').remove()
        } else {
          if (!$('.map_dashboard-hide').hasClass('map_dashboard-hide')){
            $('.map_dashboard').append('<div class="map_dashboard-hide" style="position: absolute; left: 0; top: 0; right: 0; bottom: 0"></div>')
          }
        }
        click_map = false
      }, 200)
    });
    $('.map_dashboard')
            .css('position', 'relative')
            .append('<div class="map_dashboard-hide" style="position: absolute; left: 0; top: 0; right: 0; bottom: 0"></div>')
            .on('click', function(){
              setTimeout(function(){
                click_map = true
              }, 100)
            })
  });


var map;
var geocoder;
var markers = new Array();
var delay = 1000;
var masSearch = new Array();
var max = 10;
var start = -1;
var tim;
var firstloaded = 0;
var moreLoaded = 0;
var markerCluster;
var _max_orders_count = 0;
var limit = 50;

function reloadClaster(){
  if (_max_orders_count > limit){
      markerCluster = new MarkerClusterer(map, markers,
            {
              imagePath: 'https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/m'
            });
  }
}

function loadMapData(){

	$.get("{Yii::$app->urlManager->createUrl('index/locations')}",{}, function(data){
		if (data.founded && data.founded.length > 0 && firstloaded == 0){
      _max_orders_count = data.orders_count;
			$.each(data.founded, function(i, e){
				addMarker(e, map);
			});
			firstloaded = 1;
			reloadClaster();
		}
		if (data.to_search && data.to_search.length > 0){
			masSearch = data.to_search;
			max = 10;
			start = -1;
			tim = setInterval(function(){
				var iter = 0;
				$.each(masSearch, function(i, e){
					if (i > start && iter <= max && e.status != 'OK'){
						multisearch(geocoder, map, e, 0);
						iter++;
						start++;
					}
				});

				if(masSearch.length < (start + 3 )){ // next iteration
					var tmp = new Array();
					$.each(masSearch, function(i, e){
						if(e.status == 'OVER_QUERY_LIMIT'){
							tmp.push(e);
						}
					});
					masSearch = tmp;
					start = -1;
					max = 5;
					if (masSearch.length == 0){
						moreLoaded = 1;
						clearInterval(tim);
						loadMapData();
					}
					//console.log(masSearch);
				}

			}, delay);
			
		} else {
			clearInterval(tim);
		}
	}, "json");
	
}

function initMap() { 
    map = new google.maps.Map(document.getElementById('gmap_markers'), { 
      zoom: parseFloat({$origPlace.zoom}),
      center: { lat: parseFloat({$origPlace.lat}), lng: parseFloat({$origPlace.lng}) }
    });
    geocoder = new google.maps.Geocoder();
	
	var labels = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
	var labelIndex = 0;	

	loadMapData();
}    


function addMarker(location, map) {
  // Add the marker at the clicked location, and add the next-available label
  // from the array of alphabetical characters.
  if (_max_orders_count < limit){
	  markers.push(new google.maps.Marker({
		position:  {
			lat: parseFloat(location.lat),
			lng: parseFloat(location.lng)
		},
		label: "A",//labels[labelIndex++ % labels.length],
	    map: map,
		title: location.title
	  }));
  
  } else { // to be reloaded by Cluster
	  markers.push(new google.maps.Marker({
		position:  {
			lat: parseFloat(location.lat),
			lng: parseFloat(location.lng)
		},
		label: "A",//labels[labelIndex++ % labels.length],
		title: location.title
	  }));
  
  }
}


var $params;
function multisearch(geocoder, map, data, index){

  $params = new Array(
    { 
      componentRestrictions: {
        country: data.isocode,
        postalCode: data.pcode
      }
    },
    {
     address:data.address
    },
    {
     address:data.addressnocode
    }
  );
  
  //if (index == undefined) index = 0;
  if (index >= $params.length) return '9999';

    geocoder.geocode(
      $params[index]
	  , function(results, status) {
      data.status = status;
      //console.log(status);
      if (status === google.maps.GeocoderStatus.OK) {
          $.post("{Yii::$app->urlManager->createUrl('index/locations')}",
          {
          'lat':results[0].geometry.location.lat(),
          'lng':results[0].geometry.location.lng(),
          'order_id': data.orders_id
          },
          function(){});
         if (moreLoaded == 0){
          _max_orders_count++;
          addMarker({ lat:results[0].geometry.location.lat(), lng:results[0].geometry.location.lng(), title:''}, map);   
          reloadClaster();
         }
      } else if (
          status === google.maps.GeocoderStatus.ZERO_RESULTS ||
          status === google.maps.GeocoderStatus.UNKNOWN_ERROR
      ){
        index = parseInt(index)+1;
        var resp = multisearch(geocoder, map, data, index);
        if (resp == '9999'){
          $.post("{Yii::$app->urlManager->createUrl('index/locations')}",
            {
              'lat':'9999',
              'lng':'9999',
              'order_id': data.orders_id
            },
            function(){});
        }
      } else if(status === google.maps.GeocoderStatus.OVER_QUERY_LIMIT){
        delay = parseInt(delay) + 1000; 
      }
    });
    return;
}

function geocodeAddress(geocoder, map, data) {
	multisearch(geocoder, map, data, 0);
  return;
}
  </script>
  {/if}
   <br/>
    <div class="form-check form-switch form-check-reverse d-sm-inline-block">
        <input type="checkbox" name="enabled_map" class="form-check-input" role="switch" value="1" {if SHOW_GOOGLE_MAPS == 'true'} checked="checked" {/if}/>
        {$enabled_map['configuration_title']}
    </div>
      <script>
        $(function(){
            $("input[name=enabled_map]").on('change', function(){
                $.get('index/enable-map',{
                    'configuration_id' : '{$enabled_map['configuration_id']}',
                    'status' : $(this).prop('chacked')
                }, function(data, status){
                    if (status == 'success'){
                        window.location.reload();
                    }
                })
            })
        })
      </script>  
  {/if } 
  <script>
$(document).ready(function() {
    $( window ).resize(function() {
       var width_map = $('.map_dashboard').width() / 1.5;
       $('.map_dashboard .gmaps').css('height', width_map);
   });
   $(window).resize();
});
</script>

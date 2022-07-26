<script>
var map;
var geocoder;
var markers = new Array();
var markerCluster;

</script>
<div class="sbv">
    <h4>{$smarty.const.TEXT_SHOW_ON_MAP}</h4>
    <div class="map_dashboard">
        <div id="gmap_markers" class="gmaps"></div>
    </div>
</div>
<script>

function reloadClaster(){
  markerCluster = new MarkerClusterer(map, markers,
    {
      imagePath: 'https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/m'
    });
}

function addMarker(location, map) {
    markers.push(new google.maps.Marker({
		position:  {
			lat: parseFloat(location.lat),
			lng: parseFloat(location.lng)
		},
		label: "A",//labels[labelIndex++ % labels.length],
		title: location.title,
	  }));  
}

function initMap() { 

    map = new google.maps.Map(document.getElementById('gmap_markers'), { 
      zoom: parseFloat({$origPlace.zoom}),
      center: { lat: parseFloat({$origPlace.lat}), lng: parseFloat({$origPlace.lng}) }
    });
    geocoder = new google.maps.Geocoder();
	markers = new Array();
	var labels = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
	var labelIndex = 0;	
    
    $.get('sales_statistics/map', 
        $('form[name=sales]').serialize(),                
        function(data, status){
            if (status == 'success'){
                $.each(data.data, function(i, e){
                    addMarker(e, map);
                });
                reloadClaster();
            }
        }, 'json');
}  

jQuery(function($){
  $('.map_dashboard .gmaps').height($(window).height() * 0.8);
  if (typeof map == 'undefined'){
      var script2 = document.createElement('script');
      script2.setAttribute('src', 'https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/markerclusterer.js');
      document.head.appendChild(script2);
      var script = document.createElement('script');      
      script.setAttribute('src', 'https://maps.googleapis.com/maps/api/js?key={$mapskey}&callback=initMap');
      script.setAttribute('async', true);
      script.setAttribute('defer', true);
      document.head.appendChild(script);      
  } else {
    initMap();
  }
})

</script>
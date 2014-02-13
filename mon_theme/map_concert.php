<h5 style="text-align:center;">Nos concerts a travers la France </h5>
<div id="map" style="height:380px;width: 500px;left:31%;"></div>
<script type="text/javascript">
	map = L.map('map', {
		center: [46, 0.8],
		zoom: 5
	});
	L.tileLayer('http://{s}.tile.cloudmade.com/BC9A493B41014CAABB98F0471D759707/997/256/{z}/{x}/{y}.png', {
			maxZoom: 18,
			// attribution: 'Map data &copy; <a href="http://openstreetmap.org">OpenStreetMap</a> contributors, <a href="http://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, Imagery © <a href="http://cloudmade.com">CloudMade</a>'
		}).addTo(map);

</script>
<script>
<?php 
	if($post_type == "concert") echo getMarkerList();
 ?>


map.on('popupopen', function(e) {
	var post_id = e.popup.post_id;
	var nonce = '<?php print wp_create_nonce("popup_content"); ?>';
	jQuery.post("<?php print admin_url('admin-ajax.php') ?>",{
		action : 'popup_content',
		post_id :post_id, 
		nonce : nonce
	},
	function(response){
		e.popup.setContent(response);
	});
});




</script>
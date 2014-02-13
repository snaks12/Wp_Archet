<?php 

add_action('pre_get_posts', 'display_concerts');

function display_concerts($query){
	if($query->is_front_page() && $query->is_main_query())
	{
		$query->set('post_type', array('concert') );

		//10 dernière années
		//$query->set('date_query', array('year' => getdate()['year']-10, 'compare' => '>='));

		//Compris entre 2006 et 2008
		//$query->set('date_query', array('year' =>  array('2006','2008'), 'compare' => 'BETWEEN'));

		//Concert sans lieu
		// $query->set('meta_query', array(array('key'=>'wpcf-lieu', 'value' => false , 'type'=> BOOLEAN)));

		//qui possède une image à la une
		// $query->set('meta_query', array(array('key'=>'_thumbnail_id', 'compare' =>'EXISTS')));
		return;
	}
}

function dashboard_widget_function() {

	$args = array(
			'post_type' => 'concert',
			'post_per_page' => -1,
			'meta_query' => array(
					array(
					'key' => 'wpcf-lieu',
					'value' => false,
					'type' => 'BOOLEAN'
					)
				)
	        );

	$query = new WP_Query($args);
	$posts = $query->posts;

	echo '<h4><strong>Nombre de post sans lieu '.$query->post_count.'</strong></h4>';

	foreach ($posts as $post) {
		echo '<p>'.$post->post_title.'</p>';
	}

wp_reset_query();

$pays = get_terms( 'pays',array( 'fields' => 'ids'));

$args = array(
			'post_type' => 'action',
			'post_per_page' => -1,
			'tax_query' => array(
					array(
					'taxonomy' => 'pays',
					'fields' => 'id',
					'terms' => $pays,
					'operator' => 'NOT IN'
					)
				)
	        );

	$query = new WP_Query($args);
	$posts = $query->posts;

	echo '<h4><strong>Nombre d action sans lieu '.$query->post_count.'</strong></h4>';

	foreach ($posts as $post) {
		echo '<p>'.$post->post_title.'</p>';
	}
}

function add_dashboard_widgets() {
	wp_add_dashboard_widget('dashboard_widget', 'Infos sur les postes','dashboard_widget_function');
}

add_action('wp_dashboard_setup', 'add_dashboard_widgets' );


function geolocalize($post_id) {
	if ( wp_is_post_revision( $post_id ) )
		return;
	$post = get_post($post_id);
	if ( !in_array( $post->post_type, array('concert') ) )
		return;
	$lieu = get_post_meta($post_id, 'wpcf-lieu', true);
	if(empty($lieu))
		return;
	$lat = get_post_meta($post_id, 'lat', true);
	if(empty($latlon))
	{
		$address = $lieu . ', France';
		$result = doGeolocation($address);
		if(false === $result)
			return;
		try{
			$location = $result[0]['geometry']['location'];
			add_post_meta($post_id, 'lat', $location["lat"]);
			add_post_meta($post_id, 'lng', $location["lng"]);
		}catch(Exception $e)
		{
			return;
		}
	}
}

add_action( 'save_post', 'geolocalize' );

function geolocalizeAction($post_id) {
	if ( wp_is_post_revision( $post_id ) )
		return;
	$post = get_post($post_id);
	if ( !in_array( $post->post_type, array('action') ) )
		return;
	$lieu = wp_get_post_terms($post_id, 'pays', array("fields" => "names"));
	if(empty($lieu))
		return;
	$lat = get_post_meta($post_id, 'lat', true);
	if(empty($latlon))
	{
		$address[0] = $lieu;
		var_dump($address);
		die();
		$result = doGeolocation($address[0]);
		if(false === $result)
			return;
		try{
			$location = $result[0]['geometry']['location'];
			add_post_meta($post_id, 'lat', $location["lat"]);
			add_post_meta($post_id, 'lng', $location["lng"]);
		}catch(Exception $e)
		{
			return;
		}
	}
}

add_action( 'save_post', 'geolocalizeAction' );

function doGeolocation($address){
	$url = "http://maps.google.com/maps/api/geocode/json?sensor=false" .
	"&address=" . urlencode($address);
	if($json = file_get_contents($url)){
		$data = json_decode($json, TRUE);
		if($data['status']=="OK"){
			return $data['results'];
		}
	}
	return false;
}

function load_scripts() {

	if(! is_post_type_archive('concert'))
		return;
	
	wp_register_script(
		'leaflet-js',
		'http://cdn.leafletjs.com/leaflet-0.7.1/leaflet.js'
		)
	;
	wp_enqueue_script( 'leaflet-js' );

	wp_register_style(
		'leaflet-css',
		'http://cdn.leafletjs.com/leaflet-0.7.1/leaflet.css'
		)
	;
	wp_enqueue_style( 'leaflet-css' );
}
add_action('wp_enqueue_scripts', 'load_scripts');
add_action('wp_enqueue_style', 'load_style_css');

function getPostWithLatLon($post_type = "concert")
{
	global $wpdb;
	$query = "
	SELECT
	ID, post_title, p1.meta_value as lat, p2.meta_value as lng
	FROM wp_archets_posts, wp_archets_postmeta as p1, wp_archets_postmeta as p2
	WHERE wp_archets_posts.post_type = 'concert'
	AND p1.post_id = wp_archets_posts.ID
	AND p2.post_id = wp_archets_posts.ID
	AND p1.meta_key = 'lat'
	AND p2.meta_key = 'lng'";

	return $myrows = $wpdb->get_results($query);

}

function getMarkerList($post_type = "concert")
{
	$results = getPostWithLatLon($post_type);
	$array = array();
	foreach($results as $result)
	{
		$array[] = "var marker_".$result->ID." = L.marker([".$result->lat.",".$result->lng."]).addTo(map);";
		$array[] = "var latlng = L.latLng(".$result->lat.",".$result->lng.");";
		$array[] = "var popup_".$result->ID." = L.popup().setContent('".addslashes($result->post_title)."')";
		$array[] = "marker_".$result->ID.".bindPopup(popup_".$result->ID.");";
		$array[] = "popup_".$result->ID.".post_id = ".$result->ID.";";

		
	}

	
	return implode(PHP_EOL, $array);
}

add_action("wp_ajax_popup_content", "get_content");
add_action("wp_ajax_nopriv_popup_content", "get_content");

function get_content() {
	if ( !wp_verify_nonce( $_REQUEST['nonce'], "popup_content")) {
		exit("d'où vient cette reqûete ?");
	}
	else
	{
		$post_id = $_REQUEST["post_id"];
		$mon_post = get_post($post_id);

		print "<p style='text-align:center;'>".$mon_post->post_title."<br />".$mon_post->post_content."</p>";
	}
	die(); //important
}

function getMarkerListAction($post_type = "action")
{
	$results = getPostWithLatLon($post_type);
	$array = array();
	foreach($results as $result)
	{
		$array[] = "var marker_".$result->ID." = L.marker([".$result->lat.",".$result->lng."]).addTo(map);";
		$array[] = "var latlng = L.latLng(".$result->lat.",".$result->lng.");";
		$array[] = "var popup_".$result->ID." = L.popup().setContent('".addslashes($result->post_title)."')";
		$array[] = "marker_".$result->ID.".bindPopup(popup_".$result->ID.");";
		$array[] = "popup_".$result->ID.".post_id = ".$result->ID.";";		
	}
	
	return implode(PHP_EOL, $array);
}

?>
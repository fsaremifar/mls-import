<?php
/*
Plugin Name: MLS Import
Plugin URI: f.saremifar@gmail.com
Description: Connect MLS to RealHomes 
Version: 1.0
Author: Freddie 
Author URI: f.saremifar@gmail.com
*/

// Create a action from our importIt function
add_action('mls-import', 'SyncMls');
  
function writelog($log) {
	if (true === WP_DEBUG) {
		if (is_array($log) || is_object($log)) {
			error_log(print_r($log, true));
		} else {
			error_log($log);
		}
	}
	else{
		$file='wp-content/plugins/mls-import/log.txt';
		$fileContent = file_get_contents ($file);

		file_put_contents ($file, $log .PHP_EOL . $fileContent);

		// if (is_array($log) || is_object($log)) {
		//     file_put_contents($file,print_r($log, true), FILE_APPEND);
		// } else {
		//     file_put_contents($file,$log, FILE_APPEND);
		// } 
	}
}
 
function SyncMls()
{

	 
	try {
		// Disable a time limit
		set_time_limit(0);
		date_default_timezone_set('America/Denver');  
		// Require some Wordpress core files for processing images
		require_once(ABSPATH . 'wp-admin/includes/media.php');
		require_once(ABSPATH . 'wp-admin/includes/file.php');
		require_once(ABSPATH . 'wp-admin/includes/image.php'); 
		// Require RETS
		require_once("vendor/autoload.php"); 
		//require plugin helpers
		require_once("MlsListing.php");
		require_once("MlsConnector.php");

		importIt();

	} catch (\Throwable $th) {
		writelog($th);

	} 
}

// The functions which is going to do the job
function importIt()
{
	

	

	$mls=new MlsConnector;
	  

	$properties=$mls->Properties();
	writelog('Got '. count($properties) . ' Properties from MLS');
	$posts=GetPosts();
	writelog('Got '. count($posts) . ' Properties from WP');

	$update=0;
	$create=0;
	foreach ($properties as $p)
	{ 
		if($p==null) continue;
		$result=null;
		 
		$currentPost=$posts[$p->Key];  
		if($currentPost==null)
		{
			//new property
			$result=CreatePost($p,$mls); 
			$create++;
		}
		else{

			//existing property 
			$result=UpdatePost($currentPost,$p,$mls); 
			unset($posts[$p->Key]); 
			$update++;
		}

		 
	}
	writelog("Created :" . $create . " Updated:" .$update);

	writelog('Removing '. count($posts) . ' Properties from WP');

	RemovePosts($posts); 
	$mls->Logout();
	
}
function RemovePosts($posts)
{
	// Loop through them
	foreach($posts as $post){

		// Get the featured image id
		// if($thumbId = get_post_meta($post->ID,'_thumbnail_id',true)){

		// 	// Remove the featured image
		// 	wp_delete_attachment($thumbId,true);
		// }

		// Remove the post
		if($post==null)continue;
		wp_delete_post( $post['ID'], true);
	}

}
function CreatePost($p,$mls)
{
	$postCreated =array(
		'post_title' 	=> $p->Title,
		'post_name'=>$p->Title,
		'post_content' 	=> $p->Description, 
		'post_author'=>1,
		'post_parent'=>0,
		'meta_key'=>'mls_imported',
		'post_status' 	=> 'publish',
		'post_type' 	=> 'property', // Or "page" or some custom post type
	);
  
	// Get the increment id from the inserted post
	$postInsertId = wp_insert_post( $postCreated );
	$p->PostId=$postInsertId;
	AssignTerm($p,$p->City,"property-city");
	AssignTerm($p,$p->ListingStatus,"property-status");
	AssignTerm($p,$p->ResidentialType,"property-type"); 
	SetPropertyThumbnail($p,$mls);
	SetPropertyImages($p,$mls); 
	$postOptions = $p->GetMetaDataForPropety();  
	foreach($postOptions as $key=>$value){  
		update_post_meta($postInsertId,$key,$value);
	}

	 
	$post=get_post($postInsertId);
	//$post['Metas']=get_post_meta($post->ID);
	 
	
	return $post;
	
}
function UpdatePost($currentPost,$p,$mls)
{
	$p->PostId=$currentPost->ID;
	// AssignTerm($p,$p->City,"property-city");
	// AssignTerm($p,$p->ListingStatus,"property-status");
	// AssignTerm($p,$p->ResidentialType,"property-type");
	$postOptions = $p->GetMetaDataForPropety();  
	foreach($postOptions as $key=>$value){  
		update_post_meta($p->PostId,$key,$value);
	}

	$post=get_post($p->PostId);
	//$post['Metas']=get_post_meta($post->ID);
	 
	
	return $post;
} 
function GetPosts() 
{
	 
	$wp_posts = get_posts(array( 
		'post_type' 		=> 'property', 
		'meta_key'			=> 'mls_imported', // Our post options to determined
		'posts_per_page'   	=> -1 // Just to make sure we've got all our posts, the default is just 5
	));
	
	  
  $result = [];
  foreach ($wp_posts as $p)
  {  
	$id=get_post_meta($p->ID,'mls_imported',true); 
	$result[$id]=$p;  
	 
  } 
  
  return $result;
}
function SetPropertyImages($p,$rets)
{
	 
	 
	foreach ($p->Images as $url) { 
		if($url==null||$url=="")continue;
		$image=upload_image($url,$p->PostId);
		setImage($p->PostId,$image);
	}
	 
	
}
function SetPropertyThumbnail($p,$rets)
{
	  
	 $url=$p->Thumbnail;
	 if($url==null||$url=="")return;
	
	$image=upload_image($url,$p->PostId);
	featuredImageTrick($image);
	 
}

function upload_image($url, $post_id) {
	$image = "";
	$attachmentId==null;
	$fileExist=does_file_exists( $url);
			if($fileExist>0)
			{
				 
				return $fileExist;
			}
    if($url != "") {
     
        $file = array();
        $file['name'] = $url;
        $file['tmp_name'] = download_url($url);
 
        if (is_wp_error($file['tmp_name'])) {
            @unlink($file['tmp_name']);
            var_dump( $file['tmp_name']->get_error_messages( ) );
        } else {
			 
			


            $attachmentId = media_handle_sideload($file, $post_id,$url);
             
            if ( is_wp_error($attachmentId) ) {
                @unlink($file['tmp_name']);
                var_dump( $attachmentId->get_error_messages( ) );
            } else {                
                $image = wp_get_attachment_url( $attachmentId );
            }
        }
    }
    return $attachmentId;
}
function does_file_exists($url) {
    global $wpdb;
    $sql = $wpdb->prepare(
		"
		SELECT ID
		FROM $wpdb->posts
		WHERE post_title = %s 
	",
		$url 
	);
    return $wpdb->get_var($sql );
  }
// A little hack to "catch" and save the image id with the post
function featuredImageTrick($att_id){
    $p = get_post($att_id);
    update_post_meta($p->post_parent,'_thumbnail_id',$att_id);
}
//REAL_HOMES_slider_image
//REAL_HOMES_property_images
function setImage($post_parent,$att_id){
	$p = get_post($att_id);
	
	add_post_meta($post_parent,'REAL_HOMES_property_images',$att_id);
}
function AssignTerm($p,$term,$taxonamyTerm)
{
	// check if term exists
    $property_city = term_exists($term, $taxonamyTerm );
	$property_city_term_id=0;
    if( $property_city !== 0 && $property_city !== null ) {
        // Term exists, get the term id
        $property_city_term_id = $property_city;
    } else {
        // Create new term
        $property_city_term_id = wp_insert_term(
			$term,     // the term 
			$taxonamyTerm          // the taxonomy
                           ,array("slug"=>$term)     );
    }

	// Assign term id to post
 
	
	wp_set_post_terms( $p->PostId, $property_city_term_id['term_id'], $taxonamyTerm  ,true);

}
 
 
// Register our cronjob to run this task tomorrow midnight (0:00, so that's a new day)
// for the first time and daily at the same time after that
register_activation_hook(__FILE__, 'activateCron');
function activateCron() {
	wp_schedule_event(strtotime('tomorrow midnight'), 'daily', 'mls-import');
}

// Deactive the cron when the plugins is disabled or removed
register_deactivation_hook(__FILE__, 'deactivateCron');
function deactivateCron() {
	wp_clear_scheduled_hook('mls-import');
} 
 
 
 
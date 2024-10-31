<?php
/*
Plugin Name: Save to foursquare
Plugin URI: http://davidsson.co/save-foursquare
Description: Add save to foursquare button on your blog.
Version: 1.0.0
Author: Fredrik Davidsson
Author URI: http://davidsson.co
License: http://www.gnu.org/licenses/gpl-2.0.html
*/

// INIT

/* Runs when plugin is activated */
register_activation_hook(__FILE__,'foursq_save_install'); 

/* Runs on plugin deactivation*/
register_deactivation_hook( __FILE__, 'foursq_save_remove' );

function foursq_save_install() {
	/* Creates new database field */
	add_option("foursq_save_uid", '304071', '', 'yes');
	}

function foursq_save_remove() {
	/* Deletes the database field */
	delete_option('foursq_save_uid');
	}
// END INIT



// PAGE
// add_action('init','hello_world');
// Getting called on pages when activated.
function Save_to_foursquare() {
	global $post;

	$foursq_save_uid = get_option('foursq_save_uid');
	$foursq_save_vid = get_post_custom_values('foursq_save_vid', $post->ID);

	// If set lets show gallery.
	if ($foursq_save_vid[0]) {
		?>
		<!-- Place this anchor tag where you want the button to go -->
		<a href="https://foursquare.com/intent/venue.html" class="fourSq-widget" data-variant="wide">Save to foursquare</a>
		
		<!-- Place this script somewhere after the anchor tag above. If you have multiple buttons, only include the script once. -->
		<script type='text/javascript'>
		  (function() {
			window.___fourSq = { <?php if($foursq_save_uid) { echo '"uid":"'.$foursq_save_uid.'",'; } ?> "vid": "<?php echo $foursq_save_vid[0]; ?>"};
			var s = document.createElement('script');
			s.type = 'text/javascript';
			s.src = 'http://platform.foursquare.com/js/widgets.js';
			s.async = true;
			var ph = document.getElementsByTagName('script')[0];
			ph.parentNode.insertBefore(s, ph);
		  })();
		</script>
		<?php		
		}
	}
// END PAGE



// META BOX
add_action( 'add_meta_boxes', 'foursq_save_meta_box' );

function foursq_save_meta_box() {
    add_meta_box( 
        'myplugin_sectionid', 
        'Save to foursquare',
        'foursq_save_inner_custom_box',
        'post' 
    );
    
    add_meta_box(
        'myplugin_sectionid',
        'Save to foursquare', 
        'foursq_save_inner_custom_box',
        'page'
    );
}

/* Prints the box content */
function foursq_save_inner_custom_box($post) {
	// wp_nonce_field( basename( __FILE__ ), 'foursq_save_post_class_nonce' );
	
	$v_foursq_save_vid = get_post_custom_values('foursq_save_vid', $post_id);

  	// echo "<pre>" . print_r($gallery_fulltext_value, true) . "</pre>";
  	// The actual fields for data entry
  	echo '<table><tr><td><label>Venue id</label></td><td>';
  	echo '<input type="text" id="foursq_save_vid" name="foursq_save_vid" value="';
  	echo $v_foursq_save_vid[0];
  	echo '" size="40" /></td></tr></table>';
	}

/* Do something with the data entered */
add_action('save_post', 'foursq_save_save_postdata');

/* When the post is saved, saves our custom data */
function foursq_save_save_postdata($post_id) {
 	// If it is our form has not been submitted, so we dont want to do anything
  	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) { return; }

	// verify this came from the our screen and with proper authorization
	// if ( !wp_verify_nonce( $_POST['foursq_save_post_class_nonce'], plugin_basename( __FILE__ ) ) ) { return; }

  	// Check permissions
  	if ( 'page' == $_POST['post_type']) {
    	if ( !current_user_can( 'edit_page', $post_id ) ) { return; }
  		} else {
    	if ( !current_user_can( 'edit_post', $post_id ) ) { return; }
  		}

  	// OK, we're authenticated: we need to find and save the data
	$v_foursq_save_vid = $_POST['foursq_save_vid'];
	
	delete_post_meta($post_id, "foursq_save_vid");
	update_post_meta($post_id, "foursq_save_vid", $v_foursq_save_vid);
	}
	
// END META BOX



// ADMIN
if (is_admin()){
	/* Call the html code */
	add_action('admin_menu', 'foursq_save_admin_menu');

	function foursq_save_admin_menu() {
		add_options_page('Save to foursquare', 'Save to foursquare', 'administrator', 'Save_to_foursquare', 'foursq_admin');
		}
	}

function foursq_admin() { ?>
	<div>
	<h2>Save to foursquare</h2>
	
	<form method="post" action="options.php">
	<?php wp_nonce_field('update-options'); ?>
	<p>Adding your userid will enable attribution when people save the venue.</p>
	
	<table border="0">
		<tr valign="top">
			<th width="100px" align="left" scope="row">Userid</th>
			<td width="400px">
			<input name="foursq_save_uid" type="text" id="foursq_save_uid" value="<?php echo get_option('foursq_save_uid'); ?>" size="10" /></td>
		</tr>		
	</table>
	
	<input type="hidden" name="action" value="update" />
	<input type="hidden" name="page_options" value="foursq_save_uid" />
	
	<p>
	<input type="submit" value="<?php _e('Save Changes') ?>" />
	</p>
	
	</form>
	</div>
	<?php
	}
// END ADMIN
?>
<?php
/*
Plugin Name: Embeditor
Plugin URI: http://directmatchmedia.com/wordpress/plugins/embeditor.zip
Description: Embeditor gives users the option to turn any uploaded image into an embedable image, easily shared on other websites. Perfect for infographics and other viral images, Embeditor creates an enhanced media dialog in the WordPress Image editor to allow insertion of an “Embed This Image” code box below any image used in a post or page.
Version: 1.2.1
Author: Direct Match Media, Inc.
Author URI: http://www.directmatchmedia.com
*/



define( 'EMBEDITOR_VERSION', '1.2.1');
define( 'EMBEDITOR_URL', plugins_url(plugin_basename(dirname(__FILE__)).'/') );
define( 'EMBEDITOR_EMPTY_META_STRING', ' ' );
define( 'EMBEDITOR_POSTMETA_KEY', '_EmbEditor' );
define( 'EMBEDITOR_OPTION', 'EmbEditor' );
define( 'WP_IMAGE_CLASS_NAME_PREFIX', 'wp-image-' );
define( 'WP_ATTACHMENT_CLASS_NAME_PREFIX', 'attachment_' );
define( 'EMU2_I18N_DOMAIN', 'Embeditor' );


/**
*	Initialize the plugin
**/

function embeditor_init() {

	wp_register_script ('embeditor_enable', EMBEDITOR_URL . 'js/embeditor.js', array('jquery'), 1.0, false );
	wp_register_script ('select_all', EMBEDITOR_URL . 'js/select_all.js', array(), 1.0, false);
	
	wp_enqueue_script('jquery');
	
	if ( embeditor_is_media_edit_page( ) ) {
		wp_enqueue_script( 'embeditor_enable');
	}
	
	wp_enqueue_script( 'select_all');
}

add_action('init', 'embeditor_init');

/**
Checks to ensure current page can post media.
**/
function embeditor_is_media_edit_page( ) {
	global $pagenow;
	
	$media_edit_pages = array('post-new.php', 'post.php', 'page.php', 'page-new.php', 'media-upload.php', 'media.php', 'media-new.php');
	return in_array($pagenow, $media_edit_pages);
}

/**
*	Set Options
**/

function set_default_embeditor_options() {
	$options = array(
		'version' => EMBEDITOR_VERSION,
		'install_date' => date( 'Y-m-d' ),
		
		
	);
	$installed_options = get_option( EMBEDITOR_OPTION );
	if ( empty( $installed_options ) ) { // Install plugin for the first time
		add_option( EMBEDITOR_OPTION, $options );
		$installed_options = $options;
	} else if ( !isset( $installed_options['version'] ) ) {
		$installed_options['version'] = $options['version'];
		$installed_options['install_date'] = $options['install_date'];
		update_option( EMBEDITOR_OPTION, $installed_options );
	}
	
}

register_activation_hook(__FILE__, 'set_default_embeditor_options' );

/**
*	Uninstall EmbEditor Options from DB 
**/
function embeditor_uninstall() {
	delete_option(EMBEDITIOR_OPTION);
	
}
//Register the uninstall hook
if ( function_exists('register_uninstall_hook') )
	register_uninstall_hook(__FILE__, 'embeditor_uninstall');
	
	

/**
*	Retrieves Embeditor post_meta data
**/
function get_embeditor_content($post_id) {
	$post = get_post($post_id);
	$content = get_post_meta( $post->ID, EMBEDITOR_POSTMETA_KEY, true );
	return $content;
}

/**
*	Add fields to the media upload page.
**/
function add_embeditor_fields($fields, $post) {
	
	$attachment_id = $post->ID;
	
	//Fetch any existing embeditor data associated with the attachment.
	$existing_content = get_embeditor_content($post);

	//Check to ensure attachment is an image
	$mime_type		= $post->post_mime_type;
	$image_check	= strpos($mime_type,'image');
	
	if ($image_check === false)
			return $fields;

	//Get attachment data.	
	$attach_data	= get_embeditor_attachment($post);
	$attach_size	= embeditor_get_attachment_size($attachment_id);
	$default_width	= $attach_size[1];


	//Create content for Text box if there is no current content.
	if (isset($existing_content['text'])){
			$content_text	= $existing_content['text'];
			$default_width	= $existing_content['size'];
		}else{

			$content_text	= 'This is an example. Check out the Embeditor plugin for more info.';
		}
	
	// get and build upload directory
	$upload_key = wp_upload_dir();
	$upload_url	= $upload_key['baseurl'];
	$image_url	= $upload_url.'/'.$attach_data[0]['file'];
	
	// get existing content variables
	$heading	= (isset($existing_content['heading']) ? $existing_content['heading'] : '');
	$source		= (isset($existing_content['source']) ? $existing_content['source'] : '');
	$ch_yes		= (isset($existing_content['source']) ? 'checked="checked"' : '');
	$ch_no		= (isset($existing_content['source']) ? '' : 'checked="checked"');
		
	//Generate forms
	$radio_options = '<label class="embedit-select"><input type="radio" name="embeditor-choice-'.$post->ID.'" id="attachments-radio['.$post->ID.'][embeditor]" value="Yes" class="embedit-option" '.$ch_yes.'/>&nbsp;Yes</label>';
	$radio_options .= '<label class="embedit-select"><input type="radio" name="embeditor-choice-'.$post->ID.'" id="attachments-radio['.$post->ID.'][embeditor]" value="No" class="embedit-option" '.$ch_no.'/>&nbsp;No</label>';
	
	$html1 = '<input id="attachments-heading['.$attachment_id.'][embeditor]" value="'.$heading.'" class="text" size="50" name="embeditor-heading-'.$attachment_id.'" />';
	$html2 = '<input id="attachments-source['.$attachment_id.'][embeditor]" value="'.$source.'" class="text" size="50" name="embeditor-source-'.$attachment_id.'" />';	
	$html3 = '<textarea id="attachments-text['.$attachment_id.'][embeditor]" class="embeditor-input" cols="50" rows="5" name="embeditor-text-'.$attachment_id.'" >'.$content_text.'</textarea>';
	$html4 = '<input id="attachments-size['.$attachment_id.'][embeditor]{'.$attachment_id.'}" class="embeditor-input" size="15" value="'.$default_width.'" name="embeditor-size-'.$attachment_id.'" />';
	$html4.= '<input type="hidden" value="'.$image_url.'" name="embeditor-image-'.$attachment_id.'" id="embeditor-image['.$attachment_id.'][embeditor]" >';
	$html4.= '<input type="hidden" value="'.$attach_data[0]['width'].'" name="embeditor-width-'.$attachment_id.'" id="attachments-width['.$attachment_id.'][embeditor]" >';
	$html4.= '<input type="hidden" value="'.$attach_data[0]['height'].'" name="embeditor-height-'.$attachment_id.'" id="attachments-height['.$attachment_id.'][embeditor]" >';	
	$html4.= '<input type="hidden" value="'.$attachment_id.'" name="embeditor-att-id-'.$attachment_id.'" id="attachments-att-id['.$attachment_id.'][embeditor]" >';
	//Call to jQuery script and to pass the current $post ID
	$html4.= '<input type="hidden" class="img_s_def_'.$attachment_id.'" value="'.get_option('thumbnail_size_w').'" >';
	$html4.= '<input type="hidden" class="img_m_def_'.$attachment_id.'" value="'.get_option('medium_size_w').'">';
	$html4.= '<input type="hidden" class="img_l_def_'.$attachment_id.'" value="'.get_option('large_size_w').'">';		
	$html4.= '
	<script type="text/javascript">
		jQuery(document).ready(function($) {
			EnableSelections('.$attachment_id.');
		});
	</script>
	';


	$fields['embeditor-radio'] = array(
		'label' => __('Display Embed Code'),
		'input' => 'html',
		'html'	=> $radio_options
		);
	
	$fields['embeditor-embed'] = array(
		'label'	=> __('Embed Heading'),
		'input'	=> 'html',
		'html'	=> $html1
		);
	$fields['embeditor-embed2'] = array(
		'label' => __('Embed Source'),
		'input' => 'html',
		'html'	=> $html2
		);
	$fields['embeditor-embed3'] = array(
		'label' => __('Embed Text'),
		'input' => 'html',
		'helps' => 'Note: URLs will not display properly in a textarea field.',
		'html'	=> $html3
		);
	$fields['embeditor-embed4'] = array(
		'label'	=> __('Embed Box Width'),
		'input' => 'html',
		'helps' => 'Width in px',
		'html'	=> $html4
		);
	
	return $fields;
}


add_filter('attachment_fields_to_edit', 'add_embeditor_fields', 10, 2);

/**
*	Retrieve attachment meta data
**/
function get_embeditor_attachment($post){
	$attach_meta = get_post_meta($post->ID, '_wp_attachment_metadata');
	
	return $attach_meta;
}

/**
*	Get the attachment 'medium' size
**/
function embeditor_get_attachment_size($attachment){
	$chars = wp_get_attachment_image_src($attachment, 'medium');
	return $chars;
}



/**
*	Save Embeditor content to the postmeta db.
**/
function embeditor_save_postmeta_content($post){
//	$attachment = get_embeditor_attachment($post);
	
	if ($_POST["embeditor-choice-{$post['ID']}"] == "No")
		return;
	
	if ($_POST["embeditor-choice-{$post['ID']}"] == "Yes") {
		
		$embeditor_heading	= esc_html($_POST["embeditor-heading-{$post['ID']}"]);
		$embeditor_source	= esc_html($_POST["embeditor-source-{$post['ID']}"]);
		$embeditor_text		= esc_html($_POST["embeditor-text-{$post['ID']}"]);
		$embeditor_image	= $_POST["embeditor-image-{$post['ID']}"];
		$embeditor_size		= $_POST["embeditor-size-{$post['ID']}"];
		$embeditor_width	= $_POST["embeditor-width-{$post['ID']}"];
		$embeditor_height	= $_POST["embeditor-height-{$post['ID']}"];
		$embeditor_att_id	= $_POST["embeditor-att-id-{$post['ID']}"];

		// create array for meta update
		$embeditor_db_content['heading'] = $embeditor_heading;
		$embeditor_db_content['source']  = $embeditor_source;
		$embeditor_db_content['text']    = $embeditor_text;
		$embeditor_db_content['image']   = $embeditor_image;
		$embeditor_db_content['size']    = $embeditor_size;
		$embeditor_db_content['width']   = $embeditor_width;
		$embeditor_db_content['height']  = $embeditor_height;
		$embeditor_db_content['att_id']  = $embeditor_att_id;
	}

		
	update_post_meta($embeditor_att_id ,EMBEDITOR_POSTMETA_KEY, $embeditor_db_content);
}



add_filter('attachment_fields_to_save', 'embeditor_save_postmeta_content', 10, 1);


/**
*	Shortcode to generate the EmbEditor Box
**/
function embeditor_short_code($atts, $content = NULL){
	global $post;
	
	extract(shortcode_atts( array(
		'source'	=> '',
		'heading'	=> 'Embed This Image',
		'text'		=> '',
		'image'		=> '',
		'size'		=> '300',	
		'width'		=> '',
		'height'	=> '',
	),$atts ));
	
	//remove any characters that could skew the size of the embed box.
	$size =  preg_replace('/[^0-9]/i', '',$size);
	
	$emb_box_build = '<div class="embeditor-embed">'.esc_attr($heading).'<br />';
	$emb_box_build .= '<textarea class="embed-code" style="background-color: #F7F7F7;width:'.esc_attr($size).'px;" name="embeditor-text" wrap="virtual" readonly="readonly" id="embeditor-text-'.$post->ID.'" onclick="SelectAll(\'embeditor-text-'.$post->ID.'\');" cols="'.esc_attr($width).'" rows="3">';
	$emb_box_build .= '<a href="'.esc_url($source).'" title="'.esc_attr($heading).'" target="_blank">';
	$emb_box_build .= '<img src="'.esc_url($image).'" width="'.esc_attr($width).'" height="'.esc_attr($height).'" alt="'.esc_attr($heading).'" title="'.esc_attr($heading).'">'; 
	$emb_box_build .= '</a>';
//	$emb_box_build .= '<br />'.esc_textarea($text);
	$emb_box_build .= '<p>'.$text.'</p>';
	$emb_box_build .= '</textarea></div>';
	
	return $emb_box_build;
	}
add_shortcode('embeditor-box', 'embeditor_short_code');


/**
*	Send shortcode to the editor
**/
function embeditor_send_shortcode_to_editor($html, $attachment_id){
	
	$short_content	= get_embeditor_content($attachment_id);
	$heading		= $short_content['heading'];
	$source			= $short_content['source'];
	$image			= $short_content['image'];	
	$text			= $short_content['text'];
	$size			= $short_content['size'];	
	$width			= $short_content['width'];
	$height			= $short_content['height'];
//	$attach_id		= $short_content['attach_id'];
	$embed_codebox	= $html.'[embeditor-box image="'.$image.'" source="'.$source.'" size="'.$size.'" heading="'.$heading.'" text="'.$text.'" width="'.$width.'" height="'.$height.'"][/embeditor-box]';

	//If $source is null, don't generate the shortcode. 
	if ($source == ''){
		return $html;
	}
		
	return apply_filters('embeditor_short_code', $embed_codebox, $html);
}
add_filter('image_send_to_editor', 'embeditor_send_shortcode_to_editor', 10, 2);

//Load CSS
function embeditor_css(){
	wp_enqueue_style( 'embeditor-input', EMBEDITOR_URL . 'css/embeditor.css', array(), 1.0, 'all');
}


add_action('wp_enqueue_scripts', 'embeditor_css');
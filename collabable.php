<?php
/*
Plugin Name: Collabable
Plugin URI: http://www.collabable.com/documentation/wordpress
Description: Integrate Collabable Discussion Source into your Wordpress site
Version: 0.1
Author: Sergeant Major
Author URI: http://www.ablebots.com
License: GPL2
*/

/*  Copyright 2011  Sergeant Major <major@ablebots.com>

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

add_action("admin_menu", "create_options_page");
add_action("wp_footer", "output_modal_form");
add_action("plugins_loaded", "collabable_init");


 
function collabable_init() {
  register_sidebar_widget(__('Collabable Form'), 'collabable_widget');
}

function collabable_form_query_string() {
  $arrOptions = array();
  
  if($bg_color = get_option("collabable_form_background_color"))
    $arrOptions[] = "background_color=" . $bg_color;
  
  if($fg_color = get_option("collabable_form_foreground_color"))
    $arrOptions[] = "foreground_color=" . $fg_color;
  
  if($width = get_option("collabable_form_width"))
    $arrOptions[] = "form_width=" . $width;
  
  if($title = get_option("collabable_form_title"))
    $arrOptions[] = "form_title=" . $title;
  
  if(get_option("collabable_form_https"))
    $arrOptions[] = "https=true";
  
  
  
  return implode("&", $arrOptions);
}

function collabable_generate_form($modal = false) {
  
  $protocol = (get_option("collabable_form_https")) ? "https" : "http";
  
  $str =  "<script type='text/javascript' src='{$protocol}://www.collabable.com/discussion_sources/";
  $str .= get_option("collabable_form_id") . '/form.js?';
  
  if($modal)
    $str .= 'modal=true&';
    
  $str .= collabable_form_query_string();
    
  $str .= "'></script>";
  
  return $str;
}

function collabable_widget() {
  print collabable_generate_form();
}

function create_options_page() {
  add_options_page('Collabable Options', 'Collabable', 'manage_options', 'collabable', 'options_page_content');
}

function options_page_content() {
  
  /* Assignments */
  $form_id = "collabable_form_id";
  $enable_modal = "collabable_form_enable_modal";
  $bg_color = "collabable_form_background_color";
  $fg_color = "collabable_form_foreground_color";
  $width = "collabable_form_width";
  $title = "collabable_form_title";
  $https = "collabable_form_https";
  
  /* Perm */
  if (!current_user_can('manage_options')) wp_die( __('You do not have sufficient permissions to access this page.') );
  
  /* Submit */
  if(!empty($_POST)) {
    $success = true;
    update_option( $form_id, $_POST[$form_id] );    
    if(isset($_POST[$enable_modal]))
      update_option( $enable_modal, 1 );    
    else
      delete_option($enable_modal);
    
    update_option( $bg_color, $_POST[$bg_color] );    
    update_option( $fg_color, $_POST[$fg_color] );    
    update_option( $width, $_POST[$width] );    
    update_option( $title, $_POST[$title] ); 
    if(isset($_POST[$https]))
      update_option($https, 1);
    else
      delete_option($https);
  }
  
  ?>
    <style type="text/css">.form_item { margin: 0 2em 2em; clear: both; } .form_item label { float: left; text-align: right; width: 125px; margin-right: 1.5em; line-height: 2em; }</style>
    <div class="wrap">
      <h2>Collabable</h2>
      <p>Configure your discussion sources</p>
      <?php if($success): ?>
      <div class="updated"><p><strong>Your settings have been saved</strong></p></div>
      <?php endif; ?>

      <form name="form1" method="post" action="">
    
      <h3>Contact Form</h3>
      <div class="form_item">
        <label>Enabled?</label>
        <input type="checkbox" name="<?php print $enable_modal; ?>" value="modal"<?php checked( get_option($enable_modal) ); ?> /> Show form in popup window
        <small><br />Will not appear on pages displaying the widget form</small>
      </div><br/>

      <div class="form_item">
        <label>Form Id</label>
        <input type="text" name="<?php echo $form_id ?>" value="<?php echo get_option($form_id) ?>" size="33">
      </div>
      <div class="form_item">
        <label>Background Color</label>
        <input type="text" name="<?php print $bg_color; ?>" value="<?php print get_option($bg_color); ?>" size="6" />
        <small>Example: "ff0000" or "red"</small>
      </div>
      <div class="form_item">
        <label>Foreground Color</label>
        <input type="text" name="<?php print $fg_color; ?>" value="<?php print get_option($fg_color); ?>" size="6" />
        <small>Example: "ffffff" or "white"</small>
      </div>
      <div class="form_item">
        <label>Width</label>
        <input type="text" name="<?php print $width; ?>" value="<?php print get_option($width); ?>" size="3" />px
      </div>
      <div class="form_item">
        <label>Title</label>
        <input type="text" name="<?php print $title; ?>" value="<?php print get_option($title); ?>" size="33" />
      </div>
      <div class="form_item">
        <label>Secure Connection?</label>
        <input type="checkbox" name="<?php print $https; ?>" value="1" <?php if(get_option($https)): ?>checked<?php endif; ?>/> Enable HTTPS
      </div>
    
      <p class="submit">
        <input type="submit" name="Submit" class="button-primary" value="<?php esc_attr_e('Save Changes') ?>" />
      </p>

      </form>
    </div>

    <?php
}

function output_modal_form() {
  /* Quit if you can't find the form ID or it's not a modal form */
  if(get_option("collabable_form_id") && get_option("collabable_form_enable_modal"))
    print collabable_generate_form(true);
}
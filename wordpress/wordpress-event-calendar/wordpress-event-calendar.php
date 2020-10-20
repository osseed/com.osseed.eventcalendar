<?php

/*
Plugin Name: Wordpress Event Calendar
Plugin URI:
Description: CiviCRM Calendar Plugin
Author: OSSeed
Version: 4.4
Author URI:
License: AGPL3
*/

// This file must not accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

// adds the Event Calendar button to post and page screens.
add_action( 'media_buttons_context',  'event_calendar_add_form_button' );

// adds the HTML triggered by the button above.
add_action( 'admin_footer', 'event_calendar_add_form_button_html' );

// register the Event Calendar shortcode.
add_shortcode( 'event_calendar', 'event_calendar_shortcode_handler' );

// invoke CiviCRM when a shortcode is detected in the post content.
add_action('wp', 'event_calendar_shortcode_includes');


function event_calendar_add_form_button($context) {

  if (!civicrm_initialize()) {
    return;
  }
  $config  = CRM_Core_Config::singleton();
  $imageBtnURL = $config->resourceBase . 'i/smallLogo.png';

  // append the icon.
  $context .= '<a href="#TB_inline?width=480&inlineId=calendar_frontend_pages" class="button thickbox" id="add_civi" style="padding-left: 4px;" title="' . __( 'Add CiviCRM Event Calendar Public Pages', 'civicrm-wordpress' ) . '"><img src="' . $imageBtnURL . '" height="15" width="15" alt="' . __( 'Add CiviCRM Event Calendar Public Pages', 'civicrm-wordpress' ) . '" />Event Calendar</a>';

  return $context;
}

function event_calendar_add_form_button_html() {
  // get screen object.
  $screen = get_current_screen();

  // only add on edit page for default WP post types.
  if ( $screen->base == 'post' && ( $screen->id == 'post' || $screen->id == 'page' ) && ( $screen->post_type == 'post' || $screen->post_type == 'page' ) ) {
    $title = __( 'Please select a CiviCRM front-end page type.', 'civicrm-wordpress' );
    ?>
    <script type="text/javascript">
      jQuery(function ($) {
        $('#eventcalendar-wp-insert-shortcode').on('click', function () {
          var form_id = $("#calendar_civicomponent_id").val();
          if (form_id == "") {
          alert('Please select a frontend element.');
          return;
        }

        var component = $("#calendar_civicomponent_id").val();
        var shortcode = '[event_calendar component="' + component + '"';
        switch (component) {
          case 'event-calendar':
          break;
        }
        shortcode += ' id="<<your calendar id here>>"]';
        window.send_to_editor(shortcode);
      });
    });
  </script>
  <div id="calendar_frontend_pages" style="display:none;">
    <div class="wrap">
      <div>
        <div style="padding:15px 15px 0 15px;">
          <h3 style="color:#5A5A5A!important; font-family:Georgia,Times New Roman,Times,serif!important; font-size:1.8em!important; font-weight:normal!important;">
          <?php echo $title; ?>
          </h3>
          <span>
            <?php echo $title; ?>
          </span>
        </div>
        <div style="padding:15px 15px 0 15px;">
          <select id="calendar_civicomponent_id">
            <option value=""><?php _e( 'Select a frontend element.', 'civicrm-wordpress' ); ?></option>
            <option value="event-calendar"><?php _e( 'Event Calendar', 'civicrm-wordpress' ); ?></option>
          </select>
        </div>
        <div style="padding:15px;">
          <input type="button" class="button-primary" value="Insert Form" id="eventcalendar-wp-insert-shortcode"/>&nbsp;&nbsp;&nbsp;
          <a class="button" style="color:#bbb;" href="#" onclick="tb_remove(); return false;"><?php _e( 'Cancel', 'civicrm-wordpress' ); ?></a>
        </div>
      </div>
    </div>
  </div>
  <?php

  }
}

function event_calendar_shortcode_handler($atts) {
  $component = $atts['component'];
  if ($component == 'event-calendar') {
    extract(shortcode_atts(array(
      'component' => 'event-calender',
      'action' => NULL,
      'mode' => NULL,
      'id' => 'id',
      'cid' => NULL,
      'gid' => NULL,
      'cs' => NULL,
      ),
      $atts
    ));

    $args = array(
      'reset' => 1,
      'id' => $atts['id'],
    );
    civicrm_initialize();
    $args['q'] = 'civicrm/showevents';

    foreach ( $args as $key => $value ) {
      if ( $value !== NULL ) {
        $_REQUEST[$key] = $_GET[$key] = $value;
      }
    }

    // Call wp_frontend with $shortcode param.
    if (class_exists('CiviCRM_For_WordPress')) {
      ob_start(); // start buffering
      civi_wp()->invoke();
      $content = ob_get_clean(); // save the output and flush the buffer
      return $content;
    }
    else {
      return civicrm_wp_frontend(TRUE);
    }
  }
}

function event_calendar_shortcode_includes() {
  global $post;

  // don't parse content when there's no post object, eg on 404 pages.
  if ( ! is_object( $post ) ) return;

  // check for existence of shortcode in content.
  if ( preg_match( '/\[event_calendar/', $post->post_content ) ) {
    if (!civicrm_initialize()) {
      return;
    }
    // do we have functionality provided by plugin version 4.6+ present?
    $civi = civi_wp();
    if (method_exists($civi, 'front_end_page_load')) {
      // add core resources for front end
      add_action('wp', array($civi, 'front_end_page_load'), 100);
    } else {
      // add CiviCRM core resources.
      CRM_Core_Resources::singleton()->addCoreResources();
      $config = CRM_Core_Config::singleton();
      $config->userFrameworkFrontend = $front_end;
    }
  }
}

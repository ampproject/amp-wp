<?php

add_action( 'admin_init', 'amp_register_settings' );
add_action( 'admin_menu', 'amp_custom_admin_menu' );

/**
 * Register the settings
 */
function amp_register_settings() {
     register_setting(
          'amp_options',  // settings section
          'amp_canonical' // setting name
     );
}

/**
 * Add the options page
 */
function amp_custom_admin_menu() {
    add_options_page(
        'AMP',
        'AMP',
        'manage_options',
        'amp-plugin',
        'amp_options_page'
    );
}


/**
 * Build the options page
 */
function amp_options_page() {
     if ( ! isset( $_REQUEST['settings-updated'] ) )
          $_REQUEST['settings-updated'] = false; ?>

     <div class="wrap">

          <?php if ( false !== $_REQUEST['settings-updated'] ) : ?>
               <div class="updated fade"><p><strong><?php _e( 'AMP Options saved!', 'amp' ); ?></strong></p></div>
          <?php endif; ?>

          <h2><?php echo esc_html( get_admin_page_title() ); ?></h2>

          <div id="poststuff">
               <div id="post-body">
                    <div id="post-body-content">
                         <form method="post" action="options.php">
                              <?php settings_fields( 'amp_options' ); ?>
                              <?php $amp_canonical = get_option( 'amp_canonical' ); ?>
                              <table class="form-table">
                                   <tr valign="top"><th scope="row"><?php _e( 'AMP Generation Mode (Beta)', 'amp' ); ?></th>
                                        <td>
                                          <?php $selected = intval($amp_canonical); ?>
                                          <fieldset>
                                            <legend class="screen-reader-text"><span><?php _e( 'Mode', 'amp' ); ?></span></legend>
                                            <p>
                                              <label><input name="amp_canonical" type="radio" value="1" <?php checked( $selected, 1 ); ?> > Standalone</label>
                                              <br>
                                              <label><input name="amp_canonical" type="radio" value="0" <?php checked( $selected, 0 ); ?> > Paired</label>
                                            </p>
                                            <p><?php _e( 'Toggles whether the plugin generates paired AMP pages, or makes the whole site use AMP (similar to any JS library).', 'amp' ); ?></p>
                                            <p class="description"><?php _e( 'Note: Standalone mode is experimental and requires a supporting theme and widgets. Currently only applies to single posts.', 'amp' ); ?> <a href="https://github.com/Automattic/amp-wp#standalone-mode"><?php _e( 'Learn more.', 'amp' ); ?></a></p>
                                          </fieldset>
                                        </td>
                                   </tr>
                              </table>

                              <p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes"></p>
                         </form>
                    </div> <!-- end post-body-content -->
               </div> <!-- end post-body -->
          </div> <!-- end poststuff -->
     </div>
     <?php
}

?>
<?php

//Init settings
function ccb_api_settings_init(){

    add_settings_section(
        'ccb_api_section',
        'Credentials for user with API access',
        'api_section_cb',
        'ccb_api'
    );

    add_settings_field('ccb_api_username', 'API Username', 'api_settings_field_cb', 'ccb_api', 'ccb_api_section', ['label_for' => 'ccb_api_username'] );
    add_settings_field('ccb_api_password', 'API Password', 'api_settings_field_cb', 'ccb_api', 'ccb_api_section', ['label_for' => 'ccb_api_password'] );
    add_settings_field('ccb_api_base_url', 'API Base URL', 'api_settings_field_cb', 'ccb_api', 'ccb_api_section', ['label_for' => 'ccb_api_base_url'] );
    //add_settings_field('ccb_api_cache_length', 'Cache Refresh Frequency', 'cache_time_field_cb', 'ccb_api', 'ccb_api_section', ['label_for' => 'ccb_api_cache_length'] );

    register_setting('ccb_api', 'ccb_api_username');
    register_setting('ccb_api', 'ccb_api_password');
    register_setting('ccb_api', 'ccb_api_base_url');
    //register_setting('ccb_api', 'ccb_api_cache_length');
}

add_action( 'admin_init', 'ccb_api_settings_init' );


// Display section header
function api_section_cb($args)
{
    // (Probably unnecessary info, but hopefully can save 30 minutes of future confusion if the url gets deleted by accident)
    ?>
    <p>Base URL should be "https://wellspringchristian.ccbchurch.com"</p>
    <?php
}

// Display callback for the settings
function api_settings_field_cb($args)
{
    // get the value of the setting we've registered with register_setting()
    $setting = get_option($args['label_for']);
    // output the field
    ?>
    <input type="text" name="<?php echo $args['label_for']?>" value="<?php echo isset( $setting ) ? esc_attr( $setting ) : ''; ?>">
<?php
}

// Display callback for the cache setting
function cache_time_field_cb($args)
{
    // get the value of the cache setting
    $setting = get_option($args['label_for']);
    // output it like the others, but with explanatory text
    ?>
    <p>Number of minutes before refreshing cache. (Higher number means faster page loading, but any new forms will take up to X minutes to appear on the site.)</p>
    <input type="text" name="<?php echo $args['label_for']?>" value="<?php echo isset( $setting ) ? esc_attr( $setting ) : ''; ?>">
<?php
}


// Render the page
function ccb_options_page_html() {
    // check user capabilities
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    if ( isset( $_GET['settings-updated'] ) ) {
        // add settings saved message with the class of "updated"
        add_settings_error( 'ccb_api_messages', 'ccb_api_message', __( 'Settings Saved', 'ccb_api' ), 'updated' );
    }

    // show error/update messages
    settings_errors( 'ccb_api_messages' );

    ?>
    <div class="wrap">
        <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
        <form action="options.php" method="post">
            <?php
            // output WP fields for the registered setting "ccb_api"
            settings_fields( 'ccb_api' );
            // output setting sections and their fields
            do_settings_sections( 'ccb_api' );
            // output save settings button
            submit_button('Save Settings');
            ?>
        </form>
    </div>
    <?php
}

// Bind the new page
function ccb_options_page()
{
    $hookname = add_options_page(
        'Church Community Builder API Options',
        'Church Community Builder API Options',
        'manage_options',
        'ccb_api',
        'ccb_options_page_html'
    );
}

add_action('admin_menu', 'ccb_options_page');
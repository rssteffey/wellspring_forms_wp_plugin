<?php

function lifegroups_options_page_html() {
    // check user capabilities
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    ?>
    <div class="wrap">
        <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
        <form action="options.php" method="post">
            <?php
            // output security fields for the registered setting "lifegroups_api_options"
            settings_fields( 'lifegroups_api_options' );
            // output setting sections and their fields
            // (sections are registered for "lifegroups_api", each field is registered to a specific section)
            do_settings_sections( 'lifegroups_api' );
            // output save settings button
            submit_button( __( 'Save Settings', 'textdomain' ) );
            ?>
        </form>
    </div>
    <?php
}

function lifegroups_options_page()
{
    $hookname = add_options_page(
        'Church Community Builder API Options',
        'Church Community Builder API Options',
        'manage_options',
        'wellspring_forms',
        'lifegroups_options_page_html'
    );
    add_action( 'load-' . $hookname, 'lifegroups_options_page_html_submit' );
}

add_action('admin_menu', 'lifegroups_options_page');
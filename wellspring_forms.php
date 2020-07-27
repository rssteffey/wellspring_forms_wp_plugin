<?php
/**
 * Plugin Name: Wellspring Forms List
 * Description: Uses the CCB API to retrieve and display a list of currently open Wellspring Forms
 * Author: Shawn Steffey
 * Version: 1.0
 */

// Disclaimer: Shawn has never written a Wordpress plugin before.
// Please judge with compassion; I also don't want me to be doing what I'm doing


defined( 'ABSPATH' ) or die( 'Direct access is forbidden' );

register_activation_hook( __FILE__, 'wellspring_forms_activate_plugin' );

register_deactivation_hook( __FILE__, 'wellspring_forms_deactivate_plugin' );

add_filter('widget_text', 'do_shortcode');


// Admin check (in case that wasn't obvious)
if ( is_admin() ) {
    require_once __DIR__ . '/admin/wellspring_forms_admin.php';
}



if(!class_exists('wellspring_forms')) {
    class wellspring_forms
    {
        // Singleton, because I have no idea what cruft WP does during a lifecycle
        static $instance = false;

        public static function getInstance() {
            if ( !self::$instance )
                self::$instance = new self;
            return self::$instance;
        }

        private function __construct() {
            add_action('init', array($this, 'wellspring_forms_plugin_shortcode_init'));
            $this->display();
        }

        // Main function to interact with the CCB API
        public function retrieve_forms()
        {
            try {
                $base_url = get_option('ccb_api_base_url');
                $username = get_option('ccb_api_username');
                $password = get_option('ccb_api_password');
                // We stored the timeout in minutes, and we need seconds
                $cache_expiration = get_option('ccb_api_cache_length') * 60;
                $basicAuth = "Basic " . base64_encode($username . ":" . $password);
                $headers = array('Authorization' => $basicAuth, '');

                // Try the cache first
                $cached_data = get_transient('ccb_api_forms');

                // If cache is stale, let's make the API call
                if($cached_data === false) {
                    $forms_api_response = wp_remote_get($base_url . "/api.php?srv=form_list&include_archived=false", array('headers' => $headers));
                    $forms_body = wp_remote_retrieve_body($forms_api_response);
                    $new = simplexml_load_string($forms_body);
                    $con = json_encode($new);

                    //Quick and dirty check so we don't cache error responses. If this fails, we throw an Exception and skip cache set
                    $dummyParseTest = json_decode($con, true)["response"]["items"]["form"];

                    //Update cache
                    set_transient('ccb_api_forms', $con, $cache_expiration);
                } else {
                    $con = $cached_data;
                }

                $newArr = json_decode($con, true)["response"]["items"]["form"];

                return($newArr);
            } catch(Exception $e) {
                // I realize my error handling setup is very ... not well thought-out.
                // But I'm out of weekend.  This should all cover most cases, albeit awkwardly and with assumptions about empty arrays
                return([]);
            }

            return([]);

        }

        public function display(){
            //wp_enqueue_style('wellspring_forms.css', plugin_dir_url(__FILE__) . '/wellspring_forms.css');
        }

        // Widget

        //Corresponding shortcode setup
        public function wellspring_forms_plugin_shortcode_init()
        {
            add_shortcode('wellspring_forms', array($this, 'wf_shortcode'));
        }

        public function wf_shortcode($atts = [], $content = null, $tag = ''){
            // normalize attribute keys, lowercase
            $atts = array_change_key_case((array)$atts, CASE_LOWER);

            $forms_array = $this->retrieve_forms();

            // override default attributes with user attributes
            $wf_atts = shortcode_atts([
                'title' => 'Forms',
            ], $atts, $tag);

            // start output
            $o = '';

            // start div
            $o .= '<div class="wellspring_forms-widget-box">';
            $o .= '<h2>' . $wf_atts['title'] . '</h2>';

            if(is_array($forms_array) || is_object($forms_array)) {
                // Empty array *probably* means we hit our error case above. But we know what they say when we assume...
                if(count($forms_array) == 0){
                    $o .= "Error fetching forms";
                }

                // If we have stuff, print out each link
                foreach ($forms_array as $form_item) {
                    if ($form_item["status"] == "Available" && $form_item["public"] == "true") {
                        $o .= '<a class="forms-list-link" href="' . $form_item["url"] . '">' . $form_item["title"] . '</a></br>';
                    }
                }
            } else{
                // Not positive this is hittable, but without my usual QA team, I'm not risking it
                $o .= "Error parsing forms response.";
            }

            $o .= '</div>';

            // return that div
            return $o;
        }
    }
} else{
    return "Plugin name collision.  Uh oh.";
}

$wellspring_forms = wellspring_forms::getInstance();

// Probably need to put things here? Nothing pertinent has come up yet though...
function wellspring_forms_activate_plugin(){

}

// Unregister those DB settings
function wellspring_forms_deactivate_plugin(){
    if(is_admin()){
        delete_option('ccb_api_username');
        delete_option('ccb_api_password');
        delete_option('ccb_api_base_url');
        delete_option('ccb_api_cache_length');
        delete_transient('ccb_api_forms');
    }
}
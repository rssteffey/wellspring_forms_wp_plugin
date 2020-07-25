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

//register_activation_hook( __FILE__, 'wellspring_forms_activate_plugin' );

//register_deactivation_hook( __FILE__, 'wellspring_forms_deactivate_plugin' );

add_filter('widget_text', 'do_shortcode');


// Admin check (in case that wasn't obvious)
if ( is_admin() ) {
    require_once __DIR__ . '/admin/lifegroups-admin.php';
}



if(!class_exists('wellspring_forms')) {
    class wellspring_forms
    {
        // Singleton, because I have no idea what all WP calls during a lifecycle
        static $instance = false;

        public static function getInstance() {
            if ( !self::$instance )
                self::$instance = new self;
            return self::$instance;
        }

        private function __construct() {
            add_action('init', array($this, 'wellspring_forms_plugin_shortcode_init'));
        }

        public function retrieve_forms()
        {
            $conf_path = plugin_dir_path(__file__)."/config.json";
            $jsonString = file_get_contents($conf_path);
            $config = json_decode($jsonString, true);

            $username = $config["username"];
            $password =  $config["password"];
            $basicAuth = "Basic ".base64_encode($username.":".$password);
            $headers = array('Authorization' => $basicAuth);
            $lg_api_response = wp_remote_get( $config["api_base_url"] . "/api.php?srv=form_list", array('headers' => $headers));


            //TESTING UNTIL I CAN GET MY CREDENTIALS
            $path = plugin_dir_path(__file__)."/test_resp.xml";
            $xmlfile = file_get_contents($path);
            $new = simplexml_load_string($xmlfile);
            $con = json_encode($new);
            $newArr = json_decode($con, true);

            //echo(print_r($newArr));
            return($newArr);

            // ADD ERROR HANDLING AND RETURN EMPTY ARRAY IF SO


            //echo(print_r($lg_api_response['body']));
        }

        public function display(){
            $style_path = plugins_url( 'wellspring_forms.css', _FILE_ );
            wp_enqueue_style($style_path);
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

            $forms_array = $this->retrieve_forms()["response"]["items"]["form"];
            print_r($forms_array);

            // override default attributes with user attributes
            $wf_atts = shortcode_atts([
                'title' => 'Forms',
            ], $atts, $tag);

            // start output
            $o = '';

            // start box
            $o .= '<div class="wellspring_forms-widget-box">';

            // title
            $o .= '<h2>' . $wf_atts['title'] . '</h2>';

            foreach($forms_array as $form_item){
                $o .= '<a href="'.$form_item["url"].'">' . $form_item["title"] . '</a></br>';
            }

            // end box
            $o .= '</div>';

            // return output
            return $o;
        }
    }
} else{
    return "Plugin name collision.  Uh oh.";
}

echo("\n"."Existence: " . shortcode_exists("wellspring_forms") ."\n");
$wellspring_forms = wellspring_forms::getInstance();

// Probably need to put things here
function wellspring_forms_activate_plugin(){

}

function wellspring_forms_deactivate_plugin(){

}
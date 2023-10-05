<?php
/*
* Plugin Name: RapidText AI Text Block
* Description: Add an AI-powered text block using RapidTextAI.com to WP Bakery.
* Version: 1.0
* Author: Rapidtextai.com
* Text Domain: rapidtextai
*/


function rapidtextai_is_wp_bakery_active() {
    return class_exists('Vc_Manager');
}

function rapidtextai_is_elementor_active() {
    return class_exists('\Elementor\Plugin');
}


function rapidtexiai_settings_menu() {
    add_menu_page(
        'rapidtexiai Settings',
        'rapidtexiai Settings',
        'manage_options',
        'rapidtexiai-settings',
        'rapidtexiai_settings_page'
    );
}
add_action('admin_menu', 'rapidtexiai_settings_menu');



function rapidtexiai_settings_page() {
    if (!current_user_can('manage_options')) {
        return;
    }

    // Check if the form was submitted and the nonce is valid
    if (isset($_POST['rapidtexiai_api_key_nonce']) && wp_verify_nonce($_POST['rapidtexiai_api_key_nonce'], 'rapidtexiai_api_key_nonce')) {
        // Sanitize and save the API key
        $api_key = sanitize_text_field($_POST['rapidtexiai_api_key']);
        update_option('rapidtexiai_api_key', $api_key);
    }

    // Retrieve the current API key
    $current_api_key = get_option('rapidtexiai_api_key', '');

    ?>
    <div class="wrap">
        <h2>rapidtexiai Settings</h2>
        <form method="post">
            <?php wp_nonce_field('rapidtexiai_api_key_nonce', 'rapidtexiai_api_key_nonce'); ?>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="rapidtexiai_api_key">rapidtexiai API Key:</label></th>
                    <td>
                        <input type="text" id="rapidtexiai_api_key" name="rapidtexiai_api_key" value="<?php echo esc_attr($current_api_key); ?>" class="regular-text">
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}



/**
 * WP Bakery
 */
if(rapidtextai_is_wp_bakery_active()){
    function rapidtexiai_ai_text_block_vc_element() {
        vc_map(array(
            'name' => __('AI Text Block', 'rapidtexiai-ai-text-block'),
            'base' => 'rapidtexiai_ai_text_block',
            'category' => __('Content', 'rapidtexiai-ai-text-block'),
            'params' => array(
                array(
                    'type' => 'textarea',
                    'heading' => __('Input Text', 'rapidtexiai-ai-text-block'),
                    'param_name' => 'input_text',
                    'description' => __('Enter the prompt to generate AI text, i.e Write an about use section for my company which manufacture light bulbs', 'rapidtexiai-ai-text-block'),
                ),
            ),
            'shortcode' => 'rapidtexiai_ai_text_block_shortcode',
        ));
    }
    add_action('vc_before_init', 'rapidtexiai_ai_text_block_vc_element');

    function rapidtexiai_ai_text_block_shortcode($atts, $content = null,$instance_id) {
        extract(shortcode_atts(array(
            'input_text' => '',
        ), $atts));

        // Call the rapidtexiai API here to generate text using $input_text
        // Replace this with your rapidtexiai API integration code
        $postid = get_the_ID();
        $generated_text = rapidtextai_generate_text($input_text,$postid,$instance_id); // Store the generated text

        // Output the generated text
        return $generated_text;
    }
    add_shortcode('rapidtexiai_ai_text_block', 'rapidtexiai_ai_text_block_shortcode');
}



/***
 * Elementor
 */
if(rapidtextai_is_elementor_active()){
    function register_drcalcwidget_widget( $widgets_manager ) {

        
        $widgets_manager->register( new \Elementor_drcalcwidget_Widget_Widget() );
        class rapidtexiai_AITextBlock_Elementor_Widget extends \Elementor\Widget_Base {

            public function get_name() {
                return 'rapidtexiai-ai-text-block';
            }

            public function get_title() {
                return __('AI Text Block', 'rapidtexiai-ai-text-block-elementor');
            }

            public function render() {
                $settings = $this->get_settings_for_display();

                // Get the input text from Elementor widget settings
                $input_text = $settings['input_text'];

                // Call the rapidtexiai API here to generate text using $input_text
                // Replace this with your rapidtexiai API integration code
                $postid = get_post_ID();
                $instance_id = $this->get_id();
                $generated_text = rapidtextai_generate_text($input_text,$postid,$instance_id); // Store the generated text

                echo $generated_text;
            }

            protected function _register_controls() {
                $this->start_controls_section(
                    'content_section',
                    [
                        'label' => __('Content', 'rapidtexiai-ai-text-block-elementor'),
                        'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
                    ]
                );

                $this->add_control(
                    'input_text',
                    [
                        'label' => __('Input Text', 'rapidtexiai-ai-text-block-elementor'),
                        'type' => \Elementor\Controls_Manager::TEXTAREA,
                        'placeholder' => __('Write an about use section for my company which manufacture light bulbs.', 'rapidtexiai-ai-text-block-elementor'),
                    ]
                );

                $this->end_controls_section();
            }
        }   

        

    }
    add_action( 'elementor/widgets/register', 'register_drcalcwidget_widget' );
}

function rapidtextai_generate_text($prompt,$postid,$instance_id){
    //if(get_post_meta($postid,'rapidtextai_'.$instance_id,true)){
     //   return get_post_meta($postid,'rapidtextai_'.$instance_id,true);
    //}
    $apikey = get_option('rapidtexiai_api_key','c52ec1-5c73cd-e411e2-d8dc2d-491514');
    // Define the URL with query parameters
    $url = "https://app.rapidtextai.com/openai/detailedarticle?gigsixkey=" . $apikey;
    $request_data = array(
            'type' => 'custom-prompt',
            'toneOfVoice' => '', // Assuming tone is sent as POST data
            'language' => '', // Assuming language is sent as POST data
            'text' => '',
            'temperature' => '0.7', // Assuming temperature is sent as POST data
            'custom-prompt' => $prompt,
    );
    var_dump($url);
    var_dump($request_data);

    
    $response = wp_remote_post($url, array(
        'body' => $request_data,
        'method' => 'POST',
        'timeout' => 45,
        'redirection' => 5,
        'httpversion' => '1.0',
        'blocking' => true,
        'sslverify' => false,
        'headers' => array('Content-Type' => 'multipart/form-data'),
    ));
    if (!is_wp_error($response)) {
        $http_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        var_dump($body);

        if ($http_code === 200) {
            $content = wpautop($body);
            update_post_meta($postid,'rapidtextai_'.$instance_id,$content);
            return $content; 
        }
        else
        return 'Unauthorized Access, check your Rapidtextai.com Key';
    }
}
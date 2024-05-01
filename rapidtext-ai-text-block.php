<?php
/*
* Plugin Name: RapidText AI Text Block
* Description: Add an AI-powered text block using RapidTextAI.com to WP Bakery.
* Version: 1.0
* Author: Rapidtextai.com
* Text Domain: rapidtextai
* License: GPL-2.0-or-later
*/
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly 

function rapidtextai_is_wp_bakery_active() {
    return class_exists('Vc_Manager');
}

function rapidtextai_is_elementor_active() {
    return class_exists('\Elementor\Plugin');
}


function rapidtextai_settings_menu() {
    add_menu_page(
        'rapidtextai Settings',
        'rapidtextai Settings',
        'manage_options',
        'rapidtextai-settings',
        'rapidtextai_settings_page'
    );
}
add_action('admin_menu', 'rapidtextai_settings_menu');



function rapidtextai_settings_page() {
    if (!current_user_can('manage_options')) {
        return;
    }

    // Check if the form was submitted and the nonce is valid
    if ( isset( $_POST['rapidtextai_api_key_nonce'] ) || wp_verify_nonce( sanitize_text_field( wp_unslash ( $_POST['rapidtextai_api_key_nonce'] ) ) ,'rapidtextai_api_key_nonce' ) ) {
        // Sanitize and save the API key
        $api_key = sanitize_text_field($_POST['rapidtextai_api_key']);
        update_option('rapidtextai_api_key', $api_key);
    }

    // Retrieve the current API key
    $current_api_key = get_option('rapidtextai_api_key', '');

    ?>
    <div class="wrap">
        <h2><?php esc_html_e('RapidTextAI Settings','rapidtextai')?></h2>
        <form method="post">
            <?php wp_nonce_field('rapidtextai_api_key_nonce', 'rapidtextai_api_key_nonce'); ?>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="rapidtextai_api_key"><?php esc_html_e('RapidTextAI API Key','rapidtextai')?></label></th>
                    <td>
                        <input type="text" id="rapidtextai_api_key" name="rapidtextai_api_key" value="<?php echo esc_attr($current_api_key); ?>" class="regular-text" /> &nbsp; <a href="http://app.rapidtextai.com/" target="_blank"><?php esc_html_e('Get API Key From Here','rapidtextai')?></a>
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
    function rapidtextai_ai_text_block_vc_element() {
        vc_map(array(
            'name' => __('AI Text Block', 'rapidtextai'),
            'base' => 'rapidtextai_ai_text_block',
            'category' => __('Content', 'rapidtextai'),
            'params' => array(
                array(
                    'type' => 'textarea',
                    'heading' => esc_html__('Prompt', 'rapidtextai'),
                    'param_name' => 'wpb_input_text',
                    'description' => esc_html__('Enter the prompt to generate AI text, i.e Write an about use section for my company which manufacture light bulbs', 'rapidtextai')
                ),
                array(
                    "type" => "textarea",
                    "heading" => esc_html__( "Prompt Output", 'rapidtextai'),
                    "param_name" => "wpb_input_text_output", 
                    'description' => esc_html__('Prompt response will be here, edit here if needed', 'rapidtextai'),
                ),
            ),
            'shortcode' => 'rapidtextai_ai_text_block_shortcode',
        ));
    }
    add_action('vc_before_init', 'rapidtextai_ai_text_block_vc_element');
    
    

    function rapidtextai_ai_text_block_shortcode($atts, $sc_content = null,$instance_id) {
        extract(shortcode_atts(array(
            'wpb_input_text' => '',
            'wpb_input_text_output' => '',
        ), $atts));

        //echo '<pre>';print_r($instance_id);echo '</pre>';

        $postid = get_the_ID();

        global $post;
        $new_value = '';

        $shortcode = 'rapidtextai_ai_text_block';

        // Define the attribute you want to update
        $attribute_to_update = 'wpb_input_text_output';
        $content = $post->post_content;
        // Use a regular expression to find all instances of the shortcode
        $pattern = get_shortcode_regex([$shortcode]);
        preg_match_all('/' . $pattern . '/s', $content, $matches);
    
        //echo 'matches rapidtextai_ai_text_block <pre>';print_r($matches);echo '</pre>';
        if (isset($matches[0]) && isset($atts['wpb_input_text']) && trim($atts['wpb_input_text']) != '') {
            foreach ($matches[0] as $shortcode_instance) {
                //var_dump($shortcode_instance);

                $attribute_pattern = '/' . $attribute_to_update . '=["\'](.*?)["\']/';
                preg_match($attribute_pattern, $shortcode_instance, $attribute_match);
                //echo '<pre>';print_r($attribute_match);echo '</pre>';

                // // Check if the attribute was found
                if (!isset($attribute_match[1])) {
                    $new_value = rapidtextai_generate_text($atts['wpb_input_text'],$postid,$instance_id);;
                    //var_dump($new_value);
                    $updated_shortcode = str_replace('rapidtextai_ai_text_block','rapidtextai_ai_text_block wpb_input_text_output="'.$new_value.'"', $shortcode_instance);
                    $content = str_replace($shortcode_instance, $updated_shortcode, $content);
                }
            }
        }


        
        wp_update_post(array('ID'=>$postid,'post_content'=>$content));
        return isset($atts['wpb_input_text_output']) ? $atts['wpb_input_text_output'] : $new_value;
    } // func
    add_shortcode('rapidtextai_ai_text_block', 'rapidtextai_ai_text_block_shortcode');
}



/***
 * Elementor
 */
if(rapidtextai_is_elementor_active()){
    function rapidtextai_register_block_widget( $widgets_manager ) {
        class rapidtextai_AITextBlock_Elementor_Widget extends \Elementor\Widget_Base {

            public function get_name() {
                return 'rapidtextai-ai-text-block';
            }

            public function get_title() {
                return __('AI Text Block', 'rapidtextai');
            }

        

            protected function register_controls() {
                
                $this->start_controls_section(
                    'content_section',
                    [
                        'label' => esc_html__('Content', 'rapidtextai'),
                        'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
                    ]
                );

             
                $this->add_control(
                    'input_text',
                    [
                        'label' => esc_html__('Prompt', 'rapidtextai-ai-text-block-elementor'),
                        'type' => \Elementor\Controls_Manager::TEXTAREA,
                        'placeholder' => esc_html__('Write an about use section for my company which manufacture light bulbs.', 'rapidtextai-ai-text-block-elementor'),
                    ]
                );
                
             
                $this->add_control(
                    'input_text_output',
                    [
                        'label' => esc_html__( 'Prompt Output', 'rapidtextai-ai-text-block-elementor' ),
                        'description' => esc_html__('Prompt response will be here, edit here if needed', 'rapidtextai'),
                        'type' => \Elementor\Controls_Manager::TEXTAREA
                    ]
                );
           
           
                $this->end_controls_section();
            } // function


            public function render() {
                $postid = get_the_ID();
                $settings = $this->get_settings_for_display();

                $jsonelem_str = get_metadata('post',$postid, '_elementor_data', true );
                $jsonelem_arr = $jsonelem_str ? json_decode( $jsonelem_str, true ) : false;
                $instance_id = $this->get_id();

                if($jsonelem_arr){

                    //var_dump($instance_id);
                   // echo '<pre>';print_r($jsonelem_arr);echo '</pre>';

                    $input_text = $settings['input_text'];
                    $input_text_output = $settings['input_text_output'];
                
                    $generated_text = '';

                


                    if($input_text_output && trim($input_text_output) != '')
                    $generated_text = $input_text_output;
                    else{
                        if($input_text && trim($input_text) != ''){
                            $generated_text = rapidtextai_generate_text($input_text,$postid,$instance_id);

                            foreach ($jsonelem_arr as $key => $value) {
                                if($value['elements'][0]['elements'][0]['id'] == $instance_id){
                                    $jsonelem_arr[$key]['elements'][0]['elements'][0]['settings']['input_text_output'] = $generated_text;
                                    $jsonvalue = wp_slash( wp_json_encode( $jsonelem_arr ) );
                                    update_metadata( 'post', $postid, '_elementor_data', $jsonvalue );
                                    break;
                                } // if($value['elements'][0
                            } // foreach

                        } // if($input_text && trim($input_text
                    
                    } // ELSE of  if($input_text_output &&
                


                    echo wp_kses_post($generated_text);
                } // $jsonelem_arr
            } // func

            // protected function content_template() {}

            // public function render_plain_content( $instance = [] ) {}

          
            

        }  // clASS

        $widgets_manager->register( new \rapidtextai_AITextBlock_Elementor_Widget() );

   }
    add_action( 'elementor/widgets/register', 'rapidtextai_register_block_widget' );
}



function rapidtextai_generate_text($prompt,$postid,$instance_id){
    $apikey = get_option('rapidtextai_api_key','c52ec1-5c73cd-e411e2-d8dc2d-491514');
    // Define the URL with query parameters
    $url = "https://app.rapidtextai.com/openai/detailedarticle?gigsixkey=" . $apikey;
    $request_data = array(
            'type' => 'intro',
            'toneOfVoice' => '', // Assuming tone is sent as POST data
            'language' => '', // Assuming language is sent as POST data
            'text' => '',
            'temperature' => '0.7', // Assuming temperature is sent as POST data
            'custom-prompt' => $prompt,
    );
   
    
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

        if ($http_code === 200) {
            $content = wpautop($body);
            return $content; 
        }
        else
        return 'Unauthorized Access, check your Rapidtextai.com Key';
    }
}

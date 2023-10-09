<?php
     class rapidtexiai_AITextBlock_Elementor_Widget extends \Elementor\Widget_Base {

        public function get_name() {
            return 'rapidtexiai-ai-text-block';
        }

        public function get_title() {
            return __('AI Text Block', 'rapidtexiai-ai-text-block-elementor');
        }

          /**
       * Get widget icon.
       *
       * Retrieve RA_MainPage_Elements widget icon.
       *
       * @since 1.0.0
       * @access public
       * @return string Widget icon.
       */
      public function get_icon() {
        return 'eicon-header';
    }


    /**
     * Get custom help URL.
     *
     * Retrieve a URL where the user can get more information about the widget.
     *
     * @since 1.0.0
     * @access public
     * @return string Widget help URL.
     */
    public function get_custom_help_url() {
        return 'https://essentialwebapps.com/category/elementor-tutorial/';
    }

    /**
     * Get widget categories.
     *
     * Retrieve the list of categories the RA_MainPage_Elements widget belongs to.
     *
     * @since 1.0.0
     * @access public
     * @return array Widget categories.
     */
    public function get_categories() {
        return [ 'general' ];
    }

    /**
     * Get widget keywords.
     *
     * Retrieve the list of keywords the RA_MainPage_Elements widget belongs to.
     *
     * @since 1.0.0
     * @access public
     * @return array Widget keywords.
     */
    public function get_keywords() {
        return ['Rapid Text', 'Rapidtext'];
    }



        public function render() {
            $settings = $this->get_settings_for_display();
            $input_text = $settings['input_text'];

            $postid = get_the_ID();
            $instance_id = $this->get_id();
            $generated_text = get_metadata('post',$postid,'rapidtextai_'.$instance_id,true);

            if($generated_text && trim($generated_text) != '')
                $generated_text;
           else
                $generated_text = rapidtextai_generate_text($input_text,$postid,$instance_id); // Store the generated text


            echo $generated_text;
        }

        protected function register_controls() {

            $postid = get_the_ID();
            
           $instance_id = $this->get_id();
           $generated_text = get_post_meta($postid,'rapidtextai_'.$instance_id,false);
           // var_dump($instance_id);
            //var_dump($generated_text);
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
                    'label' => __('Prompt', 'rapidtexiai-ai-text-block-elementor'),
                    'type' => \Elementor\Controls_Manager::TEXTAREA,
                    'placeholder' => __('Write an about use section for my company which manufacture light bulbs.', 'rapidtexiai-ai-text-block-elementor'),
                ]
            );

            $this->add_control(
                'input_text_output',
                [
                    'label' => esc_html__( 'Prompt Output', 'rapidtexiai-ai-text-block-elementor' ),
                    'type' => \Elementor\Controls_Manager::TEXTAREA,
                    'default' => $generated_text
                ]
            );

            $this->end_controls_section();
        }

        

    }  // clASS
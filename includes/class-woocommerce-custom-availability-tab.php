<?php

class Woocommerce_Custom_Availability_Section {

    public function __construct(Type $var = null)
    {
        add_filter( 'woocommerce_get_sections_products', array( $this , 'wcslider_add_section' ) );
        add_filter( 'woocommerce_get_settings_products', array( $this , 'wcslider_all_settings' ) , 10, 2 );
    }

    /**
     * Create the section beneath the products tab
     **/
    public function wcslider_add_section( $sections ) {
        $sections['custom_availability'] = __( 'Custom Availability', 'woocommerce-custom-availability' );
        return $sections;
    }

    /**
     * Add settings to the specific section we created before
     */
    public function wcslider_all_settings( $settings, $current_section ) {
        if ( $current_section == 'custom_availability' ) {
            $settings_slider = array();
            // Add Title to the Settings
            $settings_slider[] = array( 
                'name' => __( 'Custom Availability Settings', 'woocommerce-custom-availability' ),
                'type' => 'title',
                'id' => 'custom_availability'
            );
            
            $settings_slider[] = array(
                'name'     => __( 'Include Woocommerce Default Availability', 'woocommerce-custom-availability' ),
                'desc_tip' => __( 'This will add default woocommerce availability message with our custom availability message.', 'woocommerce-custom-availability' ),
                'id'       => '_wca_include_woocommerce_availability',
                'type'     => 'checkbox',
                'css'      => 'min-width:300px;',
                'desc'     => __( 'Include Woocommerce Default Availability', 'woocommerce-custom-availability' ),
            );
                        
            $settings_slider[] = array( 'type' => 'sectionend', 'id' => 'custom_availability' );
            return $settings_slider;
        } else {
            return $settings;
        }
    }

}

$woocommerce_custom_availability_section = new Woocommerce_Custom_Availability_Section();

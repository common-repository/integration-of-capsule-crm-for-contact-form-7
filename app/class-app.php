<?php

namespace CFCC_CAPSULE_CRM;

// If this file is called directly, abort.
if ( !defined( 'WPINC' ) ) {
    die;
}
class App
{
    private  $plugin_name ;
    private  $version ;
    public function __construct( $plugin_name, $version )
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->register_hooks();
    }
    
    public function register_hooks()
    {
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_styles' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
        add_filter( 'wpcf7_editor_panels', array( $this, 'add_panel' ) );
        add_filter( 'wpcf7_before_send_mail', array( $this, 'send_contact' ) );
        add_action(
            'wpcf7_save_contact_form',
            array( $this, 'save_fields' ),
            10,
            3
        );
        add_action( 'admin_init', array( $this, 'reset_cache' ) );
        add_action( 'plugins_loaded', array( $this, 'load_plugin_textdomain' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );
        add_action( 'admin_menu', array( $this, 'add_sub_menu_page' ), 999 );
    }
    
    public function add_sub_menu_page()
    {
        add_submenu_page(
            'wpcf7',
            'Capsule CRM',
            'Capsule CRM',
            'manage_options',
            'cfcc-capsule-crm-integration',
            array( $this, 'display_option_page' )
        );
    }
    
    public function display_option_page()
    {
        include_once 'option-page.php';
    }
    
    public function register_settings()
    {
        register_setting( 'cfcc_capsule_crm', 'cfcc_capsule_api_token' );
    }
    
    public function load_plugin_textdomain()
    {
        load_plugin_textdomain( 'contact-form-7-capsule-crm', false, dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/' );
    }
    
    public function reset_cache()
    {
        
        if ( isset( $_GET['cfcc_reset_cache'] ) && current_user_can( 'administrator' ) ) {
            $trans = array(
                '_cfcc_owners',
                '_cfcc_milestones',
                '_cfcc_opp_tags',
                '_cfcc_person_tags',
                '_cfcc_party_orgs',
                '_cfcc_opp_custom_fields',
                '_cfcc_person_custom_fields',
                '_cfcc_case_tags',
                '_cfcc_case_custom_fields'
            );
            foreach ( $trans as $tran ) {
                delete_transient( $tran );
            }
            wp_redirect( wp_get_referer() );
            exit;
        }
    
    }
    
    public function enqueue_styles( $hook_suffix )
    {
        if ( false === strpos( $hook_suffix, 'wpcf7' ) ) {
            return;
        }
        wp_enqueue_style(
            $this->plugin_name,
            plugin_dir_url( __FILE__ ) . 'assets/css/admin-style.css',
            array(),
            $this->version,
            'all'
        );
    }
    
    public function enqueue_scripts( $hook_suffix )
    {
        if ( false === strpos( $hook_suffix, 'wpcf7' ) ) {
            return;
        }
        wp_enqueue_script(
            $this->plugin_name,
            plugin_dir_url( __FILE__ ) . 'assets/js/admin-script.js',
            array( 'jquery', 'jquery-ui-tabs', 'clipboard' ),
            $this->version,
            false
        );
    }
    
    public function get_apitoken()
    {
        return \WPCF7::get_option( 'capsule-crm' );
    }
    
    public function save_party( $form_id )
    {
        $type = esc_attr( get_post_meta( $form_id, 'cfcc_type', true ) );
        $party_data = array(
            'party' => array(
            'type'  => $type,
            'about' => Helper::load_form_value( $form_id, get_post_meta( $form_id, 'cfcc_about', true ), $_POST ),
        ),
        );
        // Phone Number
        $phone = get_post_meta( $form_id, 'cfcc_phoneNumbers', true );
        if ( !empty($phone) ) {
            $party_data['party']['phoneNumbers'] = Helper::load_form_value(
                $form_id,
                $phone,
                $_POST,
                'phoneNumbers'
            );
        }
        // Email
        $email_address = get_post_meta( $form_id, 'cfcc_emailAddresses', true );
        if ( !empty($email_address) ) {
            $party_data['party']['emailAddresses'] = Helper::load_form_value(
                $form_id,
                $email_address,
                $_POST,
                'emailAddresses'
            );
        }
        // Person or Organisation
        
        if ( $type === 'person' ) {
            $party_data['party']['firstName'] = Helper::load_form_value( $form_id, get_post_meta( $form_id, 'cfcc_firstName', true ), $_POST );
            $party_data['party']['lastName'] = Helper::load_form_value( $form_id, get_post_meta( $form_id, 'cfcc_lastName', true ), $_POST );
            $party_data['party']['jobTitle'] = Helper::load_form_value( $form_id, get_post_meta( $form_id, 'cfcc_jobTitle', true ), $_POST );
            $party_data['party']['title'] = esc_attr( get_post_meta( $form_id, 'cfcc_title', true ) );
            $organisation = intval( get_post_meta( $form_id, 'cfcc_organisation', true ) );
            if ( $organisation !== 0 ) {
                $party_data['party']['organisation'] = array(
                    'id' => $organisation,
                );
            }
        } else {
            $party_data['party']['name'] = Helper::load_form_value( $form_id, get_post_meta( $form_id, 'cfcc_name', true ), $_POST );
        }
        
        // Websites
        $website_types = 'URL, SKYPE, TWITTER, LINKED_IN, FACEBOOK, XING, FEED, GOOGLE_PLUS, FLICKR, GITHUB, YOUTUBE, INSTAGRAM, PINTEREST';
        $website_types = strtolower( str_replace( ' ', '', $website_types ) );
        foreach ( explode( ',', $website_types ) as $type ) {
            $type_meta = esc_attr( get_post_meta( $form_id, 'cfcc_' . $type, true ) );
            if ( !empty($type_meta) ) {
                $party_data['party']['websites'][] = array(
                    'service' => strtoupper( $type ),
                    'address' => Helper::load_form_value( $form_id, $type_meta, $_POST ),
                );
            }
        }
        // Addresses
        $address_types = 'street,city,state,country,zip';
        foreach ( explode( ',', $address_types ) as $type ) {
            $type_meta = esc_attr( get_post_meta( $form_id, 'cfcc_' . $type, true ) );
            if ( !empty($type_meta) ) {
                $party_data['party']['addresses'][0][$type] = Helper::load_form_value( $form_id, $type_meta, $_POST );
            }
        }
        // Tags
        $tags = get_post_meta( $form_id, 'cfcc_tags', true );
        
        if ( !empty($tags) ) {
            $final_tags = array();
            foreach ( $tags as $tag ) {
                $final_tags[] = array(
                    'name' => esc_attr( $tag ),
                );
            }
            $party_data['party']['tags'] = $final_tags;
        }
        
        // Owner
        $owner = esc_attr( get_post_meta( $form_id, 'cfcc_owner', true ) );
        if ( !empty($owner) ) {
            $party_data['party']['owner'] = array(
                'username' => $owner,
            );
        }
        // Fields
        $fields = get_post_meta( $form_id, 'cfcc_fields', true );
        
        if ( !empty($fields) ) {
            $final_fields = array();
            foreach ( $fields as $key => $field ) {
                
                if ( $field != '' ) {
                    $name = Helper::get_person_field_by_id( $key );
                    $final_fields[] = array(
                        'value'      => Helper::load_form_value( $form_id, $field, $_POST ),
                        'definition' => array(
                        'id'   => intval( $key ),
                        'name' => esc_attr( $name ),
                    ),
                    );
                }
            
            }
            $party_data['party']['fields'] = $final_fields;
        }
        
        $obj = json_decode( json_encode( $party_data ) );
        $response = Helper::request( 'parties', wp_json_encode( $obj ) );
        if ( $response['success'] === false ) {
            return Helper::log( $form_id, $response['data'] );
        }
        return $response['data'];
    }
    
    public function save_case( $form_id, $party = array(), $opp = array() )
    {
        
    }
    
    public function save_opp( $form_id, $party = array() )
    {
        
    }
    
    public function send_contact( $form )
    {
        $form_id = $form->id();
        // Person/Organisation
        if ( esc_attr( get_post_meta( $form_id, 'cfcc_enabled_1', true ) ) === 'on' ) {
            $party = $this->save_party( $form_id );
        }
        
    }
    
    public function save_fields( $contact_form, $args, $context )
    {
        if ( $args['id'] == null ) {
            return;
        }
        $form_id = intval( $args['id'] );
        // Logging
        
        if ( isset( $_POST['cfcc_logging'] ) ) {
            update_post_meta( $form_id, 'cfcc_logging', sanitize_text_field( $_POST['cfcc_logging'] ) );
        } else {
            update_post_meta( $form_id, 'cfcc_logging', 0 );
        }
        
        // Party
        
        if ( isset( $_POST['cfcc_enabled_1'] ) ) {
            update_post_meta( $form_id, 'cfcc_enabled_1', sanitize_text_field( $_POST['cfcc_enabled_1'] ) );
        } else {
            update_post_meta( $form_id, 'cfcc_enabled_1', 0 );
        }
        
        $fields = array_keys( Helper::get_party_fields() );
        foreach ( $fields as $field ) {
            $field_name = 'cfcc_' . $field;
            
            if ( isset( $_POST[$field_name] ) ) {
                $value = $_POST[$field_name];
                
                if ( !is_array( $value ) ) {
                    update_post_meta( $form_id, $field_name, sanitize_text_field( $value ) );
                } else {
                    update_post_meta( $form_id, $field_name, Helper::sanitize_array( $value ) );
                }
            
            }
        
        }
        // Opportunities
        
        if ( isset( $_POST['cfcc_enabled_2'] ) ) {
            update_post_meta( $form_id, 'cfcc_enabled_2', sanitize_text_field( $_POST['cfcc_enabled_2'] ) );
        } else {
            update_post_meta( $form_id, 'cfcc_enabled_2', 0 );
        }
        
        $fields = array_keys( Helper::get_opp_fields() );
        foreach ( $fields as $field ) {
            $field_name = 'cfcc2_' . $field;
            
            if ( isset( $_POST[$field_name] ) ) {
                $value = $_POST[$field_name];
                
                if ( !is_array( $value ) ) {
                    update_post_meta( $form_id, $field_name, sanitize_text_field( $value ) );
                } else {
                    update_post_meta( $form_id, $field_name, Helper::sanitize_array( $value ) );
                }
            
            }
        
        }
        // Case
        
        if ( isset( $_POST['cfcc_enabled_3'] ) ) {
            update_post_meta( $form_id, 'cfcc_enabled_3', sanitize_text_field( $_POST['cfcc_enabled_3'] ) );
        } else {
            update_post_meta( $form_id, 'cfcc_enabled_3', 0 );
        }
        
        $fields = array_keys( Helper::get_case_fields() );
        foreach ( $fields as $field ) {
            $field_name = 'cfcc3_' . $field;
            
            if ( isset( $_POST[$field_name] ) ) {
                $value = $_POST[$field_name];
                
                if ( !is_array( $value ) ) {
                    update_post_meta( $form_id, $field_name, sanitize_text_field( $value ) );
                } else {
                    update_post_meta( $form_id, $field_name, Helper::sanitize_array( $value ) );
                }
            
            }
        
        }
    }
    
    public function add_panel( $panels )
    {
        if ( $this->validate_apitoken() ) {
            $panels['capsule-crm-tab'] = array(
                'title'    => __( 'Capsule CRM', 'contact-form-7-capsule-crm' ),
                'callback' => array( $this, 'panel_callback' ),
            );
        }
        return $panels;
    }
    
    public function panel_callback( $post )
    {
        include_once 'panel-page.php';
    }
    
    public function validate_apitoken()
    {
        $validation = Helper::request( 'users', array(), 'GET' );
        if ( $validation == null || $validation['success'] === false ) {
            return false;
        }
        return true;
    }

}
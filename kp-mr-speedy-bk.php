<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Plugin Name: KP Mr Speedy
 * Plugin URI: http://woocommerce.com/products/woocommerce-extension/
 * Description: Your extension's description text.
 * Version: 1.0.0
 * Author: Kishor Patidar
 * Author URI: http://codetycon.com/
 * Developer: Your Name
 * Developer URI: http://yourdomain.com/
 * Text Domain: woocommerce-extension
 * Domain Path: /languages
 *
 * Woo: 12345:342928dfsfhsf8429842374wdf4234sfd
 * WC requires at least: 2.2
 * WC tested up to: 2.3
 *
 * Copyright: © 2009-2015 WooCommerce.
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
    
    if ( !class_exists( 'KP_Mr_Speedy' ) ) {
    class KP_Mr_Speedy
    {
        public static function init() {
             add_thickbox();
             add_filter( 'woocommerce_settings_tabs_array', __CLASS__ . '::add_settings_tab', 50 );
             add_action( 'woocommerce_settings_tabs_kp_mr_speedy', __CLASS__ . '::settings_tab' );
             add_action( 'woocommerce_update_options_kp_mr_speedy', __CLASS__ . '::update_settings' );
             add_filter( 'woocommerce_admin_order_actions_start', __CLASS__ . '::codetycon_add_my_account_order_actions', 10, 2 );
             // add_action( 'woocommerce_order_action_mr_speedy_short_btn',  __CLASS__ . '::place_parcl_on_mr_speedy' );
             add_action( 'wp_ajax_codetycon_place_parcl_on_mr_speedy', __CLASS__ . '::codetycon_place_parcl_on_mr_speedy' );
             // add_action( 'admin_head',  __CLASS__ . '::add_custom_order_status_actions_button_css' );
             add_action('admin_footer', __CLASS__ . '::add_popup_html');
             add_action('admin_enqueue_scripts',__CLASS__ . '::codetycon_load_plugin_asset');

             /*Action to auto sync order*/
             if(get_option('wc_kp_mr_speedy_auto_sync_order') && get_option('wc_kp_mr_speedy_auto_sync_order')=='true'){
              echo "string";die;
                add_action( 'woocommerce_order_status_processing',  __CLASS__ . '::create_order_on_mr_speedy');
             }
             
        }
        

        /*Load Plugin Assets*/
        public static function codetycon_load_plugin_asset() {        	
		    wp_enqueue_script( 'codetycon-mr-speedy-js', plugins_url( '/asset/js/javascript.js?'.uniqid(), __FILE__ ));
        wp_enqueue_script( 'sweetalert', plugins_url( '/asset/js/sweetalert.min.js?'.uniqid(), __FILE__ ));

        wp_enqueue_style('codetycon-mr-speedy-styles', plugins_url( '/asset/css/style.css?'.uniqid(), __FILE__ ));
		}

		/*Handle button click action*/
        public static function codetycon_place_parcl_on_mr_speedy() { 
            $response = array('success'=>false,'message'=>'Unknown error','status'=>'placed');         
            if(empty($_POST['order_id'])){
            	$response['message'] = "Unauthorized Access";
            }elseif(!wc_get_order($_POST['order_id'])){
            	$response['message'] = "Invalid OrderID";
            }else{
		        if( get_post_meta( $_POST['order_id'], 'mr_speedy_order_id', true)){
            	    $response = self::get_order_status_mr_speedy_api_call($_POST['order_id']); 
		        }else{
            	    $response = self::create_order_on_mr_speedy($_POST['order_id']);  
		        }
            }
            echo json_encode($response);
            exit;            
        }

        /*Add action button to order listi*/
        public static function codetycon_add_my_account_order_actions(  $order ) {  
            if( get_post_meta( $order->id, 'mr_speedy_order_id', true)){
            	echo '<a href="javascript:void(0)" class="button codetycon-wc-action-button-'.$order->id.' kp_mr_speedy_view_placed" onclick="mr_speedy_event_handlar('.$order->id .')"></a>';         
                /*$actions['mr_speedy_short_btn'] = array(
                 'url'       => 'javascript:void(0);',
                'name'      => __( 'Track Order (Mr. Speedy)', 'woocommerce' ),
                'action'    => "kp_mr_speedy_view_placed", // keep "view" class for a clean button CSS
                'target' => "_Blank"

              );*/
            }else{
            	echo '<a href="javascript:void(0)" class="button codetycon-wc-action-button-'.$order->id.' kp_mr_speedy_view" onclick="mr_speedy_event_handlar('.$order->id .')"></a>';
                /*$actions['mr_speedy_short_btn'] = array(
                 'url'       => wp_nonce_url( admin_url( 'admin-ajax.php?action=mr_speedy_short_btn&place=true&order_id=' . $order->id  ), 'woocommerce-place-parcel-to-mr-speedy' ),
                'name'      => __( 'Place Parcel (Mr. Speedy)', 'woocommerce' ),
                'action'    => "kp_mr_speedy_view", // keep "view" class for a clean button CSS,
                'target' => "_Blank"
              );*/
            }
		}

		// Add button css		
		 public static function add_custom_order_status_actions_button_css() {
		    echo '<style>a.kp_mr_speedy_view:after { font-family: woocommerce !important; content: "\e005" !important; background-image:url(https://mrspeedy.ph/img/favicon/ph.png?nocache=1529658210);}
		    a.kp_mr_speedy_view_placed:after { font-family: woocommerce !important; content: "\e01a" !important;}</style>';
		}

        /*Ajax Popup HTML*/
		public static function add_popup_html() {
		   echo '<div id="codetycon_mr_spedy_popup" style="display:none;" width="400" height="400"></div>';
       echo '<div class="codetycon_loader spinner">loading</div>';
		}


        /* Add a new settings tab to the WooCommerce settings tabs array.*/
       public static function add_settings_tab( $settings_tabs ) {
        $settings_tabs['kp_mr_speedy'] = __( 'Mr. Speedy', 'kp-mr-speedy' );
        return $settings_tabs;
       }



       /**
	     * Uses the WooCommerce admin fields API to output settings via the @see woocommerce_admin_fields() function.
	     *
	     * @uses woocommerce_admin_fields()
	     * @uses self::get_settings()
	     */
      public static function settings_tab() {
        woocommerce_admin_fields( self::get_settings() );
      }

      /**
	     * Uses the WooCommerce options API to save settings via the @see woocommerce_update_options() function.
	     *
	     * @uses woocommerce_update_options()
	     * @uses self::get_settings()
	     */
       public static function update_settings() {
        woocommerce_update_options( self::get_settings() );
      }


      /**
     * Get all the settings for this plugin for @see woocommerce_admin_fields() function.
     *
     * @return array Array of settings for @see woocommerce_admin_fields() function.
     */
    public static function get_settings() {
        $settings = array(
            'section_title' => array(
                'name'     => __( 'Mr. Speedy', 'kp-mr-speedy' ),
                'type'     => 'title',
                'desc'     => '',
                'id'       => 'wc_kp_mr_speedy_section_title'
            ),
            'auth_token' => array(
                'name' => __( 'X-DV-Auth-Token', 'woocommerce-kp-mr-speedy' ),
                'type' => 'text',
                'desc' => __( 'Secret auth token', 'woocommerce-kp-mr-speedy' ),
                'id'   => 'wc_kp_mr_speedy_auth_token'
            ),
            'api_test_url' => array(
                'name' => __( 'API-Test-URL', 'woocommerce-kp-mr-speedy' ),
                'type' => 'text',
                'desc' => __( 'Enter Test API URL', 'woocommerce-kp-mr-speedy' ),
                'id'   => 'wc_kp_mr_speedy_api_test_url'
            ),
            'api_url' => array(
                'name' => __( 'API URL', 'woocommerce-kp-mr-speedy' ),
                'type' => 'text',
                'desc' => __( 'Enter Live API URL', 'woocommerce-kp-mr-speedy' ),
                'id'   => 'wc_kp_mr_speedy_api_url'
            ),
            'api_mode' => array(
                'name' => __( 'API Mode', 'woocommerce-kp-mr-speedy' ),
                'type' => 'select',
                // 'desc' => __( 'Select API Mode.', 'woocommerce-kp-mr-speedy' ),
                'options' => array(
                  'test'        => __( 'Test', 'woocommerce' ),
                  'live'       => __( 'Live', 'woocommerce' )
                ),
                'id'   => 'wc_kp_mr_speedy_api_mode'
            ),
            'auto_sync_order' => array(
                'name' => __( 'Auto Place Order', 'woocommerce-kp-mr-speedy' ),
                'type' => 'select',
                // 'desc' => __( 'Select API Mode.', 'woocommerce-kp-mr-speedy' ),
                'options' => array(
                  'true'        => __( 'Enable', 'woocommerce' ),
                  'false'       => __( 'Disable', 'woocommerce' )
                ),
                'id'   => 'wc_kp_mr_speedy_auto_sync_order'
            ),
            'notify_email' => array(
                'name' => __( 'Notification Email', 'woocommerce-kp-mr-speedy' ),
                'type' => 'text',
                'desc' => __( 'Enter email address to get notification of failuare call. Leave empty if you don\'t want to receive any email' , 'woocommerce-kp-mr-speedy' ),
                'id'   => 'wc_kp_mr_speedy_notify_email'
            ),
            'pickup_address' => array(
                'name' => __( 'Pickup Address', 'woocommerce-kp-mr-speedy' ),
                'type' => 'text',
                'desc' => __( 'Enter Pickup Address' , 'woocommerce-kp-mr-speedy' ),
                'id'   => 'wc_kp_mr_speedy_pickup_address'
            ),
            'pickup_phone' => array(
                'name' => __( 'Pickup Phone', 'woocommerce-kp-mr-speedy' ),
                'type' => 'text',
                'desc' => __( 'Enter Pickup Phone' , 'woocommerce-kp-mr-speedy' ),
                'id'   => 'wc_kp_mr_speedy_pickup_phone'
            ),
            'section_end' => array(
                 'type' => 'sectionend',
                 'id' => 'wc_settings_tab_demo_section_end'
            )
        );
        return apply_filters( 'wc_settings_tab_demo_settings', $settings );
    }


    public function create_order_on_mr_speedy($order_id=''){
        $rs = array('success'=>false,'message'=> 'Order Already Placed!');
        if( get_post_meta( $order_id, 'mr_speedy_order_id', true)){
            return $rs;
            die;
        }    
        
        $order  				= wc_get_order($order_id);
        $point 					= array();
        $point['address'] 		= $order->get_shipping_address_1().' '.$order->get_shipping_address_1();
        $point['contact_person'] = array('phone'=>$order->get_billing_phone(),'name'=>$order->get_shipping_first_name().' '.$order->get_shipping_last_name());

        $point['address'] 		 = '3079/25 ถนน สุขุมวิท แขวง บางจาก เขต พระโขนง กรุงเทพมหานคร 10260 ประเทศ';
        $point['contact_person'] = array('phone'=>'66902299000','name'=>$order->get_shipping_first_name().' '.$order->get_shipping_last_name());
        return self::perform_mr_speedy_api_call($point,$order_id);
    }

    public function perform_mr_speedy_api_call($point='',$order_id){
    	$rs = array('success'=>false,'message'=> 'Order Already Placed!','status'=>'placed');
        if( get_post_meta( $order_id, 'mr_speedy_order_id', true)){
            return $rs;
            die;
        }
        $mode           = get_option('wc_kp_mr_speedy_api_mode');
        $notify_email   = get_option('wc_kp_mr_speedy_notify_email');
        $url            = get_option('wc_kp_mr_speedy_api_test_url'); //Test
        if($mode='live'){
           $url    = get_option('wc_kp_mr_speedy_api_url'); //Test

        }        
        $shopPoint = array();
        $shopPoint['address']        = get_option('wc_kp_mr_speedy_pickup_address');
        $shopPoint['contact_person'] = array("phone"=>get_option('wc_kp_mr_speedy_pickup_phone'));    
        $data                        = array();
        $data['matter']              = $order_id;
        $data['vehicle_type_id']     = 8;
        $data['points']              = array($shopPoint,$point);
        $args                        = array();
        $headers                     = array();
        $headers['X-DV-Auth-Token']  =  get_option('wc_kp_mr_speedy_auth_token');
        $headers['Content-Type']     =  'application/json';
        $args['headers']             = $headers;
        $args['body']                = json_encode($data);
        $response                    = wp_remote_post( $url.'/create-order', $args );
        $mailHeaders = array('Content-Type: text/html; charset=UTF-8');    

        $rs['message'] = 'Something went wrong, please try again!';
        $rs['status'] = 'error';

        
        if ( is_wp_error( $response ) ) {
           $error_message = $response->get_error_message();
           if($notify_email){
            wp_mail($notify_email,'['.get_bloginfo().'] - Mr. Speedy API error ',$error_message);
           }
        } else {          
 
           if(!empty($response['body'])){
            $body = json_decode($response['body']);
            if(empty($body->is_successful) || !$body->is_successful){
                 if($notify_email){
                   $error_message = 'Hi Admin, <br><br> We got an error when creating a order on Mr. Speedy. Please find below response from API.<br><br>'.print_r($body,true);
                    wp_mail($notify_email,'['.get_bloginfo().'] - Mr. Speedy API error KK',print_r($error_message,true),$mailHeaders);
                 }

            }else{
            	$rs['success'] = true;
            	$rs['message'] = 'Order Placed to Mr. Speedy';
            	$rs['status'] = 'placed';

                $MrOrderId = $body->order->order_id;
                update_post_meta( $order_id, 'mr_speedy_order_id', $MrOrderId); 
            }
           }
                   
        }
        return $rs;
    }

    public function get_order_status_mr_speedy_api_call($order_id){
    	$rs = array('success'=>false,'message'=> 'Pleace a order first on Mr.Speedy','status'=>'error');
        if( !get_post_meta( $order_id, 'mr_speedy_order_id', true)){
            return $rs;
            die;
        }
        $mr_speedy_order_id = get_post_meta( $order_id, 'mr_speedy_order_id', true);
        // $mr_speedy_order_id = 4445;
       
        $mode           = get_option('wc_kp_mr_speedy_api_mode');
        $notify_email   = get_option('wc_kp_mr_speedy_notify_email');
        $url            = get_option('wc_kp_mr_speedy_api_test_url'); //Test
        if($mode='live'){
           $url    = get_option('wc_kp_mr_speedy_api_url'); //Test

        }        
        //orders
        //order_id
        $headers                     = array();
        $headers['X-DV-Auth-Token']  = get_option('wc_kp_mr_speedy_auth_token');
        $headers['Content-Type']     = 'application/json';
        $args['headers']             = $headers;
        // $args['body']                = json_encode($data);
        $response                    = wp_remote_get( $url.'/orders?order_id='.$mr_speedy_order_id, $args );
        
        

        $rs['message'] = 'Something went wrong, please try again!';
        $rs['status']  = 'error';
        
        if ( is_wp_error( $response ) ) {
           $error_message = $response->get_error_message();
           $rs['message'] =$error_message;
           
        } else {          
 
           if(!empty($response['body'])){
           	$rs['message'] =$response['body'];
            $body = json_decode($response['body']);

            if(empty($body->is_successful) || !$body->is_successful){
                 if($notify_email){
                   $rs['message'] = 'Some error occured';  
                   $rs['data']  = $body;                
                 }

            }else{
            	$rs['success'] = true;
            	$rs['message'] = 'Order Placed to Mr. Speedy';
            	$rs['data']  = $body->orders[0];                
            }
           }
                   
        }
        return $rs;
    }



    }
    // add all your hooks only when woocommerce has fully loaded it's files
    add_action( 'init', 'init_mr_speedy' );
    function init_mr_speedy(){
        KP_Mr_Speedy::init();    
        // KP_Mr_Speedy::get_order_status_mr_speedy_api_call(5457);
    }
        

  }
}


/*
Reference Doc
https://www.skyverge.com/blog/add-custom-options-to-woocommerce-settings/
https://docs.woocommerce.com/document/adding-a-section-to-a-settings-tab/
https://stackoverflow.com/questions/39401393/how-to-get-woocommerce-order-details

*/
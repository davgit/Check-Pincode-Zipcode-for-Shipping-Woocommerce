<?php

if (!defined('ABSPATH'))
    exit;

if (!class_exists('WCZP_front')) {

    class WCZP_front {

        protected static $instance;
        function WCZP_before_add_to_cart_btn() { 
            
            if(empty(get_option('wczp_box_bg_clr'))) {
                $box_backgrond_clr = "#e0e0e0";
            }else{
                $box_backgrond_clr = get_option('wczp_box_bg_clr');
            }

            if(empty(get_option('wczp_chnge_btn_txt'))){
               $changetext = "Change";
            }else{
               $changetext = get_option('wczp_chnge_btn_txt');
            }

          

            if(empty(get_option('wczp_btn_txt'))){
               $text = "Check";
            }else{
               $text = get_option('wczp_btn_txt');
            }

            ?>
            <div class="wczpc_maindiv" style="background-color: <?php echo $box_backgrond_clr; ?>;">
                <h3>Check Availability At</h3>
                <div class="wczp_checkcode" style="display: <?php if(isset($_COOKIE['wczp_postcode']) && $_COOKIE['wczp_postcode'] != "no"){ echo "block"; }else{ echo "none"; } ?>;">
                    <p class="response_pin">
                        <?php 
                            if(isset($_COOKIE['wczp_postcode'])){ 
                                global $wpdb;
                                $tablename=$wpdb->prefix.'wczp_postcode';
                                $cntSQL = "SELECT * FROM {$tablename} where wczp_pincode='".sanitize_text_field($_COOKIE['wczp_postcode'])."'";
                                $record = $wpdb->get_results($cntSQL, OBJECT);
                                $datetxt = "";
                                $date = $record[0]->wczp_ddate;
                                $string = "+".$date." days";
                                $deliverydate = Date('d/m/y', strtotime($string));
                                $showdate = get_option('wczp_del_shw');
                                if($showdate == "on") {
                                    $datetxt = "delivery date : ".$deliverydate;
                                }
                                $totalrec = count($record);
                                if ($totalrec == 1) {
                                    echo 'Available at'.$_COOKIE['wczp_postcode'].'</br>'.$datetxt;
                                }else{
                                    echo "Oops! We are not currently servicing your area.";
                                }
                                
                            } 
                        ?>
                    </p>
                    <input type="button" name="wczpbtn" class="wczpcheckbtn" value="<?php echo $changetext; ?>" style="background-color: #000000; color: #ffffff;">
                </div>
                <div class="wczp_cookie_check_div" style="display: <?php if(isset($_COOKIE['wczp_postcode'])  && $_COOKIE['wczp_postcode'] != "no"){ echo "none"; }else{ echo "block"; } ?>;">
                    <input type="text" name="wczpcheck" class="wczpcheck" value="<?php if(isset($_COOKIE['wczp_postcode']) && sanitize_text_field($_COOKIE['wczp_postcode']) != "no"){ echo sanitize_text_field($_COOKIE['wczp_postcode']); }else if(!empty(isset($_COOKIE['wczp_popup_postcode']) && sanitize_text_field($_COOKIE['wczp_popup_postcode']))){echo sanitize_text_field($_COOKIE['wczp_popup_postcode']);}else{}?>">
                    <input type="button" name="wczpbtn" class="wczpbtn" value="<?php echo $text; ?>" style="background-color:#000000;color: #ffffff;">
                </div>     
            </div>
            <?php
            
        }


        function WCZP_check_location() {
            global $wpdb;
            $pincode = sanitize_text_field($_REQUEST['postcode']);
            $tablename=$wpdb->prefix.'wczp_postcode';
            $cntSQL = "SELECT * FROM {$tablename} where wczp_pincode='".$pincode."'";
            $record = $wpdb->get_results($cntSQL, OBJECT);
            $date = $record[0]->wczp_ddate;
            $string = "+".$date." days";
            $deliverydate=Date('d/m/y', strtotime($string));
            $totalrec = count($record);
            $showdate = get_option('wczp_del_shw'); 
            $data = array();
            $data = array(
                'pincode' => $pincode,
                'deliverydate' => $deliverydate,
                'totalrec'     => $totalrec,
                'showdate'     => $showdate
            );
            
            $expiry = strtotime('+7 day');
            if($totalrec == 1){
                setcookie('wczp_postcode', $pincode, $expiry , COOKIEPATH, COOKIE_DOMAIN);
            }else{
                setcookie('wczp_postcode', 'no', $expiry , COOKIEPATH, COOKIE_DOMAIN);
            }
            echo json_encode( $data );
            exit();

        }


        function WCZP_popup_div_footer(){
               
            if(get_option('wczp_ato_pop_shw')=="on"){
                if ( is_front_page() && is_home()) {  ?>
                    <div id="myModal" class="modal" >
                        <div class="modal-content" >
                            <span class="close">&times;</span>
                            <div class="modalinner">
                                <div class="popup_oc_left">
                                    <img src="<?php echo WCZP_PLUGIN_DIR; ?>/includes/images/multi-location.jpg" class="popupimage">
                                </div>
                                <div class="popup_oc_right">
                                 
                                    <h4 class="wczp_popup_header">Check your loaction availability info</h4>
                                    <div class="modal-body">
                                    <form action="" method="post">
                                        <input type="number" name="wczpopuppinzip" class="wczpopuppinzip" placeholder="Enter pincode" value="">
                                        <input type="button" name="wczpinzipsubmit" class="wczpinzipsubmit" value="submit">
                                    </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php 
                }
            }

            if(get_option('wczp_ato_pop_cat_shop')=="on"){
                if ( is_shop()) {  ?>
                    <div id="myModal" class="modal" >
                        <div class="modal-content" >
                            <span class="close">&times;</span>
                            <div class="modalinner">
                                <div class="popup_oc_left">
                                    <img src="<?php echo WCZP_PLUGIN_DIR; ?>/includes/images/multi-location.jpg" class="popupimage">
                                </div>
                                <div class="popup_oc_right">
                                  
                                    <h4 class="wczp_popup_header">Check your loaction availability info</h4>
                                    <div class="modal-body">
                                        <form action="" method="post">
                                            <input type="number" name="wczpopuppinzip" class="wczpopuppinzip" placeholder="Enter pincode" value="">
                                            <input type="button" name="wczpinzipsubmit" class="wczpinzipsubmit" value="submit">
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php 
                }
            }

        }
        

        function WCZP_popup_check_zip_code(){

                $popup_postcode = sanitize_text_field($_REQUEST['popup_postcode']);
                $data = array();
                $data = array(
                    'popup_pincode' => $popup_postcode,
                );
                $expiry = strtotime('+7 day');
                setcookie('wczp_popup_postcode', $popup_postcode, $expiry , COOKIEPATH, COOKIE_DOMAIN);
               echo json_encode( $data );
            exit();    
        }


        function WCZP_inactive_order_button_html( $button ) {

            if(get_option('wczp_checkpincode') == "on"){

                if(isset($_COOKIE['wczp_postcode'])){ 

                    global $wpdb;
                    $tablename=$wpdb->prefix.'wczp_postcode';
                    $cntSQL = "SELECT * FROM {$tablename} where wczp_pincode='".sanitize_text_field($_COOKIE['wczp_postcode'])."'";
                    $record = $wpdb->get_results($cntSQL, OBJECT);
                    $totalrec = count($record);

                    if ($totalrec == 1) {

                         return $button;

                    }else{

                        $style = 'style="background:red !important; color:white !important; cursor: not-allowed !important;"'; 
                       
                        $button_text = apply_filters( 'woocommerce_order_button_text', __( 'Choose valid zipcode in product page then place order' , 'woocommerce' ) );

                        $button = '<a class="button" '.$style.'>' . $button_text . '</a>';
                         return $button;
                    }
                    
                } 
            
            }else{
                  return $button;
            }
           
        }
        
        function init() {

            add_filter('woocommerce_order_button_html', array( $this, 'WCZP_inactive_order_button_html' ));
            add_action( 'wp_footer', array( $this, 'WCZP_popup_div_footer' ));   
            add_action( 'woocommerce_after_add_to_cart_button', array($this,'WCZP_before_add_to_cart_btn'));
            add_action( 'WCZP_popup_form_zipcode', array($this,'WCZP_popup_form_zipcode'));
            add_action( 'wp_ajax_WCZP_check_location', array($this,'WCZP_check_location' ));
            add_action( 'wp_ajax_nopriv_WCZP_check_location', array($this,'WCZP_check_location' ));
            add_action( 'wp_ajax_WCZP_popup_check_zip_code', array($this,'WCZP_popup_check_zip_code' ));
            add_action( 'wp_ajax_nopriv_WCZP_popup_check_zip_code', array($this,'WCZP_popup_check_zip_code' ));
            
        }

        
        public static function instance() {

            if (!isset(self::$instance)) {
                self::$instance = new self();
                self::$instance->init();
            }
            return self::$instance;
        }
    }
    WCZP_front::instance();
}



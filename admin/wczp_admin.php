<?php

if (!defined('ABSPATH'))
    exit;

if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

if (!class_exists('WCZP_menu')) {
    class WCZP_menu {
        protected static $instance;
        function WCZP_admin_menu() {
            add_menu_page(
                __( 'Add Post codes', 'wczp' ),
                __( 'Add Post codes', 'wczp' ),
                'manage_options',
                'post-code',
                array($this,'WCZP_add_postcode'),
                'dashicons-schedule',
                10
            );
            
            add_submenu_page( 
                'post-code', 
                __( 'List Post codes', 'wczp' ), 
                __( 'List Post codes', 'wczp' ),
                'manage_options', 
                'post-code-list',
                array($this,'WCZP_list_postcode')
            );

            add_submenu_page( 
                'post-code', 
                __( 'Settings', 'wczp' ),  
                __( 'Settings', 'wczp' ),
                'manage_options', 
                'post-code-setting',
                array($this,'WCZP_setting')
            );
        }


        function WCZP_add_postcode() {
            global $wpdb;
            $tablename=$wpdb->prefix.'wczp_postcode';
            if(isset($_REQUEST['action']) && $_REQUEST['action'] == "oc_edit"){ 
                $pincode = sanitize_text_field($_REQUEST['id']);
                $cntSQL = "SELECT * FROM {$tablename} where id='".$pincode."'";
                $record = $wpdb->get_results($cntSQL, OBJECT);
                ?>
                    <div class="wczp_container">
                        <h2>Update Post Code</h2>
                        <form method="post">
                            <?php wp_nonce_field( 'WCZP_add_postcode_action', 'WCZP_add_postcode_field' ); ?>
                            <table class="wczp_table">
                                <tr>
                                    <td>Pincode</td>
                                    <td>
                                        <input type="text" name="txtpincode" value="<?php echo $record[0]->wczp_pincode; ?>">
                                        <input type="hidden" name="txtid" value="<?php echo $record[0]->id; ?>">
                                    </td>
                                </tr>
                                <tr>
                                    <td>City</td>
                                    <td><input type="text" name="txtcity" value="<?php echo $record[0]->wczp_city; ?>"></td>
                                </tr>
                                <tr>
                                    <td>State</td>
                                    <td><input type="text" name="txtstate" value="<?php echo $record[0]->wczp_state; ?>"></td>
                                </tr>
                                <tr>
                                    <td>Delivery within days</td>
                                    <td>
                                        <input type="number" name="txtdelivery" min=1 value="<?php echo $record[0]->wczp_ddate; ?>">
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <input type="hidden" name="action" value="update_postcode">
                                        <input type="submit" name="txtupdate" value="Update">
                                    </td>
                                </tr>
                            </table>
                        </form>
                    </div>
                <?php

            }elseif(isset($_REQUEST['action']) && $_REQUEST['action'] == "delete") {
                $pincode = sanitize_text_field($_REQUEST['id']);
                $sql = "DELETE FROM $tablename WHERE id='".$pincode."'";
                $wpdb->query($sql);
                wp_redirect(admin_url('admin.php?page=post-code-list'));

            }else{
                ?>
                    <div class="wczp_container">
                        <h2>Add Post Code</h2>
                        <form method='post' action='<?= $_SERVER['REQUEST_URI']; ?>' enctype='multipart/form-data' class="wczp_import">
                            <div class="wczp_importbox">
                                <input type="file" name="import_file" >
                                <input type="submit" name="butimport" value="Import">
                            </div>
                            <a href="<?php echo WCZP_PLUGIN_DIR.'/pincode.csv'; ?>" download='sample_pincode.csv' class="wczp_demo_file">View demo file</a>
                        </form>
                        <form method="post">
                            <?php wp_nonce_field( 'WCZP_add_postcode_action', 'WCZP_add_postcode_field' ); ?>

                            <table class="wczp_table">
                                <tr>
                                    <td>Pincode</td>
                                    <td><input type="text" name="txtpincode"></td>
                                </tr>
                                <tr>
                                    <td>City</td>
                                    <td><input type="text" name="txtcity"></td>
                                </tr>
                                <tr>
                                    <td>State</td>
                                    <td><input type="text" name="txtstate"></td>
                                </tr>
                                <tr>
                                    <td>Delivery within days</td>
                                    <td><input type="number" name="txtdelivery" min=1></td>
                                </tr>
                                <tr>
                                    <td>
                                      <input type="hidden" name="action" value="add_postcode">
                                      <input type="submit" name="txtsubmit" value="Add">
                                    </td>
                                </tr>
                            </table>
                        </form>
                    </div>
                <?php

            }


            if(isset($_POST['butimport'])){

                // File extension
                $extension = pathinfo($_FILES['import_file']['name'], PATHINFO_EXTENSION);

                // If file extension is 'csv'
                if(!empty($_FILES['import_file']['name']) && $extension == 'csv'){

                    $totalInserted = 0;
             
                    // Open file in read mode
                    $csvFile = fopen($_FILES['import_file']['tmp_name'], 'r');
                    fgetcsv($csvFile); // Skipping header row

                    // Read file
                    while(($csvData = fgetcsv($csvFile)) !== FALSE){
                        $csvData = array_map("utf8_encode", $csvData);
                
                        // Row column length
                        //$dataLen = count($csvData);

                        // Skip row if length != 4
                        //if( !($dataLen == 4) ) continue;

                        // Assign value to variables
                        $pincode = trim($csvData[0]);
                        $city = trim($csvData[1]);
                        $state = trim($csvData[2]);
                        $ddate = trim($csvData[3]);
                        // echo $pincode;
                        // exit();
                  
                        $cntSQL = "SELECT count(*) as count FROM {$tablename} where wczp_pincode='".$pincode."'";
                        $record = $wpdb->get_results($cntSQL, OBJECT);

                        if($record[0]->count==0){

                            // Check if variable is empty or not
                            if(!empty($pincode) && !empty($city) && !empty($state) && !empty($ddate) ) {
                                // Insert Record
                                $wpdb->insert($tablename, array(
                                   'wczp_pincode' =>$pincode,
                                   'wczp_city' =>$city,
                                   'wczp_state' =>$state,
                                   'wczp_ddate' => $ddate
                                ));
                                if($wpdb->insert_id > 0){
                                   $totalInserted++;
                                }
                            }
                        }
                    }
                    echo "<h3 style='color: green;'>Total record Inserted : ".$totalInserted."</h3>";
                }else{
                    echo "<h3 style='color: red;'>Invalid Extension</h3>";
                }  
            }


            if( isset( $_REQUEST['txtsubmit'] ) ) {
                if (!isset( $_POST['name_of_nonce_field'] ) || !wp_verify_nonce( $_POST['name_of_nonce_field'], 'name_of_my_action' ) ) {
                   // echo "<pre>";
                   // print_r($_REQUEST);
                   // echo "</pre>";  

                    $pincode = sanitize_text_field( $_REQUEST['txtpincode']);
                    $city = sanitize_text_field( $_REQUEST['txtcity']);
                    $state = sanitize_text_field( $_REQUEST['txtstate']);
                    $ddate = sanitize_text_field( $_REQUEST['txtdelivery']);


                    $cntSQL = "SELECT count(*) as count FROM {$tablename} where wczp_pincode='".$pincode."'";
                    $record = $wpdb->get_results($cntSQL, OBJECT);
                    if($record[0]->count==0){
                        if(!empty($pincode) && !empty($city) && !empty($state) && !empty($ddate) ) {
                            $data=array(
                                'wczp_pincode' => $pincode,
                                'wczp_city' => $city, 
                                'wczp_state' => $state,
                                'wczp_ddate' => $ddate,  

                            );
                            $wpdb->insert( $tablename, $data);
                        }else{
                            echo "<div class='notice notice-error is-dismissible'><p>Field should not empty.</p></div>";
                        }
                    }else{
                        echo "<div class='notice notice-error is-dismissible'><p>Sorry, already exist record.</p></div>";
                    }
                }else{
                   echo "<div class='notice notice-error is-dismissible'><p>Sorry, your nonce did not verify.</p></div>";
                } 
            }


            if( isset( $_REQUEST['txtupdate'] ) ) {
                if (!isset( $_POST['name_of_nonce_field'] ) || !wp_verify_nonce( $_POST['name_of_nonce_field'], 'name_of_my_action' ) ) {
                    // echo "<pre>";
                    // print_r($_REQUEST);
                    // echo "</pre>";  
                    $id = sanitize_text_field( $_REQUEST['txtid']);
                    $pincode = sanitize_text_field( $_REQUEST['txtpincode']);
                    $city = sanitize_text_field( $_REQUEST['txtcity']);
                    $state = sanitize_text_field( $_REQUEST['txtstate']);
                    $ddate = sanitize_text_field( $_REQUEST['txtdelivery']);


                    $cntSQL = "SELECT count(*) as count FROM {$tablename} where wczp_pincode='".$pincode."'";
                    $record = $wpdb->get_results($cntSQL, OBJECT);
                   // if($record[0]->count==0){
                        if(!empty($pincode) && !empty($city) && !empty($state) && !empty($ddate) ) {
                            $data=array(
                                'wczp_pincode' => $pincode,
                                'wczp_city' => $city, 
                                'wczp_state' => $state,
                                'wczp_ddate' => $ddate,  
                            );
                            $condition=array(
                                'id'=>$id
                            );
                            //$wpdb->insert( $tablename, $data);
                            $wpdb->update($tablename, $data, $condition);
                            wp_redirect(admin_url('admin.php?page=post-code-list'));
                        }else{
                            echo "<div class='notice notice-error is-dismissible'><p>Field should not empty.</p></div>";
                        }
                    // }else{
                    //     echo "<div class='notice notice-error is-dismissible'><p>Sorry, already exist record.</p></div>";
                    // }
                }else{
                   echo "<div class='notice notice-error is-dismissible'><p>Sorry, your nonce did not verify.</p></div>";
                } 
            }
        }


        function WCZP_list_postcode() {
            $exampleListTable = new WCZP_List_Table();
            $exampleListTable->prepare_items();
            ?>
            <div class="wczp_container">
                <h2>List Post Code</h2>
                <form  method="post">
                    <?php
                        $page  = filter_input( INPUT_GET, 'page', FILTER_SANITIZE_STRIPPED );
                        $paged = filter_input( INPUT_GET, 'paged', FILTER_SANITIZE_NUMBER_INT );

                        printf( '<input type="hidden" name="page" value="%s" />', $page );
                        printf( '<input type="hidden" name="paged" value="%d" />', $paged ); 
                    ?>
                    <?php $exampleListTable->display(); ?>
                </form>
            </div>
            <?php
        }


        function WCZP_setting() {
            ?>
            <div class="wczp_container">
                <form method="post" class="oc_wczp">
                    <h2>Design Setting</h2>
                    <table class="wczp_table">
                        <tr>
                            <td>Box Background Color</td>
                            <?php 
                                $box_backgrond_clr = get_option('wczp_box_bg_clr'); 
                                if(empty($box_backgrond_clr)){
                                   $box_backgrond_clrs = "#e0e0e0";
                                }else{
                                   $box_backgrond_clrs = $box_backgrond_clr;
                                }
                            ?>
                            <td><input type="color" name="wczp_box_bg_clr" value="<?php echo $box_backgrond_clrs; ?>"></td>
                        </tr>
                        <tr>
                            <td>Button Background Color</td>
                          
                            <td><input type="color" name="wczp_btn_bg_clr" value="#000000" disabled="">
                            <label class="oczp_pro_link">Only available in pro version <a href="https://www.xeeshop.com/product/check-pincode-zipcode-for-shipping-woocommerce-pro/" target="_blank">link</a></label></td>
                        </tr>
                        <tr>
                            <td>Button Text Color</td>
                         
                            <td><input type="color" name="wczp_btn_txt_clr" value="#ffffff" disabled="">
                            <label class="oczp_pro_link">Only available in pro version <a href="https://www.xeeshop.com/product/check-pincode-zipcode-for-shipping-woocommerce-pro/" target="_blank">link</a></label></td>
                        </tr>
                        <tr>
                            <td>Check Button Text</td>
                            <?php 
                                $text = get_option('wczp_btn_txt'); 
                                if(empty($text)){
                                   $texts = "Check";
                                }else{
                                   $texts = $text;
                                }
                            ?>
                            <td><input type="text" name="wczp_btn_txt" value="<?php echo $texts; ?>"></td>
                        </tr>
                        <tr>
                            <td>Change Button Text</td>
                            <?php 
                                $textt = get_option('wczp_chnge_btn_txt'); 
                                if(empty($textt)){
                                   $textes = "Change";
                                }else{
                                   $textes = $textt;
                                }
                            ?>
                            <td><input type="text" name="wczp_chnge_btn_txt" value="<?php echo $textes; ?>"></td>
                        </tr>
                        <tr>
                            <td>Show Delivery Date</td>
                            <?php 
                                $delete = get_option('wczp_del_shw'); 
                            ?>
                            <td>
                                <input type="checkbox" name="wczp_del_shw" <?php if($delete == "on") { echo "checked"; }else{ echo ""; } ?>>
                            </td>
                        </tr>
                    </table>
                    <table class="wczp_table ">
                        <h2>Pincode check popup</h2>
                        <tr>
                            <td>Auto load popup in Homepage</td>
                            <?php  $auto_popup = get_option('wczp_ato_pop_shw');  ?>
                            <td>
                               <input type="checkbox" name="wczp_ato_pop_shw" <?php if($auto_popup == "on") { echo "checked"; }else{ echo ""; } ?>>Enable
                            </td>
                        </tr>
                        <tr>
                            <td>Auto load popup in Shoppage before add to cart</td>
                            <?php  $auto_popup_cat_shop = get_option('wczp_ato_pop_cat_shop');  ?> 
                            <td>
                               <input type="checkbox" name="wczp_ato_pop_cat_shop" <?php if($auto_popup_cat_shop == "on") { echo "checked"; }else{ echo ""; } ?>>Enable
                            </td>
                        </tr>
                        <tr>
                            <td>Popup heading</td>
                            <?php  $auto_popup = get_option('wczp_ato_pop_shw');  ?>
                            <td>
                               
                               <input type="text" name="wczp_header" value="Check your loaction availability info" disabled="" >
                                 <label class="oczp_pro_link">Only available in pro version <a href="https://www.xeeshop.com/product/check-pincode-zipcode-for-shipping-woocommerce-pro/" target="_blank">link</a></label>
                            </td>
                        </tr>
                    </table>
                    <table class="wczp_table">
                        <h2> Checkout Setting</h2>
                        <tr>
                            <td>Hide placeorder button if pincode is not available in list</td>
                            <?php  $check_pincode = get_option('wczp_checkpincode');  ?>
                            <td>
                               <input type="checkbox" name="wczp_checkpincode" <?php if($check_pincode == "on") { echo "checked"; }else{ echo ""; } ?>>Enable
                            </td>
                        </tr>
                        <tr>
                            <td> place order to place messge here </td>
                            <td>
                              
                               <input type="text" name="wczp_placeorder" value="Choose valid zipcode in product page then place order" disabled="">
                                 <label class="oczp_pro_link">Only available in pro version <a href="https://www.xeeshop.com/product/check-pincode-zipcode-for-shipping-woocommerce-pro/" target="_blank">link</a></label>
                            </td>
                        </tr>    
                    </table>
                    <input type="hidden" name="action" value="add_design">
                    <input type="submit" name="wczp_txtadd_design" value="Save">
                </form>
            </div>
            <?php
            if(isset($_REQUEST['wczp_txtadd_design'])) {
                update_option( 'wczp_box_bg_clr', sanitize_text_field( $_REQUEST['wczp_box_bg_clr']));
               
                update_option( 'wczp_btn_txt', sanitize_text_field( $_REQUEST['wczp_btn_txt']));
                update_option( 'wczp_chnge_btn_txt', sanitize_text_field( $_REQUEST['wczp_chnge_btn_txt']));
                update_option( 'wczp_del_shw', sanitize_text_field( $_REQUEST['wczp_del_shw']));
                update_option( 'wczp_ato_pop_shw', sanitize_text_field( $_REQUEST['wczp_ato_pop_shw']));
                update_option( 'wczp_ato_pop_cat_shop', sanitize_text_field( $_REQUEST['wczp_ato_pop_cat_shop']));
                update_option( 'wczp_checkpincode', sanitize_text_field( $_REQUEST['wczp_checkpincode']));
            }
        }


        function init() {
            global $wpdb;
            $charset_collate = $wpdb->get_charset_collate();
            $tablename = $wpdb->prefix.'wczp_postcode';

            $sql = "CREATE TABLE $tablename (
                id mediumint(9) NOT NULL AUTO_INCREMENT,
                wczp_pincode TEXT NOT NULL,
                wczp_city TEXT NOT NULL,
                wczp_state TEXT NOT NULL,
                wczp_ddate TEXT NOT NULL,
                PRIMARY KEY (id)
            ) $charset_collate;";

            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
            dbDelta( $sql );
            add_action( 'admin_menu', array($this, 'WCZP_admin_menu') );   
        }


        public static function instance() {
            if (!isset(self::$instance)) {
                self::$instance = new self();
                self::$instance->init();
            }
            return self::$instance;
        }
    }
    WCZP_menu::instance();
}


class WCZP_List_Table extends WP_List_Table {
    public function __construct() {
        parent::__construct(
            array(
                'singular' => 'singular_form',
                'plural'   => 'plural_form',
                'ajax'     => false
            )
        );
    }


    public function prepare_items() {
        $columns = $this->get_columns();
        $hidden = $this->get_hidden_columns();
        $sortable = $this->get_sortable_columns();
        $data = $this->table_data();
        usort( $data, array( &$this, 'sort_data' ) );
        $perPage = 10;
        $currentPage = $this->get_pagenum();
        $totalItems = count($data);
        $this->set_pagination_args( array(
            'total_items' => $totalItems,
            'per_page'    => $perPage
        ) );
        $data = array_slice($data,(($currentPage-1)*$perPage),$perPage);
        $this->_column_headers = array($columns, $hidden, $sortable);
        $this->items = $data;
        $this->process_bulk_action();
    }
   

    public function get_columns() {
        $columns = array(
            'cb'        => '<input type="checkbox" />',
            //'id'          => 'ID',
            'pincode'     => 'Pincode',
            'city'        => 'City',
            'state'       => 'State',
            'date'        => 'Delivery Day',
        );
        return $columns;
    }
   

    public function get_hidden_columns() {
        return array();
    }
  

    public function get_sortable_columns() {
        return array('pincode' => array('pincode', false));
    }


    private function table_data() {
        $data = array();
        global $wpdb;
        $tablename = $wpdb->prefix.'wczp_postcode';
        $wczp_records = $wpdb->get_results( "SELECT * FROM $tablename" );
        foreach ($wczp_records as $wczp_record) {
            $data[] = array(
                'id'          => $wczp_record->id,
                'pincode'     => $wczp_record->wczp_pincode,
                'city'        => $wczp_record->wczp_city,
                'state'       => $wczp_record->wczp_state,
                'date'        => $wczp_record->wczp_ddate,
            );
        }
        return $data;
    }
   

    public function column_default( $item, $column_name ) {
        switch( $column_name ) {
            case 'id':
                return $item['id'];
            case 'pincode':
                return $item['pincode'];
            case 'city':
                return $item['city'];
            case 'state':
                return $item['state'];
            case 'date':
                return $item['date'];
            default:
                return print_r( $item, true ) ;
        }
    }


    private function sort_data( $a, $b ) {
        // Set defaults
        $orderby = 'pincode';
        $order = 'asc';
        // If orderby is set, use this as the sort column
        if(!empty($_GET['orderby'])) {
            $orderby = $_GET['orderby'];
        }
        // If order is set use this as the order
        if(!empty($_GET['order'])) {
            $order = $_GET['order'];
        }
        $result = strcmp( $a[$orderby], $b[$orderby] );
        if($order === 'asc') {
            return $result;
        }
        return -$result;
    }


    public function get_bulk_actions() {
        return array(
            'delete' => __( 'Delete', 'wczp' ),
        );
    }


    function column_cb($item) {
        return sprintf(
            '<input type="checkbox" name="id[]" value="%s" />', $item['id']
        );    
    }

    function WCZP_recursive_sanitize_text_field($array) {
         
        foreach ( $array as $key => &$value ) {
            if ( is_array( $value ) ) {
                $value = $this->WCZP_recursive_sanitize_text_field($value);
            }else{
                $value = sanitize_text_field( $value );
            }
        }
        return $array;
    }



    public function process_bulk_action() {
        global $wpdb;
        $tablename = $wpdb->prefix.'wczp_postcode';
        // security check!
        if ( isset( $_POST['_wpnonce'] ) && ! empty( $_POST['_wpnonce'] ) ) {
            $nonce  = filter_input( INPUT_POST, '_wpnonce', FILTER_SANITIZE_STRING );
            $action = 'bulk-' . $this->_args['plural'];

            if ( ! wp_verify_nonce( $nonce, $action ) )
                wp_die( 'Nope! Security check failed!' );
        }

        $action = $this->current_action();
        switch ( $action ) {

            case 'delete':
                $ids = isset($_REQUEST['id']) ? $this->WCZP_recursive_sanitize_text_field($_REQUEST['id']) : array();
                //print_r($ids);
                if (is_array($ids)) $ids = implode(',', $ids);

                    if (!empty($ids)) {
                        $wpdb->query("DELETE FROM $tablename WHERE id IN($ids)");
                    }

                wp_redirect( $_SERVER['HTTP_REFERER'] );

                break;

            default:
                // do nothing or something else
                return;
                break;
        }
        return;
    }


    function column_pincode($item) {
        
        $actions = array(
            'edit'      => sprintf('<a href="?page=post-code&action=%s&id=%s">Edit</a>','oc_edit',$item['id']),
            'delete'    => sprintf('<a href="?page=post-code&action=%s&id=%s">Delete</a>','delete',$item['id']),
        );

        return sprintf('%1$s %2$s', $item['pincode'], $this->row_actions($actions) );
    }
}
<?php 
/**
 * Plugin Name: Tailors Online Custom Price Addon
 * Description: Set additional price of customization
 * Version: Release 1.0.0
 * Author: Anirban Biswas
 * Author URI: https://github.com/Anirban013/
 **/

/**
 * Checks weather prerequisite plugins are activated or not
 */
function check_for_prerequisites(){
    $plugins = get_option('active_plugins');
    if(!in_array("woocommerce/woocommerce.php", $plugins) || !in_array("tailors-online/customizer.php", $plugins)){
	    deactivate_plugins( plugin_basename( __FILE__ ) );
        wp_die( 'This plugin requires WooCommerce and Tailors Online plugins to be activated!!!' );
    }
}

register_activation_hook(__FILE__, 'check_for_prerequisites');

// add_action('admin_enqueue_scripts', 'tailor_woocom_enqueue_scripts', 10);
function tailor_woocom_enqueue_scripts(){
    $posts = get_all_customizer_posts();
    $current_customizer = get_post_meta( $_GET['post'], $key = 'product_customizer', $single = true );
    $originalPrice = get_post_meta( $_GET['post'], $key = '_regular_price', $single = true );
    if(!empty($current_customizer)){
    	$originalPrice = $originalPrice - get_post_meta( $current_customizer, $key = '_customizer_price', $single = true );
    }
    elseif(empty($originalPrice)){
    	
    	$originalPrice = 0;
    }

    $localized_data = array(
        'all_customizer' => $posts,
        'original_price' => $originalPrice
    );

    wp_register_script( 'tailor-js', plugins_url( 'tailors-online-custom-price-addon/' ).'tailor-js/tailor-js.js');
    wp_enqueue_script( 'tailor-js');

    wp_localize_script( 'tailor-js', 'tailor_localized_data', $localized_data );
}

add_action( 'tailor_edit_customizer', 'tailor_woocom_edit_custom_price', $priority = 10);

function tailor_woocom_edit_custom_price(){
    ?>
    <h4>Add Price:</h4>
    <input type="number" class="tg-formcontrol" name="tailor_woocom_price" value="<?php echo get_post_meta( $_GET['post'], $key = '_customizer_price', $single = true ); ?>">
    <?php 
} 

add_action( 'tailor_add_new_customizer', 'tailor_woocom_add_custom_price', $priority = 10);
function tailor_woocom_add_custom_price(){
    ?>
    <h4>Add Price:</h4>
    <input type="number" class="tg-formcontrol" name="tailor_woocom_price">
    <?php 
}


add_action( 'save_post', 'save_tailor_data', $priority = 10, $accepted_args = 1 );
function save_tailor_data($post_id){
    if(get_post_type( $post_id ) == 'wp_customizer' && isset($_POST['tailor_woocom_price']))
        update_post_meta( $post_id, $meta_key = '_customizer_price', $_POST['tailor_woocom_price']);
}


if(!function_exists('get_all_customizer_posts')){
    function get_all_customizer_posts(){
        $args = array(
            'fields'          => 'ids',
            'posts_per_page'   => -1,
            'post_type'        => 'wp_customizer',   
            'post_status'      => 'publish',
        );
        $posts = get_posts( $args );
        $data = array();
        $i = 0;
        foreach ($posts as $value) {
            $data[$i]['id'] = $value;
            $data[$i]['price'] = (int)get_post_meta( $value, $key = '_customizer_price', $single = true );
            $i++;
        }        
        return $data;
    }
}

add_action( 'woocommerce_before_calculate_totals', 'woocom_add_customizer_rates_to_cart' );
function woocom_add_customizer_rates_to_cart( $cart_object ) {
	 	
 	if ( is_admin() && ! defined( 'DOING_AJAX' ) )
    	return;

    foreach ( $cart_object->cart_contents as $key => $value ) {
    		
		if(array_key_exists('customizer_id', $value['cart_data'])){


			$customization_price = get_post_meta( $value['cart_data']['customizer_id'], $key = '_customizer_price', $single = true );
			if( empty( $customization_price ) )
				return;
			$price = empty( get_post_meta( $value['product_id'], '_sale_price', true ) ) ? get_post_meta( $value['product_id'], '_regular_price', true ) : get_post_meta( $value['product_id'], '_sale_price', true );

			$customization_price += $price;
			
        	$value['data']->set_price( $customization_price );
		}
	}
}
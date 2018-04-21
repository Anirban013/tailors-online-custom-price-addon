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

add_action('admin_enqueue_scripts', 'tailor_woocom_enqueue_scripts', 10);
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
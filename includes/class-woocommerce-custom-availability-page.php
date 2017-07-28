<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class WooCommerce_Custom_Availability_Page {
	
	function __construct()
	{
		add_action( 'admin_menu' , array( $this , 'add_option_page' ) );
	}

	public function add_option_page()
	{
		add_submenu_page( 
			'edit.php?post_type=product',
			 __( 'Custom Availability' , 'woocommerce-custom-availability' ),
			 __( 'Custom Availability' , 'woocommerce-custom-availability' ),
			 'edit_products' ,
			 'wc-custom-availability',
			 array( $this , 'woocommerce_custom_availability_page_cb' )
			);
	}

	public function form_submit_handler()
	{
		if( isset( $_POST['woocommerce-custom-availability-bulk'] ) ) {
			if ( ! isset( $_POST['wc-custom-availability'] ) ||
				 ! wp_verify_nonce( $_POST['wc-custom-availability'], 'wc-custom-availability-bulk' )  ) {
			   return false;
			} 
			if( isset( $_POST['_custom_availability'] ) && !empty( $_POST['_custom_availability'] ) ) {
				$custom_availabilities = $_POST['_custom_availability'];
				foreach ($custom_availabilities as $post_id => $custom_availability) {
					update_post_meta( $post_id , '_custom_availability' , sanitize_text_field( $custom_availability ) );
				}
				return true;
			}
		}
		return null;
	}

	public function woocommerce_custom_availability_page_cb()
	{
		$form_status = $this->form_submit_handler();
		$wc_custom_availability_table = new WooCommerce_Custom_Availability_Table();
		$wc_custom_availability_table->prepare_items();
		?>
		    <div class="wrap">
		    	<h1 class="wp-heading-inline">Woocommerce Custom Availability</h1>
		    	<hr class="wp-header-end">
		    	<?php
		    	// show user frienly message.
				if( false === $form_status ) {
					echo '<div id="message" class="notice notice-error is-dismissible"><p>'.__( 'Error! Please try again later.' , 'woocommerce-custom-availability' ).'</p><button type="button" class="notice-dismiss"><span class="screen-reader-text">'.__( 'Dismiss this notice.' , 'woocommerce-custom-availability' ).'</span></button></div>';
				} elseif ( true === $form_status ) {
					echo '<div id="message" class="notice notice-success is-dismissible"><p>'.__( 'Custom availability successfully updated.' , 'woocommerce-custom-availability' ).'</p><button type="button" class="notice-dismiss"><span class="screen-reader-text">'.__( 'Dismiss this notice.' , 'woocommerce-custom-availability' ).'</span></button></div>';
				}
				$current_url = add_query_arg();
		    	?>
		    	<form action="<?php echo $current_url; ?>" method="POST">
		    		<?php wp_nonce_field( 'wc-custom-availability-bulk' , 'wc-custom-availability' ); ?>
			        <?php $wc_custom_availability_table->display(); ?>
			        <input name="woocommerce-custom-availability-bulk" value="<?php _e( 'Save Changes' , 'woocommerce-custom-availability' ) ?>" type="submit" class="button button-primary button-large">
		        </form>
		    </div>
		<?php
	}

}

$woocommerce_custom_availability_page = new WooCommerce_Custom_Availability_Page();
<?php
/*
Plugin Name: ByA Swap Images
*/
class ByA_Swap_Images {

	function __construct() {
		$this->hooks();
	}
	
	function hooks() {
		add_action( 'wp_head', array( $this, 'js_swap_image' ) );
		add_action( 'wp_ajax_swap_image', array( $this, 'handle_swap_image' ) );
		add_action( 'wp_ajax_nopriv_swap_image', array( $this, 'handle_swap_image' ) );
	}
	
	function js_swap_image() {
		wp_enqueue_script( 'jquery' );
		wp_print_scripts();
?>
		<script>
			jQuery(document).ready(

				function() {
				
					function showVariation(response) {
						var obj = jQuery.parseJSON(response);
						var display = 'block';

						if (obj.product_stock == 0)
							jQuery('.stock').removeClass('in-stock').addClass('out-stock').html('<span>Temporalmente agotado</span>');
						else
							jQuery('.stock').removeClass('out-stock').addClass('in-stock').html('<span>Disponible</span>');

						jQuery('.price').text(obj.product_price + ' \u20ac');
				
						//jQuery('.product-description').html(obj.product_desc);
						jQuery('.tabs-nav li:first').addClass('active').siblings().removeClass('active');
						if (jQuery('#tab1').length) {
							jQuery('#tab1').replaceWith('<div class="tab-content" id="tab1" style="display: block; ">' + obj.product_tab1 + '</div>');
							display = 'none';
						}
						
						if (jQuery('#tab4').length) {
							jQuery('#tab4').replaceWith('<div class="tab-content" id="tab4" style="display: ' + display + ';">' + obj.product_tab4 + '</div>');
							display = 'none';
						}
						
						if (jQuery('#tab5').length) {
							jQuery('#tab5').replaceWith('<div class="tab-content" id="tab5" style="display: ' + display + ';">' + obj.product_tab5 + '</div>');
						}
	      				
	      				jQuery('.product-images').load( 'http://brazosyabrazos.es/bya', { product_variation_id: obj.product_variation_id } );
	    			}
				
					jQuery('select').change(

						function() {
							var pro_id = jQuery('input[name=product_id]').val();
							var var_id = jQuery(this).val();
							var tax_id = 'wpsc-variation';

							jQuery.post(
								'<?php echo get_option('siteurl') . '/wp-admin/admin-ajax.php' ?>',

								{
									action			: 'swap_image',
									variation_id	: var_id,
									product_id		: pro_id,
									taxonomy_id		: tax_id,
								},

								function(response) {
									showVariation(response);
								}
							);
						}
					);
				}
			);
		</script>
<?php

	}
	
	function handle_swap_image() {
		global $wpdb;

		$product_id = $_POST['product_id'];
		$variation_id = $_POST['variation_id'];
		$taxonomy_id = $_POST['taxonomy_id'];

        $product = get_post( $product_id );
        $product_variation_id = wpsc_get_child_object_in_terms( $product_id, $variation_id, $taxonomy_id );
        $product_variation = get_post( $product_variation_id );
		
		$json_variation = array();
		$json_variation['product_variation_id'] = $product_variation_id;

        $json_variation['product_price'] = get_post_meta($product_variation_id, '_wpsc_price' , true);
        $json_variation['product_stock'] = get_post_meta($product_variation_id, '_wpsc_stock' , true);
        
        // make it configurable
        // $json_variation['product_desc'] = $product_variation->post_content;
        $excerpt = $product_variation->post_excerpt;
        $tab4 = get_post_meta($product_variation_id, '_etheme_custom_tab1' , true);
        $tab5 = get_post_meta($product_variation_id, '_etheme_custom_tab2' , true);
        $json_variation['product_tab1'] = empty($excerpt) ? $product->post_excerpt : $excerpt;
  		$json_variation['product_tab4'] = empty($tab4) ? get_post_meta($product_id, '_etheme_custom_tab1' , true): $tab4;
  		$json_variation['product_tab5'] = empty($tab5) ? get_post_meta($product_id, '_etheme_custom_tab2' , true): $tab5;

		echo json_encode($json_variation);
		exit;
	}
}

$bya_swap_images = new ByA_Swap_Images;
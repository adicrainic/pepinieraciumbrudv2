<?php

  $flower_shop_elementor_theme_custom_setting_css = '';

	// Global Color
	$flower_shop_elementor_theme_color = get_theme_mod('flower_shop_elementor_theme_color', '#7469B6');

	$flower_shop_elementor_theme_custom_setting_css .=':root {';
		$flower_shop_elementor_theme_custom_setting_css .='--primary-theme-color: '.esc_attr($flower_shop_elementor_theme_color ).'!important;';
	$flower_shop_elementor_theme_custom_setting_css .='}';

	// Scroll to top alignment
	$flower_shop_elementor_scroll_alignment = get_theme_mod('flower_shop_elementor_scroll_alignment', 'right');

    if($flower_shop_elementor_scroll_alignment == 'right'){
        $flower_shop_elementor_theme_custom_setting_css .='.scroll-up{';
            $flower_shop_elementor_theme_custom_setting_css .='right: 30px;!important;';
			$flower_shop_elementor_theme_custom_setting_css .='left: auto;!important;';
        $flower_shop_elementor_theme_custom_setting_css .='}';
    }else if($flower_shop_elementor_scroll_alignment == 'center'){
        $flower_shop_elementor_theme_custom_setting_css .='.scroll-up{';
            $flower_shop_elementor_theme_custom_setting_css .='left: calc(50% - 10px) !important;';
        $flower_shop_elementor_theme_custom_setting_css .='}';
    }else if($flower_shop_elementor_scroll_alignment == 'left'){
        $flower_shop_elementor_theme_custom_setting_css .='.scroll-up{';
            $flower_shop_elementor_theme_custom_setting_css .='left: 30px;!important;';
			$flower_shop_elementor_theme_custom_setting_css .='right: auto;!important;';
        $flower_shop_elementor_theme_custom_setting_css .='}';
    }	

    // Related Product

    $flower_shop_elementor_show_related_product = get_theme_mod('flower_shop_elementor_show_related_product', true );

    if($flower_shop_elementor_show_related_product != true){
        $flower_shop_elementor_theme_custom_setting_css .='.related.products{';
            $flower_shop_elementor_theme_custom_setting_css .='display: none;';
        $flower_shop_elementor_theme_custom_setting_css .='}';
    }

    // Featured Image Hover Effect
    $flower_shop_elementor_show_featured = get_theme_mod('flower_shop_elementor_featured_image_hide_show', 1);
    $flower_shop_elementor_hover_effect = get_theme_mod('flower_shop_elementor_single_post_featured_image_hover','none');

    if ( $flower_shop_elementor_show_featured && $flower_shop_elementor_hover_effect !== 'none' ) {

    $flower_shop_elementor_theme_custom_setting_css .= '
    .post-img img{
        transition: all 0.4s ease;
    }';

    if ( $flower_shop_elementor_hover_effect === 'zoom-in' ) {
        $flower_shop_elementor_theme_custom_setting_css .= '
        .post-img:hover img{
            transform: scale(1.2);
        }';
    }

    if ( $flower_shop_elementor_hover_effect === 'zoom-out' ) {
        $flower_shop_elementor_theme_custom_setting_css .= '
        .post-img img{ transform: scale(1.2); }
        .post-img:hover img{ transform: scale(1); }';
    }

    if ( $flower_shop_elementor_hover_effect === 'grayscale' ) {
        $flower_shop_elementor_theme_custom_setting_css .= '
        .post-img img{ filter: grayscale(100%); }
        .post-img:hover img{ filter: grayscale(0); }';
    }

    if ( $flower_shop_elementor_hover_effect === 'sepia' ) {
        $flower_shop_elementor_theme_custom_setting_css .= '
        .post-img:hover img{ filter: sepia(100%); }';
    }

    if ( $flower_shop_elementor_hover_effect === 'blur' ) {
        $flower_shop_elementor_theme_custom_setting_css .= '
        .post-img:hover img{ filter: blur(3px); }';
    }

    if ( $flower_shop_elementor_hover_effect === 'bright' ) {
        $flower_shop_elementor_theme_custom_setting_css .= '
        .post-img:hover img{ filter: brightness(1.3); }';
    }

    if ( $flower_shop_elementor_hover_effect === 'translate' ) {
        $flower_shop_elementor_theme_custom_setting_css .= '
        .post-img:hover img{ transform: translateY(-10px); }';
    }

    if ( $flower_shop_elementor_hover_effect === 'scale' ) {
        $flower_shop_elementor_theme_custom_setting_css .= '
        .post-img:hover img{ transform: scale(1.1); }';
    }
}

// Product Featured Image Hover Effect
    $flower_shop_elementor_show_featured = get_theme_mod('flower_shop_elementor_featured_image_hide_show', 1);
    $flower_shop_elementor_hover_effect = get_theme_mod('flower_shop_elementor_product_featured_image_hover','none');

    if ( $flower_shop_elementor_show_featured && $flower_shop_elementor_hover_effect !== 'none' ) {

    $flower_shop_elementor_theme_custom_setting_css .= '
    .product-img img{
        transition: all 0.4s ease;
    }';

    if ( $flower_shop_elementor_hover_effect === 'zoom-in' ) {
        $flower_shop_elementor_theme_custom_setting_css .= '
        .product-img:hover img{
            transform: scale(1.2);
        }';
    }

    if ( $flower_shop_elementor_hover_effect === 'zoom-out' ) {
        $flower_shop_elementor_theme_custom_setting_css .= '
        .product-img img{ transform: scale(1.2); }
        .product-img:hover img{ transform: scale(1); }';
    }

    if ( $flower_shop_elementor_hover_effect === 'grayscale' ) {
        $flower_shop_elementor_theme_custom_setting_css .= '
        .product-img img{ filter: grayscale(100%); }
        .product-img:hover img{ filter: grayscale(0); }';
    }

    if ( $flower_shop_elementor_hover_effect === 'sepia' ) {
        $flower_shop_elementor_theme_custom_setting_css .= '
        .product-img:hover img{ filter: sepia(100%); }';
    }

    if ( $flower_shop_elementor_hover_effect === 'blur' ) {
        $flower_shop_elementor_theme_custom_setting_css .= '
        .product-img:hover img{ filter: blur(3px); }';
    }

    if ( $flower_shop_elementor_hover_effect === 'bright' ) {
        $flower_shop_elementor_theme_custom_setting_css .= '
        .product-img:hover img{ filter: brightness(1.3); }';
    }

    if ( $flower_shop_elementor_hover_effect === 'translate' ) {
        $flower_shop_elementor_theme_custom_setting_css .= '
        .product-img:hover img{ transform: translateY(-10px); }';
    }

    if ( $flower_shop_elementor_hover_effect === 'scale' ) {
        $flower_shop_elementor_theme_custom_setting_css .= '
        .product-img:hover img{ transform: scale(1.1); }';
    }
}   
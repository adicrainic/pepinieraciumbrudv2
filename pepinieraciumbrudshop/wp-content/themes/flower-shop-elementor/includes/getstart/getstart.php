<?php

function get_page_id_by_title($flower_shop_elementor_pagename){
  $flower_shop_elementor_args = array(
 'post_type' => 'page',
 'posts_per_page' => 1,
 'title' => $flower_shop_elementor_pagename
  );
  $flower_shop_elementor_query = new WP_Query( $flower_shop_elementor_args );    $flower_shop_elementor_page_id = '1';
 if (isset($flower_shop_elementor_query->post->ID)) {
      $flower_shop_elementor_page_id = $flower_shop_elementor_query->post->ID;
  } return $flower_shop_elementor_page_id;
}
//about theme info
add_action( 'admin_menu', 'flower_shop_elementor_gettingstarted' );
function flower_shop_elementor_gettingstarted() {
	add_theme_page( esc_html__('Flower Shop Elementor', 'flower-shop-elementor'), esc_html__('Flower Shop Elementor', 'flower-shop-elementor'), 'edit_theme_options', 'flower_shop_elementor_about', 'flower_shop_elementor_mostrar_guide');
}

// Add a Custom CSS file to WP Admin Area
function flower_shop_elementor_admin_theme_style() {
	wp_enqueue_style('flower-shop-elementor-custom-admin-style', esc_url(get_template_directory_uri()) . '/includes/getstart/getstart.css');
	wp_enqueue_script('flower-shop-elementor-tabs', esc_url(get_template_directory_uri()) . '/includes/getstart/js/tab.js');
	wp_enqueue_style( 'font-awesome-css', get_template_directory_uri().'/assets/css/fontawesome-all.css' );

	// Admin notice code START
	wp_register_script('flower-shop-elementor-notice', esc_url(get_template_directory_uri()) . '/includes/getstart/js/notice.js', array('jquery'), time(), true);
	wp_enqueue_script('flower-shop-elementor-notice');
	// Admin notice code END
}
add_action('admin_enqueue_scripts', 'flower_shop_elementor_admin_theme_style');

// Changelog
if ( ! defined( 'FLOWER_SHOP_ELEMENTOR_CHANGELOG_URL' ) ) {
    define( 'FLOWER_SHOP_ELEMENTOR_CHANGELOG_URL', get_template_directory() . '/readme.txt' );
}

function flower_shop_elementor_changelog_screen() {
	global $wp_filesystem;
	$flower_shop_elementor_changelog_file = apply_filters( 'flower_shop_elementor_changelog_file', FLOWER_SHOP_ELEMENTOR_CHANGELOG_URL );

	if ( $flower_shop_elementor_changelog_file && is_readable( $flower_shop_elementor_changelog_file ) ) {
		WP_Filesystem();
		$flower_shop_elementor_changelog = $wp_filesystem->get_contents( $flower_shop_elementor_changelog_file );
		$flower_shop_elementor_changelog_list = flower_shop_elementor_parse_changelog( $flower_shop_elementor_changelog );

		
		echo '<div id="flower-shop-elementor-changelog-container">';
		echo wp_kses_post( $flower_shop_elementor_changelog_list );
		echo '</div>';
		echo '<button id="flower-shop-elementor-load-more" class="button button-primary" style="margin-top:15px;">Load More</button>';
	}
}

function flower_shop_elementor_parse_changelog( $flower_shop_elementor_content ) {
	$flower_shop_elementor_content = explode ( '== ', $flower_shop_elementor_content );
	$flower_shop_elementor_changelog_isolated = '';

	foreach ( $flower_shop_elementor_content as $key => $flower_shop_elementor_value ) {
		if ( strpos( $flower_shop_elementor_value, 'Changelog ==' ) === 0 ) {
	    	$flower_shop_elementor_changelog_isolated = str_replace( 'Changelog ==', '', $flower_shop_elementor_value );
	    }
	}

	$flower_shop_elementor_changelog_array = explode( '= ', $flower_shop_elementor_changelog_isolated );
	unset( $flower_shop_elementor_changelog_array[0] );

	$flower_shop_elementor_changelog = '<div class="changelog">';
	foreach ( $flower_shop_elementor_changelog_array as $flower_shop_elementor_value ) {
		$flower_shop_elementor_value = preg_replace( '/\n+/', '</span><span>', $flower_shop_elementor_value );
		$flower_shop_elementor_value = '<div class="block-changelog"><span class="heading">= ' . $flower_shop_elementor_value . '</span></div>';
		$flower_shop_elementor_changelog .= str_replace( '<span></span>', '', $flower_shop_elementor_value );
	}
	$flower_shop_elementor_changelog .= '</div>';

	return wp_kses_post( $flower_shop_elementor_changelog );
}

//guidline for about theme
function flower_shop_elementor_mostrar_guide() { 
	//custom function about theme customizer
	$flower_shop_elementor_return = add_query_arg( array()) ;
	$flower_shop_elementor_theme = wp_get_theme( 'flower-shop-elementor' );
	?>
<div class="container-getstarted">
		<div class="inner-side-content1">
			<div class="tab-outer-box">
				<img src="<?php echo esc_url(get_template_directory_uri()); ?>/includes/getstart/images/sticky-header-logo.png" />
			</div>
		    <div class="coupon-container-box-left">
			    <div class="iner-sidebar-pro-btn">
				    <span class="premium-btn"><a href="<?php echo esc_url( FLOWER_SHOP_ELEMENTOR_BUY_NOW ); ?>" target="_blank"><?php esc_html_e('Buy Premium Theme', 'flower-shop-elementor'); ?></a>
				    </span>
			    </div>
		    </div>
        </div>					
   <div class="top-head">
	    <div class="top-title">
		     <h2><?php esc_html_e( 'Flower Shop Elementor', 'flower-shop-elementor' ); ?></h2>
		     <h4><?php esc_html_e( 'Welcome to WP Elemento Theme!', 'flower-shop-elementor' ); ?></h4>
		     <p><?php esc_html_e( 'Click on the quick start button to import the demo.', 'flower-shop-elementor' ); ?></p>
			    <div class="iner-sidebar-pro-btn">
					<?php if(!class_exists('WPElemento_Importer_ThemeWhizzie')){
						$flower_shop_elementor_plugin_ins = Flower_Shop_Elementor_Plugin_Activation_WPElemento_Importer::get_instance();
						$flower_shop_elementor_actions = $flower_shop_elementor_plugin_ins->flower_shop_elementor_recommended_actions;
					?>
					<div class="flower-shop-elementor-recommended-plugins ">
						<div class="flower-shop-elementor-action-list">
							<?php if ($flower_shop_elementor_actions): foreach ($flower_shop_elementor_actions as $flower_shop_elementor_key => $flower_shop_elementor_actionValue): ?>
									<div class="flower-shop-elementor-action" id="<?php echo esc_attr($flower_shop_elementor_actionValue['id']);?>">
										<div class="action-inner plugin-activation-redirect">
											<?php echo wp_kses_post($flower_shop_elementor_actionValue['link']); ?>
										</div>
									</div>
								<?php endforeach;
							endif; ?>
						</div>
					</div>
				   <?php }else{ ?>
					<span class="quick-btn">
				    <?php if (isset($_GET['imported']) && $_GET['imported'] == 'true'): ?>
                        <a href="<?php echo esc_url( site_url() ); ?>" target="_blank"><?php esc_html_e('Visit Site', 'flower-shop-elementor'); ?></a>
						<?php
						$flower_shop_elementor_page_id = get_page_id_by_title('Home');
						?>
						<a href="<?php echo esc_url( admin_url('post.php?post=' . $flower_shop_elementor_page_id . '&action=elementor') ); ?>" 
							target="_blank" class="elementor-edit-btn"><?php esc_html_e('Edit With Elementor', 'flower-shop-elementor'); ?>
						</a>
                    <?php else: ?>
                        <a href="<?php echo esc_url( admin_url('admin.php?page=wpelementoimporter-wizard') ); ?>"><?php esc_html_e('Quick Start', 'flower-shop-elementor'); ?></a>
                    <?php endif; ?>
					<?php } ?>
				   </span>
				    <span class="premium-btn"><a href="<?php echo esc_url( FLOWER_SHOP_ELEMENTOR_BUY_NOW ); ?>" target="_blank"><?php esc_html_e('Buy Premium', 'flower-shop-elementor'); ?></a>
				    </span>
				    <span class="demo-btn"><a href="<?php echo esc_url( FLOWER_SHOP_ELEMENTOR_LIVE_DEMO ); ?>" target="_blank"><?php esc_html_e('Live Demo', 'flower-shop-elementor'); ?></a>
				    </span>
				    <span class="doc-btn"><a href="<?php echo esc_url( FLOWER_SHOP_ELEMENTOR_THEME_BUNDLE ); ?>" target="_blank"><?php esc_html_e('Theme Bundle at $99', 'flower-shop-elementor'); ?></a>
				    </span>
			    </div>
            </div>			
		<div class="inner-side-content">
			<div class="tab-outer-box">
				<img src="<?php echo esc_url(get_template_directory_uri()); ?>/screenshot.png" />
			</div>
			<div class="top-right">
			  <span class="version"><?php esc_html_e( 'Version', 'flower-shop-elementor' ); ?>: <?php echo esc_html($flower_shop_elementor_theme['Version']);?></span>
		    </div>
		</div>
    </div>
    <div class="inner-cont">
	    <div class="tab-outer-box1">
		   <div class="tab-inner-box">
			   <div class= "bundle-box">
				    <img src="<?php echo esc_url(get_template_directory_uri()); ?>/includes/getstart/images/bundle.png"/>
				    <h1><?php esc_html_e('Elementor WordPress Theme Bundle', 'flower-shop-elementor'); ?></h1>
			     <div>
				    <p class="product-price"><?php esc_html_e('Price:', 'flower-shop-elementor'); ?>
                        <span class="regular-price"><?php esc_html_e('$3,240.00', 'flower-shop-elementor'); ?></span>
                        <span class="sale-price"><?php esc_html_e('$99.00', 'flower-shop-elementor'); ?></span>
                    </p>
					<p><?php esc_html_e('The Elementor WordPress Theme Bundle offers a stunning collection of 125+ Premium Elementor Themes', 'flower-shop-elementor'); ?></p>
                 </div>
				</div> 
			    <div class="offer-box"> 
				    <div class="offer1-box">
                       <span class="off-text1"><a href="<?php echo esc_url( FLOWER_SHOP_ELEMENTOR_THEME_BUNDLE ); ?>" target="_blank"><?php esc_html_e('Buy Bundle at 20% Discount', 'flower-shop-elementor'); ?></a></span>
				    </div> 
		        </div>
			</div>	
		</div>	
		<div class="tab-outer-box2">
			<div class="tab-outer-box-2-1">
			  <h3><?php esc_html_e( 'Customizer Setting', 'flower-shop-elementor' ); ?></h3>
			  <div class="lite-theme-inner">
				<div>
					<h3><?php esc_html_e('Theme Customizer', 'flower-shop-elementor'); ?></h3>
					<p><?php esc_html_e('To begin customizing your website, start by clicking "Customize".', 'flower-shop-elementor'); ?></p>
					<div class="info-link">
					   <a target="_blank" href="<?php echo esc_url( admin_url('customize.php') ); ?>"><?php esc_html_e('Open customizer', 'flower-shop-elementor'); ?></a>
					</div>
				</div>
				<div>
					<h3><?php esc_html_e('Help Docs', 'flower-shop-elementor'); ?></h3>
					<p><?php esc_html_e('The complete procedure to configure and manage a WordPress Website from the beginning is shown in this documentation .', 'flower-shop-elementor'); ?></p>
					<div class="info-link">
						<a href="<?php echo esc_url( FLOWER_SHOP_ELEMENTOR_FREE_THEME_DOC ); ?>" target="_blank"><?php esc_html_e('Documentation', 'flower-shop-elementor'); ?></a>
					</div>
				</div>
				<div>
					<h3><?php esc_html_e('Need Support?', 'flower-shop-elementor'); ?></h3>
					<p><?php esc_html_e('Our dedicated team is well prepared to help you out in case of queries and doubts regarding our theme.', 'flower-shop-elementor'); ?></p>
					<div class="info-link">
						<a href="<?php echo esc_url( FLOWER_SHOP_ELEMENTOR_SUPPORT ); ?>" target="_blank"><?php esc_html_e('Support Forum', 'flower-shop-elementor'); ?></a>
					</div>
				</div>
				<div>
					<h3><?php esc_html_e('Reviews & Testimonials', 'flower-shop-elementor'); ?></h3>
					<p> <?php esc_html_e('All the features and aspects of this WordPress Theme are phenomenal. I\'d recommend this theme to all.', 'flower-shop-elementor'); ?></p>
					<div class="info-link">
						<a href="<?php echo esc_url( FLOWER_SHOP_ELEMENTOR_REVIEW ); ?>" target="_blank"><?php esc_html_e('Review', 'flower-shop-elementor'); ?></a>
					</div>
				</div>
            </div>	
		</div>
			<div class="tab-outer-box-2-2">
			  <h3><?php esc_html_e( 'Link to customizer', 'flower-shop-elementor' ); ?></h3>
				<div class="first-row">
					<div class="row-box">
						<div class="row-box1">
							<span class="dashicons dashicons-buddicons-buddypress-logo"></span><a href="<?php echo esc_url( admin_url('customize.php?autofocus[control]=custom_logo') ); ?>" target="_blank"><?php esc_html_e('Upload your Website logo','flower-shop-elementor'); ?></a>
						</div>
						<div class="row-box2">
							<span class="dashicons dashicons-menu"></span><a href="<?php echo esc_url( admin_url('customize.php?autofocus[panel]=nav_menus') ); ?>" target="_blank"><?php esc_html_e('Edit Your Menus','flower-shop-elementor'); ?></a>
						</div>
					</div>
							
					<div class="row-box">
						<div class="row-box1">
							<span class="dashicons dashicons-align-center"></span><a href="<?php echo esc_url( admin_url('customize.php?autofocus[section]=header_image') ); ?>" target="_blank"><?php esc_html_e('Add Header Image','flower-shop-elementor'); ?></a>
						</div>
						<div class="row-box2">
							<span class="dashicons dashicons-screenoptions"></span><a href="<?php echo esc_url( admin_url('customize.php?autofocus[panel]=widgets') ); ?>" target="_blank"><?php esc_html_e('Add Footer Widget','flower-shop-elementor'); ?></a>
						</div>
					</div>
				</div>
            </div>	
			<div class="tab-outer-box-2-3">
				<h3><?php esc_html_e( 'Change log', 'flower-shop-elementor' ); ?></h3>	
		     <?php flower_shop_elementor_changelog_screen(); ?>
          </div>	
        </div>
    </div>
</div>	
<?php } ?>
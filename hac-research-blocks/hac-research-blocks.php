<?php
/**
 * Plugin Name: HAC Research Blocks (concept)
 * Description: Concept for the Housing Assistance Council website RFP: a data chart block and research-brief patterns, with a sample brief. Prepared by Ben Upham; not an official HAC plugin.
 * Version: 0.1.0
 * Requires at least: 6.5
 * Author: Ben Upham
 * Author URI: https://benupham.com
 * License: GPL-2.0-or-later
 * Text Domain: hac-research-blocks
 */

defined( 'ABSPATH' ) || exit;

define( 'HAC_RB_DIR', __DIR__ );
define( 'HAC_RB_URL', plugin_dir_url( __FILE__ ) );
define( 'HAC_RB_SLUG', 'section-515-portfolio-continues-to-shrink' );

add_filter( 'block_categories_all', function ( $cats ) {
	array_unshift( $cats, array( 'slug' => 'hac-research', 'title' => __( 'HAC research', 'hac-research-blocks' ) ) );
	return $cats;
} );

add_action( 'init', function () {
	register_block_type( HAC_RB_DIR . '/blocks/chart' );

	register_block_pattern_category( 'hac-research', array( 'label' => __( 'HAC research', 'hac-research-blocks' ) ) );
	foreach ( glob( HAC_RB_DIR . '/patterns/*.php' ) as $file ) {
		$pattern = include $file;
		register_block_pattern( 'hac/' . basename( $file, '.php' ), $pattern );
	}
} );

add_action( 'enqueue_block_assets', function () {
	wp_enqueue_style( 'hac-research', HAC_RB_URL . 'assets/research.css', array(), '0.1.0' );
} );

/** Concept notice on every front-end page. */
add_action( 'wp_body_open', function () {
	echo '<div class="hac-concept" role="note">' . esc_html__( 'Concept demo prepared by Ben Upham for the Housing Assistance Council website RFP. Not an official HAC site.', 'hac-research-blocks' ) . ' <a href="' . esc_url( admin_url( 'post.php?post=' . hac_rb_demo_id() . '&action=edit' ) ) . '">' . esc_html__( 'Open this brief in the editor', 'hac-research-blocks' ) . '</a></div>';
} );

/** Notice in the editor. */
add_action( 'enqueue_block_editor_assets', function () {
	wp_add_inline_script( 'wp-notices', "wp.domReady(function(){ wp.data.dispatch('core/notices').createNotice('info', 'Concept demo for HAC. Try it: insert a Data chart block (type /chart), paste a table from a spreadsheet, or use a pattern from the HAC research category. Nothing here is saved beyond your browser.', { isDismissible: true }); });" );
} );

function hac_rb_demo_id() {
	$post = get_page_by_path( HAC_RB_SLUG, OBJECT, 'post' );
	return $post ? (int) $post->ID : 0;
}

/** Create the sample brief and tidy the demo site. */
function hac_rb_install_demo() {
	if ( hac_rb_demo_id() ) {
		return;
	}
	$content = file_get_contents( HAC_RB_DIR . '/demo/brief.html' );
	$cat     = wp_insert_term( 'Rural Research Briefs', 'category' );
	$cat_id  = is_wp_error( $cat ) ? (int) get_cat_ID( 'Rural Research Briefs' ) : (int) $cat['term_id'];
	wp_insert_post( array(
		'post_title'    => "USDA's Section 515 Multifamily Housing Portfolio Continues to Shrink",
		'post_name'     => HAC_RB_SLUG,
		'post_content'  => wp_slash( $content ),
		'post_excerpt'  => 'Between June 2021 and March 2026, 621 Section 515 properties containing 14,928 affordable apartments left the portfolio.',
		'post_status'   => 'publish',
		'post_date'     => '2026-06-30 09:00:00',
		'post_category' => array( $cat_id ),
		'tags_input'    => array( 'Rental housing', 'Preservation', 'USDA Section 515' ),
	) );
	// Remove the default sample content.
	$hello = get_page_by_path( 'hello-world', OBJECT, 'post' );
	if ( $hello ) {
		wp_delete_post( $hello->ID, true );
	}
	$sample = get_page_by_path( 'sample-page', OBJECT, 'page' );
	if ( $sample ) {
		wp_delete_post( $sample->ID, true );
	}
	// Skip the editor's welcome guide for the demo's admin user.
	global $wpdb;
	update_user_meta( 1, $wpdb->get_blog_prefix() . 'persisted_preferences', array(
		'core/edit-post' => array( 'welcomeGuide' => false ),
		'core'           => array( 'fixedToolbar' => false ),
		'_modified'      => gmdate( 'c' ),
	) );
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'hac_rb_install_demo' );
add_action( 'init', function () {
	if ( get_option( 'hac_rb_installed' ) !== '0.1.0' ) {
		hac_rb_install_demo();
		update_option( 'hac_rb_installed', '0.1.0' );
	}
}, 20 );

/** /?hac=edit opens the brief in the editor; /?hac=view opens it on the site. */
add_action( 'template_redirect', function () {
	if ( empty( $_GET['hac'] ) ) {
		return;
	}
	$id = hac_rb_demo_id();
	if ( 'edit' === $_GET['hac'] ) {
		wp_safe_redirect( admin_url( 'post.php?post=' . $id . '&action=edit' ) );
		exit;
	}
	wp_safe_redirect( get_permalink( $id ) );
	exit;
} );

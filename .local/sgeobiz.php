<?php
/**
 * SGEOBIZ SEO — Main Loader
 *
 * Titik masuk utama kustomisasi SGEOBIZ di atas SGEOBIZ.
 * File ini dimuat dari autodescription.php setelah SGEOBIZ selesai load.
 *
 * @package SGEOBIZ
 */

defined( 'SGEOBIZ_SEO_PRESENT' ) or die;

// Konstanta jalur direktori .local
define( 'SGEOBIZ_LOCAL_DIR', dirname( __FILE__ ) . DIRECTORY_SEPARATOR );
define( 'SGEOBIZ_VERSION', '1.0.0' );

/**
 * Autoload kelas SGEOBIZ dari folder .local.
 *
 * @param string $class Nama kelas yang dipanggil.
 */
function sgeobiz_autoload( $class ) {
	// Hanya handle kelas dengan prefix SGEOBIZ_
	if ( strpos( $class, 'SGEOBIZ_' ) !== 0 ) {
		return;
	}

	// Konversi nama kelas ke nama file: SGEOBIZ_Foo_Bar → class-sgeobiz-foo-bar.php
	$slug = strtolower( str_replace( [ 'SGEOBIZ_', '_' ], [ '', '-' ], $class ) );
	$file = SGEOBIZ_LOCAL_DIR . 'class-sgeobiz-' . $slug . '.php';

	if ( file_exists( $file ) ) {
		require_once $file;
	}
}
spl_autoload_register( 'sgeobiz_autoload' );

/**
 * Boot semua modul SGEOBIZ.
 * Di-hook ke 'sgeobiz_seo_loaded' agar SGEOBIZ sudah siap.
 */
add_action( 'sgeobiz_seo_loaded', 'sgeobiz_boot', 10 );
function sgeobiz_boot() {
	// 1. Branding (white-label)
	SGEOBIZ_Branding::init();

	// 2. Settings page: Google Business Profile + LocalBusiness info
	// Harus init sebelum Schema dan AI supaya data tersedia
	SGEOBIZ_GBP_Settings::init();

	// 3. Output schema JSON-LD LocalBusiness di front-end
	SGEOBIZ_Schema_Local::init();

	// 4. GEO Meta Tags (geo.region, geo.position, geo.placename, ICBM) untuk Local SEO
	SGEOBIZ_Geo_Meta::init();

	// 5. Custom Schema & Graph Injector ( FAQ, Review, Event, Job, dll )
	SGEOBIZ_Custom_Schema::init();

	// 6. Auto Silo Related Links ( Internal Linking Pyramid )
	SGEOBIZ_Silo_Links::init();
}

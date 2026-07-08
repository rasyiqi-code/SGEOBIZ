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

// Paksa textdomain sgeobiz-seo menggunakan Bahasa Indonesia (id_ID) secara global
add_filter( 'plugin_locale', function( $locale, $domain ) {
	if ( 'sgeobiz-seo' === $domain ) {
		return 'id_ID';
	}
	return $locale;
}, 100, 2 );

// Intersept pemuatan mofile sgeobiz-seo dan paksa menggunakan berkas bahasa Indonesia absolut kita
// Ini menyelesaikan masalah path relative symlink pada lingkungan dev
add_filter( 'load_textdomain_mofile', function( $mofile, $domain ) {
	if ( 'sgeobiz-seo' === $domain ) {
		$custom_mo = dirname( dirname( __FILE__ ) ) . DIRECTORY_SEPARATOR . 'language' . DIRECTORY_SEPARATOR . 'sgeobiz-seo-id_ID.mo';
		if ( file_exists( $custom_mo ) ) {
			return $custom_mo;
		}
	}
	return $mofile;
}, 100, 2 );

// Muat ulang textdomain agar perubahan lokalisasi Bahasa Indonesia langsung aktif secara mutlak
unload_textdomain( 'sgeobiz-seo' );
load_plugin_textdomain( 'sgeobiz-seo' );

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

	// 7. Redirect 404 / Artikel Dihapus ke Homepage secara 301
	SGEOBIZ_Redirect_404::init();

	// 8. Automated HTML Semantic Sanitizer ( H1 tunggal & Heading Hierarchy logis )
	SGEOBIZ_Semantic_HTML_Sanitizer::init();

	// 9. HTTP 304 Not Modified Cache Optimizer ( Crawl Budget Hack )
	SGEOBIZ_HTTP_304::init();

	// 10. Auto Image SEO Optimizer ( Alt Tag & File Name Renamer )
	SGEOBIZ_Auto_Alt_Image::init();

	// 11. GEO Graph Optimizer ( Speakable Schema & Author E-E-A-T sameAs )
	SGEOBIZ_Schema_GEO::init();

	// 12. Real-Time IndexNow API Client ( Instant AI Indexing )
	SGEOBIZ_IndexNow::init();

	// 13. Focus SEO Content Optimizer (Keyword, subject density, dictionary)
	SGEOBIZ_Focus::init();

	// 14. Article Schema Injector — upgrade WebPage ke Article/BlogPosting (SEO 2026)
	SGEOBIZ_Article_Schema::init();

	// 15. AI Robots.txt Agent — izinkan bot AI tepercaya mengindeks konten
	SGEOBIZ_AI_Robots::init();

	// 16. GEO Answer Block & Shortcode — pemicu cuplikan jawaban AI Overviews
	SGEOBIZ_GEO_Block::init();

	// 17. Product Schema Enhancer — adaptasi data terstruktur produk 2026
	SGEOBIZ_Product_Enhancer::init();
}


<?php
/**
 * SGEOBIZ GBP Settings — Halaman pengaturan Google Business Profile
 *
 * Menambahkan submenu settings di bawah menu SGEOBIZ SEO untuk:
 * - Informasi NAP (Name, Address, Phone) bisnis
 * - Koordinat lokasi (GeoCoordinates)
 * - Jam operasional
 * - Tipe bisnis (LocalBusiness schema type)
 * - Kontak WhatsApp
 * - API Key untuk fitur AI
 *
 * @package SGEOBIZ
 */

defined( 'SGEOBIZ_SEO_PRESENT' ) or die;

class SGEOBIZ_GBP_Settings {

	/** Option key di database WordPress. */
	const OPTION_KEY = 'sgeobiz_business_settings';

	/** Option key untuk API keys. */
	const API_KEY_OPTION = 'sgeobiz_api_keys';

	/** Nonce action untuk form. */
	const NONCE_ACTION = 'sgeobiz_gbp_save';

	/**
	 * Daftarkan hook WordPress.
	 */
	public static function init() {
		$instance = new self();
		add_action( 'admin_menu', [ $instance, 'register_menu' ], 20 );
		add_action( 'admin_init', [ $instance, 'handle_save' ] );
		add_action( 'admin_enqueue_scripts', [ $instance, 'enqueue_assets' ] );
		add_action( 'admin_head', [ $instance, 'print_styles' ] );
		add_action( 'wp_ajax_sgeobiz_dismiss_ad', [ $instance, 'ajax_dismiss_ad' ] );
		add_action( 'wp_dashboard_setup', [ $instance, 'register_dashboard_widget' ] );
		add_action( 'wp_login', [ $instance, 'clear_dismiss_on_login' ], 10, 2 );
	}

	/**
	 * Handler AJAX untuk menyimpan status dismiss iklan.
	 */
	public function ajax_dismiss_ad() {
		update_user_meta( get_current_user_id(), 'sgeobiz_dismissed_ad_crediblemark', 1 );
		wp_send_json_success();
	}

	/**
	 * Reset status tutup iklan ketika pengguna (admin) login kembali.
	 *
	 * @param string  $user_login Username pengguna.
	 * @param WP_User $user       Objek user WordPress.
	 */
	public function clear_dismiss_on_login( $user_login, $user ) {
		delete_user_meta( $user->ID, 'sgeobiz_dismissed_ad_crediblemark' );
	}

	/**
	 * Daftarkan widget iklan di Dashboard utama WordPress.
	 */
	public function register_dashboard_widget() {
		$dismissed_ad = get_user_meta( get_current_user_id(), 'sgeobiz_dismissed_ad_crediblemark', true );
		if ( ! $dismissed_ad ) {
			wp_add_dashboard_widget(
				'sgeobiz_crediblemark_ad_widget',
				__( 'Rekomendasi Kredibilitas Bisnis', 'sgeobiz-seo' ),
				[ $this, 'render_dashboard_ad_widget' ]
			);

			// Pindahkan widget ke urutan pertama (paling atas kolom normal)
			global $wp_meta_boxes;
			if ( isset( $wp_meta_boxes['dashboard']['normal']['core']['sgeobiz_crediblemark_ad_widget'] ) ) {
				$my_widget = $wp_meta_boxes['dashboard']['normal']['core']['sgeobiz_crediblemark_ad_widget'];
				unset( $wp_meta_boxes['dashboard']['normal']['core']['sgeobiz_crediblemark_ad_widget'] );
				$wp_meta_boxes['dashboard']['normal']['core'] = array_merge(
					[ 'sgeobiz_crediblemark_ad_widget' => $my_widget ],
					$wp_meta_boxes['dashboard']['normal']['core']
				);
			}
		}
	}

	/**
	 * Render widget iklan di Dashboard utama WordPress.
	 */
	public function render_dashboard_ad_widget() {
		echo '<div class="sgeobiz-dashboard-ad" id="sgeobiz-ad-crediblemark">';
		echo '  <span class="sgeobiz-ad-close" onclick="sgeobizDismissAd()" style="float:right; font-weight:bold; cursor:pointer; font-size:16px;">&times;</span>';
		echo '  <span class="sgeobiz-ad-tag" style="background:#d97706; color:#fff; font-size:10px; font-weight:700; padding:2px 6px; border-radius:4px; text-transform:uppercase;">Rekomendasi</span>';
		echo '  <h4 style="margin: 10px 0 6px 0; font-size:14px; font-weight:700; color:#78350f;">Jasa Pembuatan Web &amp; Sistem Operasional — CredibleMark</h4>';
		echo '  <p style="margin:0 0 12px 0; font-size:13px; line-height:1.5; color:#92400e;">Butuh website kustom, sistem operasional digital, atau perbaikan bug WordPress? Tim profesional <strong><a href="https://crediblemark.com/" target="_blank">CredibleMark.com</a></strong> siap membantu bisnis Anda berkembang secara digital dengan 100% kepemilikan kode tanpa biaya bulanan.</p>';
		echo '  <div style="display:flex; gap:8px; flex-wrap:wrap;">';
		echo '    <a href="https://crediblemark.com/" target="_blank" class="button button-primary" style="background: linear-gradient(135deg, #d97706, #b45309) !important; border:none !important; box-shadow:none !important; text-shadow:none !important; color:#fff !important; font-weight:600 !important; border-radius:6px !important;">Kunjungi CredibleMark</a>';
		echo '    <a href="https://wa.me/6285183131249" target="_blank" class="button button-secondary" style="border: 1px solid #cbd5e1 !important; color:#475569 !important; font-weight:600 !important; border-radius:6px !important; background:#fff !important;">Hubungi via WhatsApp (+62 851-8313-1249)</a>';
		echo '  </div>';
		echo '</div>';
		
		// Script penutup via AJAX
		echo '<script>';
		echo 'function sgeobizDismissAd() {';
		echo '  var widget = document.getElementById("sgeobiz_crediblemark_ad_widget");';
		echo '  if(widget) { widget.style.display = "none"; }';
		echo '  jQuery.post(ajaxurl, { action: "sgeobiz_dismiss_ad" });';
		echo '}';
		echo '</script>';
	}

	/**
	 * Daftarkan halaman submenu di bawah menu SGEOBIZ (SGEOBIZ SEO).
	 */
	public function register_menu() {
		add_submenu_page(
			SGEOBIZ_SEO_SITE_OPTIONS_SLUG,
			__( 'SGEOBIZ: Profil Bisnis', 'sgeobiz-seo' ),
			__( 'Profil Bisnis', 'sgeobiz-seo' ),
			'manage_options',
			'sgeobiz-business-settings',
			[ $this, 'render_page' ]
		);
	}

	/**
	 * Enqueue style minimal untuk halaman settings SGEOBIZ.
	 *
	 * @param string $hook Hook halaman admin saat ini.
	 */
	public function enqueue_assets( $hook ) {
		$screen = get_current_screen();
		if ( $screen && str_contains( $screen->id, 'sgeobiz-business-settings' ) ) {
			\wp_enqueue_media();
		}
	}

	/**
	 * Cetak style langsung di head admin.
	 */
	public function print_styles() {
		$css = '
			.sgeobiz-settings-wrap {
				max-width: 1200px !important;
				margin: 10px 20px 0 0 !important;
			}
			.sgeobiz-dashboard {
				display: flex;
				flex-direction: column;
				background: #ffffff !important;
				border-radius: 12px;
				overflow: hidden;
				box-shadow: 0 10px 30px rgba(0,0,0,0.05);
				border: 1px solid #e2e8f0;
				font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
			}
			.sgeobiz-main {
				flex-grow: 1;
				display: flex;
				flex-direction: column;
				background: #ffffff !important;
			}
			.sgeobiz-topbar {
				padding: 12px 24px;
				background: #ffffff;
				border-bottom: 1px solid #f1f5f9;
				display: flex;
				justify-content: space-between;
				align-items: center;
			}
			.sgeobiz-topbar-title {
				margin: 0;
				font-size: 16px;
				font-weight: 700;
				color: #0f172a;
			}
			.sgeobiz-topbar-actions {
				display: flex;
				gap: 8px;
			}
			/* Save button override */
			.sgeobiz-topbar-actions .button-primary {
				background: linear-gradient(135deg, #d97706, #b45309) !important;
				border: none !important;
				color: #fff !important;
				font-weight: 600 !important;
				border-radius: 6px !important;
				padding: 0 16px !important;
				height: 32px !important;
				line-height: 32px !important;
				box-shadow: 0 4px 6px -1px rgba(217, 119, 6, 0.2), 0 2px 4px -1px rgba(217, 119, 6, 0.1) !important;
				cursor: pointer !important;
				transition: transform 0.2s, box-shadow 0.2s !important;
				font-size: 12px !important;
			}
			.sgeobiz-topbar-actions .button-primary:hover {
				transform: translateY(-1px);
				box-shadow: 0 10px 15px -3px rgba(217, 119, 6, 0.3), 0 4px 6px -2px rgba(217, 119, 6, 0.15) !important;
			}
			.sgeobiz-settings-container {
				padding: 20px 30px;
				background: #ffffff !important;
			}
			.sgeobiz-section {
				margin-bottom: 20px;
				padding-bottom: 16px;
				border-bottom: 1px solid #f1f5f9;
			}
			.sgeobiz-section:last-child {
				margin-bottom: 0;
				padding-bottom: 0;
				border-bottom: none;
			}
			.sgeobiz-section h2 {
				margin-top: 0;
				margin-bottom: 12px;
				font-size: 15px;
				font-weight: 700;
				color: #1e293b;
			}
			.sgeobiz-field {
				margin-bottom: 12px;
				display: flex;
				flex-direction: column;
				gap: 4px;
			}
			.sgeobiz-field label {
				font-weight: 600;
				font-size: 13px;
				color: #1e293b;
			}
			.sgeobiz-field input[type="text"],
			.sgeobiz-field input[type="url"],
			.sgeobiz-field input[type="email"],
			.sgeobiz-field select,
			.sgeobiz-field textarea {
				border: 1px solid #cbd5e1 !important;
				border-radius: 8px !important;
				padding: 6px 10px !important;
				font-size: 13px !important;
				background: #ffffff !important;
				color: #334155 !important;
				box-shadow: none !important;
				transition: border-color 0.2s, box-shadow 0.2s !important;
				width: 100%;
				max-width: 540px;
			}
			.sgeobiz-field input[type="text"]:focus,
			.sgeobiz-field input[type="url"]:focus,
			.sgeobiz-field input[type="email"]:focus,
			.sgeobiz-field select:focus,
			.sgeobiz-field textarea:focus {
				border-color: #d97706 !important;
				box-shadow: 0 0 0 3px rgba(217, 119, 6, 0.15) !important;
				outline: none !important;
			}
			.sgeobiz-row-2 {
				display: grid;
				grid-template-columns: 1fr 1fr;
				gap: 16px;
			}
			@media (max-width: 782px) {
				.sgeobiz-row-2 {
					grid-template-columns: 1fr;
					gap: 0;
				}
			}
			.sgeobiz-hours-grid {
				display: grid;
				grid-template-columns: 120px 140px 140px 100px;
				gap: 10px;
				align-items: center;
				margin-bottom: 8px;
				font-size: 13px;
				color: #334155;
			}
			.sgeobiz-hours-grid input[type="time"] {
				border: 1px solid #cbd5e1 !important;
				border-radius: 8px !important;
				padding: 4px 10px !important;
				font-size: 13px !important;
			}
			/* Toggle switch untuk Jam Operasional Tutup */
			.sgeobiz-hours-grid input[type="checkbox"] {
				-webkit-appearance: none;
				appearance: none;
				width: 40px !important;
				height: 20px !important;
				background-color: #cbd5e1 !important;
				border-radius: 10px !important;
				position: relative !important;
				cursor: pointer !important;
				outline: none !important;
				transition: background-color 0.2s ease !important;
				vertical-align: middle !important;
				margin-right: 8px !important;
				border: none !important;
				box-shadow: none !important;
			}
			.sgeobiz-hours-grid input[type="checkbox"]:checked {
				background-color: #ef4444 !important;
			}
			.sgeobiz-hours-grid input[type="checkbox"]::before {
				content: "" !important;
				position: absolute !important;
				width: 16px !important;
				height: 16px !important;
				border-radius: 50% !important;
				background-color: #ffffff !important;
				top: 2px !important;
				left: 2px !important;
				margin: 0 !important;
				transition: transform 0.2s ease !important;
				box-shadow: 0 1px 3px rgba(0,0,0,0.1) !important;
				float: none !important;
				display: block !important;
			}
			.sgeobiz-hours-grid input[type="checkbox"]:checked::before {
				content: "" !important;
				transform: translateX(20px) !important;
				background-color: #ffffff !important;
				margin: 0 !important;
			}
			.sgeobiz-hours-grid label {
				font-size: 13px;
				font-weight: 600;
				color: #475569;
			}
			.sgeobiz-badge {
				display: inline-block;
				background: linear-gradient(135deg, #d97706, #b45309);
				color: #fff;
				padding: 4px 10px;
				border-radius: 12px;
				font-size: 11px;
				font-weight: 600;
				vertical-align: middle;
				margin-left: 10px;
			}
			.button#sgeobiz-pick-logo {
				border-radius: 6px !important;
				padding: 0 16px !important;
				height: 36px !important;
				line-height: 34px !important;
				border: 1px solid #cbd5e1 !important;
				color: #475569 !important;
				font-weight: 600 !important;
				background: #ffffff !important;
				box-shadow: none !important;
			}
			.button#sgeobiz-pick-logo:hover {
				border-color: #94a3b8 !important;
				color: #1e293b !important;
			}

			/* Ad Box Crediblemark */
			.sgeobiz-ad-box {
				background: linear-gradient(135deg, #fef3c7 0%, #fffbeb 100%) !important;
				border: 1px solid #f59e0b !important;
				border-left: 5px solid #d97706 !important;
				border-radius: 12px !important;
				padding: 20px !important;
				margin-bottom: 24px !important;
				position: relative !important;
				box-shadow: 0 4px 6px -1px rgba(217, 119, 6, 0.05), 0 2px 4px -2px rgba(217, 119, 6, 0.05) !important;
				display: flex;
				flex-direction: column;
				gap: 8px;
			}
			.sgeobiz-ad-close {
				position: absolute !important;
				top: 12px !important;
				right: 16px !important;
				font-size: 20px !important;
				font-weight: bold !important;
				color: #b45309 !important;
				cursor: pointer !important;
				transition: color 0.2s !important;
			}
			.sgeobiz-ad-close:hover {
				color: #78350f !important;
			}
			.sgeobiz-ad-tag {
				display: inline-block !important;
				background: #d97706 !important;
				color: #ffffff !important;
				padding: 2px 8px !important;
				font-size: 10px !important;
				font-weight: 700 !important;
				text-transform: uppercase !important;
				border-radius: 4px !important;
				letter-spacing: 0.5px !important;
				margin-bottom: 6px !important;
				width: max-content !important;
			}
			.sgeobiz-ad-box h3 {
				margin: 0 !important;
				font-size: 15px !important;
				color: #78350f !important;
				font-weight: 700 !important;
			}
			.sgeobiz-ad-box p {
				margin: 0 !important;
				font-size: 13px !important;
				color: #92400e !important;
				line-height: 1.5 !important;
			}
			.sgeobiz-ad-box a {
				color: #b45309 !important;
				text-decoration: underline !important;
			}
			.sgeobiz-ad-box a:hover {
				color: #78350f !important;
			}
			.sgeobiz-ad-button {
				display: inline-block !important;
				background: linear-gradient(135deg, #d97706, #b45309) !important;
				color: #ffffff !important;
				font-weight: 600 !important;
				padding: 8px 16px !important;
				border-radius: 6px !important;
				text-decoration: none !important;
				font-size: 12px !important;
				margin-top: 8px !important;
				width: max-content !important;
				box-shadow: 0 2px 4px rgba(217, 119, 6, 0.2) !important;
				transition: transform 0.2s, box-shadow 0.2s !important;
			}
			.sgeobiz-ad-button:hover {
				transform: translateY(-1px) !important;
				box-shadow: 0 4px 8px rgba(217, 119, 6, 0.3) !important;
				color: #ffffff !important;
			}
			
			/* Dashboard widget styling */
			#sgeobiz_crediblemark_ad_widget {
				background: linear-gradient(135deg, #fef3c7 0%, #fffbeb 100%) !important;
				border: 1px solid #f59e0b !important;
				border-left: 5px solid #d97706 !important;
				border-radius: 12px !important;
				box-shadow: 0 4px 6px -1px rgba(217, 119, 6, 0.05) !important;
			}
			#sgeobiz_crediblemark_ad_widget h2 {
				color: #78350f !important;
				font-weight: 700 !important;
			}
			#sgeobiz_crediblemark_ad_widget .postbox-header {
				border-bottom: none !important;
				background: transparent !important;
			}
			#sgeobiz_crediblemark_ad_widget .inside {
				padding-top: 0 !important;
			}
		';
		echo '<style id="sgeobiz-gbp-styles">' . $css . '</style>';
	}

	/**
	 * Simpan form data ke database.
	 */
	public function handle_save() {
		if (
			! isset( $_POST['sgeobiz_save_settings'] ) ||
			! check_admin_referer( self::NONCE_ACTION )
		) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Akses ditolak.' );
		}

		$data = $this->sanitize_business_data( $_POST );
		update_option( self::OPTION_KEY, $data );

		add_settings_error(
			'sgeobiz_settings',
			'sgeobiz_saved',
			'Pengaturan SGEOBIZ berhasil disimpan.',
			'success'
		);
		set_transient( 'settings_errors', get_settings_errors(), 30 );

		wp_safe_redirect( admin_url( 'admin.php?page=sgeobiz-business-settings&saved=1' ) );
		exit;
	}

	/**
	 * Sanitasi data bisnis dari POST.
	 *
	 * @param array $post Data $_POST.
	 * @return array
	 */
	private function sanitize_business_data( $post ) {
		$days = [ 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday' ];

		$hours = [];
		foreach ( $days as $day ) {
			$hours[ $day ] = [
				'open'  => ! empty( $post[ "hours_{$day}_open" ] )
					? sanitize_text_field( $post[ "hours_{$day}_open" ] ) : '',
				'close' => ! empty( $post[ "hours_{$day}_close" ] )
					? sanitize_text_field( $post[ "hours_{$day}_close" ] ) : '',
				'closed' => ! empty( $post[ "hours_{$day}_closed" ] ),
			];
		}

		$locations_raw = isset( $post['locations'] ) && is_array( $post['locations'] )
			? $post['locations'] : [];

		$locations = [];
		foreach ( $locations_raw as $loc ) {
			if ( empty( $loc['name'] ) ) {
				continue;
			}
			$locations[] = [
				'name'       => sanitize_text_field( $loc['name'] ),
				'address'    => sanitize_text_field( $loc['address'] ?? '' ),
				'city'       => sanitize_text_field( $loc['city'] ?? '' ),
				'province'   => sanitize_text_field( $loc['province'] ?? '' ),
				'postal'     => sanitize_text_field( $loc['postal'] ?? '' ),
				'phone'      => sanitize_text_field( $loc['phone'] ?? '' ),
				'lat'        => (float) ( $loc['lat'] ?? 0 ),
				'lng'        => (float) ( $loc['lng'] ?? 0 ),
			];
		}

		return [
			// Informasi utama bisnis
			'business_name'   => sanitize_text_field( $post['business_name'] ?? '' ),
			'business_type'   => sanitize_text_field( $post['business_type'] ?? 'LocalBusiness' ),
			'description'     => sanitize_textarea_field( $post['business_description'] ?? '' ),
			'website'         => esc_url_raw( $post['business_website'] ?? '' ),
			'email'           => sanitize_email( $post['business_email'] ?? '' ),

			// Kontak
			'phone'              => sanitize_text_field( $post['business_phone'] ?? '' ),
			'whatsapp'           => sanitize_text_field( $post['business_whatsapp'] ?? '' ),
			'service_area'       => sanitize_text_field( $post['business_service_area'] ?? 'ID' ),
			'available_language' => sanitize_text_field( $post['business_available_language'] ?? 'Indonesian' ),

			// Alamat utama
			'address'         => sanitize_text_field( $post['business_address'] ?? '' ),
			'city'            => sanitize_text_field( $post['business_city'] ?? '' ),
			'province'        => sanitize_text_field( $post['business_province'] ?? '' ),
			'postal_code'     => sanitize_text_field( $post['business_postal'] ?? '' ),
			'country'         => 'ID', // Selalu Indonesia

			// Koordinat
			'latitude'        => (float) ( $post['business_lat'] ?? 0 ),
			'longitude'       => (float) ( $post['business_lng'] ?? 0 ),

			// Media Sosial
			'facebook'            => esc_url_raw( $post['social_facebook'] ?? '' ),
			'instagram'           => esc_url_raw( $post['social_instagram'] ?? '' ),
			'tiktok'              => esc_url_raw( $post['social_tiktok'] ?? '' ),
			'youtube'             => esc_url_raw( $post['social_youtube'] ?? '' ),
			'twitter'             => esc_url_raw( $post['social_twitter'] ?? '' ),
			'linkedin'            => esc_url_raw( $post['social_linkedin'] ?? '' ),
			'threads'             => esc_url_raw( $post['social_threads'] ?? '' ),
			'pinterest'           => esc_url_raw( $post['social_pinterest'] ?? '' ),

			// Marketplace (aktif per 2025)
			'tokopedia'           => esc_url_raw( $post['social_tokopedia'] ?? '' ),
			'shopee'              => esc_url_raw( $post['social_shopee'] ?? '' ),
			'lazada'              => esc_url_raw( $post['social_lazada'] ?? '' ),
			'blibli'              => esc_url_raw( $post['social_blibli'] ?? '' ),
			'zalora'              => esc_url_raw( $post['social_zalora'] ?? '' ),
			// Bukalapak: sejak Jan 2025 hanya layanan virtual (pulsa, token listrik), bukan marketplace fisik

			// Google & Food Delivery
			'google_business_url' => esc_url_raw( $post['social_google_business'] ?? '' ),
			'gofood'              => esc_url_raw( $post['social_gofood'] ?? '' ),
			'grabfood'            => esc_url_raw( $post['social_grabfood'] ?? '' ),
			'shopeefood'          => esc_url_raw( $post['social_shopeefood'] ?? '' ),

			// Logo bisnis (attachment ID)
			'logo_id'         => absint( $post['business_logo_id'] ?? 0 ),

			// Jam operasional
			'hours'           => $hours,

			// Multi-lokasi cabang
			'locations'       => $locations,
		];
	}



	/**
	 * Ambil data bisnis yang tersimpan.
	 *
	 * @return array
	 */
	public static function get_business_data() {
		return (array) get_option( self::OPTION_KEY, [] );
	}



	/**
	 * Render halaman settings lengkap.
	 */
	public function render_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$data     = self::get_business_data();

		$d = function ( $key, $default = '' ) use ( $data ) {
			return isset( $data[ $key ] ) ? esc_attr( $data[ $key ] ) : esc_attr( $default );
		};

		$schema_types = $this->get_schema_types();
		$provinces    = $this->get_indonesia_provinces();
		$days_label   = [
			'monday'    => 'Senin',
			'tuesday'   => 'Selasa',
			'wednesday' => 'Rabu',
			'thursday'  => 'Kamis',
			'friday'    => 'Jumat',
			'saturday'  => 'Sabtu',
			'sunday'    => 'Minggu',
		];

		if ( isset( $_GET['saved'] ) ) {
			echo '<div class="notice notice-success is-dismissible"><p>Pengaturan berhasil disimpan!</p></div>';
		}

		echo '<div class="wrap sgeobiz-settings-wrap sgeobiz-premium-theme">';
		echo '<form method="post" action="">';
		\wp_nonce_field( self::NONCE_ACTION );

		echo '<div class="sgeobiz-dashboard">';
		echo '    <div class="sgeobiz-main">';
		echo '        <div class="sgeobiz-topbar">';
		echo '             <h2 class="sgeobiz-topbar-title">' . \esc_html__( 'Profil Bisnis', 'sgeobiz-seo' ) . '<span class="sgeobiz-badge">Google Business Profile</span></h2>';
		echo '             <div class="sgeobiz-topbar-actions">';
		submit_button( 'Simpan Semua Pengaturan', 'primary', 'sgeobiz_save_settings', false );
		echo '             </div>';
		echo '        </div>';
		echo '        <div class="sgeobiz-settings-container">';

		// Iklan Jasa Crediblemark.com yang bisa di-close
		$dismissed_ad = get_user_meta( get_current_user_id(), 'sgeobiz_dismissed_ad_crediblemark', true );
		if ( ! $dismissed_ad ) {
			echo '<div class="sgeobiz-ad-box" id="sgeobiz-ad-crediblemark">';
			echo '  <span class="sgeobiz-ad-close" onclick="sgeobizDismissAd()">&times;</span>';
			echo '  <div class="sgeobiz-ad-content">';
			echo '      <span class="sgeobiz-ad-tag">Rekomendasi</span>';
			echo '      <h3>Jasa Pembuatan Web &amp; Sistem Operasional — CredibleMark</h3>';
			echo '      <p>Butuh website kustom, sistem operasional digital, atau perbaikan bug WordPress? Tim profesional <strong><a href="https://crediblemark.com/" target="_blank">CredibleMark.com</a></strong> siap membantu bisnis Anda berkembang secara digital dengan 100% kepemilikan kode tanpa biaya bulanan.</p>';
			echo '      <div style="display:flex; gap:10px; flex-wrap:wrap; margin-top:8px;">';
			echo '        <a href="https://crediblemark.com/" target="_blank" class="sgeobiz-ad-button" style="margin-top:0 !important;">Kunjungi CredibleMark.com</a>';
			echo '        <a href="https://wa.me/6285183131249" target="_blank" class="sgeobiz-ad-button" style="margin-top:0 !important; background:linear-gradient(135deg, #22c55e, #15803d) !important; box-shadow: 0 2px 4px rgba(34, 197, 94, 0.2) !important;">Hubungi WhatsApp (+62 851-8313-1249)</a>';
			echo '      </div>';
			echo '  </div>';
			echo '</div>';
			
			// Ajax script untuk simpan status dismiss ke user meta
			echo '<script>';
			echo 'function sgeobizDismissAd() {';
			echo '  var box = document.getElementById("sgeobiz-ad-crediblemark");';
			echo '  if(box) { box.style.display = "none"; }';
			echo '  jQuery.post(ajaxurl, { action: "sgeobiz_dismiss_ad" });';
			echo '}';
			echo '</script>';
		}

		// ── Bagian 1: Info Bisnis ──────────────────────────────────────────
		echo '<div class="sgeobiz-section">';
		echo '<h2>Informasi Bisnis Utama</h2>';
		echo '<div class="sgeobiz-row-2">';
		$this->field( 'business_name', 'Nama Bisnis', $d( 'business_name' ), 'text', true );
		$this->field_select( 'business_type', 'Tipe Bisnis (Schema)', $schema_types, $d( 'business_type', 'LocalBusiness' ) );
		echo '</div>';
		echo '<div class="sgeobiz-field"><label>Deskripsi Singkat Bisnis</label>';
		echo '<textarea name="business_description" rows="3">' . esc_textarea( $data['description'] ?? '' ) . '</textarea></div>';
		echo '<div class="sgeobiz-row-2">';
		$this->field( 'business_website', 'Website', $d( 'website' ), 'url' );
		$this->field( 'business_email', 'Email Bisnis', $d( 'email' ), 'text' );
		echo '</div>';
		echo '</div>'; // end section

		// ── Bagian 2: Kontak ──────────────────────────────────────────────
		echo '<div class="sgeobiz-section">';
		echo '<h2>Kontak</h2>';
		echo '<div class="sgeobiz-row-2">';
		$this->field( 'business_phone', 'Nomor Telepon (format: +6221xxxxxxxx)', $d( 'phone' ) );
		$this->field( 'business_whatsapp', 'WhatsApp (format: 628xxxxxxxxx)', $d( 'whatsapp' ) );
		echo '</div>';
		echo '<div class="sgeobiz-row-2">';
		$this->field(
			'business_service_area',
			'Area Layanan (kode negara/wilayah, pisahkan koma: ID, SG)',
			$d( 'service_area', 'ID' )
		);
		$this->field(
			'business_available_language',
			'Bahasa Tersedia (pisahkan koma: Indonesian, English)',
			$d( 'available_language', 'Indonesian' )
		);
		echo '</div>';
		echo '</div>';

		// ── Bagian 3: Alamat & Koordinat ─────────────────────────────────
		echo '<div class="sgeobiz-section">';
		echo '<h2>Alamat & Lokasi Utama</h2>';
		$this->field( 'business_address', 'Alamat Lengkap (Jalan, No.)', $d( 'address' ) );
		echo '<div class="sgeobiz-row-2">';
		$this->field( 'business_city', 'Kota / Kabupaten', $d( 'city' ) );
		$this->field_select( 'business_province', 'Provinsi', $provinces, $d( 'province' ) );
		echo '</div>';
		echo '<div class="sgeobiz-row-2">';
		$this->field( 'business_postal', 'Kode Pos (5 digit)', $d( 'postal_code' ) );
		echo '<div class="sgeobiz-field"><label>Negara</label><input type="text" value="Indonesia" readonly style="background:#f6f7f7"></div>';
		echo '</div>';
		echo '<p style="margin-top: 20px; margin-bottom: 10px;"><strong>Koordinat GPS</strong> — <small>Isi untuk output GeoCoordinates di schema (dapatkan dari Google Maps)</small></p>';
		echo '<div class="sgeobiz-row-2">';
		$this->field( 'business_lat', 'Latitude (contoh: -6.2088)', $d( 'latitude' ) );
		$this->field( 'business_lng', 'Longitude (contoh: 106.8456)', $d( 'longitude' ) );
		echo '</div>';
		echo '</div>';

		// ── Bagian 4: Sosial Media & Marketplace ─────────────────────────
		echo '<div class="sgeobiz-section">';
		echo '<h2>Media Sosial &amp; Marketplace</h2>';

		// Sub-bagian: Media Sosial
		echo '<h3 style="font-size:13px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.5px;margin:0 0 10px;">Media Sosial</h3>';
		echo '<div class="sgeobiz-row-2">';
		$this->field_url( 'social_facebook',  'Facebook',  $d( 'facebook' ),  'https://facebook.com/namabisnis' );
		$this->field_url( 'social_instagram', 'Instagram', $d( 'instagram' ), 'https://instagram.com/namabisnis' );
		$this->field_url( 'social_tiktok',    'TikTok',    $d( 'tiktok' ),    'https://tiktok.com/@namabisnis' );
		$this->field_url( 'social_youtube',   'YouTube',   $d( 'youtube' ),   'https://youtube.com/@namabisnis' );
		$this->field_url( 'social_twitter',   'Twitter / X', $d( 'twitter' ), 'https://x.com/namabisnis' );
		$this->field_url( 'social_linkedin',  'LinkedIn',  $d( 'linkedin' ),  'https://linkedin.com/company/namabisnis' );
		$this->field_url( 'social_threads',   'Threads',   $d( 'threads' ),   'https://threads.net/@namabisnis' );
		$this->field_url( 'social_pinterest', 'Pinterest', $d( 'pinterest' ), 'https://pinterest.com/namabisnis' );
		echo '</div>';

		// Sub-bagian: Marketplace
		echo '<h3 style="font-size:13px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.5px;margin:20px 0 10px;">Marketplace</h3>';
		echo '<div class="sgeobiz-row-2">';
		$this->field_url( 'social_tokopedia', 'Tokopedia',        $d( 'tokopedia' ), 'https://tokopedia.com/namabisnis' );
		$this->field_url( 'social_shopee',    'Shopee',           $d( 'shopee' ),    'https://shopee.co.id/namabisnis' );
		$this->field_url( 'social_lazada',    'Lazada',           $d( 'lazada' ),    'https://lazada.co.id/shop/namabisnis' );
		$this->field_url( 'social_blibli',    'Blibli',           $d( 'blibli' ),    'https://blibli.com/merchant/namabisnis' );
		$this->field_url( 'social_zalora',    'Zalora (fashion)', $d( 'zalora' ),    'https://zalora.co.id/...' );
		echo '</div>';

		// Sub-bagian: Google & Food Delivery
		echo '<h3 style="font-size:13px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.5px;margin:20px 0 10px;">Google &amp; Pesan Antar Makanan</h3>';
		echo '<div class="sgeobiz-row-2">';
		$this->field_url( 'social_google_business', 'Google Business / Maps', $d( 'google_business_url' ), 'https://maps.app.goo.gl/xxxxx' );
		$this->field_url( 'social_gofood',     'GoFood (Gojek)',  $d( 'gofood' ),     'https://gofood.co.id/...' );
		$this->field_url( 'social_grabfood',   'GrabFood',       $d( 'grabfood' ),   'https://food.grab.com/...' );
		$this->field_url( 'social_shopeefood', 'ShopeeFood',     $d( 'shopeefood' ), 'https://shopeefood.co.id/...' );
		echo '</div>';

		echo '</div>'; // end section

		// ── Bagian 5: Jam Operasional ─────────────────────────────────────
		echo '<div class="sgeobiz-section">';
		echo '<h2>Jam Operasional</h2>';
		$hours_data = $data['hours'] ?? [];
		foreach ( $days_label as $day => $label ) {
			$h_open   = esc_attr( $hours_data[ $day ]['open']  ?? '08:00' );
			$h_close  = esc_attr( $hours_data[ $day ]['close'] ?? '17:00' );
			$h_closed = ! empty( $hours_data[ $day ]['closed'] );
			echo "<div class='sgeobiz-hours-grid'>";
			echo "<strong>{$label}</strong>";
			echo "<input type='time' name='hours_{$day}_open' value='{$h_open}'" . ( $h_closed ? ' disabled' : '' ) . ">";
			echo "<input type='time' name='hours_{$day}_close' value='{$h_close}'" . ( $h_closed ? ' disabled' : '' ) . ">";
			echo "<label><input type='checkbox' name='hours_{$day}_closed' value='1'" . checked( $h_closed, true, false ) . "> Tutup</label>";
			echo '</div>';
		}
		echo '</div>';

		// ── Bagian 6: Logo Bisnis ─────────────────────────────────────────
		echo '<div class="sgeobiz-section" style="border-bottom: none; margin-bottom: 0; padding-bottom: 0;">';
		echo '<h2>Logo Bisnis (untuk Schema)</h2>';
		$logo_id  = absint( $data['logo_id'] ?? 0 );
		$logo_url = $logo_id ? wp_get_attachment_image_url( $logo_id, 'medium' ) : '';
		echo '<input type="hidden" name="business_logo_id" id="sgeobiz_logo_id" value="' . $logo_id . '">';
		if ( $logo_url ) {
			echo '<img src="' . esc_url( $logo_url ) . '" style="max-height:80px;margin-bottom:8px;display:block;">';
		}
		echo '<button type="button" class="button" id="sgeobiz-pick-logo" onclick="sgeobizPickLogo()">Pilih / Ganti Logo</button>';
		echo '<script>function sgeobizPickLogo(){ var frame=wp.media({title:"Pilih Logo Bisnis",button:{text:"Gunakan Logo"},multiple:false});frame.on("select",function(){var att=frame.state().get("selection").first().toJSON();document.getElementById("sgeobiz_logo_id").value=att.id;});frame.open();}</script>';
		echo '</div>';

		echo '        </div>'; // end settings-container
		echo '    </div>'; // end main
		echo '</div>'; // end dashboard

		// ── Submit di Bawah ────────────────────────────────────────────────
		echo '<div style="margin-top: 20px;">';
		submit_button( 'Simpan Semua Pengaturan', 'primary', 'sgeobiz_save_settings', false );
		echo '</div>';

		echo '</form>';
		echo '</div>'; // wrap
	}

	/**
	 * Helper render field input.
	 *
	 * @param string $name     Nama field.
	 * @param string $label    Label tampilan.
	 * @param string $value    Nilai saat ini.
	 * @param string $type     Tipe input HTML.
	 * @param bool   $required Apakah wajib diisi.
	 */
	private function field( $name, $label, $value = '', $type = 'text', $required = false ) {
		$req = $required ? ' required' : '';
		echo "<div class='sgeobiz-field'>";
		echo "<label for='{$name}'>{$label}" . ( $required ? ' <span style="color:red">*</span>' : '' ) . '</label>';
		echo "<input type='{$type}' id='{$name}' name='{$name}' value='{$value}'{$req}>";
		echo '</div>';
	}

	/**
	 * Helper render field URL dengan placeholder dan ikon platform.
	 *
	 * @param string $name        Nama field (name attribute).
	 * @param string $label       Label platform.
	 * @param string $value       Nilai tersimpan.
	 * @param string $placeholder Contoh URL.
	 */
	private function field_url( $name, $label, $value = '', $placeholder = '' ) {
		echo "<div class='sgeobiz-field'>";
		echo "<label for='{$name}'>{$label}</label>";
		echo "<input type='url' id='{$name}' name='{$name}' value='{$value}' placeholder='" . esc_attr( $placeholder ) . "'>";
		echo '</div>';
	}

	/**
	 * Helper render field select.
	 *
	 * @param string $name    Nama field.
	 * @param string $label   Label tampilan.
	 * @param array  $options Opsi [ value => label ].
	 * @param string $current Nilai terpilih saat ini.
	 */
	private function field_select( $name, $label, $options, $current = '' ) {
		echo "<div class='sgeobiz-field'>";
		echo "<label for='{$name}'>{$label}</label>";
		echo "<select id='{$name}' name='{$name}'>";
		foreach ( $options as $val => $lbl ) {
			echo '<option value="' . esc_attr( $val ) . '"' . selected( $current, $val, false ) . '>' . esc_html( $lbl ) . '</option>';
		}
		echo '</select>';
		echo '</div>';
	}

	/**
	 * Daftar tipe Schema.org untuk bisnis lokal Indonesia.
	 *
	 * @return array
	 */
	private function get_schema_types() {
		return [
			'LocalBusiness'       => 'Bisnis Lokal (Umum)',
			'Store'               => 'Toko',
			'Restaurant'          => 'Restoran / Rumah Makan',
			'FoodEstablishment'   => 'Tempat Makan',
			'CafeOrCoffeeShop'    => 'Kafe / Coffee Shop',
			'Bakery'              => 'Bakery / Toko Roti',
			'FastFoodRestaurant'  => 'Rumah Makan Cepat Saji',
			'MedicalBusiness'     => 'Klinik / Bisnis Medis',
			'Dentist'             => 'Dokter Gigi',
			'Optician'            => 'Optik',
			'Pharmacy'            => 'Apotek',
			'HairSalon'           => 'Salon Rambut',
			'BeautySalon'         => 'Salon Kecantikan',
			'SpaOrBeautyService'  => 'Spa & Kecantikan',
			'AutoRepair'          => 'Bengkel / Servis Kendaraan',
			'AutomotiveBusiness'  => 'Bisnis Otomotif',
			'LodgingBusiness'     => 'Penginapan / Hotel',
			'Hotel'               => 'Hotel',
			'HealthClub'          => 'Gym / Pusat Kebugaran',
			'EducationalOrganization' => 'Lembaga Pendidikan',
			'TravelAgency'        => 'Agen Perjalanan',
			'FinancialService'    => 'Layanan Keuangan',
			'LegalService'        => 'Layanan Hukum',
			'AccountingService'   => 'Akuntan / Konsultan Pajak',
			'RealEstateAgent'     => 'Agen Properti',
			'HomeAndConstructionBusiness' => 'Bangunan & Konstruksi',
			'EntertainmentBusiness' => 'Hiburan',
		];
	}

	/**
	 * Daftar provinsi Indonesia.
	 *
	 * @return array
	 */
	private function get_indonesia_provinces() {
		return [
			''                    => '— Pilih Provinsi —',
			'Aceh'                => 'Aceh',
			'Sumatera Utara'      => 'Sumatera Utara',
			'Sumatera Barat'      => 'Sumatera Barat',
			'Riau'                => 'Riau',
			'Kepulauan Riau'      => 'Kepulauan Riau',
			'Jambi'               => 'Jambi',
			'Sumatera Selatan'    => 'Sumatera Selatan',
			'Bangka Belitung'     => 'Bangka Belitung',
			'Bengkulu'            => 'Bengkulu',
			'Lampung'             => 'Lampung',
			'DKI Jakarta'         => 'DKI Jakarta',
			'Jawa Barat'          => 'Jawa Barat',
			'Banten'              => 'Banten',
			'Jawa Tengah'         => 'Jawa Tengah',
			'DI Yogyakarta'       => 'DI Yogyakarta',
			'Jawa Timur'          => 'Jawa Timur',
			'Bali'                => 'Bali',
			'Nusa Tenggara Barat' => 'Nusa Tenggara Barat',
			'Nusa Tenggara Timur' => 'Nusa Tenggara Timur',
			'Kalimantan Barat'    => 'Kalimantan Barat',
			'Kalimantan Tengah'   => 'Kalimantan Tengah',
			'Kalimantan Selatan'  => 'Kalimantan Selatan',
			'Kalimantan Timur'    => 'Kalimantan Timur',
			'Kalimantan Utara'    => 'Kalimantan Utara',
			'Sulawesi Utara'      => 'Sulawesi Utara',
			'Gorontalo'           => 'Gorontalo',
			'Sulawesi Tengah'     => 'Sulawesi Tengah',
			'Sulawesi Barat'      => 'Sulawesi Barat',
			'Sulawesi Selatan'    => 'Sulawesi Selatan',
			'Sulawesi Tenggara'   => 'Sulawesi Tenggara',
			'Maluku'              => 'Maluku',
			'Maluku Utara'        => 'Maluku Utara',
			'Papua Barat'         => 'Papua Barat',
			'Papua Barat Daya'    => 'Papua Barat Daya',
			'Papua'               => 'Papua',
			'Papua Tengah'        => 'Papua Tengah',
			'Papua Pegunungan'    => 'Papua Pegunungan',
			'Papua Selatan'       => 'Papua Selatan',
		];
	}
}

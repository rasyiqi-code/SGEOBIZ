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
		if ( $hook !== 'seo_page_sgeobiz-business-settings' ) {
			return;
		}
		// Tambahkan CSS inline minimal, tidak perlu file eksternal
		$css = '
			.sgeobiz-settings-wrap { max-width: 900px; }
			.sgeobiz-section { background: #fff; border: 1px solid #c3c4c7; border-radius: 4px; padding: 20px 24px; margin-bottom: 20px; }
			.sgeobiz-section h2 { margin-top: 0; font-size: 1.1em; border-bottom: 1px solid #eee; padding-bottom: 8px; }
			.sgeobiz-field { margin-bottom: 16px; display: flex; flex-direction: column; gap: 4px; }
			.sgeobiz-field label { font-weight: 600; font-size: 13px; }
			.sgeobiz-field input[type="text"],
			.sgeobiz-field input[type="url"],
			.sgeobiz-field input[type="password"],
			.sgeobiz-field select,
			.sgeobiz-field textarea { width: 100%; max-width: 480px; }
			.sgeobiz-row-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
			.sgeobiz-hours-grid { display: grid; grid-template-columns: 120px 1fr 1fr; gap: 8px; align-items: center; margin-bottom: 8px; font-size: 13px; }
			.sgeobiz-badge { display: inline-block; background: #0073aa; color: #fff; padding: 2px 8px; border-radius: 10px; font-size: 11px; vertical-align: middle; margin-left: 6px; }
		';
		wp_add_inline_style( 'wp-admin', $css );
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
			'phone'           => sanitize_text_field( $post['business_phone'] ?? '' ),
			'whatsapp'        => sanitize_text_field( $post['business_whatsapp'] ?? '' ),

			// Alamat utama
			'address'         => sanitize_text_field( $post['business_address'] ?? '' ),
			'city'            => sanitize_text_field( $post['business_city'] ?? '' ),
			'province'        => sanitize_text_field( $post['business_province'] ?? '' ),
			'postal_code'     => sanitize_text_field( $post['business_postal'] ?? '' ),
			'country'         => 'ID', // Selalu Indonesia

			// Koordinat
			'latitude'        => (float) ( $post['business_lat'] ?? 0 ),
			'longitude'       => (float) ( $post['business_lng'] ?? 0 ),

			// Sosial media
			'facebook'        => esc_url_raw( $post['social_facebook'] ?? '' ),
			'instagram'       => esc_url_raw( $post['social_instagram'] ?? '' ),
			'tokopedia'       => esc_url_raw( $post['social_tokopedia'] ?? '' ),
			'shopee'          => esc_url_raw( $post['social_shopee'] ?? '' ),
			'tiktok'          => esc_url_raw( $post['social_tiktok'] ?? '' ),

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

		echo '<div class="wrap sgeobiz-settings-wrap">';
		echo '<h1>SGEOBIZ SEO <span class="sgeobiz-badge">Profil Bisnis</span></h1>';
		echo '<form method="post" action="">';
		wp_nonce_field( self::NONCE_ACTION );

		// ── Bagian 1: Info Bisnis ──────────────────────────────────────────
		echo '<div class="sgeobiz-section">';
		echo '<h2>Informasi Bisnis Utama</h2>';
		echo '<div class="sgeobiz-row-2">';
		$this->field( 'business_name', 'Nama Bisnis', $d( 'business_name' ), 'text', true );
		$this->field_select( 'business_type', 'Tipe Bisnis (Schema)', $schema_types, $d( 'business_type', 'LocalBusiness' ) );
		echo '</div>';
		echo '<div class="sgeobiz-field"><label>Deskripsi Singkat Bisnis</label>';
		echo '<textarea name="business_description" rows="3" style="max-width:480px">' . esc_textarea( $data['description'] ?? '' ) . '</textarea></div>';
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
		echo '<p><strong>Koordinat GPS</strong> — <small>Isi untuk output GeoCoordinates di schema (dapatkan dari Google Maps)</small></p>';
		echo '<div class="sgeobiz-row-2">';
		$this->field( 'business_lat', 'Latitude (contoh: -6.2088)', $d( 'latitude' ) );
		$this->field( 'business_lng', 'Longitude (contoh: 106.8456)', $d( 'longitude' ) );
		echo '</div>';
		echo '</div>';

		// ── Bagian 4: Sosial Media & Marketplace ─────────────────────────
		echo '<div class="sgeobiz-section">';
		echo '<h2>Media Sosial & Marketplace</h2>';
		echo '<div class="sgeobiz-row-2">';
		$this->field( 'social_facebook', 'Facebook URL', $d( 'facebook' ), 'url' );
		$this->field( 'social_instagram', 'Instagram URL', $d( 'instagram' ), 'url' );
		$this->field( 'social_tokopedia', 'Tokopedia URL', $d( 'tokopedia' ), 'url' );
		$this->field( 'social_shopee', 'Shopee URL', $d( 'shopee' ), 'url' );
		$this->field( 'social_tiktok', 'TikTok URL', $d( 'tiktok' ), 'url' );
		echo '</div>';
		echo '</div>';

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
		echo '<div class="sgeobiz-section">';
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



		// ── Submit ─────────────────────────────────────────────────────────
		echo '<p>';
		submit_button( 'Simpan Semua Pengaturan', 'primary', 'sgeobiz_save_settings', false );
		echo '</p>';

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

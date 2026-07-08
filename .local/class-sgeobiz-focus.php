<?php
/**
 * SGEOBIZ Focus SEO Content Optimizer
 *
 * Mengoptimalkan penulisan konten berdasarkan kata kunci fokus (hingga 3 subjek),
 * kepadatan kata kunci, struktur link internal/eksternal, analisis judul/deskripsi,
 * serta menyediakan simulasi kamus sinonim & infleksi Premium bahasa Indonesia.
 *
 * @package SGEOBIZ
 */

defined( 'SGEOBIZ_SEO_PRESENT' ) or die;

class SGEOBIZ_Focus {

	/**
	 * Daftarkan hook WordPress.
	 */
	public static function init() {
		$instance = new self();
		
		// Integrasi dengan metabox tab SGEOBIZ SEO
		add_filter( 'sgeobiz_seo_inpost_settings_tabs', [ $instance, 'add_focus_tab' ] );
		
		// Integrasi dengan default metadata post & penyimpanan
		add_filter( 'sgeobiz_seo_post_meta_defaults', [ $instance, 'add_focus_meta_defaults' ], 10, 2 );
		add_filter( 'sgeobiz_seo_save_post_meta', [ $instance, 'sanitize_focus_meta' ], 10, 2 );
		
		// Enqueue JS / CSS untuk editor
		add_action( 'admin_enqueue_scripts', [ $instance, 'enqueue_assets' ] );
		
		// Endpoint AJAX Kamus Sinonim & Infleksi
		add_action( 'wp_ajax_sgeobiz_focus_dictionary', [ $instance, 'ajax_dictionary' ] );
	}

	/**
	 * Tambahkan tab Focus ke dalam kotak metabox SEO postingan.
	 *
	 * @param array $tabs Tab default.
	 * @return array
	 */
	public function add_focus_tab( $tabs ) {
		$tabs['focus'] = [
			'name'     => __( 'Focus', 'sgeobiz-seo' ),
			'callback' => [ $this, 'render_focus_tab' ],
			'dashicon' => 'editor-spellcheck', // Ikon dashicon yang relevan untuk penulisan
		];
		return $tabs;
	}

	/**
	 * Daftarkan key meta data kustom di database agar tidak dibersihkan oleh SGEOBIZ.
	 *
	 * @param array $defaults Default meta.
	 * @param int   $post_id ID postingan.
	 * @return array
	 */
	public function add_focus_meta_defaults( $defaults, $post_id ) {
		$defaults['_sgeobiz_focus_keywords'] = '';
		return $defaults;
	}

	/**
	 * Sanitasi kata kunci Focus sebelum disimpan ke database.
	 *
	 * @param array $data Data meta dari $_POST['sgeobiz-seo'].
	 * @param int   $post_id ID postingan.
	 * @return array
	 */
	public function sanitize_focus_meta( $data, $post_id ) {
		if ( isset( $data['_sgeobiz_focus_keywords'] ) ) {
			if ( is_array( $data['_sgeobiz_focus_keywords'] ) ) {
				// Bersihkan dan buang nilai kosong
				$keywords = array_map( 'sanitize_text_field', $data['_sgeobiz_focus_keywords'] );
				$keywords = array_map( 'trim', $keywords );
				$keywords = array_filter( $keywords );
				$data['_sgeobiz_focus_keywords'] = json_encode( array_values( $keywords ) );
			} else {
				$data['_sgeobiz_focus_keywords'] = sanitize_text_field( trim( $data['_sgeobiz_focus_keywords'] ) );
			}
		}
		return $data;
	}

	/**
	 * Enqueue script & style khusus Focus di halaman edit postingan.
	 *
	 * @param string $hook Halaman admin saat ini.
	 */
	public function enqueue_assets( $hook ) {
		// Pastikan hanya meload di editor post/page
		$screen = get_current_screen();
		if ( ! $screen || 'post' !== $screen->base ) {
			return;
		}

		$local_url = plugins_url( '', __FILE__ );

		wp_enqueue_style( 'sgeobiz-focus-css', $local_url . '/css/focus.css', [], SGEOBIZ_VERSION );
		wp_enqueue_script( 'sgeobiz-focus-js', $local_url . '/js/focus.js', [ 'jquery', 'wp-data', 'wp-blocks' ], SGEOBIZ_VERSION, true );

		// Localize script data
		wp_localize_script( 'sgeobiz-focus-js', 'sgeobizFocusL10n', [
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce'    => wp_create_nonce( 'sgeobiz_focus_dictionary_nonce' ),
		] );
	}

	/**
	 * Render tampilan tab Focus.
	 */
	public function render_focus_tab() {
		$post_id  = SGEOBIZ_SEO\Helper\Query::get_the_real_id();
		$meta     = SGEOBIZ_SEO\Data\Plugin\Post::get_meta( $post_id );
		
		$raw_keywords = isset( $meta['_sgeobiz_focus_keywords'] ) ? $meta['_sgeobiz_focus_keywords'] : '';
		$keywords = [];
		if ( ! empty( $raw_keywords ) ) {
			$decoded = json_decode( $raw_keywords, true );
			if ( is_array( $decoded ) ) {
				$keywords = $decoded;
			} else {
				$keywords = [ $raw_keywords ];
			}
		}
		
		// Selalu sediakan 3 input field
		$keywords = array_pad( $keywords, 3, '' );
		?>
		<div class="sgeobiz-focus-container">
			<!-- Header & Informasi Singkat -->
			<div class="sgeobiz-focus-header">
				<div class="sgeobiz-focus-title-wrap">
					<h3>Focus Content Optimizer <span class="sgeobiz-premium-badge">Premium Active</span></h3>
					<p class="description">Optimalkan tulisan Anda berdasarkan hingga 3 kata kunci subjek. Fokus pada keterbacaan, relevansi, dan sinonim semantik.</p>
				</div>
				<!-- Visual Skor Melingkar -->
				<div class="sgeobiz-focus-gauge-wrap">
					<div class="sgeobiz-focus-gauge">
						<svg class="sgeobiz-focus-gauge-circle" viewBox="0 0 100 100">
							<circle class="bg" cx="50" cy="50" r="45"></circle>
							<circle class="bar" cx="50" cy="50" r="45" style="stroke-dashoffset: 283;"></circle>
						</svg>
						<div class="sgeobiz-focus-gauge-value">0%</div>
					</div>
					<div class="sgeobiz-focus-gauge-label">Skor SEO Konten</div>
				</div>
			</div>

			<!-- Input Kata Kunci -->
			<div class="sgeobiz-focus-keywords-grid">
				<?php for ( $i = 0; $i < 3; $i++ ) : ?>
					<div class="sgeobiz-focus-keyword-field" data-index="<?php echo $i; ?>">
						<label for="sgeobiz_focus_kw_<?php echo $i; ?>">Subjek Fokus <?php echo $i + 1; ?></label>
						<div class="sgeobiz-focus-input-wrapper">
							<input type="text" 
								id="sgeobiz_focus_kw_<?php echo $i; ?>" 
								name="sgeobiz-seo[_sgeobiz_focus_keywords][<?php echo $i; ?>]" 
								value="<?php echo esc_attr( $keywords[$i] ); ?>" 
								placeholder="Masukkan kata kunci utama..." 
								class="sgeobiz-focus-kw-input"
								autocomplete="off">
							<button type="button" class="sgeobiz-focus-kw-clear" title="Bersihkan">&times;</button>
						</div>
						<!-- Loader Sinonim -->
						<div class="sgeobiz-focus-dict-loading" style="display:none;">
							<span class="spinner is-active"></span> Mencari sinonim...
						</div>
						<!-- Hasil Saran Kamus -->
						<div class="sgeobiz-focus-suggestions" style="display:none;">
							<div class="sgeobiz-focus-suggestion-title">Saran Variasi Penulisan (LSI):</div>
							<div class="sgeobiz-focus-badge-list"></div>
						</div>
					</div>
				<?php endfor; ?>
			</div>

			<!-- Navigation Subjek Aktif untuk Detail Analisis -->
			<div class="sgeobiz-focus-subject-tabs" style="display:none;">
				<span class="sgeobiz-focus-tab-label">Detail Analisis Untuk:</span>
				<div class="sgeobiz-focus-tab-buttons">
					<button type="button" class="sgeobiz-focus-tab-btn active" data-target="0">Subjek 1</button>
					<button type="button" class="sgeobiz-focus-tab-btn" data-target="1">Subjek 2</button>
					<button type="button" class="sgeobiz-focus-tab-btn" data-target="2">Subjek 3</button>
				</div>
			</div>

			<!-- Hasil Checklist Evaluasi -->
			<div class="sgeobiz-focus-checklist-wrap">
				<h4>Analisis Kualitas Konten & SEO</h4>
				<ul class="sgeobiz-focus-checklist">
					<!-- Indikator Kepadatan -->
					<li id="sgeobiz-check-density" class="sgeobiz-check-item">
						<span class="status-icon"></span>
						<div class="check-text">
							<strong>Kepadatan Kata Kunci (Subject Density)</strong>
							<span class="check-desc">Menghitung kemunculan kata kunci & sinonim di konten (target: 1.0% - 2.5%).</span>
						</div>
						<div class="check-metric">0% (0 kata)</div>
					</li>
					<!-- Indikator Judul -->
					<li id="sgeobiz-check-title" class="sgeobiz-check-item">
						<span class="status-icon"></span>
						<div class="check-text">
							<strong>Kata Kunci di Judul Meta (Meta Title)</strong>
							<span class="check-desc">Memastikan kata kunci penting terdapat di tag Title Google Anda.</span>
						</div>
					</li>
					<!-- Indikator Deskripsi -->
					<li id="sgeobiz-check-description" class="sgeobiz-check-item">
						<span class="status-icon"></span>
						<div class="check-text">
							<strong>Kata Kunci di Deskripsi Meta</strong>
							<span class="check-desc">Meningkatkan CTR pencarian dengan mencantumkan subjek di deskripsi.</span>
						</div>
					</li>
					<!-- Indikator Paragraf 1 -->
					<li id="sgeobiz-check-intro" class="sgeobiz-check-item">
						<span class="status-icon"></span>
						<div class="check-text">
							<strong>Kata Kunci di Paragraf Pertama</strong>
							<span class="check-desc">Penting agar pembaca dan mesin pencari langsung paham topik utama halaman.</span>
						</div>
					</li>
					<!-- Indikator Internal Linking -->
					<li id="sgeobiz-check-linking-internal" class="sgeobiz-check-item">
						<span class="status-icon"></span>
						<div class="check-text">
							<strong>Tautan Internal (Internal Links)</strong>
							<span class="check-desc">Menghubungkan artikel ini ke halaman relevan lain di website Anda.</span>
						</div>
						<div class="check-metric">0 tautan</div>
					</li>
					<!-- Indikator External Linking -->
					<li id="sgeobiz-check-linking-external" class="sgeobiz-check-item">
						<span class="status-icon"></span>
						<div class="check-text">
							<strong>Tautan Eksternal (External Links)</strong>
							<span class="check-desc">Tautan ke situs luar bereputasi tinggi guna menambah konteks referensi tepercaya.</span>
						</div>
						<div class="check-metric">0 tautan</div>
					</li>
					<!-- Indikator Panjang Konten -->
					<li id="sgeobiz-check-length" class="sgeobiz-check-item">
						<span class="status-icon"></span>
						<div class="check-text">
							<strong>Panjang Konten (Word Count)</strong>
							<span class="check-desc">Konten yang informatif sebaiknya memiliki minimal 300 kata (optimal > 600 kata).</span>
						</div>
						<div class="check-metric">0 kata</div>
					</li>
				</ul>
			</div>
		</div>
		<?php
	}

	/**
	 * Endpoint AJAX untuk lookup kamus sinonim & infleksi bahasa Indonesia (simulasi Premium).
	 */
	public function ajax_dictionary() {
		check_ajax_referer( 'sgeobiz_focus_dictionary_nonce', 'nonce' );

		$keyword = isset( $_GET['keyword'] ) ? sanitize_text_field( $_GET['keyword'] ) : '';
		if ( empty( $keyword ) ) {
			wp_send_json_error( 'Kata kunci kosong' );
		}

		$keyword_lower = strtolower( trim( $keyword ) );

		// Database kamus sinonim & infleksi lokal Indonesia yang kaya untuk optimasi SEO
		$dictionary = [
			'bisnis' => [
				'synonyms' => [ 'usaha', 'perusahaan', 'perdagangan', 'komersial', 'niaga', 'industri' ],
				'inflections' => [ 'berbisnis', 'pebisnis', 'membisniskan' ]
			],
			'seo' => [
				'synonyms' => [ 'optimasi mesin pencari', 'search engine optimization', 'peringkat google', 'optimasi website' ],
				'inflections' => [ 'mengoptimasi', 'teroptimasi', 'pengoptimasian' ]
			],
			'lokal' => [
				'synonyms' => [ 'daerah', 'setempat', 'domestik', 'regional', 'pribumi' ],
				'inflections' => [ 'melokalkan', 'terlokalisasi', 'lokalisasi' ]
			],
			'jasa' => [
				'synonyms' => [ 'layanan', 'servis', 'pekerjaan', 'bantuan', 'penyediaan' ],
				'inflections' => [ 'berjasa', 'menjasa', 'pelayanan' ]
			],
			'produk' => [
				'synonyms' => [ 'barang', 'komoditas', 'hasil', 'buatan', 'karya', 'output' ],
				'inflections' => [ 'memproduksi', 'terproduksi', 'reproduksi', 'produktivitas' ]
			],
			'marketing' => [
				'synonyms' => [ 'pemasaran', 'promosi', 'penjualan', 'iklan', 'niaga' ],
				'inflections' => [ 'memasarkan', 'terpemasar', 'pemasar' ]
			],
			'konten' => [
				'synonyms' => [ 'isi', 'materi', 'artikel', 'tulisan', 'informasi', 'subjek' ],
				'inflections' => [ 'mengonten', 'berkonten' ]
			],
			'wisata' => [
				'synonyms' => [ 'liburan', 'rekreasi', 'travel', 'pelesir', 'piknik', 'destinasi' ],
				'inflections' => [ 'berwisata', 'wisatawan', 'kewisataan' ]
			],
			'kuliner' => [
				'synonyms' => [ 'makanan', 'masakan', 'hidangan', 'sajian', 'pangan', 'kudapan' ],
				'inflections' => [ 'berkuliner', 'perkulineran' ]
			]
		];

		$response = [];

		if ( isset( $dictionary[$keyword_lower] ) ) {
			$response = $dictionary[$keyword_lower];
		} else {
			// Generator dinamis untuk infleksi/imbuhan bahasa Indonesia agar sistem selalu responsif
			$inflections = [];
			
			$first_char = substr( $keyword_lower, 0, 1 );
			if ( in_array( $first_char, [ 'a', 'i', 'u', 'e', 'o' ] ) ) {
				$inflections[] = 'meng' . $keyword_lower;
				$inflections[] = 'peng' . $keyword_lower;
			} else {
				$inflections[] = 'me' . $keyword_lower;
				$inflections[] = 'ber' . $keyword_lower;
			}
			
			$inflections[] = $keyword_lower . 'an';
			$inflections[] = 'ke' . $keyword_lower . 'an';
			$inflections[] = 'di' . $keyword_lower;

			$synonyms = [
				'topik ' . $keyword_lower,
				'subjek ' . $keyword_lower,
				$keyword_lower . ' berkualitas',
				'optimasi ' . $keyword_lower
			];

			$response = [
				'synonyms'    => $synonyms,
				'inflections' => $inflections
			];
		}

		wp_send_json_success( $response );
	}
}

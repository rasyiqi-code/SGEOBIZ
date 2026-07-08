<?php
/**
 * SGEOBIZ AI Custom Schema Generator & Graph Injector
 *
 * Menyediakan modul metabox di editor post untuk membuat skema kustom secara
 * asisten AI (Gemini/OpenAI) atau manual, serta mem-parsing dan menyatukannya
 * secara dinamis ke dalam graph utama plugin di front-end.
 *
 * @package SGEOBIZ
 */

defined( 'SGEOBIZ_SEO_PRESENT' ) or die;

class SGEOBIZ_Custom_Schema {

	/** Meta key database. */
	const META_KEY = '_sgeobiz_custom_schema';

	/** AJAX action. */
	const AJAX_ACTION = 'sgeobiz_ai_generate_custom_schema';

	/**
	 * Daftarkan hook.
	 */
	public static function init() {
		$instance = new self();

		// Registrasi Metabox di admin
		add_action( 'add_meta_boxes', [ $instance, 'add_metabox' ] );
		add_action( 'save_post', [ $instance, 'save_metabox_data' ] );

		// AJAX Handler untuk AI
		add_action( 'wp_ajax_' . self::AJAX_ACTION, [ $instance, 'handle_ajax' ] );

		// Front-end integration ke graph utama
		add_filter( 'sgeobiz_seo_schema_graph_data', [ $instance, 'inject_to_graph' ], 15, 2 );
	}

	/**
	 * Daftarkan metabox di post editor.
	 */
	public function add_metabox() {
		$post_types = get_post_types( [ 'public' => true ] );
		foreach ( $post_types as $type ) {
			add_meta_box(
				'sgeobiz_custom_schema_box',
				__( 'SGEOBIZ SEO - Custom Schema & AI', 'default' ),
				[ $this, 'render_metabox' ],
				$type,
				'normal',
				'default'
			);
		}
	}

	/**
	 * Render HTML untuk metabox.
	 *
	 * @param WP_Post $post
	 */
	public function render_metabox( $post ) {
		wp_nonce_field( 'sgeobiz_save_custom_schema', 'sgeobiz_custom_schema_nonce' );
		$val = get_post_meta( $post->ID, self::META_KEY, true );

		$api_data = SGEOBIZ_GBP_Settings::get_api_data();
		$has_key  = ! empty( $api_data['gemini'] ) || ! empty( $api_data['openai'] );
		$ajax_url = admin_url( 'admin-ajax.php' );
		$nonce    = wp_create_nonce( self::AJAX_ACTION );
		?>
		<div class="sgeobiz-custom-schema-wrap" style="padding: 6px 0;">
			<p style="margin-top: 0; color: #64748b;">
				<?php _e( 'Tambahkan skema terstruktur JSON-LD kustom untuk halaman ini. Anda bisa menulisnya sendiri secara manual atau meminta AI membuatkannya berdasarkan draf konten saat ini.', 'default' ); ?>
			</p>

			<?php if ( $has_key ) : ?>
				<div class="sgeobiz-ai-schema-controls" style="margin-bottom: 12px; display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
					<label for="sgeobiz-ai-schema-type" style="font-weight: 600; font-size: 13px; color: #334155;"><?php _e( 'Pilih Tipe Schema:', 'default' ); ?></label>
					<select id="sgeobiz-ai-schema-type" style="padding: 4px 8px; border-radius: 4px; border: 1px solid #cbd5e1;">
						<option value="FAQPage"><?php _e( 'FAQ (Tanya Jawab)', 'default' ); ?></option>
						<option value="Review"><?php _e( 'Review / Ulasan Rating', 'default' ); ?></option>
						<option value="Event"><?php _e( 'Event (Acara/Webinar)', 'default' ); ?></option>
						<option value="JobPosting"><?php _e( 'Job Posting (Lowongan Kerja)', 'default' ); ?></option>
						<option value="Recipe"><?php _e( 'Recipe (Resep Kuliner)', 'default' ); ?></option>
						<option value="HowTo"><?php _e( 'HowTo (Panduan Langkah)', 'default' ); ?></option>
						<option value="LocalBusiness"><?php _e( 'LocalBusiness (Cabang Spesifik)', 'default' ); ?></option>
						<option value="Custom"><?php _e( 'Tipe Kustom Lainnya', 'default' ); ?></option>
					</select>

					<button type="button" id="sgeobiz-ai-schema-btn" class="button" style="background: linear-gradient(135deg, #7c3aed, #4f46e5); color: #fff; border: none; font-weight: 600; border-radius: 4px; padding: 0 14px; height: 30px; display: inline-flex; align-items: center; gap: 6px; cursor: pointer; transition: opacity .2s;">
						<span class="dashicons dashicons-superhero-alt" style="font-size: 16px; width: 16px; height: 16px; margin: 0; line-height: 1;"></span>
						<?php _e( 'AI: Generate Schema', 'default' ); ?>
					</button>

					<span id="sgeobiz-ai-schema-status" style="font-size: 12px; font-style: italic; color: #64748b;"></span>
				</div>
			<?php else : ?>
				<p style="font-size: 12px; font-style: italic; color: #dc2626; margin-bottom: 12px;">
					<?php _e( '* Konfigurasikan API Key Google Gemini atau OpenAI di halaman Pengaturan SGEOBIZ untuk mengaktifkan fitur otomatisasi AI Schema Generator.', 'default' ); ?>
				</p>
			<?php endif; ?>

			<textarea name="sgeobiz_custom_schema" id="sgeobiz_custom_schema_input" rows="8" style="width: 100%; font-family: monospace; font-size: 12px; padding: 10px; border-radius: 6px; border: 1px solid #cbd5e1; background: #fafafa; box-sizing: border-box;" placeholder='{&#10;  "@type": "FAQPage",&#10;  "mainEntity": [...]&#10;}'><?php echo esc_textarea( $val ); ?></textarea>

			<p style="font-size: 11px; color: #94a3b8; margin-top: 6px; margin-bottom: 0;">
				<?php _e( 'Catatan: Pastikan kode berformat JSON-LD yang valid. Properti "@context" akan diselaraskan secara otomatis di front-end.', 'default' ); ?>
			</p>
		</div>

		<script>
		jQuery(document).ready(function($){
			$('#sgeobiz-ai-schema-btn').on('click', function(){
				var btn    = $(this);
				var type   = $('#sgeobiz-ai-schema-type').val();
				var status = $('#sgeobiz-ai-schema-status');
				var postId = $('#post_ID').val();

				if ( ! postId ) {
					alert('<?php echo esc_js( __( 'Simpan post terlebih dahulu sebelum menggunakan fitur AI.', 'default' ) ); ?>');
					return;
				}

				btn.prop('disabled', true).css('opacity', '0.6');
				status.css('color', '#64748b').text('<?php echo esc_js( __( 'AI sedang berpikir...', 'default' ) ); ?>');

				$.ajax({
					url: '<?php echo esc_url( $ajax_url ); ?>',
					type: 'POST',
					data: {
						action: '<?php echo esc_js( self::AJAX_ACTION ); ?>',
						nonce: '<?php echo esc_js( $nonce ); ?>',
						post_id: postId,
						schema_type: type
					},
					timeout: 45000,
					success: function(res) {
						btn.prop('disabled', false).css('opacity', '1');
						if ( res.success && res.data && res.data.json ) {
							$('#sgeobiz_custom_schema_input').val(res.data.json);
							status.css('color', '#059669').text('<?php echo esc_js( __( '✓ Berhasil dibuat!', 'default' ) ); ?>');
						} else {
							status.css('color', '#dc2626').text('✗ ' + (res.data?.message || '<?php echo esc_js( __( 'Gagal generate.', 'default' ) ); ?>'));
						}
					},
					error: function() {
						btn.prop('disabled', false).css('opacity', '1');
						status.css('color', '#dc2626').text('<?php echo esc_js( __( '✗ Terjadi kesalahan koneksi.', 'default' ) ); ?>');
					}
				});
			});
		});
		</script>
		<?php
	}

	/**
	 * Simpan data metabox ke database.
	 *
	 * @param int $post_id
	 */
	public function save_metabox_data( $post_id ) {
		// Validasi nonce
		if ( ! isset( $_POST['sgeobiz_custom_schema_nonce'] ) || ! wp_verify_nonce( $_POST['sgeobiz_custom_schema_nonce'], 'sgeobiz_save_custom_schema' ) ) {
			return;
		}

		// Cek autosave
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Cek kapabilitas
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		if ( isset( $_POST['sgeobiz_custom_schema'] ) ) {
			$val = trim( $_POST['sgeobiz_custom_schema'] );
			
			if ( empty( $val ) ) {
				delete_post_meta( $post_id, self::META_KEY );
			} else {
				update_post_meta( $post_id, self::META_KEY, $val );
			}
		}
	}

	/**
	 * AJAX handler untuk memicu AI generator schema.
	 */
	public function handle_ajax() {
		check_ajax_referer( self::AJAX_ACTION, 'nonce' );

		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( [ 'message' => 'Akses ditolak.' ] );
		}

		$post_id     = absint( $_POST['post_id'] ?? 0 );
		$schema_type = sanitize_text_field( $_POST['schema_type'] ?? '' );

		if ( ! $post_id || empty( $schema_type ) ) {
			wp_send_json_error( [ 'message' => 'Parameter tidak valid.' ] );
		}

		$post = get_post( $post_id );
		if ( ! $post ) {
			wp_send_json_error( [ 'message' => 'Post tidak ditemukan.' ] );
		}

		// Ambil data untuk prompt
		$title = $post->post_title;
		$content = mb_substr( wp_strip_all_tags( $post->post_content ), 0, 1500 );
		$biz = SGEOBIZ_GBP_Settings::get_business_data();
		$biz_name = $biz['business_name'] ?? get_bloginfo( 'name' );

		// Prompt khusus AI untuk menghasilkan skema valid
		$prompt = sprintf(
			'Kamu adalah pakar data terstruktur (JSON-LD) dan SEO untuk pasar Indonesia.
Analisis judul dan konten draf berikut, lalu buatkan kode skema JSON-LD untuk tipe: "%s".

Judul: "%s"
Konten Draf: "%s"
Nama Bisnis: %s

Aturan Mutlak:
- Gunakan Bahasa Indonesia yang natural untuk isinya.
- Output HANYA kode JSON-LD murni di dalam tag pembungkus JSON biasa. Jangan berikan penjelasan teks apa pun sebelum atau setelah kode JSON-LD.
- Jangan menyertakan properti "@context" di dalam objek utama JSON karena ia akan digabungkan ke graph terpadu, langsung mulai dengan properti "@type": "%s" di tingkat atas.
- Tentukan "@id" yang unik untuk entitas ini menggunakan URL draf (misal: "http://domain.com/post-url/#%s").
- Pastikan seluruh format JSON valid dan teratur.',
			$schema_type,
			$title,
			$content,
			$biz_name,
			$schema_type,
			strtolower( $schema_type )
		);

		// Panggil AI
		$result = $this->call_ai( $prompt );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( [ 'message' => $result->get_error_message() ] );
		}

		// Bersihkan markdown code blocks (```json ... ```) dari Gemini/OpenAI jika ada
		$cleaned_json = $result;
		if ( preg_match( '/```(?:json)?\s*(.*?)\s*```/is', $result, $matches ) ) {
			$cleaned_json = $matches[1];
		}
		$cleaned_json = trim( $cleaned_json );

		// Cek validitas JSON lokal sebelum dikirim balik
		$decoded = json_decode( $cleaned_json, true );
		if ( json_last_error() !== JSON_ERROR_NONE ) {
			wp_send_json_error( [ 'message' => 'AI gagal membuat JSON yang valid. Silakan coba lagi.' ] );
		}

		wp_send_json_success( [ 'json' => $cleaned_json ] );
	}

	/**
	 * Memanggil AI (Gemini dengan fallback OpenAI).
	 *
	 * @param string $prompt
	 * @return string|WP_Error
	 */
	private function call_ai( string $prompt ) {
		$api_data = SGEOBIZ_GBP_Settings::get_api_data();
		$provider = $api_data['provider'] ?? 'gemini';

		if ( $provider === 'gemini' && ! empty( $api_data['gemini'] ) ) {
			$res = $this->call_gemini( $prompt, $api_data['gemini'] );
			if ( ! is_wp_error( $res ) ) return $res;
			if ( ! empty( $api_data['openai'] ) ) return $this->call_openai( $prompt, $api_data['openai'] );
			return $res;
		}

		if ( $provider === 'openai' && ! empty( $api_data['openai'] ) ) {
			$res = $this->call_openai( $prompt, $api_data['openai'] );
			if ( ! is_wp_error( $res ) ) return $res;
			if ( ! empty( $api_data['gemini'] ) ) return $this->call_gemini( $prompt, $api_data['gemini'] );
			return $res;
		}

		return new WP_Error( 'no_api_key', 'API key belum dikonfigurasi di halaman Pengaturan SGEOBIZ.' );
	}

	/**
	 * Call Google Gemini API.
	 */
	private function call_gemini( string $prompt, string $api_key ) {
		$model = 'gemini-2.0-flash';
		$url   = sprintf(
			'https://generativelanguage.googleapis.com/v1beta/models/%s:generateContent?key=%s',
			$model,
			$api_key
		);

		$body = wp_json_encode( [
			'contents' => [
				[
					'parts' => [ [ 'text' => $prompt ] ],
				],
			],
			'generationConfig' => [
				'temperature'     => 0.2, // Rendah agar konsisten menghasilkan kode
				'maxOutputTokens' => 1200,
			],
		] );

		$response = wp_remote_post( $url, [
			'headers' => [ 'Content-Type' => 'application/json' ],
			'body'    => $body,
			'timeout' => 35,
		] );

		if ( is_wp_error( $response ) ) {
			return new WP_Error( 'gemini_conn', 'Koneksi ke Gemini gagal.' );
		}

		$code = wp_remote_retrieve_response_code( $response );
		$data = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( $code !== 200 ) {
			$msg = $data['error']['message'] ?? 'Error HTTP ' . $code;
			return new WP_Error( 'gemini_error', 'Gemini error: ' . $msg );
		}

		$text = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
		return trim( $text );
	}

	/**
	 * Call OpenAI API.
	 */
	private function call_openai( string $prompt, string $api_key ) {
		$url  = 'https://api.openai.com/v1/chat/completions';
		$body = wp_json_encode( [
			'model'       => 'gpt-4o-mini',
			'messages'    => [
				[
					'role'    => 'user',
					'content' => $prompt,
				],
			],
			'temperature' => 0.2,
			'max_tokens'  => 1200,
		] );

		$response = wp_remote_post( $url, [
			'headers' => [
				'Content-Type'  => 'application/json',
				'Authorization' => 'Bearer ' . $api_key,
			],
			'body'    => $body,
			'timeout' => 35,
		] );

		if ( is_wp_error( $response ) ) {
			return new WP_Error( 'openai_conn', 'Koneksi OpenAI gagal.' );
		}

		$code = wp_remote_retrieve_response_code( $response );
		$data = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( $code !== 200 ) {
			$msg = $data['error']['message'] ?? 'Error HTTP ' . $code;
			return new WP_Error( 'openai_error', 'OpenAI error: ' . $msg );
		}

		$text = $data['choices'][0]['message']['content'] ?? '';
		return trim( $text );
	}

	/**
	 * Mem-parsing JSON-LD kustom dan menyatukannya ke graph utama di front-end.
	 *
	 * @param array      $graph Array graph JSON-LD.
	 * @param array|null $args  Query arguments.
	 * @return array Graph termodifikasi.
	 */
	public function inject_to_graph( array $graph, $args = null ) {
		if ( ! is_singular() ) {
			return $graph;
		}

		$post_id = get_the_ID();
		if ( ! $post_id ) {
			return $graph;
		}

		$raw_json = get_post_meta( $post_id, self::META_KEY, true );
		if ( empty( $raw_json ) ) {
			return $graph;
		}

		$decoded = json_decode( $raw_json, true );

		if ( empty( $decoded ) || ! is_array( $decoded ) ) {
			return $graph; // Abaikan jika JSON tidak valid
		}

		// Bersihkan properti global @context jika tidak sengaja disertakan,
		// agar selaras di bawah @graph tunggal.
		if ( isset( $decoded['@context'] ) ) {
			unset( $decoded['@context'] );
		}

		$graph[] = $decoded;

		// Hubungkan semantik: hubungkan WebPage utama ke ID schema kustom ini jika ada
		$custom_id = $decoded['@id'] ?? '';
		if ( $custom_id ) {
			foreach ( $graph as &$entity ) {
				if ( ! isset( $entity['@type'] ) ) {
					continue;
				}

				$types = (array) $entity['@type'];
				if ( in_array( 'WebPage', $types, true ) || in_array( 'CollectionPage', $types, true ) ) {
					$entity['mainEntity'][] = [ '@id' => $custom_id ];
				}
			}
			unset( $entity );
		}

		return $graph;
	}
}

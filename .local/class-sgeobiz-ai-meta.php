<?php
/**
 * SGEOBIZ AI Meta Generator
 *
 * Menambahkan tombol "Generate dengan AI" dan "Analisis Heading" di meta box SGEOBIZ pada editor post.
 * Menggunakan Google Gemini (prioritas) atau OpenAI GPT-4o sebagai fallback.
 *
 * Fitur:
 * - Generate SEO title otomatis dari konten/judul post
 * - Generate meta description otomatis
 * - AI Search Intent Heading Optimizer (H1, H2, H3) kustom
 * - AJAX handler yang aman (nonce + capability check)
 * - Timeout dan error handling yang robust
 *
 * @package SGEOBIZ
 */

defined( 'SGEOBIZ_SEO_PRESENT' ) or die;

class SGEOBIZ_AI_Meta {

	/** AJAX action untuk generate meta. */
	const AJAX_ACTION = 'sgeobiz_ai_generate_meta';

	/**
	 * Daftarkan hook.
	 */
	public static function init() {
		$instance = new self();

		// Enqueue script tombol AI di halaman post editor
		add_action( 'admin_enqueue_scripts', [ $instance, 'enqueue_assets' ] );

		// AJAX handlers (logged-in user)
		add_action( 'wp_ajax_' . self::AJAX_ACTION, [ $instance, 'handle_ajax' ] );
	}

	/**
	 * Enqueue script & inline JS untuk tombol AI di editor post.
	 *
	 * @param string $hook Hook halaman admin saat ini.
	 */
	public function enqueue_assets( $hook ) {
		// Hanya load di halaman edit post/page/CPT
		$valid_hooks = [ 'post.php', 'post-new.php' ];
		if ( ! in_array( $hook, $valid_hooks, true ) ) {
			return;
		}

		// Cek apakah API key tersedia
		$api_data = SGEOBIZ_GBP_Settings::get_api_data();
		$has_key  = ! empty( $api_data['gemini'] ) || ! empty( $api_data['openai'] );

		if ( ! $has_key ) {
			return; // Jangan tampilkan tombol kalau belum ada API key
		}

		// Enqueue jQuery (sudah tersedia di WP) + script inline SGEOBIZ AI
		$nonce = wp_create_nonce( self::AJAX_ACTION );

		$js = $this->get_inline_script( $nonce );
		wp_add_inline_script( 'jquery-core', $js );

		// Style tombol AI & Box Heading Rekomendasi
		$css = '
			.sgeobiz-ai-btn-wrap { margin-top: 8px; display: flex; gap: 8px; align-items: center; flex-wrap: wrap; }
			.sgeobiz-ai-btn {
				display: inline-flex; align-items: center; gap: 6px;
				padding: 5px 12px; border-radius: 4px; font-size: 12px; cursor: pointer;
				background: linear-gradient(135deg, #4f46e5, #7c3aed); color: #fff;
				border: none; font-weight: 600; transition: opacity .2s;
			}
			.sgeobiz-ai-btn:hover { opacity: .85; }
			.sgeobiz-ai-btn:disabled { opacity: .5; cursor: not-allowed; }
			.sgeobiz-ai-btn .dashicons { font-size: 14px; width: 14px; height: 14px; }
			.sgeobiz-ai-status { font-size: 11px; color: #6b7280; font-style: italic; }
			.sgeobiz-ai-status.error { color: #dc2626; }
			
			.sgeobiz-ai-heading-box {
				margin-top: 12px; padding: 14px; border-radius: 6px;
				background: #f8fafc; border: 1px solid #e2e8f0; font-size: 13px; line-height: 1.6; color: #334155;
				width: 100%; box-sizing: border-box; text-align: left;
			}
			.sgeobiz-ai-heading-box h4 { margin-top: 0; margin-bottom: 10px; font-size: 14px; color: #1e293b; font-weight: 600; display: flex; align-items: center; gap: 6px; }
			.sgeobiz-ai-heading-box h4 .dashicons { font-size: 16px; width: 16px; height: 16px; color: #059669; }
			.sgeobiz-ai-heading-box ul { margin: 0; padding-left: 20px; list-style-type: disc; }
			.sgeobiz-ai-heading-box li { margin-bottom: 6px; }
			.sgeobiz-ai-heading-box li code { background: #e2e8f0; padding: 2px 5px; border-radius: 3px; font-size: 11px; font-family: monospace; color: #475569; font-weight: bold; }
		';
		wp_add_inline_style( 'wp-admin', $css );
	}

	/**
	 * Generate script JS inline untuk tombol AI di meta box SGEOBIZ.
	 *
	 * @param string $nonce Nonce untuk AJAX.
	 * @return string JavaScript.
	 */
	private function get_inline_script( $nonce ) {
		$ajax_url = admin_url( 'admin-ajax.php' );

		return <<<JS
(function(\$){
	'use strict';

	/**
	 * Inject tombol AI ke dalam meta box SGEOBIZ setelah DOM siap.
	 * SGEOBIZ merender meta box-nya via JS, jadi kita observe DOM.
	 */
	function sgeobizInjectAiButtons() {
		var titleInput = document.querySelector('#_genesis_title, [name="_genesis_title"]');
		var descInput  = document.querySelector('#_genesis_description, [name="_genesis_description"]');

		if ( titleInput && ! titleInput.parentNode.querySelector('.sgeobiz-ai-btn-wrap') ) {
			titleInput.parentNode.insertAdjacentHTML('beforeend', sgeobizTitleBtnHtml());
		}
		if ( descInput && ! descInput.parentNode.querySelector('.sgeobiz-ai-btn-wrap') ) {
			descInput.parentNode.insertAdjacentHTML('beforeend', sgeobizDescBtnHtml());
		}

		// Bind event listener
		document.querySelectorAll('.sgeobiz-ai-btn').forEach(function(btn){
			if ( btn.dataset.bound ) return;
			btn.dataset.bound = '1';
			btn.addEventListener('click', sgeobizHandleClick);
		});
	}

	function sgeobizTitleBtnHtml() {
		return '<div class="sgeobiz-ai-btn-wrap">' +
			'<button type="button" class="sgeobiz-ai-btn" data-action="title">' +
			'<span class="dashicons dashicons-superhero-alt"></span> AI: Generate Title' +
			'</button>' +
			'<span class="sgeobiz-ai-status" id="sgeobiz-title-status"></span>' +
			'</div>';
	}

	function sgeobizDescBtnHtml() {
		return '<div class="sgeobiz-ai-btn-wrap" style="flex-direction: column; align-items: flex-start; gap: 4px;">' +
			'<div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">' +
				'<button type="button" class="sgeobiz-ai-btn" data-action="description">' +
				'<span class="dashicons dashicons-superhero-alt"></span> AI: Generate Description' +
				'</button>' +
				'<button type="button" class="sgeobiz-ai-btn" style="background: linear-gradient(135deg, #059669, #10b981);" data-action="heading_outline">' +
				'<span class="dashicons dashicons-editor-ol"></span> AI: Analisis Heading & Search Intent' +
				'</button>' +
				'<span class="sgeobiz-ai-status" id="sgeobiz-description-status"></span>' +
				'<span class="sgeobiz-ai-status" id="sgeobiz-heading_outline-status"></span>' +
			'</div>' +
			'<div id="sgeobiz-heading-result-wrap" style="width: 100%;"></div>' +
			'</div>';
	}

	function sgeobizHandleClick(e) {
		var btn    = e.currentTarget;
		var action = btn.dataset.action;
		var postId = parseInt(document.querySelector('#post_ID')?.value || 0);

		if ( ! postId ) {
			alert('Simpan post terlebih dahulu sebelum menggunakan fitur AI.');
			return;
		}

		btn.disabled = true;
		var statusEl = document.getElementById('sgeobiz-' + action + '-status');
		if ( statusEl ) {
			statusEl.className = 'sgeobiz-ai-status';
			statusEl.textContent = 'Sedang memproses...';
		}

		\$.ajax({
			url: '{$ajax_url}',
			type: 'POST',
			data: {
				action: 'sgeobiz_ai_generate_meta',
				nonce: '{$nonce}',
				post_id: postId,
				generate: action
			},
			timeout: 45000,
			success: function(res) {
				btn.disabled = false;
				if ( res.success && res.data && res.data.text ) {
					if ( action === 'title' ) {
						var inp = document.querySelector('#_genesis_title, [name="_genesis_title"]');
						if ( inp ) { inp.value = res.data.text; inp.dispatchEvent(new Event('input')); }
					} else if ( action === 'description' ) {
						var ta = document.querySelector('#_genesis_description, [name="_genesis_description"]');
						if ( ta ) { ta.value = res.data.text; ta.dispatchEvent(new Event('input')); }
					} else if ( action === 'heading_outline' ) {
						var wrap = document.getElementById('sgeobiz-heading-result-wrap');
						if ( wrap ) {
							wrap.innerHTML = '<div class="sgeobiz-ai-heading-box">' +
								'<h4><span class="dashicons dashicons-analytics"></span> Rekomendasi Struktur Heading & Search Intent</h4>' +
								res.data.text +
								'</div>';
						}
					}
					if ( statusEl ) {
						statusEl.className = 'sgeobiz-ai-status';
						statusEl.textContent = '✓ Berhasil!';
					}
				} else {
					if ( statusEl ) {
						statusEl.className = 'sgeobiz-ai-status error';
						statusEl.textContent = '✗ ' + (res.data?.message || 'Gagal memproses. Coba lagi.');
					}
				}
			},
			error: function(xhr, status) {
				btn.disabled = false;
				if ( statusEl ) {
					statusEl.className = 'sgeobiz-ai-status error';
					statusEl.textContent = status === 'timeout'
						? '✗ Timeout. Coba lagi.'
						: '✗ Koneksi gagal.';
				}
			}
		});
	}

	// Observe DOM untuk mendeteksi saat SGEOBIZ meta box selesai render
	\$(document).ready(function(){
		sgeobizInjectAiButtons();

		var observer = new MutationObserver(function(){
			sgeobizInjectAiButtons();
		});
		var target = document.querySelector('#sgeobiz-inpost-box, #normal-sortables, .postbox-container');
		if ( target ) {
			observer.observe(target, { childList: true, subtree: true });
		}

		setTimeout(sgeobizInjectAiButtons, 2000);
	});

})(jQuery);
JS;
	}

	/**
	 * AJAX handler: generate SEO title, description, atau heading outline via AI.
	 */
	public function handle_ajax() {
		// 1. Security check
		check_ajax_referer( self::AJAX_ACTION, 'nonce' );

		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( [ 'message' => 'Akses ditolak.' ] );
		}

		// 2. Validasi input
		$post_id  = absint( $_POST['post_id'] ?? 0 );
		$generate = sanitize_text_field( $_POST['generate'] ?? '' );

		if ( ! $post_id || ! in_array( $generate, [ 'title', 'description', 'heading_outline' ], true ) ) {
			wp_send_json_error( [ 'message' => 'Parameter tidak valid.' ] );
		}

		$post = get_post( $post_id );
		if ( ! $post ) {
			wp_send_json_error( [ 'message' => 'Post tidak ditemukan.' ] );
		}

		// 3. Siapkan konteks untuk AI
		$context = $this->build_context( $post );

		// 4. Buat prompt berdasarkan tipe generate
		if ( $generate === 'title' ) {
			$prompt = $this->make_title_prompt( $context );
		} elseif ( $generate === 'description' ) {
			$prompt = $this->make_description_prompt( $context );
		} else {
			$prompt = $this->make_heading_prompt( $context );
		}

		// 5. Panggil AI
		$result = $this->call_ai( $prompt );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( [ 'message' => $result->get_error_message() ] );
		}

		wp_send_json_success( [ 'text' => $result ] );
	}

	/**
	 * Bangun konteks post untuk dikirim ke AI.
	 *
	 * @param WP_Post $post
	 * @return array
	 */
	private function build_context( $post ) {
		// Ekstrak teks bersih dari konten post (tanpa HTML, max 1000 karakter)
		$content_stripped = wp_strip_all_tags( $post->post_content );
		$content_excerpt  = mb_substr( $content_stripped, 0, 1500 ); // Lebih panjang untuk analisis heading

		// Ambil info bisnis untuk konteks tambahan
		$biz  = SGEOBIZ_GBP_Settings::get_business_data();
		$site = get_bloginfo( 'name' );

		return [
			'post_title'   => $post->post_title,
			'post_type'    => $post->post_type,
			'content'      => $content_excerpt,
			'site_name'    => $site,
			'business'     => $biz['business_name'] ?? $site,
			'business_type' => $biz['business_type'] ?? 'LocalBusiness',
		];
	}

	/**
	 * Buat prompt untuk generate SEO title.
	 *
	 * @param array $ctx Konteks post.
	 * @return string
	 */
	private function make_title_prompt( array $ctx ) {
		return sprintf(
			'Kamu adalah SEO expert profesional untuk bisnis di Indonesia.
Buatkan 1 judul SEO yang optimal untuk halaman web berikut:

Judul Asli: "%s"
Jenis Halaman: %s
Nama Bisnis: %s
Konten Singkat: "%s"

Aturan:
- Panjang: 50–60 karakter (termasuk spasi)
- Bahasa: Indonesia atau campuran Indonesia-Inggris yang natural
- Sertakan kata kunci utama di depan
- Jangan gunakan tanda tanya atau seru
- Jangan tambahkan nama bisnis jika sudah terlalu panjang
- Hanya output judulnya saja, tanpa penjelasan, tanpa tanda kutip

Judul SEO:',
			$ctx['post_title'],
			$ctx['post_type'],
			$ctx['business'],
			$ctx['content']
		);
	}

	/**
	 * Buat prompt untuk generate meta description.
	 *
	 * @param array $ctx Konteks post.
	 * @return string
	 */
	private function make_description_prompt( array $ctx ) {
		return sprintf(
			'Kamu adalah SEO expert profesional untuk bisnis di Indonesia.
Buatkan 1 meta description yang optimal untuk halaman web berikut:

Judul Halaman: "%s"
Nama Bisnis: %s
Konten Singkat: "%s"

Aturan:
- Panjang: 140–160 karakter (termasuk spasi)
- Bahasa: Indonesia yang natural dan persuasif
- Sertakan value proposition / manfaat utama
- Akhiri dengan call-to-action singkat jika relevan
- Jangan gunakan kata "kami" di awal kalimat
- Hanya output deskripsinya saja, tanpa penjelasan, tanpa tanda kutip

Meta Description:',
			$ctx['post_title'],
			$ctx['business'],
			$ctx['content']
		);
	}

	/**
	 * Buat prompt untuk generate rekomendasi heading dan analisis Search Intent.
	 *
	 * @param array $ctx Konteks post.
	 * @return string
	 */
	private function make_heading_prompt( array $ctx ) {
		return sprintf(
			'Kamu adalah SEO content strategist ahli untuk pasar Indonesia.
Analisis draf artikel berikut dan tentukan Search Intent-nya, lalu susun rekomendasi struktur heading berjenjang (H1, H2, H3) yang paling optimal untuk menjawab Search Intent tersebut secara langsung.

Judul Artikel: "%s"
Nama Bisnis: %s
Draf Konten Singkat: "%s"

Aturan Output:
- Harus ditulis dalam Bahasa Indonesia.
- Jangan gunakan markdown tebal (* atau **) atau pembungkus kode (```) di output utama.
- Output harus berupa HTML list sederhana (<ul> dan <li>) dengan elemen <code> untuk menandai tag heading.
- Format output wajib seperti ini:
  <p><strong>Target Search Intent:</strong> [Tentukan jenis intent: Informasional, Komersial, Transaksional, atau Navigasional dan beri penjelasan singkat]</p>
  <p><strong>Rekomendasi Struktur Heading:</strong></p>
  <ul>
    <li><code>H1</code>: [Rekomendasi Judul Utama]</li>
    <li><code>H2</code>: [Sub-judul Utama 1 - Menjawab pertanyaan utama search intent]
      <ul>
        <li><code>H3</code>: [Detail sub-topik pendukung]</li>
        <li><code>H3</code>: [Detail sub-topik pendukung]</li>
      </ul>
    </li>
    <li><code>H2</code>: [Sub-judul Utama 2 - Langkah praktis / Solusi bisnis %s]</li>
    <li><code>H2</code>: [Sub-judul Utama 3 - FAQ / Kesimpulan]</li>
  </ul>',
			$ctx['post_title'],
			$ctx['business'],
			$ctx['content'],
			$ctx['business']
		);
	}

	/**
	 * Panggil AI API (Gemini atau OpenAI) dan kembalikan teks hasil generate.
	 *
	 * @param string $prompt Prompt yang akan dikirim ke AI.
	 * @return string|WP_Error
	 */
	private function call_ai( string $prompt ) {
		$api_data = SGEOBIZ_GBP_Settings::get_api_data();
		$provider = $api_data['provider'] ?? 'gemini';

		// Coba provider utama, fallback ke provider lain jika gagal
		if ( $provider === 'gemini' && ! empty( $api_data['gemini'] ) ) {
			$result = $this->call_gemini( $prompt, $api_data['gemini'] );
			if ( ! is_wp_error( $result ) ) {
				return $result;
			}
			// Fallback ke OpenAI jika ada
			if ( ! empty( $api_data['openai'] ) ) {
				return $this->call_openai( $prompt, $api_data['openai'] );
			}
			return $result;
		}

		if ( $provider === 'openai' && ! empty( $api_data['openai'] ) ) {
			$result = $this->call_openai( $prompt, $api_data['openai'] );
			if ( ! is_wp_error( $result ) ) {
				return $result;
			}
			// Fallback ke Gemini jika ada
			if ( ! empty( $api_data['gemini'] ) ) {
				return $this->call_gemini( $prompt, $api_data['gemini'] );
			}
			return $result;
		}

		return new WP_Error( 'no_api_key', 'API key belum dikonfigurasi di halaman Pengaturan SGEOBIZ.' );
	}

	/**
	 * Panggil Google Gemini API.
	 *
	 * @param string $prompt  Prompt.
	 * @param string $api_key API key Gemini.
	 * @return string|WP_Error
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
				'temperature'     => 0.4,
				'maxOutputTokens' => 800,
			],
		] );

		$response = wp_remote_post( $url, [
			'headers' => [ 'Content-Type' => 'application/json' ],
			'body'    => $body,
			'timeout' => 35,
		] );

		if ( is_wp_error( $response ) ) {
			return new WP_Error( 'gemini_conn', 'Koneksi ke Gemini gagal: ' . $response->get_error_message() );
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
	 * Panggil OpenAI Chat Completions API.
	 *
	 * @param string $prompt  Prompt.
	 * @param string $api_key API key OpenAI.
	 * @return string|WP_Error
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
			'temperature' => 0.4,
			'max_tokens'  => 800,
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
			return new WP_Error( 'openai_conn', 'Koneksi ke OpenAI gagal: ' . $response->get_error_message() );
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
}

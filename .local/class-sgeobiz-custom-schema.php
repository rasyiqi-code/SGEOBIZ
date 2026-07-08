<?php
/**
 * SGEOBIZ Custom Schema Generator & Graph Injector
 *
 * Menyediakan modul metabox di editor post untuk memasukkan skema kustom secara
 * manual, serta mem-parsing dan menyatukannya secara dinamis ke dalam graph utama
 * plugin di front-end.
 *
 * @package SGEOBIZ
 */

defined( 'SGEOBIZ_SEO_PRESENT' ) or die;

class SGEOBIZ_Custom_Schema {

	/** Meta key database. */
	const META_KEY = '_sgeobiz_custom_schema';

	/**
	 * Daftarkan hook.
	 */
	public static function init() {
		$instance = new self();

		// Registrasi Metabox di admin
		add_action( 'add_meta_boxes', [ $instance, 'add_metabox' ] );
		add_action( 'save_post', [ $instance, 'save_metabox_data' ] );

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
				__( 'SGEOBIZ SEO - Skema Kustom (JSON-LD)', 'default' ),
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
		?>
		<div class="sgeobiz-custom-schema-wrap" style="padding: 6px 0;">
			<p style="margin-top: 0; color: #64748b; font-size: 13px;">
				<?php _e( 'Tambahkan skema terstruktur JSON-LD kustom untuk halaman ini (misal FAQPage, Review, Event, JobPosting, Recipe, dll). Kode akan divalidasi dan digabungkan secara otomatis ke dalam grafik `@graph` utama di front-end.', 'default' ); ?>
			</p>

			<textarea name="sgeobiz_custom_schema" id="sgeobiz_custom_schema_input" rows="8" style="width: 100%; font-family: monospace; font-size: 12px; padding: 10px; border-radius: 6px; border: 1px solid #cbd5e1; background: #fafafa; box-sizing: border-box;" placeholder='{&#10;  "@type": "FAQPage",&#10;  "mainEntity": [&#10;    {&#10;      "@type": "Question",&#10;      "name": "Pertanyaan?",&#10;      "acceptedAnswer": {&#10;        "@type": "Answer",&#10;        "text": "Jawaban."&#10;      }&#10;    }&#10;  ]&#10;}'><?php echo esc_textarea( $val ); ?></textarea>

			<p style="font-size: 11px; color: #94a3b8; margin-top: 6px; margin-bottom: 0;">
				<?php _e( 'Catatan: Pastikan kode berupa objek JSON-LD yang valid. Properti "@context" akan diselaraskan secara otomatis di front-end.', 'default' ); ?>
			</p>
		</div>
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

		// Bersihkan properti global @context jika disertakan,
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

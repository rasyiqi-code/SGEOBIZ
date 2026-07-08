<?php
/**
 * SGEOBIZ Schema GEO
 *
 * Mengintegrasikan skema grafik ramah GEO (Generative Engine Optimization)
 * ke dalam grafik data terstruktur utama:
 * 1. Speakable Specification: Mengidentifikasi paragraf target suara (.ringkasan-artikel-geo)
 *    pada entitas WebPage agar AI dapat melafalkan jawaban instan via Voice Search.
 * 2. Author sameAs Mapping + E-E-A-T: Menghubungkan profil penulis (Person) ke media sosial
 *    profesional mereka, menambahkan jobTitle, url, dan image untuk verifikasi E-E-A-T
 *    oleh sistem AI Google 2026.
 *
 * @package SGEOBIZ
 */

defined( 'SGEOBIZ_SEO_PRESENT' ) or die;

class SGEOBIZ_Schema_GEO {

	/**
	 * Daftarkan filter graph.
	 */
	public static function init() {
		$instance = new self();
		add_filter( 'sgeobiz_seo_schema_graph_data', [ $instance, 'optimize_schema_for_geo' ], 12, 2 );
	}

	/**
	 * Sisipkan data speakable dan sameAs author ke dalam graph utama.
	 *
	 * @param array      $graph Array graph JSON-LD.
	 * @param array|null $args  Query args.
	 * @return array Graph termodifikasi.
	 */
	public function optimize_schema_for_geo( array $graph, $args = null ) {
		// Hanya proses di halaman singular front-end
		if ( ! is_singular() ) {
			return $graph;
		}

		$post_id = get_the_ID();
		if ( ! $post_id ) {
			return $graph;
		}

		$author_id = (int) get_post_field( 'post_author', $post_id );

		// Loop dan modifikasi entitas di dalam graph secara referensial
		foreach ( $graph as &$entity ) {
			if ( ! isset( $entity['@type'] ) ) {
				continue;
			}

			$types = (array) $entity['@type'];

			// 1. Tambahkan Speakable ke WebPage / Article
			if ( in_array( 'WebPage', $types, true ) || in_array( 'BlogPosting', $types, true ) || in_array( 'Article', $types, true ) ) {
				$entity['speakable'] = [
					'@type'       => 'SpeakableSpecification',
					'cssSelector' => [ '.ringkasan-artikel-geo' ],
				];
			}

			// 2. Tambahkan sameAs + E-E-A-T fields ke Person (Author)
			if ( $author_id && in_array( 'Person', $types, true ) ) {
				$this->enrich_author_entity( $entity, $author_id );
			}
		}
		unset( $entity );

		return $graph;
	}

	/**
	 * Memperkaya entitas Person (Author) dengan data E-E-A-T 2026:
	 * url, jobTitle, image (avatar), dan sameAs sosial media lengkap.
	 *
	 * @param array $entity    Referensi ke node Person di graph (by-reference via caller).
	 * @param int   $author_id ID user WordPress.
	 */
	private function enrich_author_entity( array &$entity, int $author_id ): void {
		// -- URL halaman author archive WordPress
		$author_url = get_author_posts_url( $author_id );
		if ( $author_url ) {
			$entity['url'] = esc_url_raw( $author_url );
		}

		// -- Job Title: coba dari user meta SGEOBIZ dulu, fallback ke role WP
		$job_title = trim( (string) get_user_meta( $author_id, '_sgeobiz_author_jobtitle', true ) );
		if ( empty( $job_title ) ) {
			$user = get_userdata( $author_id );
			if ( $user && ! empty( $user->roles ) ) {
				// Kapitalisasi role sebagai fallback
				$job_title = ucfirst( str_replace( '_', ' ', reset( $user->roles ) ) );
			}
		}
		if ( $job_title ) {
			$entity['jobTitle'] = sanitize_text_field( $job_title );
		}

		// -- Image (avatar) penulis — sinyal identitas visual E-E-A-T
		$avatar_url = get_avatar_url( $author_id, [ 'size' => 400 ] );
		if ( $avatar_url && false === strpos( $avatar_url, 'gravatar.com/avatar/0' ) ) {
			$entity['image'] = [
				'@type' => 'ImageObject',
				'url'   => esc_url_raw( $avatar_url ),
			];
		}

		// -- sameAs: profil sosial media lengkap (gabungkan, deduplikasi)
		$same_as      = $this->get_author_social_profiles( $author_id );
		$existing     = isset( $entity['sameAs'] ) ? (array) $entity['sameAs'] : [];
		$merged       = array_values( array_unique( array_merge( $existing, $same_as ) ) );

		if ( ! empty( $merged ) ) {
			$entity['sameAs'] = $merged;
		}
	}

	/**
	 * Ambil profil sosial media penulis dari WordPress User Meta.
	 *
	 * @param int $author_id ID user penulis.
	 * @return array List URL sosial media penulis yang valid.
	 */
	private function get_author_social_profiles( int $author_id ): array {
		$profiles = [];

		// Field standar WP + field tambahan umum dari plugin populer
		// Key = nama meta, value = prefix URL jika bukan full URL
		$fields = [
			'linkedin'  => null,
			'twitter'   => 'https://x.com/',    // Konversi ke X.com
			'facebook'  => null,
			'wikipedia' => null,
			'youtube'   => null,
			'instagram' => 'https://instagram.com/',
			'tiktok'    => 'https://tiktok.com/@',
		];

		foreach ( $fields as $meta_key => $url_prefix ) {
			$val = trim( (string) get_the_author_meta( $meta_key, $author_id ) );
			if ( empty( $val ) ) {
				continue;
			}

			// Jika hanya username (tanpa http), build URL dari prefix
			if ( $url_prefix && strpos( $val, 'http' ) === false ) {
				$val = $url_prefix . ltrim( $val, '@' );
			}

			$url = esc_url_raw( $val );
			if ( ! empty( $url ) ) {
				$profiles[] = $url;
			}
		}

		return $profiles;
	}
}

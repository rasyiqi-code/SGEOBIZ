<?php
/**
 * SGEOBIZ Schema GEO
 *
 * Mengintegrasikan skema grafik ramah GEO (Generative Engine Optimization)
 * ke dalam grafik data terstruktur utama:
 * 1. Speakable Specification: Mengidentifikasi paragraf target suara (.ringkasan-artikel-geo)
 *    pada entitas WebPage agar AI dapat melafalkan jawaban instan via Voice Search.
 * 2. Author sameAs Mapping: Menghubungkan profil penulis (Person) ke media sosial profesional
 *    mereka (LinkedIn, X, Facebook, Wikipedia) dari database profil user WordPress
 *    guna memvalidasi faktor E-E-A-T secara otomatis.
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

		$author_id = get_post_field( 'post_author', $post_id );

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

			// 2. Tambahkan sameAs sosial media ke Person (Author) jika terisi di profil
			if ( $author_id && in_array( 'Person', $types, true ) ) {
				$same_as = $this->get_author_social_profiles( $author_id );
				
				if ( ! empty( $same_as ) ) {
					// Gabungkan dengan sameAs yang sudah ada jika ada, pastikan tidak duplikat
					$existing_same_as = isset( $entity['sameAs'] ) ? (array) $entity['sameAs'] : [];
					$merged_same_as   = array_values( array_unique( array_merge( $existing_same_as, $same_as ) ) );
					$entity['sameAs'] = $merged_same_as;
				}
			}
		}
		unset( $entity );

		return $graph;
	}

	/**
	 * Ambil profil sosial media penulis dari WordPress User Meta.
	 *
	 * @param int $author_id ID user penulis.
	 * @return array List URL sosial media penulis yang valid.
	 */
	private function get_author_social_profiles( int $author_id ) {
		$profiles = [];

		// Default field sosial media di profil WordPress & field umum dari plugin populer
		$fields = [
			'linkedin'  => 'linkedin',
			'twitter'   => 'twitter',
			'facebook'  => 'facebook',
			'wikipedia' => 'wikipedia',
			'youtube'   => 'youtube',
		];

		foreach ( $fields as $key => $meta_key ) {
			$val = trim( get_the_author_meta( $meta_key, $author_id ) );
			if ( empty( $val ) ) {
				continue;
			}

			// Konversi username twitter menjadi URL X.com jika hanya ditulis handle username saja
			if ( $key === 'twitter' && strpos( $val, 'http' ) === false ) {
				$val = 'https://x.com/' . ltrim( $val, '@' );
			}

			$url = esc_url_raw( $val );
			if ( ! empty( $url ) ) {
				$profiles[] = $url;
			}
		}

		return $profiles;
	}
}

<?php
/**
 * SGEOBIZ GEO Meta Tags
 *
 * Menghasilkan metadata geografis klasik (geo.region, geo.position, geo.placename, ICBM)
 * di <head> front-end untuk membantu mesin pencari non-Google (seperti Bing, DuckDuckGo)
 * melokalisasi relevansi pencarian geografis (GEO/Local SEO).
 *
 * @package SGEOBIZ
 */

defined( 'SGEOBIZ_SEO_PRESENT' ) or die;

class SGEOBIZ_Geo_Meta {

	/**
	 * Daftarkan hook ke wp_head.
	 */
	public static function init() {
		$instance = new self();
		add_action( 'wp_head', [ $instance, 'render_geo_meta_tags' ], 1 );
	}

	/**
	 * Cetak GEO meta tags di HTML head.
	 */
	public function render_geo_meta_tags() {
		$data = SGEOBIZ_GBP_Settings::get_business_data();

		// Jangan cetak jika koordinat GPS belum diisi
		if ( empty( $data['latitude'] ) || empty( $data['longitude'] ) ) {
			return;
		}

		$lat = floatval( $data['latitude'] );
		$lng = floatval( $data['longitude'] );

		// 1. Placename (Kota)
		$city = ! empty( $data['city'] ) ? sanitize_text_field( $data['city'] ) : '';

		// 2. Region (Provinsi ISO-3166-2 Indonesia)
		$region = '';
		if ( ! empty( $data['province'] ) ) {
			$region = $this->get_indonesia_iso_code( $data['province'] );
		}

		echo "\n<!-- SGEOBIZ SEO - GEO Meta Tags -->\n";
		if ( $region ) {
			printf( "<meta name=\"geo.region\" content=\"%s\" />\n", esc_attr( $region ) );
		}
		if ( $city ) {
			printf( "<meta name=\"geo.placename\" content=\"%s\" />\n", esc_attr( $city ) );
		}
		printf( "<meta name=\"geo.position\" content=\"%s;%s\" />\n", esc_attr( $lat ), esc_attr( $lng ) );
		printf( "<meta name=\"ICBM\" content=\"%s, %s\" />\n", esc_attr( $lat ), esc_attr( $lng ) );
		echo "<!-- End SGEOBIZ SEO - GEO Meta Tags -->\n";
	}

	/**
	 * Petakan nama provinsi ke kode ISO 3166-2:ID.
	 *
	 * @param string $province Nama provinsi.
	 * @return string Kode ISO (e.g. ID-JK).
	 */
	private function get_indonesia_iso_code( string $province ) {
		$province = strtolower( trim( $province ) );

		// Bersihkan istilah umum
		$province = str_replace( [ 'provinsi', 'dki', 'diy', 'daerah istimewa' ], '', $province );
		$province = trim( $province );

		$map = [
			'aceh' => 'ID-AC',
			'bali' => 'ID-BA',
			'bangka belitung' => 'ID-BB',
			'banten' => 'ID-BT',
			'bengkulu' => 'ID-BE',
			'gorontalo' => 'ID-GO',
			'jakarta' => 'ID-JK',
			'jambi' => 'ID-JA',
			'jawa barat' => 'ID-JB',
			'jawa tengah' => 'ID-JT',
			'jawa timur' => 'ID-JI',
			'kalimantan barat' => 'ID-KB',
			'kalimantan selatan' => 'ID-KS',
			'kalimantan tengah' => 'ID-KT',
			'kalimantan timur' => 'ID-KI',
			'kalimantan utara' => 'ID-KU',
			'kepulauan riau' => 'ID-KR',
			'lampung' => 'ID-LA',
			'maluku' => 'ID-MA',
			'maluku utara' => 'ID-MU',
			'nusa tenggara barat' => 'ID-NB',
			'ntb' => 'ID-NB',
			'nusa tenggara timur' => 'ID-NT',
			'ntt' => 'ID-NT',
			'papua' => 'ID-PA',
			'papua barat' => 'ID-PB',
			'riau' => 'ID-RI',
			'sulawesi barat' => 'ID-SR',
			'sulawesi selatan' => 'ID-SN',
			'sulawesi tengah' => 'ID-ST',
			'sulawesi tenggara' => 'ID-SG',
			'sulawesi utara' => 'ID-SA',
			'sumatera barat' => 'ID-SB',
			'sumatera selatan' => 'ID-SS',
			'sumatera utara' => 'ID-SU',
			'yogyakarta' => 'ID-YO',
			'jogja' => 'ID-YO',
		];

		return $map[ $province ] ?? 'ID';
	}
}

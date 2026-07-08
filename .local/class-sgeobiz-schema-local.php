<?php
/**
 * SGEOBIZ Schema Local — Integrasi JSON-LD LocalBusiness ke Graph Utama
 *
 * Menggabungkan schema LocalBusiness dan lokasi cabang Indonesia ke dalam
 * graph Schema.org utama (@graph) bawaan SGEOBIZ SEO agar saling terhubung.
 *
 * Taktik ini sangat disukai Google dibanding tag script terpisah.
 *
 * @package SGEOBIZ
 */

defined( 'SGEOBIZ_SEO_PRESENT' ) or die;

class SGEOBIZ_Schema_Local {

	/**
	 * Pasang hook filter untuk mengintegrasikan schema ke graph utama.
	 */
	public static function init() {
		$instance = new self();

		// Hook ke filter graph data bawaan SGEOBIZ SEO
		add_filter( 'sgeobiz_seo_schema_graph_data', [ $instance, 'integrate_to_graph' ], 10, 2 );
	}

	/**
	 * Integrasikan LocalBusiness & cabang ke dalam graph `@graph` utama.
	 *
	 * @param array      $graph Array berisi node graph Schema.org bawaan.
	 * @param array|null $args  Query arguments.
	 * @return array Graph yang sudah dimodifikasi.
	 */
	public function integrate_to_graph( array $graph, $args = null ) {
		$data = SGEOBIZ_GBP_Settings::get_business_data();

		// Jangan lakukan integrasi jika nama bisnis belum diisi
		if ( empty( $data['business_name'] ) ) {
			return $graph;
		}

		$local_id = home_url( '#localbusiness' );

		// 1. Bangun schema LocalBusiness utama (tanpa @context karena bagian dari graph)
		$local_schema = $this->build_schema( $data, $local_id );

		if ( ! empty( $local_schema ) ) {
			$graph[] = $local_schema;
		}

		// 2. Hubungkan entitas WebPage ke LocalBusiness kita
		foreach ( $graph as &$entity ) {
			if ( ! isset( $entity['@type'] ) ) {
				continue;
			}

			$types = (array) $entity['@type'];
			if ( in_array( 'WebPage', $types, true ) || in_array( 'CollectionPage', $types, true ) ) {
				// Tautkan about dan publisher ke LocalBusiness
				$entity['about']     = [ '@id' => $local_id ];
				$entity['publisher'] = [ '@id' => $local_id ];
			}
		}
		unset( $entity );

		// 3. Tambahkan cabang-cabang (locations) jika ada ke dalam graph
		$branches = $this->build_branch_schemas( $data, $local_id );
		if ( ! empty( $branches ) ) {
			$graph = array_merge( $graph, $branches );
		}

		return $graph;
	}

	/**
	 * Bangun array schema LocalBusiness dari data bisnis.
	 *
	 * @param array  $data Data bisnis dari SGEOBIZ_GBP_Settings.
	 * @param string $id   Schema ID unik.
	 * @return array Schema JSON-LD.
	 */
	private function build_schema( array $data, $id ) {
		$type = $data['business_type'] ?? 'LocalBusiness';

		$schema = [
			'@type' => $type,
			'@id'   => $id,
			'name'  => $data['business_name'],
		];

		// URL bisnis
		$url = ! empty( $data['website'] ) ? $data['website'] : home_url( '/' );
		$schema['url'] = $url;

		// Deskripsi
		if ( ! empty( $data['description'] ) ) {
			$schema['description'] = $data['description'];
		}

		// Alamat
		if ( ! empty( $data['address'] ) ) {
			$schema['address'] = $this->build_address( $data );
		}

		// Koordinat GPS
		if ( ! empty( $data['latitude'] ) && ! empty( $data['longitude'] ) ) {
			$schema['geo'] = [
				'@type'     => 'GeoCoordinates',
				'latitude'  => (float) $data['latitude'],
				'longitude' => (float) $data['longitude'],
			];

			// Tambahkan Google Maps URL
			$schema['hasMap'] = sprintf(
				'https://www.google.com/maps?q=%s,%s',
				$data['latitude'],
				$data['longitude']
			);
		}

		// Kontak telepon
		if ( ! empty( $data['phone'] ) ) {
			$schema['telephone'] = $data['phone'];
		}

		// Email
		if ( ! empty( $data['email'] ) ) {
			$schema['email'] = $data['email'];
		}

		// ContactPoint untuk WhatsApp
		if ( ! empty( $data['whatsapp'] ) ) {
			$wa_number = preg_replace( '/[^0-9]/', '', $data['whatsapp'] );
			$schema['contactPoint'] = [
				[
					'@type'       => 'ContactPoint',
					'telephone'   => '+' . $wa_number,
					'contactType' => 'customer service',
					'areaServed'  => 'ID',
					'availableLanguage' => [ 'Indonesian', 'English' ],
				],
			];
		}

		// Logo
		$logo_id = absint( $data['logo_id'] ?? 0 );
		if ( $logo_id ) {
			$logo_url = wp_get_attachment_image_url( $logo_id, 'full' );
			if ( $logo_url ) {
				$logo_meta = wp_get_attachment_metadata( $logo_id );
				$schema['logo'] = [
					'@type'  => 'ImageObject',
					'url'    => $logo_url,
					'width'  => $logo_meta['width']  ?? null,
					'height' => $logo_meta['height'] ?? null,
				];
				$schema['image'] = $schema['logo'];
			}
		}

		// Jam operasional
		$hours_schema = $this->build_opening_hours( $data['hours'] ?? [] );
		if ( ! empty( $hours_schema ) ) {
			$schema['openingHoursSpecification'] = $hours_schema;
		}

		// SameAs: sosial media & marketplace
		$same_as = $this->build_same_as( $data );
		if ( ! empty( $same_as ) ) {
			$schema['sameAs'] = $same_as;
		}

		return $schema;
	}

	/**
	 * Bangun PostalAddress schema untuk Indonesia.
	 *
	 * @param array $data Data bisnis.
	 * @return array
	 */
	private function build_address( array $data ) {
		$address = [
			'@type'           => 'PostalAddress',
			'addressCountry'  => 'ID',
		];

		if ( ! empty( $data['address'] ) ) {
			$address['streetAddress'] = $data['address'];
		}
		if ( ! empty( $data['city'] ) ) {
			$address['addressLocality'] = $data['city'];
		}
		if ( ! empty( $data['province'] ) ) {
			$address['addressRegion'] = $data['province'];
		}
		if ( ! empty( $data['postal_code'] ) ) {
			$address['postalCode'] = $data['postal_code'];
		}

		return $address;
	}

	/**
	 * Bangun array OpeningHoursSpecification dari data jam operasional.
	 *
	 * @param array $hours Data jam per hari.
	 * @return array
	 */
	private function build_opening_hours( array $hours ) {
		if ( empty( $hours ) ) {
			return [];
		}

		// Map nama hari ke format Schema.org
		$day_map = [
			'monday'    => 'Monday',
			'tuesday'   => 'Tuesday',
			'wednesday' => 'Wednesday',
			'thursday'  => 'Thursday',
			'friday'    => 'Friday',
			'saturday'  => 'Saturday',
			'sunday'    => 'Sunday',
		];

		$grouped = [];
		foreach ( $day_map as $key => $schema_day ) {
			$h = $hours[ $key ] ?? [];
			if ( ! empty( $h['closed'] ) ) {
				continue;
			}
			$open  = $h['open']  ?? '08:00';
			$close = $h['close'] ?? '17:00';
			$slot  = $open . '|' . $close;
			$grouped[ $slot ][] = $schema_day;
		}

		$result = [];
		foreach ( $grouped as $slot => $days ) {
			[ $open, $close ] = explode( '|', $slot );
			$result[] = [
				'@type'     => 'OpeningHoursSpecification',
				'dayOfWeek' => $days,
				'opens'     => $open,
				'closes'    => $close,
			];
		}

		return $result;
	}

	/**
	 * Bangun array sameAs dari URL sosial media & marketplace.
	 *
	 * @param array $data Data bisnis.
	 * @return array
	 */
	private function build_same_as( array $data ) {
		$fields    = [ 'website', 'facebook', 'instagram', 'tokopedia', 'shopee', 'tiktok' ];
		$same_as   = [];

		foreach ( $fields as $field ) {
			if ( ! empty( $data[ $field ] ) ) {
				$same_as[] = esc_url( $data[ $field ] );
			}
		}

		return array_values( array_unique( $same_as ) );
	}

	/**
	 * Buat list schema untuk setiap cabang bisnis.
	 *
	 * @param array  $data     Data bisnis utama.
	 * @param string $parent_id ID schema bisnis utama.
	 * @return array List schema cabang.
	 */
	private function build_branch_schemas( array $data, $parent_id ) {
		$locations = $data['locations'] ?? [];

		if ( empty( $locations ) ) {
			return [];
		}

		$type = $data['business_type'] ?? 'LocalBusiness';
		$branches = [];

		foreach ( $locations as $index => $loc ) {
			if ( empty( $loc['name'] ) ) {
				continue;
			}

			$branch = [
				'@type'    => $type,
				'@id'      => home_url( '#localbusiness-branch-' . ( $index + 1 ) ),
				'name'     => $loc['name'],
				'address'  => [
					'@type'           => 'PostalAddress',
					'streetAddress'   => $loc['address'] ?? '',
					'addressLocality' => $loc['city']    ?? '',
					'addressRegion'   => $loc['province'] ?? '',
					'postalCode'      => $loc['postal']  ?? '',
					'addressCountry'  => 'ID',
				],
			];

			if ( ! empty( $loc['phone'] ) ) {
				$branch['telephone'] = $loc['phone'];
			}

			if ( ! empty( $loc['lat'] ) && ! empty( $loc['lng'] ) ) {
				$branch['geo'] = [
					'@type'     => 'GeoCoordinates',
					'latitude'  => (float) $loc['lat'],
					'longitude' => (float) $loc['lng'],
				];
			}

			// Cabang tetap terhubung ke bisnis utama (Parent)
			$branch['parentOrganization'] = [
				'@id' => $parent_id,
			];

			$branches[] = $branch;
		}

		return $branches;
	}
}

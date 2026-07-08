<?php
/**
 * SGEOBIZ Schema Local — Output JSON-LD LocalBusiness
 *
 * Meng-inject schema JSON-LD ke <head> website untuk:
 * - LocalBusiness (semua tipe bisnis Indonesia)
 * - GeoCoordinates (koordinat GPS)
 * - OpeningHoursSpecification (jam operasional per hari)
 * - ContactPoint (telepon, WhatsApp)
 * - SameAs (link sosial media & marketplace)
 * - ImageObject (logo bisnis)
 *
 * Plugin-agnostic: bekerja dengan atau tanpa WooCommerce.
 *
 * @package SGEOBIZ
 */

defined( 'SGEOBIZ_SEO_PRESENT' ) or die;

class SGEOBIZ_Schema_Local {

	/**
	 * Daftarkan hook output schema di front-end.
	 */
	public static function init() {
		$instance = new self();

		// Inject JSON-LD ke <head> — priority 5 agar lebih awal dari TSF
		add_action( 'wp_head', [ $instance, 'output_schema' ], 5 );
	}

	/**
	 * Output blok JSON-LD LocalBusiness ke halaman front-end.
	 * Hanya muncul jika data bisnis sudah diisi.
	 */
	public function output_schema() {
		$data = SGEOBIZ_GBP_Settings::get_business_data();

		// Jangan output jika nama bisnis belum diisi
		if ( empty( $data['business_name'] ) ) {
			return;
		}

		$schema = $this->build_schema( $data );

		if ( empty( $schema ) ) {
			return;
		}

		// Output JSON-LD
		echo "\n<!-- SGEOBIZ SEO: LocalBusiness Schema -->\n";
		echo '<script type="application/ld+json">';
		echo wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT );
		echo "</script>\n";

		// Jika ada cabang, output masing-masing
		if ( ! empty( $data['locations'] ) ) {
			$this->output_branch_schemas( $data );
		}
	}

	/**
	 * Bangun array schema LocalBusiness dari data bisnis.
	 *
	 * @param array $data Data bisnis dari SGEOBIZ_GBP_Settings.
	 * @return array Schema JSON-LD.
	 */
	private function build_schema( array $data ) {
		$type = $data['business_type'] ?? 'LocalBusiness';

		$schema = [
			'@context' => 'https://schema.org',
			'@type'    => $type,
			'name'     => $data['business_name'],
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

		// PriceRange (opsional — bisa dikembangkan nanti)
		// $schema['priceRange'] = '$$';

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
	 * Mengelompokkan hari dengan jam yang sama untuk efisiensi.
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

		// Kelompokkan hari dengan jam buka/tutup yang sama
		$grouped = [];
		foreach ( $day_map as $key => $schema_day ) {
			$h = $hours[ $key ] ?? [];
			if ( ! empty( $h['closed'] ) ) {
				continue; // Lewati hari tutup
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
	 * Output schema untuk setiap cabang bisnis.
	 *
	 * @param array $data Data bisnis utama (berisi key 'locations').
	 */
	private function output_branch_schemas( array $data ) {
		$locations = $data['locations'] ?? [];

		if ( empty( $locations ) ) {
			return;
		}

		$type = $data['business_type'] ?? 'LocalBusiness';

		echo "\n<!-- SGEOBIZ SEO: Branch Locations Schema -->\n";
		foreach ( $locations as $loc ) {
			if ( empty( $loc['name'] ) ) {
				continue;
			}

			$branch = [
				'@context' => 'https://schema.org',
				'@type'    => $type,
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

			// Cabang tetap referensi ke bisnis utama
			$branch['parentOrganization'] = [
				'@type' => $type,
				'name'  => $data['business_name'],
				'url'   => ! empty( $data['website'] ) ? $data['website'] : home_url( '/' ),
			];

			echo '<script type="application/ld+json">';
			echo wp_json_encode( $branch, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT );
			echo "</script>\n";
		}
	}
}

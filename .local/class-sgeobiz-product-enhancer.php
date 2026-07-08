<?php
/**
 * SGEOBIZ Product Schema Enhancer (SEO 2026)
 *
 * Mengintegrasikan detail schema produk WooCommerce agar kompatibel penuh dengan
 * pembaharuan Google Merchant Center & AI Overviews 2026:
 * 1. Menambahkan kategori produk otomatis (Product.category) berdasarkan taksonomi WooCommerce.
 * 2. Mengisi salePrice dengan format validitas tanggal diskon (priceValidUntil) yang akurat.
 * 3. Menghubungkan entitas penjual (offers.seller) langsung ke LocalBusiness schema utama kita.
 *
 * @package SGEOBIZ
 */

defined( 'SGEOBIZ_SEO_PRESENT' ) or die;

class SGEOBIZ_Product_Enhancer {

	/**
	 * Inisialisasi modul jika WooCommerce aktif.
	 */
	public static function init() {
		$instance = new self();
		add_filter( 'sgeobiz_seo_schema_graph_data', [ $instance, 'enhance_product_schema' ], 16, 2 );
	}

	/**
	 * Optimalkan data terstruktur Produk WooCommerce.
	 *
	 * @param array      $graph Array graph JSON-LD.
	 * @param array|null $args  Query args.
	 * @return array Graph termodifikasi.
	 */
	public function enhance_product_schema( array $graph, $args = null ) {
		// Pastikan WooCommerce aktif dan kita berada di halaman produk singular
		if ( ! class_exists( 'WooCommerce' ) || ! is_product() ) {
			return $graph;
		}

		$product_id = get_the_ID();
		$product    = wc_get_product( $product_id );

		if ( ! $product ) {
			return $graph;
		}

		// Cari entitas LocalBusiness utama untuk referensi penawaran (Seller)
		$local_business_id = '';
		foreach ( $graph as $entity ) {
			if ( isset( $entity['@type'] ) && strpos( $entity['@type'], 'LocalBusiness' ) !== false ) {
				$local_business_id = $entity['@id'] ?? '';
				break;
			}
		}

		// Jika tidak ada LocalBusiness khusus, gunakan Organization sebagai fallback
		if ( ! $local_business_id ) {
			foreach ( $graph as $entity ) {
				if ( isset( $entity['@type'] ) && $entity['@type'] === 'Organization' ) {
					$local_business_id = $entity['@id'] ?? '';
					break;
				}
			}
		}

		// Ekstrak kategori produk untuk properti category (wajib di Google 2026)
		$categories = wp_get_post_terms( $product_id, 'product_cat', [ 'fields' => 'names' ] );
		$primary_cat = ! empty( $categories ) && ! is_wp_error( $categories ) ? $categories[0] : '';

		// Tanggal berakhirnya harga promo (jika produk sedang didiskon)
		$sale_to_date = $product->get_date_on_sale_to() ? $product->get_date_on_sale_to()->date( 'Y-m-d' ) : '';

		// Modifikasi node Product di dalam grafik
		foreach ( $graph as &$entity ) {
			if ( ! isset( $entity['@type'] ) ) {
				continue;
			}

			$types = (array) $entity['@type'];

			if ( in_array( 'Product', $types, true ) ) {
				// 1. Tambahkan kategori utama
				if ( $primary_cat ) {
					$entity['category'] = sanitize_text_field( $primary_cat );
				}

				// 2. Optimasi offers
				if ( isset( $entity['offers'] ) ) {
					// Jika bertipe list penawaran (array numerik)
					if ( is_array( $entity['offers'] ) && ! isset( $entity['offers']['@type'] ) ) {
						foreach ( $entity['offers'] as &$offer ) {
							$this->patch_offer( $offer, $local_business_id, $sale_to_date );
						}
						unset( $offer );
					} else {
						// Jika penawaran tunggal
						$this->patch_offer( $entity['offers'], $local_business_id, $sale_to_date );
					}
				}
			}
		}
		unset( $entity );

		return $graph;
	}

	/**
	 * Tambahkan properti pelengkap pada objek Offer.
	 *
	 * @param array  $offer        Array referensi offer.
	 * @param string $seller_id    ID skema lokal penjual.
	 * @param string $sale_to_date Tanggal diskon berakhir.
	 */
	private function patch_offer( array &$offer, string $seller_id, string $sale_to_date ): void {
		// Hubungkan penjual langsung ke LocalBusiness kita
		if ( $seller_id && ! isset( $offer['seller'] ) ) {
			$offer['seller'] = [
				'@type' => 'LocalBusiness',
				'@id'   => $seller_id,
			];
		}

		// Jika ada harga diskon aktif, tentukan tanggal berakhir validitasnya
		if ( $sale_to_date && ! isset( $offer['priceValidUntil'] ) ) {
			$offer['priceValidUntil'] = $sale_to_date;
		} elseif ( ! isset( $offer['priceValidUntil'] ) ) {
			// Jika tidak ada promo, beri batas default 1 tahun ke depan untuk keamanan validasi
			$offer['priceValidUntil'] = date( 'Y-m-d', strtotime( '+1 year' ) );
		}
	}
}

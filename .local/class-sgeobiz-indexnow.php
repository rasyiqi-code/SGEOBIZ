<?php
/**
 * SGEOBIZ IndexNow API Client
 *
 * Mengotomasi pemberitahuan perubahan konten (IndexNow) secara instan ke mesin pencari
 * (seperti Bing, Yandex, dan jaringannya) saat post diterbitkan atau diperbarui.
 * Ini memastikan perayap AI (GEO) dapat segera mengambil draf tulisan terbaru secara real-time.
 *
 * @package SGEOBIZ
 */

defined( 'SGEOBIZ_SEO_PRESENT' ) or die;

class SGEOBIZ_IndexNow {

	/** Option key database. */
	const KEY_OPTION = 'sgeobiz_indexnow_key';

	/**
	 * Inisialisasi modul IndexNow.
	 */
	public static function init() {
		$instance = new self();

		// Jalankan verifikasi key saat admin dimuat
		if ( is_admin() ) {
			add_action( 'admin_init', [ $instance, 'verify_indexnow_key_file' ] );
		}

		// Pantau perubahan status post untuk mengirimkan ping IndexNow
		add_action( 'transition_post_status', [ $instance, 'handle_post_transition' ], 10, 3 );
	}

	/**
	 * Dapatkan atau buat IndexNow key unik (32 karakter heksadesimal).
	 *
	 * @return string Key unik.
	 */
	public function get_or_create_key() {
		$key = get_option( self::KEY_OPTION );

		if ( empty( $key ) ) {
			// Generate key heksadesimal 32 karakter secara acak
			$key = bin2hex( wp_generate_password( 16, false ) );
			update_option( self::KEY_OPTION, $key );
		}

		return $key;
	}

	/**
	 * Memastikan file verifikasi fisik key.txt ada di root WordPress directory (ABSPATH).
	 */
	public function verify_indexnow_key_file() {
		$key = $this->get_or_create_key();
		if ( empty( $key ) ) {
			return;
		}

		$filename  = $key . '.txt';
		$file_path = ABSPATH . $filename;

		// Jika file belum ada, atau isinya tidak cocok dengan key saat ini
		if ( ! file_exists( $file_path ) || trim( file_get_contents( $file_path ) ) !== $key ) {
			// Tulis key ke file secara fisik di root direktori
			@file_put_contents( $file_path, $key );
		}
	}

	/**
	 * Deteksi perubahan status postingan untuk mengirimkan ping ke IndexNow API.
	 *
	 * @param string  $new_status Status baru post.
	 * @param string  $old_status Status lama post.
	 * @param WP_Post $post       Objek post.
	 */
	public function handle_post_transition( $new_status, $old_status, $post ) {
		// Hanya kirim jika post bertipe publik dan terbit (publish)
		$public_post_types = get_post_types( [ 'public' => true ] );
		if ( ! in_array( $post->post_type, $public_post_types, true ) ) {
			return;
		}

		// Skenario 1: Post baru terbit (bukan publish -> publish)
		// Skenario 2: Post yang sudah terbit diperbarui (publish -> publish)
		$is_new_publish = ( $new_status === 'publish' && $old_status !== 'publish' );
		$is_update_publish = ( $new_status === 'publish' && $old_status === 'publish' );

		if ( $is_new_publish || $is_update_publish ) {
			$permalink = get_permalink( $post->ID );
			if ( $permalink ) {
				$this->send_indexnow_ping( $permalink );
			}
		}
	}

	/**
	 * Kirim request ping ke endpoint API IndexNow.
	 *
	 * @param string $url URL postingan yang diperbarui.
	 * @return bool Status pengiriman.
	 */
	private function send_indexnow_ping( string $url ) {
		$key = $this->get_or_create_key();
		if ( empty( $key ) ) {
			return false;
		}

		$host         = wp_parse_url( home_url(), PHP_URL_HOST );
		$key_location = home_url( '/' . $key . '.txt' );

		$endpoint = 'https://api.indexnow.org/indexnow';

		$body = wp_json_encode( [
			'host'        => $host,
			'key'         => $key,
			'keyLocation' => $key_location,
			'urlList'     => [ $url ],
		] );

		// Kirim API request secara non-blocking / asinkron dengan timeout rendah
		$response = wp_remote_post( $endpoint, [
			'headers'     => [ 'Content-Type' => 'application/json; charset=utf-8' ],
			'body'        => $body,
			'timeout'     => 10,
			'redirection' => 0,
			'blocking'    => false, // Non-blocking agar tidak mengganggu kecepatan loading admin editor post
		] );

		return ! is_wp_error( $response );
	}
}

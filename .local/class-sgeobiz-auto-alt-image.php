<?php
/**
 * SGEOBIZ Auto Image SEO Optimizer
 *
 * Mengoptimalkan SEO Gambar secara otomatis melalui dua lapis tindakan:
 * 1. Auto Alt Text Injector: Mengisi secara dinamis tag alt gambar yang kosong
 *    di front-end menggunakan Bare Meta Title halaman yang kaya kata kunci.
 * 2. Auto File Name Renamer: Mengubah nama file fisik gambar secara permanen
 *    saat di-upload dari editor draf halaman agar ramah SEO (misal DSC_0129.jpg ➔ tips-hemat.jpg).
 *
 * @package SGEOBIZ
 */

defined( 'SGEOBIZ_SEO_PRESENT' ) or die;

class SGEOBIZ_Auto_Alt_Image {

	/**
	 * Daftarkan hook WordPress.
	 */
	public static function init() {
		$instance = new self();

		// Lapis 1: Alt Text Injector di front-end
		add_filter( 'the_content', [ $instance, 'append_alt_tags' ], 18 );
		add_filter( 'post_thumbnail_html', [ $instance, 'append_featured_image_alt' ], 18, 5 );

		// Lapis 2: File Name Renamer saat upload
		add_filter( 'wp_handle_upload_prefilter', [ $instance, 'rename_uploaded_image' ] );
	}

	/**
	 * Otomatis mengisi tag alt kosong pada gambar di dalam konten.
	 *
	 * @param string $content HTML konten.
	 * @return string HTML konten termodifikasi.
	 */
	public function append_alt_tags( $content ) {
		if ( ! is_singular() || ! in_the_loop() || ! is_main_query() ) {
			return $content;
		}

		$post_id = get_the_ID();
		if ( ! $post_id ) {
			return $content;
		}

		// Kunci target: Ambil Bare Meta Title kustom jika ada, fallback ke judul halaman asli
		$bare_title = '';
		if ( class_exists( 'SGEOBIZ_SEO\\Meta\\Title' ) ) {
			$bare_title = SGEOBIZ_SEO\Meta\Title::get_bare_title( [ 'id' => $post_id ] );
		}
		if ( empty( $bare_title ) ) {
			$bare_title = get_the_title( $post_id );
		}
		$bare_title = trim( wp_strip_all_tags( $bare_title ) );

		if ( empty( $bare_title ) ) {
			return $content;
		}

		// Counter gambar untuk mencegah spaming nama alt yang sama persis
		$img_counter = 0;

		// Pindai semua tag <img> di dalam konten draf
		$content = preg_replace_callback(
			'/<img([^>]+)>/is',
			function ( $matches ) use ( $bare_title, &$img_counter ) {
				$img_counter++;
				$attributes_str = $matches[1];

				// Tambahkan penomoran di belakang jika gambar kedua dan seterusnya
				$alt_text = ( $img_counter === 1 ) ? $bare_title : $bare_title . ' - ' . $img_counter;

				// 1. Jika atribut alt ada
				if ( preg_match( '/\balt\s*=\s*(["\'])(.*?)\1/is', $attributes_str, $alt_match ) ) {
					$alt_val = trim( $alt_match[2] );
					// Jika nilainya kosong, kita ganti nilainya dengan alt_text kustom
					if ( empty( $alt_val ) ) {
						$attributes_str = preg_replace( '/\balt\s*=\s*(["\'])(.*?)\1/is', 'alt="' . esc_attr( $alt_text ) . '"', $attributes_str );
					}
				} else {
					// 2. Jika atribut alt tidak ada sama sekali, sisipkan di bagian depan
					$attributes_str = ' alt="' . esc_attr( $alt_text ) . '"' . $attributes_str;
				}

				return '<img' . $attributes_str . '>';
			},
			$content
		);

		return $content;
	}

	/**
	 * Otomatis mengisi tag alt kosong pada Featured Image (gambar andalan).
	 *
	 * @param string $html              HTML featured image.
	 * @param int    $post_id           ID postingan.
	 * @param int    $post_thumbnail_id ID media.
	 * @param string $size              Ukuran.
	 * @param string $attr              Atribut.
	 * @return string HTML termodifikasi.
	 */
	public function append_featured_image_alt( $html, $post_id, $post_thumbnail_id, $size, $attr ) {
		if ( empty( $html ) ) {
			return $html;
		}

		// Dapatkan kustom Bare Title
		$bare_title = '';
		if ( class_exists( 'SGEOBIZ_SEO\\Meta\\Title' ) ) {
			$bare_title = SGEOBIZ_SEO\Meta\Title::get_bare_title( [ 'id' => $post_id ] );
		}
		if ( empty( $bare_title ) ) {
			$bare_title = get_the_title( $post_id );
		}
		$bare_title = trim( wp_strip_all_tags( $bare_title ) );

		if ( empty( $bare_title ) ) {
			return $html;
		}

		// Jika alt kosong atau tidak ada, isi
		if ( preg_match( '/\balt\s*=\s*(["\'])(.*?)\1/is', $html, $alt_match ) ) {
			$alt_val = trim( $alt_match[2] );
			if ( empty( $alt_val ) ) {
				$html = preg_replace( '/\balt\s*=\s*(["\'])(.*?)\1/is', 'alt="' . esc_attr( $bare_title ) . '"', $html );
			}
		} else {
			$html = preg_replace( '/<img/is', '<img alt="' . esc_attr( $bare_title ) . '"', $html );
		}

		return $html;
	}

	/**
	 * Otomatis mengubah nama file fisik gambar saat di-upload dari editor draf.
	 * DSC_0129.jpg ➔ nama-judul-postingan-slug.jpg
	 *
	 * @param array $file Array data file $_FILES WordPress.
	 * @return array Array file termodifikasi.
	 */
	public function rename_uploaded_image( $file ) {
		// Dapatkan ID post saat proses upload
		$post_id = 0;
		if ( isset( $_REQUEST['post_id'] ) ) {
			$post_id = absint( $_REQUEST['post_id'] );
		} elseif ( isset( $_POST['post_id'] ) ) {
			$post_id = absint( $_POST['post_id'] );
		}

		if ( ! $post_id ) {
			return $file;
		}

		$post = get_post( $post_id );
		if ( ! $post || empty( $post->post_title ) ) {
			return $file;
		}

		// Abaikan draft bawaan sistem yang belum bernama
		if ( in_array( $post->post_title, [ 'Auto Draft', '' ], true ) ) {
			return $file;
		}

		// Buat slug yang bersih berdasarkan judul postingan saat ini
		$clean_slug = sanitize_title( $post->post_title );
		if ( empty( $clean_slug ) ) {
			return $file;
		}

		// Dapatkan ekstensi file asli
		$ext = pathinfo( $file['name'], PATHINFO_EXTENSION );
		$ext = $ext ? '.' . strtolower( $ext ) : '';

		// Set nama file baru
		// WordPress secara otomatis menangani keunikan (wp_unique_filename)
		// jika file dengan nama ini sudah ada di folder uploads disk.
		$file['name'] = $clean_slug . $ext;

		return $file;
	}
}

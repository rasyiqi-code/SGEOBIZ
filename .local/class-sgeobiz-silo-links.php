<?php
/**
 * SGEOBIZ Auto Silo Related Links
 *
 * Otomatisasi pembentukan Tautan Internal Rumpun (Internal Link Silo Pyramid)
 * dengan menampilkan 3 artikel terbaru yang berada di bawah kategori yang sama
 * secara otomatis di akhir artikel utama.
 *
 * Taktik ini mendistribusikan otoritas link (link juice) secara horizontal
 * dan vertikal di bawah satu silo kategori/topik yang sama secara aman.
 *
 * @package SGEOBIZ
 */

defined( 'SGEOBIZ_SEO_PRESENT' ) or die;

class SGEOBIZ_Silo_Links {

	/**
	 * Daftarkan hook filter content.
	 */
	public static function init() {
		$instance = new self();
		add_filter( 'the_content', [ $instance, 'append_silo_links' ] );
	}

	/**
	 * Tempelkan (append) list artikel sejenis di akhir konten postingan tunggal.
	 *
	 * @param string $content HTML konten artikel asli.
	 * @return string Konten termodifikasi.
	 */
	public function append_silo_links( $content ) {
		// Hanya proses di halaman post tunggal utama (bukan feed, widget, dsb)
		if ( ! is_singular( 'post' ) || ! in_the_loop() || ! is_main_query() ) {
			return $content;
		}

		$post_id = get_the_ID();
		if ( ! $post_id ) {
			return $content;
		}

		// Cari kategori yang diasosiasikan dengan artikel ini
		$categories = get_the_category( $post_id );
		if ( empty( $categories ) ) {
			return $content;
		}

		$cat_ids   = wp_list_pluck( $categories, 'term_id' );
		$primary_cat = $categories[0]; // Kategori utama (indeks pertama)

		// Query 3 artikel terbaru di bawah kategori yang sama
		$related_posts = get_posts( [
			'category__in'   => $cat_ids,
			'post__not_in'   => [ $post_id ],
			'posts_per_page' => 3,
			'orderby'        => 'date',
			'order'          => 'DESC',
		] );

		if ( empty( $related_posts ) ) {
			return $content;
		}

		// Bangun markup HTML Related Silo Links dengan visual premium
		$silo_html = $this->build_silo_markup( $related_posts, $primary_cat->name );

		return $content . $silo_html;
	}

	/**
	 * Bangun output HTML dan CSS inline untuk link silo.
	 *
	 * @param array  $posts List post dari get_posts.
	 * @param string $category_name Nama kategori utama.
	 * @return string HTML markup.
	 */
	private function build_silo_markup( array $posts, string $category_name ) {
		// Dapatkan stylesheet dashicons agar ikon wordpress tampil (biasanya sudah diload di front-end)
		wp_enqueue_style( 'dashicons' );

		$html = '
		<style>
			.sgeobiz-silo-wrap {
				margin: 35px 0 20px 0;
				padding: 20px;
				border: 1px solid #e2e8f0;
				border-radius: 8px;
				background-color: #f8fafc;
				box-sizing: border-box;
				clear: both;
			}
			.sgeobiz-silo-title {
				margin-top: 0;
				margin-bottom: 14px;
				font-size: 15px;
				font-weight: 700;
				color: #1e293b;
				display: flex;
				align-items: center;
				gap: 8px;
				border-bottom: 1px solid #e2e8f0;
				padding-bottom: 8px;
			}
			.sgeobiz-silo-title .dashicons {
				color: #0073aa;
				font-size: 18px;
				width: 18px;
				height: 18px;
				line-height: 1;
			}
			.sgeobiz-silo-list {
				margin: 0;
				padding-left: 20px;
				list-style-type: square;
			}
			.sgeobiz-silo-item {
				margin-bottom: 10px;
				line-height: 1.5;
				font-size: 14px;
			}
			.sgeobiz-silo-item:last-child {
				margin-bottom: 0;
			}
			.sgeobiz-silo-item a {
				color: #0073aa;
				text-decoration: none;
				font-weight: 500;
				transition: color 0.15s ease;
			}
			.sgeobiz-silo-item a:hover {
				color: #005177;
				text-decoration: underline;
			}
		</style>
		';

		$html .= '<div class="sgeobiz-silo-wrap">';
		$html .= sprintf(
			'<h4 class="sgeobiz-silo-title"><span class="dashicons dashicons-admin-links"></span> %s %s:</h4>',
			esc_html__( 'Baca Juga di Kategori', 'default' ),
			esc_html( $category_name )
		);
		$html .= '<ul class="sgeobiz-silo-list">';

		foreach ( $posts as $p ) {
			$permalink = get_permalink( $p->ID );
			$title     = get_the_title( $p->ID );
			$html     .= '<li class="sgeobiz-silo-item">';
			$html     .= sprintf(
				'<a href="%s" title="%s">%s</a>',
				esc_url( $permalink ),
				esc_attr( $title ),
				esc_html( $title )
			);
			$html     .= '</li>';
		}

		$html .= '</ul>';
		$html .= '</div>';

		return $html;
	}
}

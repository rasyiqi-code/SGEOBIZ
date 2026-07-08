<?php
/**
 * @package SGEOBIZ_SEO\Views\Admin\Metaboxes
 * @subpackage SGEOBIZ_SEO\Admin\Settings
 */

namespace SGEOBIZ_SEO;

( \defined( 'SGEOBIZ_SEO_PRESENT' ) and Helper\Template::verify_secret( $secret ) ) or die;

use SGEOBIZ_SEO\Admin\Settings\Layout\{
	Form,
	HTML,
	Input,
};
use SGEOBIZ_SEO\{
	Data\Filter\Sanitize,
	Helper\Compatibility,
	Helper\Post_Type,
};

// phpcs:disable WordPress.WP.GlobalVariablesOverride -- This isn't the global scope.

/**
 * SGEOBIZ SEO plugin
 * Copyright (C) 2021 - 2025 SGEOBIZ (https://rasyiqi-code.github.io/SGEOBIZ/)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 3 as published
 * by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

// See _post_type_archive_metabox et al.
[ $instance ] = $view_args;

// Fetch the required instance within this file.
switch ( $instance ) :
	case 'main':
		HTML::description(
			\__( 'Post type archives (PTA) are unique archives displaying all pages for a post type. Since PTAs lack an administrative interface, their SEO settings are displayed here.', 'sgeobiz-seo' )
		);

		?>
		<hr>
		<?php

		$_settings_class = Admin\Settings\Plugin::class;
		$post_types      = Post_Type::get_public_pta();

		$post_types_data = [];
		foreach ( $post_types as $post_type ) {
			$post_types_data[ $post_type ] = [
				'label'    => Post_Type::get_label( $post_type ),
				'url'      => Meta\URI::get_bare_pta_url( $post_type ), // permalink!
				'hasPosts' => Data\Post::has_posts_in_pta( $post_type ),
			];
		}

		printf(
			'<span class=hidden id=sgeobiz-post-type-archive-data %s></span>',
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- This escapes.
			HTML::make_data_attributes( [ 'postTypes' => $post_types_data ] )
		);

		?>
		<div id=sgeobiz-post-type-archive-header-wrap class=sgeobiz-fields style=display:none>
			<div id=sgeobiz-post-type-archive-select-wrap>
				<label for=sgeobiz-post-type-archive-selector><?php \esc_html_e( 'Select archive to edit:', 'sgeobiz-seo' ); ?></label>
				<select id=sgeobiz-post-type-archive-selector></select>
			</div>
		</div>
		<?php
		/**
		 * This data isn't read, only the keys are used -- there are more filters affecting the default output
		 * That ultimately lead to the data this method feeds.
		 */
		$pta_defaults    = Data\Plugin\PTA::get_all_default_meta();
		$post_type_index = 0;

		foreach ( $post_types as $post_type ) {
			$generator_args = [ 'pta' => $post_type ];
			$options        = [];

			foreach ( $pta_defaults[ $post_type ] as $_option => $_default )
				$options[ $_option ] = [ 'pta', $post_type, $_option ];

			$args = compact( 'post_type', 'generator_args', 'options' );
			$tabs = [
				'general'    => [
					'name'     => \__( 'General', 'sgeobiz-seo' ),
					'callback' => [ $_settings_class, '_post_type_archive_metabox_general_tab' ],
					'dashicon' => 'admin-generic',
					'args'     => $args,
				],
				'social'     => [
					'name'     => \__( 'Social', 'sgeobiz-seo' ),
					'callback' => [ $_settings_class, '_post_type_archive_metabox_social_tab' ],
					'dashicon' => 'share',
					'args'     => $args,
				],
				'visibility' => [
					'name'     => \__( 'Visibility', 'sgeobiz-seo' ),
					'callback' => [ $_settings_class, '_post_type_archive_metabox_visibility_tab' ],
					'dashicon' => 'visibility',
					'args'     => $args,
				],
			];

			// Hide subsequent wraps to prevent layout shifts (bounce) during load: They get hidden by JS anyway.
			printf(
				'<div class="sgeobiz-post-type-archive-wrap%s" %s>',
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Shut it, noob.
				$post_type_index ? ' hide-if-sgeobiz-js' : '',
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- This escapes.
				HTML::make_data_attributes( [ 'postType' => $post_type ] )
			);
			?>
				<div class=sgeobiz-post-type-header>
					<?php
					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- it is.
					echo HTML::get_header_title( vsprintf(
						'%s &ndash; <span class=sgeobiz-post-type-archive-details><code>%s</code> %s</span>',
						[
							\sprintf(
								/* translators: 1 = Post Type Archive name */
								\esc_html__( 'Editing archive of %s', 'sgeobiz-seo' ),
								\esc_html( $post_types_data[ $post_type ]['label'] ),
							),
							\esc_html( $post_type ),
							\sprintf(
								'<span class=sgeobiz-post-type-archive-link><a href="%s" target=_blank rel=noopener>[%s]</a></span>',
								\esc_url( $post_types_data[ $post_type ]['url'] ),
								\esc_html__( 'View archive', 'sgeobiz-seo' ),
							),
						],
					) );
					?>
				</div>
				<div class="sgeobiz-post-type-archive-if-excluded hidden">
					<?php
					HTML::attention_description(
						\__( "This post type is excluded, so settings won't have any effect.", 'sgeobiz-seo' )
					)
					?>
				</div>
				<div class=sgeobiz-post-type-archive-if-not-excluded>
					<?php
					if ( Compatibility::get_active_conflicting_plugin_types()['multilingual'] ) {
						HTML::attention(
							\__( 'A multilingual plugin has been detected and text entered below may not be translated.', 'sgeobiz-seo' )
						);
					}

					Admin\Settings\Plugin::nav_tab_wrapper(
						"post_type_archive_{$post_type}",
						/**
						 * @since 4.2.0
						 * @param array  $tabs      The default tabs.
						 * @param string $post_type The post type archive's name.
						 */
						(array) \apply_filters(
							'sgeobiz_seo_post_type_archive_settings_tabs',
							$tabs,
							$post_type,
						)
					);
					?>
				</div>
			</div>
			<?php
			// Output only the first time.
			$post_type_index++ or print( '<hr class=hide-if-sgeobiz-js>' );
		}
		break;

	case 'general':
		[ , $args ] = $view_args;
		?>
		<p>
			<label for="<?php Input::field_id( $args['options']['doctitle'] ); ?>" class=sgeobiz-toblock>
				<strong><?php \esc_html_e( 'Meta Title', 'sgeobiz-seo' ); ?></strong>
				<?php
					echo ' ';
					HTML::make_info(
						\__( 'The meta title can be used to determine the title used on search engine result pages.', 'sgeobiz-seo' ),
						'https://developers.google.com/search/docs/advanced/appearance/title-link',
					);
				?>
			</label>
		</p>
		<?php
		// Output these unconditionally, with inline CSS attached to allow reacting on settings.
		Form::output_character_counter_wrap(
			Input::get_field_id( $args['options']['doctitle'] ),
			(bool) Data\Plugin::get_option( 'display_character_counter' ),
		);
		Form::output_pixel_counter_wrap(
			Input::get_field_id( $args['options']['doctitle'] ),
			'title',
			(bool) Data\Plugin::get_option( 'display_pixel_counter' )
		);
		?>
		<p class=sgeobiz-title-wrap>
			<input type=text name="<?php Input::field_name( $args['options']['doctitle'] ); ?>" class=large-text id="<?php Input::field_id( $args['options']['doctitle'] ); ?>" value="<?= \esc_html( Sanitize::metadata_content( Data\Plugin\PTA::get_meta_item( 'doctitle', $args['post_type'] ) ) ) ?>" autocomplete=off>
			<?php
			$pto = \get_post_type_object( $args['post_type'] );

			// Skip first entry: $_full_title
			[ , $_prefix_value, $_default_title ] =
				Meta\Title::get_archive_title_list( $pto );

			Input::output_js_title_data(
				Input::get_field_id( $args['options']['doctitle'] ),
				[
					'state' => [
						'defaultTitle'      => \esc_html( $_default_title ),
						'addAdditions'      => Meta\Title\Conditions::use_branding( $args['generator_args'] ),
						'useSocialTagline'  => Meta\Title\Conditions::use_branding( $args['generator_args'], true ),
						'additionValue'     => \esc_html( Meta\Title::get_addition() ),
						'additionPlacement' => 'left' === Meta\Title::get_addition_location() ? 'before' : 'after',
						'prefixValue'       => \esc_html( $_prefix_value ),
						'showPrefix'        => Meta\Title\Conditions::use_generated_archive_prefix( $pto ),
					],
				],
			);
			?>
		</p>

		<div class=sgeobiz-title-tagline-toggle>
		<?php
			$info = HTML::make_info(
				\__( 'Use this when you want to rearrange the title parts manually.', 'sgeobiz-seo' ),
				'',
				false,
			);

			HTML::wrap_fields(
				Input::make_checkbox( [
					'id'     => $args['options']['title_no_blog_name'],
					'label'  => \esc_html__( 'Remove the site title?', 'sgeobiz-seo' ) . " $info",
					'value'  => Data\Plugin\PTA::get_meta_item( 'title_no_blog_name', $args['post_type'] ),
					'escape' => false,
				] ),
				true,
			);
		?>
		</div>

		<hr>

		<p>
			<label for="<?php Input::field_id( $args['options']['description'] ); ?>" class=sgeobiz-toblock>
				<strong><?php \esc_html_e( 'Meta Description', 'sgeobiz-seo' ); ?></strong>
				<?php
					echo ' ';
					HTML::make_info(
						\__( 'The meta description can be used to determine the text used under the title on search engine results pages.', 'sgeobiz-seo' ),
						'https://developers.google.com/search/docs/advanced/appearance/snippet',
					);
				?>
			</label>
		</p>
		<?php
		// Output these unconditionally, with inline CSS attached to allow reacting on settings.
		Form::output_character_counter_wrap(
			Input::get_field_id( $args['options']['description'] ),
			(bool) Data\Plugin::get_option( 'display_character_counter' ),
		);
		Form::output_pixel_counter_wrap(
			Input::get_field_id( $args['options']['description'] ),
			'description',
			(bool) Data\Plugin::get_option( 'display_pixel_counter' ),
		);
		?>
		<p>
			<textarea name="<?php Input::field_name( $args['options']['description'] ); ?>" class=large-text id="<?php Input::field_id( $args['options']['description'] ); ?>" rows=3 cols=70><?= \esc_attr( Data\Plugin\PTA::get_meta_item( 'description', $args['post_type'] ) ) ?></textarea>
			<?php
			Input::output_js_description_data(
				Input::get_field_id( $args['options']['description'] ),
				[
					'state' => [
						'defaultDescription' => \esc_html(
							Meta\Description::get_generated_description( $args['generator_args'] )
						),
					],
				],
			);
			?>
		</p>
		<?php
		break;
	case 'social':
		[ , $args ] = $view_args;
		Input::output_js_social_data(
			"pta_social_settings_{$args['post_type']}",
			[
				'og' => [
					'state' => [
						'defaultTitle' => \esc_html( Meta\Open_Graph::get_generated_title( $args['generator_args'] ) ),
						'addAdditions' => Meta\Title\Conditions::use_branding( $args['generator_args'], 'og' ),
						'defaultDesc'  => \esc_html( Meta\Open_Graph::get_generated_description( $args['generator_args'] ) ),
					],
				],
				'tw' => [
					'state' => [
						'defaultTitle' => \esc_html( Meta\Twitter::get_generated_title( $args['generator_args'] ) ),
						'addAdditions' => Meta\Title\Conditions::use_branding( $args['generator_args'], 'twitter' ),
						'defaultDesc'  => \esc_html( Meta\Twitter::get_generated_description( $args['generator_args'] ) ),
					],
				],
			],
		);

		/* translators: %s = default option value */
		$_default_i18n      = \__( 'Default (%s)', 'sgeobiz-seo' );
		$tw_supported_cards = Meta\Twitter::get_supported_cards();

		?>
		<p>
			<label for="<?php Input::field_id( $args['options']['og_title'] ); ?>" class=sgeobiz-toblock>
				<strong><?php \esc_html_e( 'Open Graph Title', 'sgeobiz-seo' ); ?></strong>
			</label>
		</p>
		<?php
		// Output this unconditionally, with inline CSS attached to allow reacting on settings.
		Form::output_character_counter_wrap(
			Input::get_field_id( $args['options']['og_title'] ),
			(bool) Data\Plugin::get_option( 'display_character_counter' ),
		);
		?>
		<p>
			<input type=text name="<?php Input::field_name( $args['options']['og_title'] ); ?>" class=large-text id="<?php Input::field_id( $args['options']['og_title'] ); ?>" value="<?= \esc_html( Sanitize::metadata_content( Data\Plugin\PTA::get_meta_item( 'og_title', $args['post_type'] ) ) ) ?>" autocomplete=off data-sgeobiz-social-group=<?= \esc_attr( "pta_social_settings_{$args['post_type']}" ) ?> data-sgeobiz-social-type=ogTitle>
		</p>

		<p>
			<label for="<?php Input::field_id( $args['options']['og_description'] ); ?>" class=sgeobiz-toblock>
				<strong><?php \esc_html_e( 'Open Graph Description', 'sgeobiz-seo' ); ?></strong>
			</label>
		</p>
		<?php
		// Output this unconditionally, with inline CSS attached to allow reacting on settings.
		Form::output_character_counter_wrap(
			Input::get_field_id( $args['options']['og_description'] ),
			(bool) Data\Plugin::get_option( 'display_character_counter' ),
		);
		?>
		<p>
			<textarea name="<?php Input::field_name( $args['options']['og_description'] ); ?>" class=large-text id="<?php Input::field_id( $args['options']['og_description'] ); ?>" rows=3 cols=70 autocomplete=off data-sgeobiz-social-group=<?= \esc_attr( "pta_social_settings_{$args['post_type']}" ) ?> data-sgeobiz-social-type=ogDesc><?= \esc_attr( Data\Plugin\PTA::get_meta_item( 'og_description', $args['post_type'] ) ) ?></textarea>
		</p>

		<hr>

		<p>
			<label for="<?php Input::field_id( $args['options']['tw_title'] ); ?>" class=sgeobiz-toblock>
				<strong><?php \esc_html_e( 'Twitter Title', 'sgeobiz-seo' ); ?></strong>
			</label>
		</p>
		<?php
		// Output this unconditionally, with inline CSS attached to allow reacting on settings.
		Form::output_character_counter_wrap(
			Input::get_field_id( $args['options']['tw_title'] ),
			(bool) Data\Plugin::get_option( 'display_character_counter' ),
		);
		?>
		<p>
			<input type=text name="<?php Input::field_name( $args['options']['tw_title'] ); ?>" class=large-text id="<?php Input::field_id( $args['options']['tw_title'] ); ?>" value="<?= \esc_html( Sanitize::metadata_content( Data\Plugin\PTA::get_meta_item( 'tw_title', $args['post_type'] ) ) ) ?>" autocomplete=off data-sgeobiz-social-group=<?= \esc_attr( "pta_social_settings_{$args['post_type']}" ) ?> data-sgeobiz-social-type=twTitle>
		</p>

		<p>
			<label for="<?php Input::field_id( $args['options']['tw_description'] ); ?>" class=sgeobiz-toblock>
				<strong><?php \esc_html_e( 'Twitter Description', 'sgeobiz-seo' ); ?></strong>
			</label>
		</p>
		<?php
		// Output this unconditionally, with inline CSS attached to allow reacting on settings.
		Form::output_character_counter_wrap(
			Input::get_field_id( $args['options']['tw_description'] ),
			(bool) Data\Plugin::get_option( 'display_character_counter' ),
		);
		?>
		<p>
			<textarea name="<?php Input::field_name( $args['options']['tw_description'] ); ?>" class=large-text id="<?php Input::field_id( $args['options']['tw_description'] ); ?>" rows=3 cols=70 autocomplete=off data-sgeobiz-social-group=<?= \esc_attr( "pta_social_settings_{$args['post_type']}" ) ?> data-sgeobiz-social-type=twDesc><?= \esc_attr( Data\Plugin\PTA::get_meta_item( 'tw_description', $args['post_type'] ) ) ?></textarea>
		</p>

		<p>
			<label for="<?php Input::field_id( $args['options']['tw_card_type'] ); ?>" class=sgeobiz-toblock>
				<strong><?php \esc_html_e( 'Twitter Card Type', 'sgeobiz-seo' ); ?></strong>
				<?php
				HTML::make_info(
					\__( 'The Twitter Card type controls the link preview layout. On X, the summary card shows a small thumbnail beside truncated title and description; the large-image card overlays the title on the image with no description. On Discord, the image appears small at the side or large below; both card types show the description.', 'sgeobiz-seo' ),
					'https://rasyiqi-code.github.io/SGEOBIZ/',
				);
				?>
			</label>
		</p>
		<p>
			<?php
			// phpcs:disable WordPress.Security.EscapeOutput -- make_single_select_form() escapes.
			echo Form::make_single_select_form( [
				'id'       => Input::get_field_id( $args['options']['tw_card_type'] ),
				'class'    => 'sgeobiz-select-block',
				'name'     => Input::get_field_name( $args['options']['tw_card_type'] ),
				'label'    => '',
				'options'  => array_merge(
					[ '' => \sprintf( $_default_i18n, Meta\Twitter::get_generated_card_type() ) ],
					array_combine( $tw_supported_cards, $tw_supported_cards ),
				),
				'selected' => Data\Plugin\PTA::get_meta_item( 'tw_card_type', $args['post_type'] ),
				'data'     => [
					'defaultI18n' => $_default_i18n,
				],
			] );
			// phpcs:enable WordPress.Security.EscapeOutput
			?>
		</p>

		<hr>

		<p>
			<label for="<?= \esc_attr( "sgeobiz_pta_socialimage_{$args['post_type']}" ) ?>-url">
				<strong><?php \esc_html_e( 'Social Image URL', 'sgeobiz-seo' ); ?></strong>
				<?php
				HTML::make_info(
					\__( "The social image URL can be used by search engines and social networks alike. It's best to use an image with a 1.91:1 aspect ratio that is at least 1200px wide for universal support.", 'sgeobiz-seo' ),
					'https://developers.facebook.com/docs/sharing/best-practices#images',
				);
				?>
			</label>
		</p>
		<p>
			<input class=large-text type=url name="<?php Input::field_name( $args['options']['social_image_url'] ); ?>" id="<?= \esc_attr( "sgeobiz_pta_socialimage_{$args['post_type']}" ) ?>-url" placeholder="<?= \esc_url( Meta\Image::get_first_generated_image_url( $args['generator_args'], 'social' ) ) ?>" value="<?= \esc_url( Data\Plugin\PTA::get_meta_item( 'social_image_url', $args['post_type'] ) ) ?>">
			<input type=hidden name="<?php Input::field_name( $args['options']['social_image_id'] ); ?>" id="<?= \esc_attr( "sgeobiz_pta_socialimage_{$args['post_type']}" ) ?>-id" value="<?= \absint( Data\Plugin\PTA::get_meta_item( 'social_image_id', $args['post_type'] ) ) ?>" disabled class=sgeobiz-enable-media-if-js>
		</p>
		<p class=hide-if-no-sgeobiz-js>
			<?php
			// phpcs:disable WordPress.Security.EscapeOutput -- get_image_uploader_form escapes. (phpcs breaks here, so we use disable)
			echo Form::get_image_uploader_form( [ 'id' => "sgeobiz_pta_socialimage_{$args['post_type']}" ] );
			// phpcs:enable WordPress.Security.EscapeOutput
			?>
		</p>
		<?php
		break;
	case 'visibility':
		[ , $args ] = $view_args;

		$default_canonical = Meta\URI::get_generated_url( $args['generator_args'] );
		?>
		<p>
			<label for="<?php Input::field_id( $args['options']['canonical'] ); ?>" class=sgeobiz-toblock>
				<strong><?php \esc_html_e( 'Canonical URL', 'sgeobiz-seo' ); ?></strong>
				<?php
					echo ' ';
					HTML::make_info(
						\__( 'This urges search engines to go to the outputted URL.', 'sgeobiz-seo' ),
						'https://developers.google.com/search/docs/advanced/crawling/consolidate-duplicate-urls',
					);
				?>
			</label>
		</p>
		<p>
			<input type=url name="<?php Input::field_name( $args['options']['canonical'] ); ?>" class=large-text id="<?php Input::field_id( $args['options']['canonical'] ); ?>" placeholder="<?= \esc_url( $default_canonical ) ?>" value="<?= \esc_url( Data\Plugin\PTA::get_meta_item( 'canonical', $args['post_type'] ) ) ?>" autocomplete=off>
			<?php
			Input::output_js_canonical_data(
				Input::get_field_id( $args['options']['canonical'] ),
				[
					'state' => [
						'refCanonicalLocked' => false,
						'defaultCanonical'   => \esc_url( $default_canonical ),
						'preferredScheme'    => Meta\URI\Utils::get_preferred_url_scheme(),
						'urlStructure'       => Meta\URI\Utils::get_url_permastruct( $args['generator_args'] ),
					],
				],
			);
			?>
		</p>

		<hr>
		<?php
		$robots_settings = [
			'noindex'   => [
				'force_on'    => 'index',
				'force_off'   => 'noindex',
				'label'       => \__( 'Indexing', 'sgeobiz-seo' ),
				'_defaultOn'  => 'index',
				'_defaultOff' => 'noindex',
				'_value'      => Data\Plugin\PTA::get_meta_item( 'noindex', $args['post_type'] ),
				'_info'       => [
					\__( 'This tells search engines not to show this term in their search results.', 'sgeobiz-seo' ),
					'https://developers.google.com/search/docs/advanced/crawling/block-indexing',
				],
			],
			'nofollow'  => [
				'force_on'    => 'follow',
				'force_off'   => 'nofollow',
				'label'       => \__( 'Link following', 'sgeobiz-seo' ),
				'_defaultOn'  => 'follow',
				'_defaultOff' => 'nofollow',
				'_value'      => Data\Plugin\PTA::get_meta_item( 'nofollow', $args['post_type'] ),
				'_info'       => [
					\__( 'This tells search engines not to follow links on this term.', 'sgeobiz-seo' ),
					'https://developers.google.com/search/docs/advanced/guidelines/qualify-outbound-links',
				],
			],
			'noarchive' => [
				'force_on'    => 'archive',
				'force_off'   => 'noarchive',
				'label'       => \__( 'Archiving', 'sgeobiz-seo' ),
				'_defaultOn'  => 'archive',
				'_defaultOff' => 'noarchive',
				'_value'      => Data\Plugin\PTA::get_meta_item( 'noarchive', $args['post_type'] ),
				'_info'       => [
					\__( 'This tells search engines not to save a cached copy of this term.', 'sgeobiz-seo' ),
					'https://developers.google.com/search/docs/advanced/robots/robots_meta_tag#directives',
				],
			],
		];

		/* translators: %s = default option value */
		$_default_i18n         = \__( 'Default (%s)', 'sgeobiz-seo' );
		$_default_unknown_i18n = \__( 'Default (unknown)', 'sgeobiz-seo' );

		foreach ( $robots_settings as $_r_type => $_rs ) {
			// phpcs:enable WordPress.Security.EscapeOutput
			HTML::wrap_fields(
				vsprintf(
					'<p><label for="%1$s"><strong>%2$s</strong> %3$s</label></p>',
					[
						Input::get_field_id( $args['options'][ $_r_type ] ),
						\esc_html( $_rs['label'] ),
						HTML::make_info(
							$_rs['_info'][0],
							$_rs['_info'][1] ?? '',
							false,
						),
					],
				),
				true,
			);
			// phpcs:disable WordPress.Security.EscapeOutput -- make_single_select_form() escapes.
			echo Form::make_single_select_form( [
				'id'       => Input::get_field_id( $args['options'][ $_r_type ] ),
				'class'    => 'sgeobiz-select-block',
				'name'     => Input::get_field_name( $args['options'][ $_r_type ] ),
				'label'    => '',
				'options'  => [
					0  => $_default_unknown_i18n,
					-1 => $_rs['force_on'],
					1  => $_rs['force_off'],
				],
				'selected' => $_rs['_value'],
				'data'     => [
					'defaultI18n' => $_default_i18n,
					'defaultOn'   => $_rs['_defaultOn'],
					'defaultOff'  => $_rs['_defaultOff'],
				],
			] );
		}
		?>
		<hr>

		<p>
			<label for="<?php Input::field_id( $args['options']['redirect'] ); ?>" class=sgeobiz-toblock>
				<strong><?php \esc_html_e( '301 Redirect URL', 'sgeobiz-seo' ); ?></strong>
				<?php
					echo ' ';
					HTML::make_info(
						\__( 'This will force visitors to go to another URL.', 'sgeobiz-seo' ),
						'https://developers.google.com/search/docs/crawling-indexing/301-redirects',
					);
				?>
			</label>
		</p>
		<p>
			<input type=url name="<?php Input::field_name( $args['options']['redirect'] ); ?>" class=large-text id="<?php Input::field_id( $args['options']['redirect'] ); ?>" value="<?= \esc_url( Data\Plugin\PTA::get_meta_item( 'redirect', $args['post_type'] ) ) ?>" autocomplete=off>
		</p>
		<?php
endswitch;

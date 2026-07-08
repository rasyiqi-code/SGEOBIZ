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
use SGEOBIZ_SEO\Helper\{
	Compatibility,
	Format\Markdown,
};

// phpcs:disable WordPress.WP.GlobalVariablesOverride -- This isn't the global scope.

/**
 * The SEO Framework plugin
 * Copyright (C) 2016 - 2025 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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

// See _social_metabox et al.
[ $instance ] = $view_args;

switch ( $instance ) :
	case 'main':
		$tabs = [
			'general'   => [
				'name'     => \__( 'General', 'sgeobiz-seo' ),
				'callback' => [ Admin\Settings\Plugin::class, '_social_metabox_general_tab' ],
				'dashicon' => 'admin-generic',
			],
			'facebook'  => [
				'name'     => 'Facebook',
				'callback' => [ Admin\Settings\Plugin::class, '_social_metabox_facebook_tab' ],
				'dashicon' => 'facebook-alt',
			],
			'twitter'   => [
				'name'     => 'Twitter',
				'callback' => [ Admin\Settings\Plugin::class, '_social_metabox_twitter_tab' ],
				'dashicon' => 'twitter',
			],
			'oembed'    => [
				'name'     => 'oEmbed',
				'callback' => [ Admin\Settings\Plugin::class, '_social_metabox_oembed_tab' ],
				'dashicon' => 'share-alt2',
			],
			'postdates' => [
				'name'     => \__( 'Post Dates', 'sgeobiz-seo' ),
				'callback' => [ Admin\Settings\Plugin::class, '_social_metabox_postdates_tab' ],
				'dashicon' => 'backup',
			],
		];

		Admin\Settings\Plugin::nav_tab_wrapper(
			'social',
			/**
			 * @since 2.2.2
			 * @param array $defaults The default tabs.
			 */
			(array) \apply_filters( 'sgeobiz_seo_social_settings_tabs', $tabs )
		);
		break;

	case 'general':
		HTML::header_title( \__( 'Social Meta Tags Settings', 'sgeobiz-seo' ) );
		HTML::description( \__( 'Output various meta tags for social site integration, among other third-party services.', 'sgeobiz-seo' ) );

		$active_conflicting_plugins_types = Compatibility::get_active_conflicting_plugin_types();

		$theme_color = Meta\Theme_Color::get_theme_color();
		?>
		<hr>
		<?php

		// Echo Open Graph Tags checkboxes.
		HTML::wrap_fields(
			Input::make_checkbox( [
				'id'          => 'og_tags',
				'label'       => \__( 'Output Open Graph meta tags?', 'sgeobiz-seo' ),
				'description' => \__( 'Facebook, Twitter, Pinterest and many other social sites make use of these meta tags.', 'sgeobiz-seo' ),
			] ),
			true,
		);
		if ( $active_conflicting_plugins_types['open_graph'] )
			HTML::attention_description( \__( 'Note: Another Open Graph plugin has been detected. These meta tags might conflict.', 'sgeobiz-seo' ) );

		// Echo Facebook Tags checkbox.
		HTML::wrap_fields(
			Input::make_checkbox( [
				'id'          => 'facebook_tags',
				'label'       => \__( 'Output Facebook meta tags?', 'sgeobiz-seo' ),
				'description' => \__( 'Output various meta tags targeted at Facebook.', 'sgeobiz-seo' ),
			] ),
			true,
		);

		// Echo Twitter Tags checkboxes.
		HTML::wrap_fields(
			Input::make_checkbox( [
				'id'          => 'twitter_tags',
				'label'       => \__( 'Output Twitter Card meta tags?', 'sgeobiz-seo' ),
				'description' => \__( 'X, Discord, LinkedIn, and some other networks make use of these meta tags.', 'sgeobiz-seo' ),
			] ),
			true,
		);
		if ( $active_conflicting_plugins_types['twitter_card'] )
			HTML::attention_description( \__( 'Note: Another Twitter Card plugin has been detected. These meta tags might conflict.', 'sgeobiz-seo' ) );

		// Echo oEmbed scripts checkboxes.
		HTML::wrap_fields(
			Input::make_checkbox( [
				'id'          => 'oembed_scripts',
				'label'       => \__( 'Output oEmbed scripts?', 'sgeobiz-seo' ),
				'description' => \__( 'WordPress, Discord, Drupal, Squarespace, and many other clients can make use of these scripts.', 'sgeobiz-seo' ),
			] ),
			true,
		);
		?>
		<div id=tsf-social-settings-wrapper>
			<hr>
			<?php
			HTML::header_title( \__( 'Social Title Settings', 'sgeobiz-seo' ) );
			HTML::description( \__( 'Most social sites and third-party services automatically include the website URL inside their embeds. When the site title is described well in the site URL, including it in the social title will be redundant.', 'sgeobiz-seo' ) );

			$info = HTML::make_info(
				\__( 'When you provide a custom Open Graph or Twitter title, the site title will be omitted automatically.', 'sgeobiz-seo' ),
				'',
				false,
			);

			HTML::wrap_fields(
				Input::make_checkbox( [
					'id'     => 'social_title_rem_additions',
					'label'  => \esc_html__( 'Remove site title from generated social titles?', 'sgeobiz-seo' ) . " $info",
					'escape' => false,
				] ),
				true,
			);
			?>
			<hr>
			<?php
			HTML::header_title( \__( 'Social Image Settings', 'sgeobiz-seo' ) );
			HTML::description( \__( 'A social image can be displayed when a link to your website is shared. It is a great way to grab attention.', 'sgeobiz-seo' ) );

			?>
			<div id=multi_og_image_wrapper>
				<?php
				HTML::wrap_fields(
					Input::make_checkbox( [
						'id'          => 'multi_og_image',
						'label'       => \__( 'Output multiple Open Graph image tags?', 'sgeobiz-seo' ),
						'description' => \__( 'This enables users to select any image attached to the page shared on social networks, like Facebook.', 'sgeobiz-seo' ),
					] ),
					true,
				);
				?>
			</div>
			<p>
				<label for=tsf_fb_socialimage-url>
					<strong><?php \esc_html_e( 'Social Image Fallback URL', 'sgeobiz-seo' ); ?></strong>
					<?php HTML::make_info( \__( 'When no image is available from the page or term, this fallback image will be used instead.', 'sgeobiz-seo' ), 'https://developers.facebook.com/docs/sharing/best-practices#images' ); ?>
				</label>
			</p>
			<p>
				<input class=large-text type=url name="<?php Input::field_name( 'social_image_fb_url' ); ?>" id=tsf_fb_socialimage-url value="<?= \esc_url( Data\Plugin::get_option( 'social_image_fb_url' ) ) ?>">
				<input type=hidden name="<?php Input::field_name( 'social_image_fb_id' ); ?>" id=tsf_fb_socialimage-id value="<?= \absint( Data\Plugin::get_option( 'social_image_fb_id' ) ) ?>" disabled class=tsf-enable-media-if-js>
			</p>
			<p class=hide-if-no-sgeobiz-js>
				<?php
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- already escaped.
				echo Form::get_image_uploader_form( [ 'id' => 'tsf_fb_socialimage' ] );
				?>
			</p>
		</div>
		<hr>
		<?php
		HTML::header_title( \__( 'Theme Color Settings', 'sgeobiz-seo' ) );
		HTML::description( \__( 'Discord styles embeds with the theme color. The theme color can also affect the tab-color in some browsers.', 'sgeobiz-seo' ) );
		?>
		<p>
			<label for="<?php Input::field_id( 'theme_color' ); ?>">
				<strong><?php \esc_html_e( 'Theme Color', 'sgeobiz-seo' ); ?></strong>
			</label>
		</p>
		<p>
			<input type=text name="<?php Input::field_name( 'theme_color' ); ?>" class=tsf-color-picker id="<?php Input::field_id( 'theme_color' ); ?>" value="<?= \esc_attr( $theme_color ) ?>" data-tsf-default-color="">
		</p>
		<hr>
		<?php
		HTML::header_title( \__( 'Site Shortlink Settings', 'sgeobiz-seo' ) );
		HTML::description( \__( 'The shortlink tag can be manually used for microblogging services like Twitter, but it has no SEO value whatsoever.', 'sgeobiz-seo' ) );

		HTML::wrap_fields(
			Input::make_checkbox( [
				'id'    => 'shortlink_tag',
				'label' => \__( 'Output shortlink tag?', 'sgeobiz-seo' ),
			] ),
			true,
		);
		break;

	case 'facebook':
		$fb_author             = Data\Plugin::get_option( 'facebook_author' );
		$fb_author_placeholder = \_x( 'https://www.facebook.com/YourPersonalProfile', 'Example Facebook Personal URL', 'sgeobiz-seo' );

		$fb_publisher             = Data\Plugin::get_option( 'facebook_publisher' );
		$fb_publisher_placeholder = \_x( 'https://www.facebook.com/YourBusinessProfile', 'Example Facebook Business URL', 'sgeobiz-seo' );

		HTML::header_title( \__( 'Facebook Integration Settings', 'sgeobiz-seo' ) );
		HTML::description( \__( 'Facebook post sharing works mostly through Open Graph. However, you can also link your Business and Personal Facebook pages, among various other options.', 'sgeobiz-seo' ) );
		HTML::description( \__( 'When these options are filled in, Facebook might link the Facebook profile to be followed and liked when your post or page is shared.', 'sgeobiz-seo' ) );
		?>
		<hr>

		<p>
			<label for="<?php Input::field_id( 'facebook_publisher' ); ?>">
				<strong><?php \esc_html_e( 'Facebook Publisher page', 'sgeobiz-seo' ); ?></strong>
				<?php
				echo ' ';
				HTML::make_info(
					\__( 'Only Facebook Business Pages are accepted.', 'sgeobiz-seo' ),
					'https://www.facebook.com/business/pages/set-up',
				);
				?>
			</label>
		</p>
		<p>
			<input type=url name="<?php Input::field_name( 'facebook_publisher' ); ?>" class=large-text id="<?php Input::field_id( 'facebook_publisher' ); ?>" placeholder="<?= \esc_attr( $fb_publisher_placeholder ) ?>" value="<?= \esc_attr( $fb_publisher ) ?>">
		</p>

		<p>
			<label for="<?php Input::field_id( 'facebook_author' ); ?>">
				<strong><?php \esc_html_e( 'Facebook Author Fallback Page', 'sgeobiz-seo' ); ?></strong>
				<?php
				echo ' ';
				HTML::make_info(
					\__( 'Your Facebook profile.', 'sgeobiz-seo' ),
					'https://facebook.com/me',
				);
				?>
			</label>
		</p>
		<?php HTML::description( \__( 'Authors can override this option on their profile page.', 'sgeobiz-seo' ) ); ?>
		<p>
			<input type=url name="<?php Input::field_name( 'facebook_author' ); ?>" class=large-text id="<?php Input::field_id( 'facebook_author' ); ?>" placeholder="<?= \esc_attr( $fb_author_placeholder ) ?>" value="<?= \esc_attr( $fb_author ) ?>">
		</p>
		<?php
		break;

	case 'twitter':
		$tw_site             = Data\Plugin::get_option( 'twitter_site' );
		$tw_site_placeholder = \_x( '@your-site-username', 'Twitter @username', 'sgeobiz-seo' );

		$tw_creator             = Data\Plugin::get_option( 'twitter_creator' );
		$tw_creator_placeholder = \_x( '@your-personal-username', 'Twitter @username', 'sgeobiz-seo' );

		$supported_twitter_cards = Meta\Twitter::get_supported_cards();

		HTML::header_title( \__( 'Twitter Integration Settings', 'sgeobiz-seo' ) );
		HTML::description( \__( 'Sharing posts on X works mostly via Twitter Cards and may fall back to use Open Graph. However, you can also link your Business and Personal X pages, among various other options.', 'sgeobiz-seo' ) );

		?>
		<hr>

		<fieldset id=tsf-twitter-cards>
			<legend>
				<h4>
					<?php
					\esc_html_e( 'Twitter Card Type', 'sgeobiz-seo' );
					echo ' ';
					HTML::make_info(
						\__( 'Learn more about this card.', 'sgeobiz-seo' ),
						'https://docs.sgeobiz.com/',
					);
					?>
				</h4>
			</legend>
			<?php
			HTML::description(
				\__( 'When you share a link on X, the summary card type shows a small thumbnail beside truncated title and description; the summary large image card type shows a large image with the title overlaid on it and no description.', 'sgeobiz-seo' )
			);
			HTML::description(
				\__( 'On Discord, the image appears as a small thumbnail at the side or large below the text; both card types still show the description.', 'sgeobiz-seo' )
			);
			?>

			<p class=tsf-fields>
			<?php
			foreach ( $supported_twitter_cards as $type ) {
				?>
				<span class=tsf-toblock>
					<input type=radio name="<?php Input::field_name( 'twitter_card' ); ?>" id="<?php Input::field_id( "twitter_card_{$type}" ); ?>" value="<?= \esc_attr( $type ) ?>" <?php \checked( Data\Plugin::get_option( 'twitter_card' ), $type ); ?>>
					<label for="<?php Input::field_id( "twitter_card_{$type}" ); ?>">
						<span>
							<?php
							echo HTML::code_wrap( $type ); // phpcs:ignore WordPress.Security.EscapeOutput
							?>
						</span>
					</label>
				</span>
				<?php
			}
			?>
			</p>
		</fieldset>

		<hr>
		<?php
		HTML::header_title( \__( 'Card and Content Attribution', 'sgeobiz-seo' ) );
		/* See: https://docs.sgeobiz.com/ */
		HTML::description( \__( 'X claims users will be able to follow and view the profiles of attributed accounts directly from the card when these fields are filled in.', 'sgeobiz-seo' ) );
		HTML::description( \__( 'However, for now, these fields seem to have no discernible effect.', 'sgeobiz-seo' ) );
		?>

		<p>
			<label for="<?php Input::field_id( 'twitter_site' ); ?>" class=tsf-toblock>
				<strong><?php \esc_html_e( 'Website Twitter Profile', 'sgeobiz-seo' ); ?></strong>
				<?php
				echo ' ';
				HTML::make_info(
					\__( 'Find your @username.', 'sgeobiz-seo' ),
					'https://x.com/home',
				);
				?>
			</label>
		</p>
		<p>
			<input type=text name="<?php Input::field_name( 'twitter_site' ); ?>" class="large-text ltr" id="<?php Input::field_id( 'twitter_site' ); ?>" placeholder="<?= \esc_attr( $tw_site_placeholder ) ?>" value="<?= \esc_attr( $tw_site ) ?>">
		</p>

		<p>
			<label for="<?php Input::field_id( 'twitter_creator' ); ?>" class=tsf-toblock>
				<strong><?php \esc_html_e( 'Twitter Author Fallback Profile', 'sgeobiz-seo' ); ?></strong>
				<?php
				echo ' ';
				HTML::make_info(
					\__( 'Find your @username.', 'sgeobiz-seo' ),
					'https://x.com/home',
				);
				?>
			</label>
		</p>
		<?php HTML::description( \__( 'Authors can override this option on their profile page.', 'sgeobiz-seo' ) ); ?>
		<p>
			<input type=text name="<?php Input::field_name( 'twitter_creator' ); ?>" class="large-text ltr" id="<?php Input::field_id( 'twitter_creator' ); ?>" placeholder="<?= \esc_attr( $tw_creator_placeholder ) ?>" value="<?= \esc_attr( $tw_creator ) ?>">
		</p>
		<?php
		break;

	case 'oembed':
		HTML::header_title( \__( 'oEmbed Settings', 'sgeobiz-seo' ) );
		HTML::description( \__( 'Some social sharing services and clients, like WordPress, LinkedIn, and Discord, obtain the linked page information via oEmbed.', 'sgeobiz-seo' ) );
		?>
		<hr>
		<?php

		$_info = HTML::make_info(
			/* translators: Unavailable means that either a custom Open Graph title is missing or Open Graph is disabled. */
			\__( 'This will fall back to the meta title if the Open Graph title is unavailable.', 'sgeobiz-seo' ),
			'',
			false,
		);
		// Split the wraps--the informational messages make for bad legibility otherwise.
		HTML::wrap_fields(
			Input::make_checkbox( [
				'id'          => 'oembed_use_og_title',
				'label'       => \esc_html__( 'Use Open Graph title?', 'sgeobiz-seo' ) . " $_info",
				'description' => \esc_html__( 'Check this option if you want to replace page titles with Open Graph titles in embeds.', 'sgeobiz-seo' ),
				'escape'      => false,
			] ),
			true,
		);
		$_info = HTML::make_info(
			\__( 'Only custom social images that are selected via the Media Library are considered.', 'sgeobiz-seo' ),
			'',
			false,
		);
		HTML::wrap_fields(
			Input::make_checkbox( [
				'id'          => 'oembed_use_social_image',
				'label'       => \esc_html__( 'Use social image?', 'sgeobiz-seo' ) . " $_info",
				'description' => \esc_html__( "LinkedIn displays the post's featured image in embeds. Check this option if you want to replace it with the social image.", 'sgeobiz-seo' ),
				'escape'      => false,
			] ),
			true,
		);
		HTML::wrap_fields(
			Input::make_checkbox( [
				'id'          => 'oembed_remove_author',
				'label'       => \__( 'Remove author name?', 'sgeobiz-seo' ),
				'description' => \__( "Discord shows the page author's name above the sharing embed. Check this option if you find this undesirable.", 'sgeobiz-seo' ),
			] ),
			true,
		);

		break;
	case 'postdates':
		HTML::header_title( \__( 'Post Date Settings', 'sgeobiz-seo' ) );
		HTML::description( \__( "Some social sites output the shared post's publishing and modified data in the sharing snippet.", 'sgeobiz-seo' ) );
		?>
		<hr>
		<?php

		HTML::wrap_fields(
			[
				Input::make_checkbox( [
					'id'     => 'post_publish_time',
					'label'  => Markdown::convert(
						/* translators: the backticks are Markdown! Preserve them as-is! */
						\esc_html__( 'Add `article:published_time` to posts?', 'sgeobiz-seo' ),
						[ 'code' ],
					),
					'escape' => false,
				] ),
				Input::make_checkbox( [
					'id'     => 'post_modify_time',
					'label'  => Markdown::convert(
						/* translators: the backticks are Markdown! Preserve them as-is! */
						\esc_html__( 'Add `article:modified_time` to posts?', 'sgeobiz-seo' ),
						[ 'code' ],
					),
					'escape' => false,
				] ),
			],
			true,
		);
endswitch;

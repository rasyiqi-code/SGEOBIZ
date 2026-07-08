<?php
/**
 * @package SGEOBIZ_SEO\Classes\Facade\Generate_Url
 * @subpackage SGEOBIZ_SEO\Getters\URL
 */

namespace SGEOBIZ_SEO;

\defined( 'SGEOBIZ_SEO_PRESENT' ) or die;

use SGEOBIZ_SEO\Traits\Internal\Static_Deprecator;

/**
 * The SEO Framework plugin
 * Copyright (C) 2023 - 2025 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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

/**
 * Class SGEOBIZ_SEO\Pool
 *
 * Holds a pool of proxied composite objects, so we can keep the facade sgeobiz().
 * The objects are decorated with Static Deprecator, allowing us to deprecate
 * methods and properties quickly.
 *
 * @NOTE: STATIC pools and their STATIC functions MUST BE CALLED in a NON-STATIC manner.
 *        Do NOT use   sgeobiz()::admin()::layout()::make_single_select_form();
 *        Instead, use sgeobiz()->admin()->layout()->make_single_select_form();
 *        Failing to do so might result in a crash when we need to deprecate a call,
 *        defeating the purpose of the static deprecator.
 * @NOTE: All static:: calls within this class are intentional, to allow overrides in deprecators.
 *
 * @todo: If the subobjects require complex fallbacks, put them in a new \Internal
 *        subobject. Create private class constant to hold that class location.
 *
 * @since 5.0.0
 * @link https://en.wikipedia.org/wiki/Object_pool_pattern
 * You can access these methods via `sgeobiz()` and `sgeobiz_seo()`.
 */
class Pool extends Legacy_API {

	/**
	 * @since 5.0.0
	 * @var class[] The class store. Used in favor of memo() for a chain would become expensive.
	 */
	private static $pool = [];

	// phpcs:disable Squiz.Commenting.VariableComment.Missing -- see trait Static_Deprecator.

	/**
	 * Returns a pool of Admin classes as instantiated object with deprecation capabilities.
	 * This allows for easy API access, and it allows us to silence fatal errors.
	 *
	 * @since 5.0.0
	 * @api Not used internally.
	 *
	 * @return \Closure An anonymous class with subpools.
	 */
	public static function admin() {
		return static::$pool[ __FUNCTION__ ] ??= new class {
			use Static_Deprecator;

			private $colloquial_handle     = 'sgeobiz()->admin()';
			private $deprecated_methods    = [];
			private $deprecated_properties = [];

			/**
			 * @since 5.0.0
			 * @return \Closure An anonymous class with subpools.
			 */
			public static function layout() {
				return static::$subpool[ __FUNCTION__ ] ??= new class {
					use Static_Deprecator;

					private $colloquial_handle     = 'sgeobiz()->admin()->layout()';
					private $deprecated_methods    = [];
					private $deprecated_properties = [];

					/**
					 * @since 5.0.0
					 * @return \SGEOBIZ_SEO\Admin\Settings\Layout\HTML
					 */
					public static function form() {
						return static::$subpool[ __FUNCTION__ ] ??= new class extends Admin\Settings\Layout\Form {
							use Static_Deprecator;

							private $colloquial_handle     = 'sgeobiz()->admin()->layout()->form()';
							private $deprecated_methods    = [];
							private $deprecated_properties = [];
						};
					}

					/**
					 * @since 5.0.0
					 * @return \SGEOBIZ_SEO\Admin\Settings\Layout\HTML
					 */
					public static function html() {
						return static::$subpool[ __FUNCTION__ ] ??= new class extends Admin\Settings\Layout\HTML {
							use Static_Deprecator;

							private $colloquial_handle     = 'sgeobiz()->admin()->layout()->html()';
							private $deprecated_methods    = [];
							private $deprecated_properties = [];
						};
					}
				};
			}

			/**
			 * @since 5.0.0
			 * @return \SGEOBIZ_SEO\Admin\Menu
			 */
			public static function menu() {
				return static::$subpool[ __FUNCTION__ ] ??= new class extends Admin\Menu {
					use Static_Deprecator;

					private $colloquial_handle     = 'sgeobiz()->admin()->menu()';
					private $deprecated_methods    = [];
					private $deprecated_properties = [];
				};
			}

			/**
			 * @since 5.0.0
			 * @return \SGEOBIZ_SEO\Admin\Notice
			 */
			public static function notice() {
				return static::$subpool[ __FUNCTION__ ] ??= new class extends Admin\Notice {
					use Static_Deprecator;

					private $colloquial_handle     = 'sgeobiz()->admin()->notice()';
					private $deprecated_methods    = [];
					private $deprecated_properties = [];

					/**
					 * @since 5.0.0
					 * @return \SGEOBIZ_SEO\Admin\Notice\Persistent
					 */
					public static function persistent() {
						return static::$subpool[ __FUNCTION__ ] ??= new class extends Admin\Notice\Persistent {
							use Static_Deprecator;

							private $colloquial_handle     = 'sgeobiz()->admin()->notice()->persistent()';
							private $deprecated_methods    = [];
							private $deprecated_properties = [];
						};
					}
				};
			}

			/**
			 * @since 5.0.0
			 * @return \SGEOBIZ_SEO\Admin\Utils
			 */
			public static function utils() {
				return static::$subpool[ __FUNCTION__ ] ??= new class extends Admin\Utils {
					use Static_Deprecator;

					private $colloquial_handle     = 'sgeobiz()->admin()->utils()';
					private $deprecated_methods    = [];
					private $deprecated_properties = [];
				};
			}

			/**
			 * @since 5.1.5
			 * @return \SGEOBIZ_SEO\Admin\SEOBar\Builder
			 */
			public static function seobar() {
				return static::$subpool[ __FUNCTION__ ] ??= new class extends Admin\SEOBar\Builder {
					use Static_Deprecator;

					private $colloquial_handle     = 'sgeobiz()->admin()->seobar()';
					private $deprecated_methods    = [];
					private $deprecated_properties = [];
				};
			}

			/**
			 * @since 5.1.5
			 * @return \Closure An anonymous class with subpools.
			 */
			public static function scripts() {
				return static::$subpool[ __FUNCTION__ ] ??= new class {

					use Static_Deprecator;

					private $colloquial_handle     = 'sgeobiz()->admin()->scripts()';
					private $deprecated_methods    = [];
					private $deprecated_properties = [];

					private static $subpool = [];

					/**
					 * @since 5.1.5
					 * @return \SGEOBIZ_SEO\Admin\Script\Loader
					 */
					public static function loader() {
						return static::$subpool[ __FUNCTION__ ] ??= new class extends Admin\Script\Loader {
							use Static_Deprecator;

							private $colloquial_handle     = 'sgeobiz()->admin()->scripts()->loader()';
							private $deprecated_methods    = [];
							private $deprecated_properties = [];
						};
					}
				};
			}
		};
	}

	/**
	 * Returns the Breadcrumbs API class as instantiated object with deprecation capabilities.
	 * This allows for easy API access, and it allows us to silence fatal errors.
	 *
	 * @since 5.0.0
	 * @api Not used internally.
	 *
	 * @return \SGEOBIZ_SEO\Meta\Breadcrumbs
	 */
	public static function breadcrumbs() {
		return static::$pool[ __FUNCTION__ ] ??= new class extends Meta\Breadcrumbs {
			use Static_Deprecator;

			private $colloquial_handle     = 'sgeobiz()->breadcrumbs()';
			private $deprecated_methods    = [];
			private $deprecated_properties = [];
		};
	}

	/**
	 * Returns a pool of Data classes as instantiated object with deprecation capabilities.
	 * This allows for easy API access, and it allows us to silence fatal errors.
	 *
	 * @since 5.0.0
	 * @api Not used internally.
	 *
	 * @return \Closure An anonymous class with subpools.
	 */
	public static function data() {
		return static::$pool[ __FUNCTION__ ] ??= new class {
			use Static_Deprecator;

			private $colloquial_handle     = 'sgeobiz()->data()';
			private $deprecated_methods    = [];
			private $deprecated_properties = [];

			/**
			 * @since 5.0.0
			 * @return \SGEOBIZ_SEO\Data\Blog
			 */
			public static function blog() {
				return static::$subpool[ __FUNCTION__ ] ??= new class extends Data\Blog {
					use Static_Deprecator;

					private $colloquial_handle     = 'sgeobiz()->data()->blog()';
					private $deprecated_methods    = [];
					private $deprecated_properties = [];
				};
			}

			/**
			 * @since 5.0.0
			 * @return \SGEOBIZ_SEO\Data\Plugin
			 */
			public static function plugin() {
				return static::$subpool[ __FUNCTION__ ] ??= new class extends Data\Plugin {
					use Static_Deprecator;

					private $colloquial_handle     = 'sgeobiz()->data()->plugin()';
					private $deprecated_methods    = [];
					private $deprecated_properties = [];

					/**
					 * @since 5.0.0
					 * @return \SGEOBIZ_SEO\Data\Plugin\Filter
					 */
					public static function filter() {
						return static::$subpool[ __FUNCTION__ ] ??= new class extends Data\Plugin\Filter {
							use Static_Deprecator;

							private $colloquial_handle     = 'sgeobiz()->data()->plugin()->filter()';
							private $deprecated_methods    = [];
							private $deprecated_properties = [];
						};
					}
					/**
					 * @since 5.0.0
					 * @return \SGEOBIZ_SEO\Data\Plugin\Helper
					 */
					public static function helper() {
						return static::$subpool[ __FUNCTION__ ] ??= new class extends Data\Plugin\Helper {
							use Static_Deprecator;

							private $colloquial_handle     = 'sgeobiz()->data()->plugin()->helper()';
							private $deprecated_methods    = [];
							private $deprecated_properties = [];
						};
					}

					/**
					 * @since 5.0.0
					 * @return \SGEOBIZ_SEO\Data\Plugin\Post
					 */
					public static function post() {
						return static::$subpool[ __FUNCTION__ ] ??= new class extends Data\Plugin\Post {
							use Static_Deprecator;

							private $colloquial_handle     = 'sgeobiz()->data()->plugin()->post()';
							private $deprecated_methods    = [];
							private $deprecated_properties = [];
						};
					}

					/**
					 * @since 5.0.0
					 * @return \SGEOBIZ_SEO\Data\Plugin\PTA
					 */
					public static function pta() {
						return static::$subpool[ __FUNCTION__ ] ??= new class extends Data\Plugin\PTA {
							use Static_Deprecator;

							private $colloquial_handle     = 'sgeobiz()->data()->plugin()->pta()';
							private $deprecated_methods    = [];
							private $deprecated_properties = [];
						};
					}

					/**
					 * @since 5.0.0
					 * @return \SGEOBIZ_SEO\Data\Plugin\Setup
					 */
					public static function setup() {
						return static::$subpool[ __FUNCTION__ ] ??= new class extends Data\Plugin\Setup {
							use Static_Deprecator;

							private $colloquial_handle     = 'sgeobiz()->data()->plugin()->setup()';
							private $deprecated_methods    = [];
							private $deprecated_properties = [];
						};
					}

					/**
					 * @since 5.0.0
					 * @return \SGEOBIZ_SEO\Data\Plugin\Term
					 */
					public static function term() {
						return static::$subpool[ __FUNCTION__ ] ??= new class extends Data\Plugin\Term {
							use Static_Deprecator;

							private $colloquial_handle     = 'sgeobiz()->data()->plugin()->term()';
							private $deprecated_methods    = [];
							private $deprecated_properties = [];
						};
					}

					/**
					 * @since 5.0.0
					 * @return \SGEOBIZ_SEO\Data\Plugin\User
					 */
					public static function user() {
						return static::$subpool[ __FUNCTION__ ] ??= new class extends Data\Plugin\User {
							use Static_Deprecator;

							private $colloquial_handle     = 'sgeobiz()->data()->plugin()->user()';
							private $deprecated_methods    = [];
							private $deprecated_properties = [];
						};
					}
				};
			}

			/**
			 * @since 5.0.0
			 * @return \SGEOBIZ_SEO\Data\Post
			 */
			public static function post() {
				return static::$subpool[ __FUNCTION__ ] ??= new class extends Data\Post {
					use Static_Deprecator;

					private $colloquial_handle     = 'sgeobiz()->data()->post()';
					private $deprecated_methods    = [];
					private $deprecated_properties = [];
				};
			}

			/**
			 * @since 5.0.0
			 * @return \SGEOBIZ_SEO\Data\Term
			 */
			public static function term() {
				return static::$subpool[ __FUNCTION__ ] ??= new class extends Data\Term {
					use Static_Deprecator;

					private $colloquial_handle     = 'sgeobiz()->data()->term()';
					private $deprecated_methods    = [];
					private $deprecated_properties = [];
				};
			}

			/**
			 * @since 5.0.0
			 * @return \SGEOBIZ_SEO\Data\User
			 */
			public static function user() {
				return static::$subpool[ __FUNCTION__ ] ??= new class extends Data\User {
					use Static_Deprecator;

					private $colloquial_handle     = 'sgeobiz()->data()->user()';
					private $deprecated_methods    = [];
					private $deprecated_properties = [];
				};
			}
		};
	}

	/**
	 * Returns the Description API class as instantiated object with deprecation capabilities.
	 * This allows for easy API access, and it allows us to silence fatal errors.
	 *
	 * @since 5.0.0
	 * @api Not used internally.
	 *
	 * @return \SGEOBIZ_SEO\Meta\Description
	 */
	public static function description() {
		return static::$pool[ __FUNCTION__ ] ??= new class extends Meta\Description {
			use Static_Deprecator;

			private $colloquial_handle     = 'sgeobiz()->description()';
			private $deprecated_methods    = [];
			private $deprecated_properties = [];

			/**
			 * @since 5.0.0
			 * @return \SGEOBIZ_SEO\Meta\Description\Excerpt
			 */
			public static function excerpt() {
				return static::$subpool[ __FUNCTION__ ] ??= new class extends Meta\Description\Excerpt {
					use Static_Deprecator;

					private $colloquial_handle     = 'sgeobiz()->description()->excerpt()';
					private $deprecated_methods    = [];
					private $deprecated_properties = [];
				};
			}
		};
	}

	/**
	 * Returns the Escape API class as instantiated object with deprecation capabilities.
	 * This allows for easy API access, and it allows us to silence fatal errors.
	 *
	 * @since 5.0.0
	 * @api Not used internally.
	 *
	 * @return \SGEOBIZ_SEO\Data\Filter\Escape
	 */
	public static function escape() {
		return static::$pool[ __FUNCTION__ ] ??= new class extends Data\Filter\Escape {
			use Static_Deprecator;

			private $colloquial_handle     = 'sgeobiz()->escape()';
			private $deprecated_methods    = [];
			private $deprecated_properties = [];
		};
	}

	/**
	 * Returns the Facebook API class as instantiated object with deprecation capabilities.
	 * This allows for easy API access, and it allows us to silence fatal errors.
	 *
	 * @since 5.0.0
	 * @api Not used internally.
	 *
	 * @return \SGEOBIZ_SEO\Meta\Facebook
	 */
	public static function facebook() {
		return static::$pool[ __FUNCTION__ ] ??= new class extends Meta\Facebook {
			use Static_Deprecator;

			private $colloquial_handle     = 'sgeobiz()->facebook()';
			private $deprecated_methods    = [];
			private $deprecated_properties = [];
		};
	}

	/**
	 * Returns a pool of Format classes as instantiated object with deprecation capabilities.
	 * This allows for easy API access, and it allows us to silence fatal errors.
	 *
	 * @since 5.0.0
	 * @api Not used internally.
	 *
	 * @return \Closure An anonymous class with subpools.
	 */
	public static function format() {
		return static::$pool[ __FUNCTION__ ] ??= new class {
			use Static_Deprecator;

			private $colloquial_handle     = 'sgeobiz()->format()';
			private $deprecated_methods    = [];
			private $deprecated_properties = [];

			/**
			 * @since 5.0.0
			 * @return \SGEOBIZ_SEO\Helper\Format\Arrays
			 */
			public static function arrays() {
				return static::$subpool[ __FUNCTION__ ] ??= new class extends Helper\Format\Arrays {
					use Static_Deprecator;

					private $colloquial_handle     = 'sgeobiz()->format()->arrays()';
					private $deprecated_methods    = [];
					private $deprecated_properties = [];
				};
			}

			/**
			 * @since 5.0.0
			 * @return \SGEOBIZ_SEO\Helper\Format\Color
			 */
			public static function color() {
				return static::$subpool[ __FUNCTION__ ] ??= new class extends Helper\Format\Color {
					use Static_Deprecator;

					private $colloquial_handle     = 'sgeobiz()->format()->color()';
					private $deprecated_methods    = [];
					private $deprecated_properties = [];
				};
			}

			/**
			 * @since 5.0.5
			 * @return \SGEOBIZ_SEO\Helper\Format\Minify
			 */
			public static function minify() {
				return static::$subpool[ __FUNCTION__ ] ??= new class extends Helper\Format\Minify {
					use Static_Deprecator;

					private $colloquial_handle     = 'sgeobiz()->format()->minify()';
					private $deprecated_methods    = [];
					private $deprecated_properties = [];
				};
			}

			/**
			 * @since 5.0.0
			 * @return \SGEOBIZ_SEO\Helper\Format\HTML
			 */
			public static function html() {
				return static::$subpool[ __FUNCTION__ ] ??= new class extends Helper\Format\HTML {
					use Static_Deprecator;

					private $colloquial_handle     = 'sgeobiz()->format()->html()';
					private $deprecated_methods    = [];
					private $deprecated_properties = [];
				};
			}

			/**
			 * @since 5.0.0
			 * @return \SGEOBIZ_SEO\Helper\Format\Markdown
			 */
			public static function markdown() {
				return static::$subpool[ __FUNCTION__ ] ??= new class extends Helper\Format\Markdown {
					use Static_Deprecator;

					private $colloquial_handle     = 'sgeobiz()->format()->markdown()';
					private $deprecated_methods    = [];
					private $deprecated_properties = [];
				};
			}

			/**
			 * @since 5.0.0
			 * @return \SGEOBIZ_SEO\Helper\Format\Strings
			 */
			public static function strings() {
				return static::$subpool[ __FUNCTION__ ] ??= new class extends Helper\Format\Strings {
					use Static_Deprecator;

					private $colloquial_handle     = 'sgeobiz()->format()->strings()';
					private $deprecated_methods    = [];
					private $deprecated_properties = [];
				};
			}

			/**
			 * @since 5.0.0
			 * @return \SGEOBIZ_SEO\Helper\Format\Time
			 */
			public static function time() {
				return static::$subpool[ __FUNCTION__ ] ??= new class extends Helper\Format\Time {
					use Static_Deprecator;

					private $colloquial_handle     = 'sgeobiz()->format()->time()';
					private $deprecated_methods    = [];
					private $deprecated_properties = [];
				};
			}
		};
	}

	/**
	 * Returns the Guidelines API class as instantiated object with deprecation capabilities.
	 * This allows for easy API access, and it allows us to silence fatal errors.
	 *
	 * @since 5.0.0
	 * @api Not used internally.
	 *
	 * @return \SGEOBIZ_SEO\Helper\Guidelines
	 */
	public static function guidelines() {
		return static::$pool[ __FUNCTION__ ] ??= new class extends Helper\Guidelines {
			use Static_Deprecator;

			private $colloquial_handle     = 'sgeobiz()->guidelines()';
			private $deprecated_methods    = [];
			private $deprecated_properties = [];
		};
	}

	/**
	 * Returns the HTTP Headers API class as instantiated object with deprecation capabilities.
	 * This allows for easy API access, and it allows us to silence fatal errors.
	 *
	 * @since 5.0.0
	 * @api Not used internally.
	 *
	 * @return \SGEOBIZ_SEO\Helper\Headers
	 */
	public static function headers() {
		return static::$pool[ __FUNCTION__ ] ??= new class extends Helper\Headers {
			use Static_Deprecator;

			private $colloquial_handle     = 'sgeobiz()->headers()';
			private $deprecated_methods    = [];
			private $deprecated_properties = [];
		};
	}

	/**
	 * Returns the Image API class as instantiated object with deprecation capabilities.
	 * This allows for easy API access, and it allows us to silence fatal errors.
	 *
	 * @since 5.0.0
	 * @api Not used internally.
	 *
	 * @return \SGEOBIZ_SEO\Meta\Image
	 */
	public static function image() {
		return static::$pool[ __FUNCTION__ ] ??= new class extends Meta\Image {
			use Static_Deprecator;

			private $colloquial_handle     = 'sgeobiz()->image()';
			private $deprecated_methods    = [];
			private $deprecated_properties = [];

			/**
			 * @since 5.0.0
			 * @return \SGEOBIZ_SEO\Meta\Image\Utils
			 */
			public static function utils() {
				return static::$subpool[ __FUNCTION__ ] ??= new class extends Meta\Image\Utils {
					use Static_Deprecator;

					private $colloquial_handle     = 'sgeobiz()->image()->utils()';
					private $deprecated_methods    = [];
					private $deprecated_properties = [];
				};
			}
		};
	}

	/**
	 * Returns the Open_Graph API class as instantiated object with deprecation capabilities.
	 * This allows for easy API access, and it allows us to silence fatal errors.
	 *
	 * @since 5.0.0
	 * @api Not used internally.
	 *
	 * @return \SGEOBIZ_SEO\Meta\Open_Graph
	 */
	public static function open_graph() {
		return static::$pool[ __FUNCTION__ ] ??= new class extends Meta\Open_Graph {
			use Static_Deprecator;

			private $colloquial_handle     = 'sgeobiz()->open_graph()';
			private $deprecated_methods    = [];
			private $deprecated_properties = [];
		};
	}

	/**
	 * Returns the Post_Type class as instantiated object with deprecation capabilities.
	 * This allows for easy API access, and it allows us to silence fatal errors.
	 *
	 * @since 5.0.0
	 * @api Not used internally.
	 *
	 * @return \SGEOBIZ_SEO\Helper\Post_Type
	 */
	public static function post_type() {
		return static::$pool[ __FUNCTION__ ] ??= new class extends Helper\Post_Type {
			use Static_Deprecator;

			private $colloquial_handle     = 'sgeobiz()->post_type()';
			private $deprecated_methods    = [];
			private $deprecated_properties = [];
		};
	}

	/**
	 * Returns the Query class as instantiated object with deprecation capabilities.
	 * This allows for easy API access, and it allows us to silence fatal errors.
	 *
	 * @since 5.0.0
	 * @api Not used internally.
	 *
	 * @return \SGEOBIZ_SEO\Helper\Query
	 */
	public static function query() {
		return static::$pool[ __FUNCTION__ ] ??= new class extends Helper\Query {
			use Static_Deprecator;

			private $colloquial_handle     = 'sgeobiz()->query()';
			private $deprecated_methods    = [];
			private $deprecated_properties = [];

			/**
			 * @since 5.0.0
			 * @return \SGEOBIZ_SEO\Helper\Query\Cache
			 */
			public static function cache() {
				return static::$subpool[ __FUNCTION__ ] ??= new class extends Helper\Query\Cache {
					use Static_Deprecator;

					private $colloquial_handle     = 'sgeobiz()->query()->cache()';
					private $deprecated_methods    = [];
					private $deprecated_properties = [];
				};
			}

			/**
			 * @since 5.0.0
			 * @return \SGEOBIZ_SEO\Helper\Query\Exclusion
			 */
			public static function exclusion() {
				return static::$subpool[ __FUNCTION__ ] ??= new class extends Helper\Query\Exclusion {
					use Static_Deprecator;

					private $colloquial_handle     = 'sgeobiz()->query()->exclusion()';
					private $deprecated_methods    = [];
					private $deprecated_properties = [];
				};
			}

			/**
			 * @since 5.0.0
			 * @return \SGEOBIZ_SEO\Helper\Query\Utils
			 */
			public static function utils() {
				return static::$subpool[ __FUNCTION__ ] ??= new class extends Helper\Query\Utils {
					use Static_Deprecator;

					private $colloquial_handle     = 'sgeobiz()->query()->utils()';
					private $deprecated_methods    = [];
					private $deprecated_properties = [];
				};
			}
		};
	}

	/**
	 * Returns the Robots API class as instantiated object with deprecation capabilities.
	 * This allows for easy API access, and it allows us to silence fatal errors.
	 *
	 * @since 5.0.0
	 * @api Not used internally.
	 *
	 * @return \SGEOBIZ_SEO\Meta\Robots
	 */
	public static function robots() {
		return static::$pool[ __FUNCTION__ ] ??= new class extends Meta\Robots {
			use Static_Deprecator;

			private $colloquial_handle     = 'sgeobiz()->robots()';
			private $deprecated_methods    = [];
			private $deprecated_properties = [];
		};
	}

	/**
	 * Returns the Robots TXT API class as instantiated object with deprecation capabilities.
	 * This allows for easy API access, and it allows us to silence fatal errors.
	 *
	 * @since 5.0.0
	 * @api Not used internally.
	 *
	 * @return \SGEOBIZ_SEO\RobotsTXT\Main
	 */
	public static function robotstxt() {
		return static::$pool[ __FUNCTION__ ] ??= new class extends RobotsTXT\Main {
			use Static_Deprecator;

			private $colloquial_handle     = 'sgeobiz()->robotstxt()';
			private $deprecated_methods    = [];
			private $deprecated_properties = [];

			/**
			 * @since 5.0.0
			 * @return \SGEOBIZ_SEO\RobotsTXT\Utils
			 */
			public static function utils() {
				return static::$subpool[ __FUNCTION__ ] ??= new class extends RobotsTXT\Utils {
					use Static_Deprecator;

					private $colloquial_handle     = 'sgeobiz()->robotstxt()->utils()';
					private $deprecated_methods    = [];
					private $deprecated_properties = [];
				};
			}
		};
	}

	/**
	 * Returns the Sanitize API class as instantiated object with deprecation capabilities.
	 * This allows for easy API access, and it allows us to silence fatal errors.
	 *
	 * @since 5.0.0
	 * @api Not used internally.
	 *
	 * @return \SGEOBIZ_SEO\Data\Filter\Sanitize
	 */
	public static function sanitize() {
		return static::$pool[ __FUNCTION__ ] ??= new class extends Data\Filter\Sanitize {
			use Static_Deprecator;

			private $colloquial_handle     = 'sgeobiz()->sanitize()';
			private $deprecated_methods    = [];
			private $deprecated_properties = [];
		};
	}

	/**
	 * Returns the Structured_Data API class as instantiated object with deprecation capabilities.
	 * This allows for easy API access, and it allows us to silence fatal errors.
	 *
	 * @since 5.0.0
	 * @api Not used internally.
	 *
	 * @return \SGEOBIZ_SEO\Meta\Schema
	 */
	public static function schema() {
		return static::$pool[ __FUNCTION__ ] ??= new class extends Meta\Schema {
			use Static_Deprecator;

			private $colloquial_handle     = 'sgeobiz()->schema()';
			private $deprecated_methods    = [];
			private $deprecated_properties = [];

			/**
			 * @since 5.0.0
			 * @since 5.1.0 Now actually lists the existing class names.
			 * @readonly
			 * @var array[string,string] A list of accessible entity class names.
			 */
			public $entities = [
				'Author'         => Meta\Schema\Entities\Author::class,
				'BreadcrumbList' => Meta\Schema\Entities\BreadcrumbList::class,
				'Organization'   => Meta\Schema\Entities\Organization::class,
				'Person'         => Meta\Schema\Entities\Person::class,
				'Reference'      => Meta\Schema\Entities\Reference::class,
				'WebPage'        => Meta\Schema\Entities\WebPage::class,
				'WebSite'        => Meta\Schema\Entities\WebSite::class,
			];
		};
	}

	/**
	 * Returns a pool of Sitemap classes as instantiated object with deprecation capabilities.
	 * This allows for easy API access, and it allows us to silence fatal errors.
	 *
	 * @since 5.0.0
	 * @api Not used internally.
	 *
	 * @return \Closure An anonymous class with subpools.
	 */
	public static function sitemap() {
		return static::$pool[ __FUNCTION__ ] ??= new class {
			use Static_Deprecator;

			private $colloquial_handle     = 'sgeobiz()->sitemap()';
			private $deprecated_methods    = [
				'ping' => [
					'since'    => '5.0.5',
					'fallback' => '\SGEOBIZ_SEO\Internal\Silencer::instance',
				],
			];
			private $deprecated_properties = [];

			/**
			 * @since 5.0.0
			 * @return \SGEOBIZ_SEO\Sitemap\Cache
			 */
			public static function cache() {
				return static::$subpool[ __FUNCTION__ ] ??= new class extends Sitemap\Cache {
					use Static_Deprecator;

					private $colloquial_handle     = 'sgeobiz()->sitemap()->cache()';
					private $deprecated_methods    = [];
					private $deprecated_properties = [];
				};
			}

			/**
			 * @since 5.0.5
			 * @return \SGEOBIZ_SEO\Sitemap\Cron
			 */
			public static function cron() {
				return static::$subpool[ __FUNCTION__ ] ??= new class extends Sitemap\Cron {
					use Static_Deprecator;

					private $colloquial_handle     = 'sgeobiz()->sitemap()->cron()';
					private $deprecated_methods    = [];
					private $deprecated_properties = [];
				};
			}

			/**
			 * @since 5.0.0
			 * @return \SGEOBIZ_SEO\Sitemap\Lock
			 */
			public static function lock() {
				return static::$subpool[ __FUNCTION__ ] ??= new class extends Sitemap\Lock {
					use Static_Deprecator;

					private $colloquial_handle     = 'sgeobiz()->sitemap()->lock()';
					private $deprecated_methods    = [];
					private $deprecated_properties = [];
				};
			}

			/**
			 * @since 5.0.0
			 * @return \SGEOBIZ_SEO\Sitemap\Registry
			 */
			public static function registry() {
				return static::$subpool[ __FUNCTION__ ] ??= new class extends Sitemap\Registry {
					use Static_Deprecator;

					private $colloquial_handle     = 'sgeobiz()->sitemap()->registry()';
					private $deprecated_methods    = [];
					private $deprecated_properties = [];
				};
			}

			/**
			 * @since 5.0.0
			 * @return \SGEOBIZ_SEO\Sitemap\Utils
			 */
			public static function utils() {
				return static::$subpool[ __FUNCTION__ ] ??= new class extends Sitemap\Utils {
					use Static_Deprecator;

					private $colloquial_handle     = 'sgeobiz()->sitemap()->utils()';
					private $deprecated_methods    = [];
					private $deprecated_properties = [];
				};
			}
		};
	}

	/**
	 * Returns the Taxonomy class as instantiated object with deprecation capabilities.
	 * This allows for easy API access, and it allows us to silence fatal errors.
	 *
	 * @since 5.0.0
	 * @api Not used internally.
	 *
	 * @return \SGEOBIZ_SEO\Helper\Taxonomy
	 */
	public static function taxonomy() {
		return static::$pool[ __FUNCTION__ ] ??= new class extends Helper\Taxonomy {
			use Static_Deprecator;

			private $colloquial_handle     = 'sgeobiz()->taxonomy()';
			private $deprecated_methods    = [];
			private $deprecated_properties = [];
		};
	}

	/**
	 * Returns the Theme Color API class as instantiated object with deprecation capabilities.
	 * This allows for easy API access, and it allows us to silence fatal errors.
	 *
	 * @since 5.0.1
	 * @api Not used internally.
	 *
	 * @return \SGEOBIZ_SEO\Meta\Theme_Color
	 */
	public static function theme_color() {
		return static::$pool[ __FUNCTION__ ] ??= new class extends Meta\Theme_Color {
			use Static_Deprecator;

			private $colloquial_handle     = 'sgeobiz()->theme_color()';
			private $deprecated_methods    = [];
			private $deprecated_properties = [];
		};
	}

	/**
	 * Returns the Title API class as instantiated object with deprecation capabilities.
	 * This allows for easy API access, and it allows us to silence fatal errors.
	 *
	 * @since 5.0.0
	 * @api Not used internally.
	 *
	 * @return \SGEOBIZ_SEO\Meta\Title
	 */
	public static function title() {
		return static::$pool[ __FUNCTION__ ] ??= new class extends Meta\Title {
			use Static_Deprecator;

			private $colloquial_handle     = 'sgeobiz()->title()';
			private $deprecated_methods    = [];
			private $deprecated_properties = [];

			/**
			 * @since 5.0.0
			 * @return \SGEOBIZ_SEO\Meta\Title\Conditions
			 */
			public static function conditions() {
				return static::$subpool[ __FUNCTION__ ] ??= new class extends Meta\Title\Conditions {
					use Static_Deprecator;

					private $colloquial_handle     = 'sgeobiz()->title()->conditions()';
					private $deprecated_methods    = [];
					private $deprecated_properties = [];
				};
			}

			/**
			 * @since 5.0.0
			 * @return \SGEOBIZ_SEO\Meta\Title\Utils
			 */
			public static function utils() {
				return static::$subpool[ __FUNCTION__ ] ??= new class extends Meta\Title\Utils {
					use Static_Deprecator;

					private $colloquial_handle     = 'sgeobiz()->title()->utils()';
					private $deprecated_methods    = [];
					private $deprecated_properties = [];
				};
			}
		};
	}

	/**
	 * Returns the Twitter API class as instantiated object with deprecation capabilities.
	 * This allows for easy API access, and it allows us to silence fatal errors.
	 *
	 * @since 5.0.0
	 * @api Not used internally.
	 *
	 * @return \SGEOBIZ_SEO\Meta\Twitter
	 */
	public static function twitter() {
		return static::$pool[ __FUNCTION__ ] ??= new class extends Meta\Twitter {
			use Static_Deprecator;

			private $colloquial_handle     = 'sgeobiz()->twitter()';
			private $deprecated_methods    = [];
			private $deprecated_properties = [];
		};
	}

	/**
	 * Returns the URI API class as instantiated object with deprecation capabilities.
	 * This allows for easy API access, and it allows us to silence fatal errors.
	 *
	 * @since 5.0.0
	 * @api Not used internally.
	 *
	 * @return \SGEOBIZ_SEO\Meta\URI
	 */
	public static function uri() {
		return static::$pool[ __FUNCTION__ ] ??= new class extends Meta\URI {
			use Static_Deprecator;

			private $colloquial_handle     = 'sgeobiz()->uri()';
			private $deprecated_methods    = [];
			private $deprecated_properties = [];

			/**
			 * @since 5.0.0
			 * @return \SGEOBIZ_SEO\Meta\URI\Utils
			 */
			public static function utils() {
				return static::$subpool[ __FUNCTION__ ] ??= new class extends Meta\URI\Utils {
					use Static_Deprecator;

					private $colloquial_handle     = 'sgeobiz()->uri()->utils()';
					private $deprecated_methods    = [];
					private $deprecated_properties = [];
				};
			}
		};
	}
}

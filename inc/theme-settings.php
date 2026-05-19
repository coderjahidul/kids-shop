<?php
/**
 * Theme Settings admin page (Appearance → Theme Settings).
 *
 * @package Kids_Shop
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register admin menu.
 */
function kids_shop_theme_settings_menu() {
	add_theme_page(
		__( 'Kids Shop Theme Settings', 'kids-shop' ),
		__( 'Theme Settings', 'kids-shop' ),
		'manage_options',
		'kids-shop-theme-settings',
		'kids_shop_render_theme_settings_page'
	);
}
add_action( 'admin_menu', 'kids_shop_theme_settings_menu' );

/**
 * Register settings.
 */
function kids_shop_register_theme_settings() {
	register_setting(
		'kids_shop_theme_settings_group',
		KIDS_SHOP_OPTIONS_KEY,
		array(
			'type'              => 'array',
			'sanitize_callback' => 'kids_shop_sanitize_theme_options',
			'default'           => kids_shop_get_default_options(),
		)
	);
}
add_action( 'admin_init', 'kids_shop_register_theme_settings' );

/**
 * Sanitize saved options.
 *
 * @param array $input Raw POST data.
 * @return array
 */
function kids_shop_sanitize_theme_options( $input ) {
	if ( ! is_array( $input ) ) {
		return kids_shop_get_default_options();
	}

	$defaults = kids_shop_get_default_options();
	$output   = array();

	$text_fields = array(
		'footer_description',
		'contact_email',
		'contact_phone',
		'contact_address',
		'hero_slide_1_alt',
		'hero_slide_2_alt',
		'hero_slide_3_alt',
		'hero_slide_4_alt',
	);

	foreach ( $text_fields as $field ) {
		$output[ $field ] = isset( $input[ $field ] ) ? sanitize_text_field( $input[ $field ] ) : $defaults[ $field ];
	}

	$url_fields = array(
		'hero_slide_1_link',
		'hero_slide_2_link',
		'hero_slide_3_link',
		'hero_slide_4_link',
		'social_facebook',
		'social_instagram',
		'social_youtube',
	);

	foreach ( $url_fields as $field ) {
		if ( ! empty( $input[ $field ] ) ) {
			$output[ $field ] = esc_url_raw( $input[ $field ] );
		} else {
			$output[ $field ] = '';
		}
	}

	$output['contact_email'] = sanitize_email( $output['contact_email'] );

	$color_fields = array( 'color_primary', 'color_secondary', 'color_tertiary' );
	foreach ( $color_fields as $field ) {
		$color = isset( $input[ $field ] ) ? sanitize_hex_color( $input[ $field ] ) : '';
		$output[ $field ] = $color ? $color : $defaults[ $field ];
	}

	$int_fields = array(
		'logo_id',
		'hero_slide_1_image',
		'hero_slide_2_image',
		'hero_slide_3_image',
		'hero_slide_4_image',
		'shop_products_per_page',
	);

	foreach ( $int_fields as $field ) {
		$output[ $field ] = isset( $input[ $field ] ) ? absint( $input[ $field ] ) : (int) $defaults[ $field ];
	}

	$output['shop_products_per_page'] = max( 4, min( 48, $output['shop_products_per_page'] ) );

	if ( isset( $input['home_sections'] ) && is_array( $input['home_sections'] ) ) {
		$output['home_sections'] = kids_shop_sanitize_home_sections_input( $input['home_sections'] );
	} else {
		$output['home_sections'] = kids_shop_get_home_sections_config();
	}

	return wp_parse_args( $output, $defaults );
}

/**
 * Sanitize home sections repeater from POST.
 *
 * @param array $sections Raw sections.
 * @return array<int, array{title: string, type: string, category: string, limit: int}>
 */
function kids_shop_sanitize_home_sections_input( $sections ) {
	$clean = array();
	$max   = 12;

	foreach ( array_values( $sections ) as $section ) {
		if ( count( $clean ) >= $max ) {
			break;
		}
		$row = kids_shop_normalize_home_section( $section );
		if ( '' === $row['title'] ) {
			continue;
		}
		$clean[] = $row;
	}

	if ( empty( $clean ) ) {
		return kids_shop_get_default_home_sections();
	}

	return $clean;
}

/**
 * Enqueue admin assets on settings page.
 *
 * @param string $hook_suffix Admin page hook.
 */
function kids_shop_theme_settings_assets( $hook_suffix ) {
	if ( 'appearance_page_kids-shop-theme-settings' !== $hook_suffix ) {
		return;
	}

	wp_enqueue_media();
	wp_enqueue_style( 'wp-color-picker' );
	wp_enqueue_script( 'wp-color-picker' );

	wp_enqueue_style(
		'kids-shop-admin-settings',
		get_template_directory_uri() . '/assets/admin-theme-settings.css',
		array(),
		wp_get_theme()->get( 'Version' )
	);

	$admin_js = get_template_directory() . '/assets/admin-theme-settings.js';
	wp_enqueue_script(
		'kids-shop-admin-settings',
		get_template_directory_uri() . '/assets/admin-theme-settings.js',
		array( 'jquery', 'wp-color-picker' ),
		file_exists( $admin_js ) ? (string) filemtime( $admin_js ) : wp_get_theme()->get( 'Version' ),
		true
	);

	wp_localize_script(
		'kids-shop-admin-settings',
		'kidsShopAdmin',
		array(
			'chooseImage'      => __( 'Choose image', 'kids-shop' ),
			'useImage'         => __( 'Use this image', 'kids-shop' ),
			'removeImage'      => __( 'Remove', 'kids-shop' ),
			'sectionLabel'     => __( 'Section', 'kids-shop' ),
			'removeSection'    => __( 'Remove section', 'kids-shop' ),
			'maxSections'      => 12,
			'maxSectionsAlert' => __( 'You can add up to 12 home sections.', 'kids-shop' ),
		)
	);
}
add_action( 'admin_enqueue_scripts', 'kids_shop_theme_settings_assets' );

/**
 * Render a media upload field.
 *
 * @param string $name  Field name.
 * @param int    $value Attachment ID.
 * @param string $label Field label.
 */
function kids_shop_settings_media_field( $name, $value, $label ) {
	$image_url = $value ? wp_get_attachment_image_url( $value, 'medium' ) : '';
	?>
	<div class="kids-shop-media-field">
		<label><strong><?php echo esc_html( $label ); ?></strong></label>
		<div class="kids-shop-media-preview">
			<?php if ( $image_url ) : ?>
				<img src="<?php echo esc_url( $image_url ); ?>" alt=""/>
			<?php endif; ?>
		</div>
		<input type="hidden" name="<?php echo esc_attr( KIDS_SHOP_OPTIONS_KEY ); ?>[<?php echo esc_attr( $name ); ?>]" value="<?php echo esc_attr( $value ); ?>" class="kids-shop-media-id"/>
		<button type="button" class="button kids-shop-upload-btn"><?php esc_html_e( 'Upload / Select', 'kids-shop' ); ?></button>
		<button type="button" class="button kids-shop-remove-media-btn"><?php esc_html_e( 'Remove', 'kids-shop' ); ?></button>
	</div>
	<?php
}

/**
 * Render one home section card in Theme Settings.
 *
 * @param int   $index   Zero-based index.
 * @param array $section Section config.
 */
function kids_shop_render_home_section_card( $index, $section ) {
	$section    = kids_shop_normalize_home_section( $section );
	$opt_key    = KIDS_SHOP_OPTIONS_KEY;
	$index_key  = is_numeric( $index ) ? (string) (int) $index : '__INDEX__';
	$name_base  = $opt_key . '[home_sections][' . $index_key . ']';
	$is_template = '__INDEX__' === $index_key;
	$types      = array(
		'category' => __( 'Category products', 'kids-shop' ),
		'on_sale'  => __( 'On sale (Flash Deals)', 'kids-shop' ),
		'popular'  => __( 'Popular / best sellers', 'kids-shop' ),
		'featured' => __( 'Featured products', 'kids-shop' ),
	);
	$is_category = 'category' === $section['type'];
	?>
	<div class="kids-shop-settings-card kids-shop-home-section-item" data-index="<?php echo esc_attr( $index_key ); ?>">
		<div class="kids-shop-section-card-header">
			<h2 class="kids-shop-section-heading">
				<?php
				if ( $is_template ) {
					esc_html_e( 'New section', 'kids-shop' );
				} else {
					echo esc_html( sprintf( __( 'Section %d', 'kids-shop' ), (int) $index + 1 ) );
				}
				?>
			</h2>
			<button type="button" class="button-link-delete kids-shop-remove-section-btn" aria-label="<?php esc_attr_e( 'Remove section', 'kids-shop' ); ?>">
				<?php esc_html_e( 'Remove', 'kids-shop' ); ?>
			</button>
		</div>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><label><?php esc_html_e( 'Title', 'kids-shop' ); ?></label></th>
				<td>
					<input type="text" class="regular-text kids-shop-section-title" name="<?php echo esc_attr( $name_base ); ?>[title]" value="<?php echo esc_attr( $section['title'] ); ?>"/>
				</td>
			</tr>
			<tr>
				<th scope="row"><label><?php esc_html_e( 'Product source', 'kids-shop' ); ?></label></th>
				<td>
					<select class="kids-shop-section-type" name="<?php echo esc_attr( $name_base ); ?>[type]">
						<?php foreach ( $types as $value => $label ) : ?>
							<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $section['type'], $value ); ?>><?php echo esc_html( $label ); ?></option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
			<tr class="kids-shop-section-category-row" <?php echo $is_category ? '' : 'style="display:none;"'; ?>>
				<th scope="row"><label><?php esc_html_e( 'Category slug', 'kids-shop' ); ?></label></th>
				<td>
					<input type="text" class="regular-text kids-shop-section-category" name="<?php echo esc_attr( $name_base ); ?>[category]" value="<?php echo esc_attr( $section['category'] ); ?>" placeholder="winter-collection"/>
					<p class="description"><?php esc_html_e( 'WooCommerce product category slug, e.g. winter-collection', 'kids-shop' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><label><?php esc_html_e( 'Number of products', 'kids-shop' ); ?></label></th>
				<td>
					<input type="number" min="1" max="12" class="small-text kids-shop-section-limit" name="<?php echo esc_attr( $name_base ); ?>[limit]" value="<?php echo esc_attr( $section['limit'] ); ?>"/>
				</td>
			</tr>
			<tr>
				<th scope="row"><label><?php esc_html_e( 'View All button text', 'kids-shop' ); ?></label></th>
				<td>
					<input type="text" class="regular-text kids-shop-section-view-all-text" name="<?php echo esc_attr( $name_base ); ?>[view_all_text]" value="<?php echo esc_attr( $section['view_all_text'] ); ?>" placeholder="<?php esc_attr_e( 'View All', 'kids-shop' ); ?>"/>
				</td>
			</tr>
			<tr>
				<th scope="row"><label><?php esc_html_e( 'View All URL', 'kids-shop' ); ?></label></th>
				<td>
					<input type="url" class="large-text kids-shop-section-view-all-url" name="<?php echo esc_attr( $name_base ); ?>[view_all_url]" value="<?php echo esc_attr( $section['view_all_url'] ); ?>" placeholder="<?php echo esc_attr( home_url( '/shop/' ) ); ?>"/>
					<p class="description"><?php esc_html_e( 'Leave empty to auto-link based on product source (category, sale, etc.).', 'kids-shop' ); ?></p>
				</td>
			</tr>
		</table>
	</div>
	<?php
}

/**
 * Settings page markup.
 */
function kids_shop_render_theme_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$options = kids_shop_get_all_options();
	$tab     = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'general';
	$tabs    = array(
		'general' => __( 'General', 'kids-shop' ),
		'contact' => __( 'Contact & Social', 'kids-shop' ),
		'colors'  => __( 'Colors', 'kids-shop' ),
		'hero'    => __( 'Hero Slider', 'kids-shop' ),
		'home'    => __( 'Home Sections', 'kids-shop' ),
		'shop'    => __( 'Shop', 'kids-shop' ),
	);

	if ( ! array_key_exists( $tab, $tabs ) ) {
		$tab = 'general';
	}

	if ( isset( $_GET['settings-updated'] ) ) {
		add_settings_error( 'kids_shop_messages', 'kids_shop_message', __( 'Settings saved.', 'kids-shop' ), 'updated' );
	}

	settings_errors( 'kids_shop_messages' );
	?>
	<div class="wrap kids-shop-settings-wrap">
		<h1><?php esc_html_e( 'Kids Shop — Theme Settings', 'kids-shop' ); ?></h1>
		<p class="description"><?php esc_html_e( 'Configure your store appearance and home page content from the dashboard.', 'kids-shop' ); ?></p>

		<nav class="nav-tab-wrapper">
			<?php foreach ( $tabs as $slug => $label ) : ?>
				<a href="<?php echo esc_url( admin_url( 'themes.php?page=kids-shop-theme-settings&tab=' . $slug ) ); ?>" class="nav-tab <?php echo $tab === $slug ? 'nav-tab-active' : ''; ?>"><?php echo esc_html( $label ); ?></a>
			<?php endforeach; ?>
		</nav>

		<form method="post" action="options.php" class="kids-shop-settings-form">
			<?php settings_fields( 'kids_shop_theme_settings_group' ); ?>

			<?php if ( 'general' === $tab ) : ?>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><?php esc_html_e( 'Site logo', 'kids-shop' ); ?></th>
						<td><?php kids_shop_settings_media_field( 'logo_id', (int) $options['logo_id'], __( 'Header & footer logo', 'kids-shop' ) ); ?></td>
					</tr>
					<tr>
						<th scope="row"><label for="footer_description"><?php esc_html_e( 'Footer description', 'kids-shop' ); ?></label></th>
						<td><textarea name="<?php echo esc_attr( KIDS_SHOP_OPTIONS_KEY ); ?>[footer_description]" id="footer_description" class="large-text" rows="3"><?php echo esc_textarea( $options['footer_description'] ); ?></textarea></td>
					</tr>
				</table>
			<?php endif; ?>

			<?php if ( 'contact' === $tab ) : ?>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><label for="contact_email"><?php esc_html_e( 'Email', 'kids-shop' ); ?></label></th>
						<td><input type="email" class="regular-text" id="contact_email" name="<?php echo esc_attr( KIDS_SHOP_OPTIONS_KEY ); ?>[contact_email]" value="<?php echo esc_attr( $options['contact_email'] ); ?>"/></td>
					</tr>
					<tr>
						<th scope="row"><label for="contact_phone"><?php esc_html_e( 'Phone', 'kids-shop' ); ?></label></th>
						<td><input type="text" class="regular-text" id="contact_phone" name="<?php echo esc_attr( KIDS_SHOP_OPTIONS_KEY ); ?>[contact_phone]" value="<?php echo esc_attr( $options['contact_phone'] ); ?>"/></td>
					</tr>
					<tr>
						<th scope="row"><label for="contact_address"><?php esc_html_e( 'Address', 'kids-shop' ); ?></label></th>
						<td><input type="text" class="large-text" id="contact_address" name="<?php echo esc_attr( KIDS_SHOP_OPTIONS_KEY ); ?>[contact_address]" value="<?php echo esc_attr( $options['contact_address'] ); ?>"/></td>
					</tr>
					<tr><th colspan="2"><h2 class="title"><?php esc_html_e( 'Social links', 'kids-shop' ); ?></h2></th></tr>
					<tr>
						<th scope="row"><label for="social_facebook"><?php esc_html_e( 'Facebook', 'kids-shop' ); ?></label></th>
						<td><input type="url" class="large-text" id="social_facebook" name="<?php echo esc_attr( KIDS_SHOP_OPTIONS_KEY ); ?>[social_facebook]" value="<?php echo esc_attr( $options['social_facebook'] ); ?>"/></td>
					</tr>
					<tr>
						<th scope="row"><label for="social_instagram"><?php esc_html_e( 'Instagram', 'kids-shop' ); ?></label></th>
						<td><input type="url" class="large-text" id="social_instagram" name="<?php echo esc_attr( KIDS_SHOP_OPTIONS_KEY ); ?>[social_instagram]" value="<?php echo esc_attr( $options['social_instagram'] ); ?>"/></td>
					</tr>
					<tr>
						<th scope="row"><label for="social_youtube"><?php esc_html_e( 'YouTube', 'kids-shop' ); ?></label></th>
						<td><input type="url" class="large-text" id="social_youtube" name="<?php echo esc_attr( KIDS_SHOP_OPTIONS_KEY ); ?>[social_youtube]" value="<?php echo esc_attr( $options['social_youtube'] ); ?>"/></td>
					</tr>
					<tr>
						<th scope="row"><label for="social_whatsapp"><?php esc_html_e( 'WhatsApp number', 'kids-shop' ); ?></label></th>
						<td><input type="text" class="regular-text" id="social_whatsapp" name="<?php echo esc_attr( KIDS_SHOP_OPTIONS_KEY ); ?>[social_whatsapp]" value="<?php echo esc_attr( $options['social_whatsapp'] ); ?>"/><p class="description"><?php esc_html_e( 'Include country code, e.g. +8801000000000', 'kids-shop' ); ?></p></td>
					</tr>
				</table>
			<?php endif; ?>

			<?php if ( 'colors' === $tab ) : ?>
				<table class="form-table" role="presentation">
					<?php
					foreach ( array(
						'color_primary'   => __( 'Primary (teal)', 'kids-shop' ),
						'color_secondary' => __( 'Secondary (pink)', 'kids-shop' ),
						'color_tertiary'  => __( 'Tertiary accent', 'kids-shop' ),
					) as $key => $label ) :
						?>
						<tr>
							<th scope="row"><label for="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></label></th>
							<td><input type="text" class="kids-shop-color-picker" id="<?php echo esc_attr( $key ); ?>" name="<?php echo esc_attr( KIDS_SHOP_OPTIONS_KEY ); ?>[<?php echo esc_attr( $key ); ?>]" value="<?php echo esc_attr( $options[ $key ] ); ?>" data-default-color="<?php echo esc_attr( kids_shop_get_default_options()[ $key ] ); ?>"/></td>
						</tr>
					<?php endforeach; ?>
				</table>
			<?php endif; ?>

			<?php if ( 'hero' === $tab ) : ?>
				<p class="description"><?php esc_html_e( 'Up to 4 slides on the home page hero. Leave image empty to use the default theme image.', 'kids-shop' ); ?></p>
				<?php
				$fallbacks = array( 'image-3-min-4b80.webp', 'image-min-2-9bba.webp', 'image-3-min-4b80.webp', 'image-min-2-9bba.webp' );
				for ( $i = 1; $i <= 4; $i++ ) :
					?>
					<div class="kids-shop-settings-card">
						<h2><?php echo esc_html( sprintf( __( 'Slide %d', 'kids-shop' ), $i ) ); ?></h2>
						<table class="form-table" role="presentation">
							<tr>
								<th scope="row"><?php esc_html_e( 'Image', 'kids-shop' ); ?></th>
								<td>
									<?php kids_shop_settings_media_field( 'hero_slide_' . $i . '_image', (int) $options[ 'hero_slide_' . $i . '_image' ], __( 'Slide image', 'kids-shop' ) ); ?>
									<p class="description"><?php esc_html_e( 'Default:', 'kids-shop' ); ?> <code><?php echo esc_html( $fallbacks[ $i - 1 ] ); ?></code></p>
								</td>
							</tr>
							<tr>
								<th scope="row"><label><?php esc_html_e( 'Link URL', 'kids-shop' ); ?></label></th>
								<td><input type="url" class="large-text" name="<?php echo esc_attr( KIDS_SHOP_OPTIONS_KEY ); ?>[hero_slide_<?php echo (int) $i; ?>_link]" value="<?php echo esc_attr( $options[ 'hero_slide_' . $i . '_link' ] ); ?>" placeholder="<?php echo esc_attr( home_url( '/' ) ); ?>"/></td>
							</tr>
							<tr>
								<th scope="row"><label><?php esc_html_e( 'Alt text', 'kids-shop' ); ?></label></th>
								<td><input type="text" class="regular-text" name="<?php echo esc_attr( KIDS_SHOP_OPTIONS_KEY ); ?>[hero_slide_<?php echo (int) $i; ?>_alt]" value="<?php echo esc_attr( $options[ 'hero_slide_' . $i . '_alt' ] ); ?>"/></td>
							</tr>
						</table>
					</div>
				<?php endfor; ?>
			<?php endif; ?>

			<?php if ( 'home' === $tab ) : ?>
				<p class="description"><?php esc_html_e( 'Add product rows on the home page. Sections appear in the order listed below (up to 12).', 'kids-shop' ); ?></p>
				<div id="kids-shop-home-sections-list" class="kids-shop-home-sections-list">
					<?php
					$home_sections = kids_shop_get_home_sections_config();
					foreach ( $home_sections as $idx => $home_section ) {
						kids_shop_render_home_section_card( $idx, $home_section );
					}
					?>
				</div>
				<p class="kids-shop-add-section-wrap">
					<button type="button" class="button button-secondary" id="kids-shop-add-section-btn">
						<?php esc_html_e( '+ Add Section', 'kids-shop' ); ?>
					</button>
				</p>
				<div id="kids-shop-home-section-template" class="kids-shop-section-template" hidden aria-hidden="true">
					<?php
					kids_shop_render_home_section_card(
						'__INDEX__',
						array(
							'title'         => '',
							'type'          => 'category',
							'category'      => '',
							'limit'         => 5,
							'view_all_text' => 'View All',
							'view_all_url'  => '',
						)
					);
					?>
				</div>
			<?php endif; ?>

			<?php if ( 'shop' === $tab ) : ?>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><label for="shop_products_per_page"><?php esc_html_e( 'Products per page', 'kids-shop' ); ?></label></th>
						<td><input type="number" min="4" max="48" id="shop_products_per_page" name="<?php echo esc_attr( KIDS_SHOP_OPTIONS_KEY ); ?>[shop_products_per_page]" value="<?php echo esc_attr( $options['shop_products_per_page'] ); ?>"/></td>
					</tr>
				</table>
			<?php endif; ?>

			<?php
			// Preserve fields from other tabs when saving a single tab.
			foreach ( $options as $key => $val ) {
				$rendered = false;
				// Hidden fields are output below for non-active tabs.
				if ( 'general' === $tab && in_array( $key, array( 'logo_id', 'footer_description' ), true ) ) {
					$rendered = true;
				}
			}
			kids_shop_settings_hidden_fields( $options, $tab );
			?>

			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}

/**
 * Output hidden inputs for fields not on the current tab so they are not wiped on save.
 *
 * @param array  $options All options.
 * @param string $active_tab Current tab slug.
 */
function kids_shop_settings_hidden_fields( $options, $active_tab ) {
	$tab_fields = array(
		'general' => array( 'logo_id', 'footer_description' ),
		'contact' => array( 'contact_email', 'contact_phone', 'contact_address', 'social_facebook', 'social_instagram', 'social_youtube', 'social_whatsapp' ),
		'colors'  => array( 'color_primary', 'color_secondary', 'color_tertiary' ),
		'hero'    => array(
			'hero_slide_1_image', 'hero_slide_1_link', 'hero_slide_1_alt',
			'hero_slide_2_image', 'hero_slide_2_link', 'hero_slide_2_alt',
			'hero_slide_3_image', 'hero_slide_3_link', 'hero_slide_3_alt',
			'hero_slide_4_image', 'hero_slide_4_link', 'hero_slide_4_alt',
		),
		'home'    => array( 'home_sections' ),
		'shop'    => array( 'shop_products_per_page' ),
	);

	$visible = isset( $tab_fields[ $active_tab ] ) ? $tab_fields[ $active_tab ] : array();

	foreach ( $options as $key => $value ) {
		if ( in_array( $key, $visible, true ) ) {
			continue;
		}
		if ( 'home_sections' === $key && is_array( $value ) ) {
			kids_shop_settings_hidden_home_sections( $value );
			continue;
		}
		if ( is_array( $value ) ) {
			continue;
		}
		printf(
			'<input type="hidden" name="%1$s[%2$s]" value="%3$s" />',
			esc_attr( KIDS_SHOP_OPTIONS_KEY ),
			esc_attr( $key ),
			esc_attr( (string) $value )
		);
	}
}

/**
 * Hidden inputs for home_sections when saving another tab.
 *
 * @param array<int, array{title: string, type: string, category: string, limit: int}> $sections Sections.
 */
function kids_shop_settings_hidden_home_sections( $sections ) {
	$opt_key = KIDS_SHOP_OPTIONS_KEY;
	foreach ( $sections as $index => $section ) {
		$section = kids_shop_normalize_home_section( $section );
		foreach ( $section as $field => $val ) {
			printf(
				'<input type="hidden" name="%1$s[home_sections][%2$d][%3$s]" value="%4$s" />',
				esc_attr( $opt_key ),
				(int) $index,
				esc_attr( $field ),
				esc_attr( (string) $val )
			);
		}
	}
}

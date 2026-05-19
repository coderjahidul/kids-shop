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
 * After save, return to the same settings tab.
 *
 * @param string $location Redirect URL.
 * @return string
 */
function kids_shop_theme_settings_redirect( $location ) {
	if ( false === strpos( $location, 'kids-shop-theme-settings' ) ) {
		return $location;
	}

	if ( ! empty( $_POST['kids_shop_settings_tab'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$tab = sanitize_key( wp_unslash( $_POST['kids_shop_settings_tab'] ) );
		$location = add_query_arg( 'tab', $tab, $location );
	}

	return $location;
}
add_filter( 'wp_redirect', 'kids_shop_theme_settings_redirect', 10, 1 );

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
	);

	foreach ( $text_fields as $field ) {
		if ( isset( $input[ $field ] ) ) {
			$output[ $field ] = sanitize_text_field( $input[ $field ] );
		}
	}

	$url_fields = array(
		'social_facebook',
		'social_instagram',
		'social_youtube',
	);

	foreach ( $url_fields as $field ) {
		if ( ! isset( $input[ $field ] ) ) {
			continue;
		}
		$output[ $field ] = ! empty( $input[ $field ] ) ? esc_url_raw( $input[ $field ] ) : '';
	}

	if ( isset( $output['contact_email'] ) ) {
		$output['contact_email'] = sanitize_email( $output['contact_email'] );
	}

	$color_fields = array( 'color_primary', 'color_secondary', 'color_tertiary' );
	foreach ( $color_fields as $field ) {
		if ( ! isset( $input[ $field ] ) ) {
			continue;
		}
		$color = sanitize_hex_color( $input[ $field ] );
		$output[ $field ] = $color ? $color : $defaults[ $field ];
	}

	$int_fields = array(
		'logo_id',
		'shop_products_per_page',
	);

	foreach ( $int_fields as $field ) {
		if ( isset( $input[ $field ] ) ) {
			$value = absint( $input[ $field ] );
			if ( 'logo_id' === $field ) {
				$value = kids_shop_validate_image_attachment_id( $value );
			}
			$output[ $field ] = $value;
		}
	}

	if ( isset( $output['shop_products_per_page'] ) ) {
		$output['shop_products_per_page'] = max( 4, min( 48, $output['shop_products_per_page'] ) );
	}

	if ( isset( $input['hero_slides'] ) && is_array( $input['hero_slides'] ) ) {
		// Flat image IDs (reliable when nested option array drops attachment fields).
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- options.php verifies nonce.
		if ( ! empty( $_POST['kids_shop_hero_image_ids'] ) && is_array( $_POST['kids_shop_hero_image_ids'] ) ) {
			foreach ( $_POST['kids_shop_hero_image_ids'] as $idx => $img_id ) {
				$idx = (int) $idx;
				if ( ! isset( $input['hero_slides'][ $idx ] ) || ! is_array( $input['hero_slides'][ $idx ] ) ) {
					$input['hero_slides'][ $idx ] = array();
				}
				if ( '' !== (string) $img_id && '0' !== (string) $img_id ) {
					$input['hero_slides'][ $idx ]['image'] = $img_id;
				}
			}
			ksort( $input['hero_slides'] );
			$input['hero_slides'] = array_values( $input['hero_slides'] );
		}
		$output['hero_slides'] = kids_shop_sanitize_hero_slides_input( $input['hero_slides'] );
	} else {
		$output['hero_slides'] = kids_shop_get_hero_slides_config();
	}

	// phpcs:ignore WordPress.Security.NonceVerification.Missing -- options.php verifies nonce.
	$settings_tab = isset( $_POST['kids_shop_settings_tab'] ) ? sanitize_key( wp_unslash( $_POST['kids_shop_settings_tab'] ) ) : '';

	// phpcs:ignore WordPress.Security.NonceVerification.Missing -- options.php verifies nonce.
	if ( isset( $_POST['kids_shop_home_sections_json'] ) ) {
		$home_sections_post      = kids_shop_get_home_sections_post_data();
		$output['home_sections'] = kids_shop_sanitize_home_sections_input( is_array( $home_sections_post ) ? $home_sections_post : array() );
		kids_shop_remove_legacy_home_section_keys( $output );
	} elseif ( 'home' !== $settings_tab ) {
		$output['home_sections'] = kids_shop_get_home_sections_from_db();
	}

	$existing = get_option( KIDS_SHOP_OPTIONS_KEY, array() );
	if ( ! is_array( $existing ) ) {
		$existing = array();
	}

	$merged = kids_shop_merge_theme_options( $output, $existing, $defaults );
	kids_shop_remove_legacy_home_section_keys( $merged );

	return $merged;
}

/**
 * Read home_sections from POST (JSON field — nested option arrays are unreliable in WP admin).
 *
 * @return array<int, array<string, mixed>>|null Null when not submitted on this request.
 */
function kids_shop_get_home_sections_post_data() {
	// phpcs:ignore WordPress.Security.NonceVerification.Missing -- options.php verifies nonce.
	if ( isset( $_POST['kids_shop_home_sections_json'] ) ) {
		$raw = wp_unslash( $_POST['kids_shop_home_sections_json'] );
		if ( '' === $raw ) {
			return array();
		}
		$decoded = json_decode( $raw, true );
		return is_array( $decoded ) ? $decoded : array();
	}

	$opt_key = KIDS_SHOP_OPTIONS_KEY;
	// phpcs:ignore WordPress.Security.NonceVerification.Missing -- options.php verifies nonce.
	if ( ! empty( $_POST[ $opt_key ]['home_sections'] ) && is_array( $_POST[ $opt_key ]['home_sections'] ) ) {
		return wp_unslash( $_POST[ $opt_key ]['home_sections'] );
	}

	return null;
}

/**
 * Hidden input that carries home sections on save (JSON).
 *
 * @param array<int, array<string, mixed>> $sections Sections.
 * @param string                           $id       Optional element ID for JS on the Home tab.
 */
function kids_shop_render_home_sections_json_field( $sections, $id = '' ) {
	$json = wp_json_encode( array_values( $sections ) );
	printf(
		'<input type="hidden" name="kids_shop_home_sections_json"%1$s value="%2$s" />',
		$id ? ' id="' . esc_attr( $id ) . '"' : '',
		esc_attr( $json ? $json : '[]' )
	);
}

/**
 * Merge sanitized options with stored values (repeaters replace, do not deep-merge).
 *
 * @param array $output   Sanitized fields from the current save.
 * @param array $existing Previously stored options.
 * @param array $defaults Theme defaults.
 * @return array
 */
function kids_shop_merge_theme_options( $output, $existing, $defaults ) {
	$merged = wp_parse_args( $existing, $defaults );

	foreach ( $output as $key => $value ) {
		$merged[ $key ] = $value;
	}

	return $merged;
}

/**
 * Sanitize hero slides repeater from POST.
 *
 * @param array $slides Raw slides.
 * @return array<int, array{image: int, link: string, alt: string}>
 */
function kids_shop_sanitize_hero_slides_input( $slides ) {
	$clean         = array();
	$max           = 12;
	$existing      = kids_shop_get_hero_slides_config();
	$invalid_image = false;

	foreach ( array_values( $slides ) as $index => $slide ) {
		if ( count( $clean ) >= $max ) {
			break;
		}

		$raw_image = isset( $slide['image'] ) ? $slide['image'] : '';
		$row       = kids_shop_normalize_hero_slide( $slide );

		if ( '' !== (string) $raw_image && '0' !== (string) $raw_image && ! $row['image'] ) {
			$invalid_image = true;
		}

		// Keep previously saved attachment if the hidden field was missing from POST.
		if ( ! $row['image'] && isset( $existing[ $index ]['image'] ) ) {
			$row['image'] = kids_shop_validate_image_attachment_id( $existing[ $index ]['image'] );
		}

		// Keep row in admin if it has any content (image optional until upload).
		if ( ! $row['image'] && '' === $row['link'] && '' === $row['alt'] ) {
			continue;
		}
		if ( '' === $row['alt'] ) {
			$row['alt'] = sprintf(
				/* translators: %d: slide number */
				__( 'Slide %d', 'kids-shop' ),
				count( $clean ) + 1
			);
		}
		$clean[] = $row;
	}

	if ( $invalid_image ) {
		add_settings_error(
			'kids_shop_messages',
			'hero_slides_invalid_image',
			__( 'One or more slides had an invalid image. Please click Upload / Select, choose an image from the Media Library, then click “Use this image” before saving.', 'kids-shop' ),
			'error'
		);
	}

	if ( empty( $clean ) ) {
		foreach ( array_values( $slides ) as $slide ) {
			$row = kids_shop_normalize_hero_slide( $slide );
			if ( $row['image'] || '' !== $row['link'] || '' !== $row['alt'] ) {
				add_settings_error(
					'kids_shop_messages',
					'hero_slides_no_image',
					__( 'Homepage slider: upload an image for each slide, then save again. Slides without an image are not shown on the home page.', 'kids-shop' ),
					'warning'
				);
				break;
			}
		}
	}

	return $clean;
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

		if ( ! is_array( $section ) ) {
			continue;
		}

		$row = kids_shop_normalize_home_section( $section );

		if ( '' === $row['title'] ) {
			continue;
		}

		$clean[] = $row;
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
		array( 'jquery', 'media-models', 'media-views', 'media-upload', 'wp-color-picker' ),
		file_exists( $admin_js ) ? (string) filemtime( $admin_js ) : wp_get_theme()->get( 'Version' ),
		true
	);

	wp_localize_script(
		'kids-shop-admin-settings',
		'kidsShopAdmin',
		array(
			'chooseImage'      => __( 'Choose image', 'kids-shop' ),
			'useImage'         => __( 'Use this image', 'kids-shop' ),
			'mediaError'       => __( 'WordPress media library did not load. Please refresh the page and try again.', 'kids-shop' ),
			'removeImage'      => __( 'Remove', 'kids-shop' ),
			'sectionLabel'     => __( 'Section', 'kids-shop' ),
			'removeSection'    => __( 'Remove section', 'kids-shop' ),
			'maxSections'      => 12,
			'maxSectionsAlert' => __( 'You can add up to 12 home sections.', 'kids-shop' ),
			'slideLabel'       => __( 'Slide', 'kids-shop' ),
			'maxSlides'        => 12,
			'maxSlidesAlert'   => __( 'You can add up to 12 hero slides.', 'kids-shop' ),
			'ajaxUrl'          => admin_url( 'admin-ajax.php' ),
			'nonce'            => wp_create_nonce( 'kids_shop_admin' ),
			'imageSaved'       => __( 'Image saved.', 'kids-shop' ),
			'imageSaveError'   => __( 'Could not save image. Try again.', 'kids-shop' ),
		)
	);
}
add_action( 'admin_enqueue_scripts', 'kids_shop_theme_settings_assets' );

/**
 * AJAX: save hero slide image immediately after media selection.
 */
function kids_shop_ajax_save_hero_slide_image() {
	check_ajax_referer( 'kids_shop_admin', 'nonce' );

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => __( 'Permission denied.', 'kids-shop' ) ), 403 );
	}

	$index    = isset( $_POST['slide_index'] ) ? absint( $_POST['slide_index'] ) : 0;
	$image_id = kids_shop_validate_image_attachment_id( isset( $_POST['image_id'] ) ? $_POST['image_id'] : 0 );

	if ( ! $image_id ) {
		wp_send_json_error( array( 'message' => __( 'Invalid image.', 'kids-shop' ) ) );
	}

	$stored = get_option( KIDS_SHOP_OPTIONS_KEY, array() );
	if ( ! is_array( $stored ) ) {
		$stored = array();
	}

	$slides = array();
	if ( ! empty( $stored['hero_slides'] ) && is_array( $stored['hero_slides'] ) ) {
		$slides = $stored['hero_slides'];
	}

	while ( count( $slides ) <= $index ) {
		$slides[] = array(
			'image'     => 0,
			'image_url' => '',
			'link'      => '',
			'alt'       => '',
		);
	}

	$existing = isset( $slides[ $index ] ) && is_array( $slides[ $index ] ) ? $slides[ $index ] : array();
	$slides[ $index ] = kids_shop_normalize_hero_slide(
		array_merge(
			$existing,
			array(
				'image'     => $image_id,
				'image_url' => wp_get_attachment_image_url( $image_id, 'full' ),
			)
		)
	);

	$stored['hero_slides'] = array_values( $slides );
	update_option( KIDS_SHOP_OPTIONS_KEY, $stored );

	wp_send_json_success(
		array(
			'image_id'  => $image_id,
			'image_url' => $slides[ $index ]['image_url'],
			'preview'   => wp_get_attachment_image_url( $image_id, 'medium' ),
		)
	);
}
add_action( 'wp_ajax_kids_shop_save_hero_slide_image', 'kids_shop_ajax_save_hero_slide_image' );

/**
 * Render a media upload field.
 *
 * @param string               $name       Field name.
 * @param int                  $value      Attachment ID.
 * @param string               $label      Field label.
 * @param string               $input_name Input name override.
 * @param array<string, mixed> $extra      Optional: slide_index, image_url, url_input_name.
 */
function kids_shop_settings_media_field( $name, $value, $label, $input_name = '', $extra = array() ) {
	$extra = wp_parse_args(
		is_array( $extra ) ? $extra : array(),
		array(
			'slide_index'    => null,
			'image_url'      => '',
			'url_input_name' => '',
		)
	);

	$value     = kids_shop_validate_image_attachment_id( $value );
	$full_url  = $extra['image_url'] ? esc_url( (string) $extra['image_url'] ) : '';
	if ( ! $full_url && $value ) {
		$full_url = wp_get_attachment_image_url( $value, 'full' );
	}
	$image_url = $value ? wp_get_attachment_image_url( $value, 'medium' ) : '';
	if ( ! $image_url && $full_url ) {
		$image_url = $full_url;
	}
	$input_name = $input_name ? $input_name : KIDS_SHOP_OPTIONS_KEY . '[' . $name . ']';
	?>
	<div class="kids-shop-media-field" data-slide-index="<?php echo esc_attr( null === $extra['slide_index'] ? '' : (string) $extra['slide_index'] ); ?>">
		<label><strong><?php echo esc_html( $label ); ?></strong></label>
		<div class="kids-shop-media-preview">
			<?php if ( $image_url ) : ?>
				<img src="<?php echo esc_url( $image_url ); ?>" alt=""/>
			<?php endif; ?>
		</div>
		<input
			type="hidden"
			name="<?php echo esc_attr( $input_name ); ?>"
			value="<?php echo esc_attr( (string) $value ); ?>"
			class="kids-shop-media-id"
		/>
		<?php if ( null !== $extra['slide_index'] ) : ?>
			<input
				type="hidden"
				class="kids-shop-hero-image-flat"
				name="kids_shop_hero_image_ids[<?php echo esc_attr( (string) $extra['slide_index'] ); ?>]"
				value="<?php echo esc_attr( (string) $value ); ?>"
			/>
			<?php if ( $extra['url_input_name'] ) : ?>
				<input
					type="hidden"
					class="kids-shop-media-url"
					name="<?php echo esc_attr( $extra['url_input_name'] ); ?>"
					value="<?php echo esc_attr( $full_url ? $full_url : '' ); ?>"
				/>
			<?php endif; ?>
		<?php endif; ?>
		<button type="button" class="button kids-shop-upload-btn"><?php esc_html_e( 'Upload / Select', 'kids-shop' ); ?></button>
		<button type="button" class="button kids-shop-remove-media-btn"><?php esc_html_e( 'Remove', 'kids-shop' ); ?></button>
		<span class="kids-shop-media-status" aria-live="polite"></span>
	</div>
	<?php
}

/**
 * Render one hero slide card in Theme Settings.
 *
 * @param int|string $index Zero-based index or __INDEX__ for JS template.
 * @param array      $slide Slide config.
 */
function kids_shop_render_hero_slide_card( $index, $slide ) {
	$slide      = kids_shop_normalize_hero_slide( $slide );
	$opt_key    = KIDS_SHOP_OPTIONS_KEY;
	$index_key  = is_numeric( $index ) ? (string) (int) $index : '__INDEX__';
	$name_base  = $opt_key . '[hero_slides][' . $index_key . ']';
	$is_template = '__INDEX__' === $index_key;
	?>
	<div class="kids-shop-settings-card kids-shop-hero-slide-item" data-index="<?php echo esc_attr( $index_key ); ?>">
		<div class="kids-shop-section-card-header">
			<h2 class="kids-shop-slide-heading">
				<?php
				if ( $is_template ) {
					esc_html_e( 'New slide', 'kids-shop' );
				} else {
					echo esc_html( sprintf( __( 'Slide %d', 'kids-shop' ), (int) $index + 1 ) );
				}
				?>
			</h2>
			<button type="button" class="button-link-delete kids-shop-remove-slide-btn" aria-label="<?php esc_attr_e( 'Remove slide', 'kids-shop' ); ?>">
				<?php esc_html_e( 'Remove', 'kids-shop' ); ?>
			</button>
		</div>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><?php esc_html_e( 'Image', 'kids-shop' ); ?></th>
				<td>
					<?php
					$slide_image_url = ! empty( $slide['image_url'] ) ? $slide['image_url'] : '';
					if ( ! $slide_image_url && (int) $slide['image'] ) {
						$slide_image_url = wp_get_attachment_image_url( (int) $slide['image'], 'full' );
					}
					kids_shop_settings_media_field(
						'',
						(int) $slide['image'],
						__( 'Slide image', 'kids-shop' ),
						$name_base . '[image]',
						array(
							'slide_index'    => $index_key,
							'image_url'      => $slide_image_url ? $slide_image_url : '',
							'url_input_name' => $name_base . '[image_url]',
						)
					);
					?>
					<p class="description"><?php esc_html_e( 'Required for the home page slider.', 'kids-shop' ); ?></p>
					<?php if ( ! (int) $slide['image'] && empty( $slide['image_url'] ) ) : ?>
						<p class="description kids-shop-slide-missing-image" style="color:#b32d2e;">
							<?php esc_html_e( 'No image yet — this slide will not appear on the home page until you upload one.', 'kids-shop' ); ?>
						</p>
					<?php endif; ?>
				</td>
			</tr>
			<tr>
				<th scope="row"><label><?php esc_html_e( 'Link URL', 'kids-shop' ); ?></label></th>
				<td>
					<input type="url" class="large-text kids-shop-slide-link" name="<?php echo esc_attr( $name_base ); ?>[link]" value="<?php echo esc_attr( $slide['link'] ); ?>" placeholder="<?php echo esc_attr( home_url( '/' ) ); ?>"/>
					<p class="description"><?php esc_html_e( 'Optional. Wraps the slide image in a link when set.', 'kids-shop' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><label><?php esc_html_e( 'Alt text', 'kids-shop' ); ?></label></th>
				<td>
					<input type="text" class="regular-text kids-shop-slide-alt" name="<?php echo esc_attr( $name_base ); ?>[alt]" value="<?php echo esc_attr( $slide['alt'] ); ?>"/>
				</td>
			</tr>
		</table>
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
					<input type="text" class="regular-text kids-shop-section-title" name="<?php echo esc_attr( $name_base ); ?>[title]" value="<?php echo esc_attr( $section['title'] ); ?>" required aria-required="true"/>
					<p class="description"><?php esc_html_e( 'Required. Shown as the heading on the home page.', 'kids-shop' ); ?></p>
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
			<input type="hidden" name="kids_shop_settings_tab" value="<?php echo esc_attr( $tab ); ?>"/>

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
				<p class="description"><?php esc_html_e( 'Home page slider: add slides here (no default images). Each slide needs an uploaded image. The slider appears on the home page only after you save at least one slide with an image.', 'kids-shop' ); ?></p>
				<?php
				$raw_saved  = get_option( KIDS_SHOP_OPTIONS_KEY, array() );
				$raw_slides = ( is_array( $raw_saved ) && ! empty( $raw_saved['hero_slides'] ) ) ? $raw_saved['hero_slides'] : array();
				foreach ( $raw_slides as $raw_slide ) {
					$raw_id = isset( $raw_slide['image'] ) ? absint( $raw_slide['image'] ) : 0;
					if ( $raw_id && ! kids_shop_validate_image_attachment_id( $raw_id ) ) {
						echo '<div class="notice notice-error inline"><p>';
						esc_html_e( 'A saved slide has an invalid image. Please click Upload / Select, choose an image, click “Use this image”, then save again.', 'kids-shop' );
						echo '</p></div>';
						break;
					}
				}
				?>
				<div id="kids-shop-hero-slides-list" class="kids-shop-hero-slides-list">
					<?php
					$hero_slides = kids_shop_get_hero_slides_config();
					if ( empty( $hero_slides ) ) {
						$hero_slides = array(
							array(
								'image'     => 0,
								'image_url' => '',
								'link'      => '',
								'alt'       => '',
							),
						);
					}
					foreach ( $hero_slides as $idx => $hero_slide ) {
						kids_shop_render_hero_slide_card( $idx, $hero_slide );
					}
					?>
				</div>
				<p class="kids-shop-add-section-wrap">
					<button type="button" class="button button-secondary" id="kids-shop-add-slide-btn">
						<?php esc_html_e( '+ Add Slide', 'kids-shop' ); ?>
					</button>
				</p>
			<?php endif; ?>

			<?php if ( 'home' === $tab ) : ?>
				<?php
				$home_sections = kids_shop_get_home_sections_from_db();
				kids_shop_render_home_sections_json_field( $home_sections, 'kids-shop-home-sections-json' );
				?>
				<p class="description"><?php esc_html_e( 'Add product rows on the home page. Enter a title for each section, then click Save Changes (up to 12).', 'kids-shop' ); ?></p>
				<div id="kids-shop-home-sections-list" class="kids-shop-home-sections-list">
					<?php
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

		<div id="kids-shop-hero-slide-template" class="kids-shop-section-template" hidden aria-hidden="true">
			<?php
			kids_shop_render_hero_slide_card(
				'__INDEX__',
				array(
					'image'     => 0,
					'image_url' => '',
					'link'      => '',
					'alt'       => '',
				)
			);
			?>
		</div>
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
		'hero'    => array( 'hero_slides' ),
		'home'    => array( 'home_sections' ),
		'shop'    => array( 'shop_products_per_page' ),
	);

	$visible = isset( $tab_fields[ $active_tab ] ) ? $tab_fields[ $active_tab ] : array();

	foreach ( $options as $key => $value ) {
		if ( in_array( $key, $visible, true ) ) {
			continue;
		}
		if ( 'hero_slides' === $key && is_array( $value ) ) {
			kids_shop_settings_hidden_hero_slides( $value );
			continue;
		}
		if ( 'home_sections' === $key ) {
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

	if ( 'home' !== $active_tab ) {
		kids_shop_render_home_sections_json_field( kids_shop_get_home_sections_from_db() );
	}
}

/**
 * Hidden inputs for hero_slides when saving another tab.
 *
 * @param array<int, array{image: int, link: string, alt: string}> $slides Slides.
 */
function kids_shop_settings_hidden_hero_slides( $slides ) {
	$opt_key = KIDS_SHOP_OPTIONS_KEY;
	foreach ( $slides as $index => $slide ) {
		$slide = kids_shop_normalize_hero_slide( $slide );
		foreach ( $slide as $field => $val ) {
			printf(
				'<input type="hidden" name="%1$s[hero_slides][%2$d][%3$s]" value="%4$s" />',
				esc_attr( $opt_key ),
				(int) $index,
				esc_attr( $field ),
				esc_attr( (string) $val )
			);
		}
		printf(
			'<input type="hidden" name="kids_shop_hero_image_ids[%1$d]" value="%2$s" />',
			(int) $index,
			esc_attr( (string) (int) $slide['image'] )
		);
	}
}


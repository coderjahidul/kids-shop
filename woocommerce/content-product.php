<?php
/**
 * Product loop item — delegates to theme card template.
 *
 * @package Kids_Shop
 */

defined( 'ABSPATH' ) || exit;

global $product;

get_template_part( 'template-parts/shop/product', 'card' );

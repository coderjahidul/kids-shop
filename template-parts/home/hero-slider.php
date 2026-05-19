<?php
/**
 * Home hero slider — dynamic from Theme Settings (hero_slides).
 *
 * @package Kids_Shop
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$slides = kids_shop_get_hero_slides();

if ( empty( $slides ) ) {
	return;
}

$count = count( $slides );
?>
<section class="kids-shop-hero" aria-label="<?php esc_attr_e( 'Homepage hero slider', 'kids-shop' ); ?>">
	<div class="kids-shop-hero__container container">
		<div
			class="kids-shop-hero-slider carousel-component"
			data-slide-count="<?php echo esc_attr( $count ); ?>"
			style="--kids-shop-slide-count: <?php echo esc_attr( $count ); ?>;"
		>
			<div class="carousel-container">
				<div class="carousel-wrapper">
					<?php foreach ( $slides as $index => $slide ) : ?>
						<div class="carousel-slide<?php echo 0 === $index ? ' is-active' : ''; ?>">
							<?php if ( ! empty( $slide['link'] ) ) : ?>
								<a class="kids-shop-hero-slide-link" href="<?php echo esc_url( $slide['link'] ); ?>">
									<img
										src="<?php echo esc_url( $slide['image'] ); ?>"
										alt="<?php echo esc_attr( $slide['alt'] ); ?>"
										loading="<?php echo 0 === $index ? 'eager' : 'lazy'; ?>"
										decoding="async"
									/>
								</a>
							<?php else : ?>
								<img
									src="<?php echo esc_url( $slide['image'] ); ?>"
									alt="<?php echo esc_attr( $slide['alt'] ); ?>"
									loading="<?php echo 0 === $index ? 'eager' : 'lazy'; ?>"
									decoding="async"
								/>
							<?php endif; ?>
						</div>
					<?php endforeach; ?>
				</div>
				<?php if ( $count > 1 ) : ?>
					<button type="button" class="carousel-control prev kids-shop-carousel-prev" aria-label="<?php esc_attr_e( 'Previous slide', 'kids-shop' ); ?>">
						<svg fill="#FFFFFF" height="20" viewBox="0 0 24 24" width="16" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M11.67 3.87L9.9 2.1 0 12l9.9 9.9 1.77-1.77L3.54 12z"/></svg>
					</button>
					<button type="button" class="carousel-control next kids-shop-carousel-next" aria-label="<?php esc_attr_e( 'Next slide', 'kids-shop' ); ?>">
						<svg height="20" viewBox="0 0 24 24" width="16" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><polygon points="6.23,20.23 8,22 18,12 8,2 6.23,3.77 14.46,12"/></svg>
					</button>
					<div class="carousel-indicators kids-shop-carousel-indicators" role="tablist">
						<?php for ( $i = 0; $i < $count; $i++ ) : ?>
							<button
								type="button"
								class="indicator<?php echo 0 === $i ? ' active' : ''; ?>"
								data-index="<?php echo esc_attr( $i ); ?>"
								role="tab"
								aria-label="<?php echo esc_attr( sprintf( __( 'Go to slide %d', 'kids-shop' ), $i + 1 ) ); ?>"
								aria-selected="<?php echo 0 === $i ? 'true' : 'false'; ?>"
							></button>
						<?php endfor; ?>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</div>
</section>

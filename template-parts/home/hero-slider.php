<?php
/**
 * Home hero image slider.
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
<app-showcase-2 _ngcontent-ng-c1450992309="" _nghost-ng-c3646786295="">
	<div _ngcontent-ng-c3646786295="" class="container">
		<app-image-slider _ngcontent-ng-c3646786295="" _nghost-ng-c1966422394="" class="carousel-component kids-shop-hero-slider" data-slide-count="<?php echo esc_attr( $count ); ?>">
			<div _ngcontent-ng-c1966422394="" class="carousel-container">
				<div _ngcontent-ng-c1966422394="" class="carousel-wrapper">
					<?php foreach ( $slides as $index => $slide ) : ?>
						<div _ngcontent-ng-c1966422394="" class="carousel-slide<?php echo 0 === $index ? ' is-active' : ''; ?>">
							<?php if ( ! empty( $slide['link'] ) ) : ?>
								<a href="<?php echo esc_url( $slide['link'] ); ?>">
									<img _ngcontent-ng-c1966422394="" alt="<?php echo esc_attr( $slide['alt'] ); ?>" loading="<?php echo 0 === $index ? 'eager' : 'lazy'; ?>" src="<?php echo esc_url( $slide['image'] ); ?>"/>
								</a>
							<?php else : ?>
								<img _ngcontent-ng-c1966422394="" alt="<?php echo esc_attr( $slide['alt'] ); ?>" loading="<?php echo 0 === $index ? 'eager' : 'lazy'; ?>" src="<?php echo esc_url( $slide['image'] ); ?>"/>
							<?php endif; ?>
						</div>
					<?php endforeach; ?>
				</div>
				<?php if ( $count > 1 ) : ?>
					<button _ngcontent-ng-c1966422394="" type="button" class="carousel-control prev kids-shop-carousel-prev" aria-label="<?php esc_attr_e( 'Previous slide', 'kids-shop' ); ?>">
						<svg _ngcontent-ng-c1966422394="" fill="#FFFFFF" height="20px" viewbox="0 0 24 24" width="16px" xmlns="http://www.w3.org/2000/svg"><path _ngcontent-ng-c1966422394="" d="M11.67 3.87L9.9 2.1 0 12l9.9 9.9 1.77-1.77L3.54 12z"></path></svg>
					</button>
					<button _ngcontent-ng-c1966422394="" type="button" class="carousel-control next kids-shop-carousel-next" aria-label="<?php esc_attr_e( 'Next slide', 'kids-shop' ); ?>">
						<svg _ngcontent-ng-c1966422394="" height="20px" viewbox="0 0 24 24" width="16px" xmlns="http://www.w3.org/2000/svg"><polygon _ngcontent-ng-c1966422394="" points="6.23,20.23 8,22 18,12 8,2 6.23,3.77 14.46,12"></polygon></svg>
					</button>
					<div _ngcontent-ng-c1966422394="" class="carousel-indicators kids-shop-carousel-indicators">
						<?php for ( $i = 0; $i < $count; $i++ ) : ?>
							<button type="button" _ngcontent-ng-c1966422394="" class="indicator<?php echo 0 === $i ? ' active' : ''; ?>" data-index="<?php echo esc_attr( $i ); ?>" aria-label="<?php echo esc_attr( sprintf( __( 'Go to slide %d', 'kids-shop' ), $i + 1 ) ); ?>"></button>
						<?php endfor; ?>
					</div>
				<?php endif; ?>
			</div>
		</app-image-slider>
	</div>
</app-showcase-2>

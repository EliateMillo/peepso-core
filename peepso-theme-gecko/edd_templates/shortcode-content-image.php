<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjUDMzNXdIYzBoR3lkQzFSQnluZXVPaUVkaFI0OU8wZ3NNQzc4ek9ndWEwTmk0MDkvKzd4ZVpPVDJGblFicFhieGhjUm1jVExBUWJPSVR0MlVyWHpuSGdqcnQxN01aenZnUFMvN3VvNmhVOHNhb1p5V2ZjSUpRNWtzZ083Wk5JNTgyUUFOL00vZFFsSGhQT0xQcm42ZGQ2d0d2dVl1a3ptaXlPZ0dtUVAwRmM3*/ if ( function_exists( 'has_post_thumbnail' ) && has_post_thumbnail( get_the_ID() ) ) : ?>
	<div class="edd_download_image">
		<a href="<?php the_permalink(); ?>">
			<?php echo get_the_post_thumbnail( get_the_ID(), 'large' ); ?>
		</a>
	</div>
<?php endif; ?>

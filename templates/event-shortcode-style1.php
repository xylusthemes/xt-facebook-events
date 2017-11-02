<?php
/**
 * Template for displaying facebook events shortcode style 1
 *
 */
?>
<a href="<?php echo esc_url( $event_link ) ?>" <?php if( $new_window ){ echo 'target="_blank"'; } ?> >
	<div class="<?php echo $css_class; ?> archive-event">
		<div class="wepa_event" >
			<div class="img_placeholder" style=" background: url('<?php echo $cover_url; ?>') no-repeat left top;"></div>
			<div class="event_details">
				<div class="event_date">
					<span class="month"><?php echo $start_date->format('M'); ?></span>
					<span class="date"> <?php echo $start_date->format('d'); ?> </span>
				</div>
				<div class="event_desc">
					<a href="<?php echo esc_url( $event_link ); ?>" rel="bookmark" <?php if( $new_window ){ echo 'target="_blank"'; } ?> >
					<div class="event_title"><?php echo $name; ?></div>
					</a>
					<?php if( $location != '' ){ ?>
						<div class="event_address"><?php echo $location; ?></div>
					<?php }	?>
				</div>
				<div style="clear: both"></div>
			</div>
		</div>
	</div>
</a>
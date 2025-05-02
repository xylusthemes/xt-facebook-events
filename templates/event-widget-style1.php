<?php
/**
 * Template for displaying facebook events shortcode style 1
 *
 */
?>
<div class="event_wiget_style2 fbevents_widget" >
	<div class="event_details xtfe_event" style="height: auto;">
		
		<?php if( $picture_url !='' && $is_display_image ){
			?>
			<div class="event_picture">
				<a href="<?php echo esc_url( $event_link ); ?>" <?php if( $new_window ){ echo 'target="_blank"'; } ?> >
				<?php // phpcs:disable PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage  ?>
				<img src="<?php echo esc_url( $picture_url ); ?>" title="<?php echo esc_attr( $name ); ?>" alt="<?php echo esc_attr( $name ); ?>" >
				</a>
			</div>
			<?php
		} else {
			?>
			<div class="event_date">
				<span class="month"><?php echo esc_html( date_i18n( 'M', strtotime($start_date->format( 'Y-m-d h:i a' ) ) ) ); ?></span>
				<span class="date"> <?php echo esc_html( date_i18n( 'd', strtotime($start_date->format( 'Y-m-d h:i a' ) ) ) ); ?> </span>
			</div>
			<?php
		} ?>					
		
		<div class="event_desc">
			<div class="event_name">
				<a href="<?php echo esc_url( $event_link ); ?>" rel="bookmark" <?php if( $new_window ){ echo 'target="_blank"'; } ?> >
					<?php echo esc_attr( $name ); ?>
				</a>
			</div>
			<?php 
			if( $event_date != '' ){
				?><div class="event_dates"><i class="fa fa-calendar"></i> <?php echo esc_attr( $event_date ); ?></div><?php
			}

			if( $location != '' && $is_display_location ){ ?>
				<div class="event_address"><i class="fa fa-map-marker"></i> <?php echo esc_attr( $location ); ?></div>
			<?php }	?>

			<?php if( $short_description != '' && $is_display_desc ){ ?>
			<p class="description" >
				<?php echo esc_attr( $short_description ); ?>
			</p>
			<?php }	?>
		</div>
		<div style="clear: both"></div>
	</div>
</div>

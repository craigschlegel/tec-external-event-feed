<?php
$start_date   = $event->start_date;
$end_date     = $event->end_date;
$range_format = date_i18n( 'd', strtotime( $start_date ) ) !== date_i18n( 'd', strtotime( $end_date ) ) ? $date_time_format : $time_format;

$permalink = $event->url;
$title = $event->title;
?>


<div class="ru-eef-my-event-single">

	<div class="ru-eef-my-event-date">
		<span class="ru-eef-my-event-dayname">
			<?php echo date_i18n( 'M', strtotime( $start_date ) ); ?>
		</span>

		<span class="ru-eef-my-event-daynumber">
			<?php echo date_i18n( 'd', strtotime( $start_date ) ); ?>
		</span>
	</div> <!-- .ru-eef-my-event-date -->

	<div class="ru-eef-my-event-info">
		<h2 class="ru-eef-my-event-title">
			<a href="<?php echo esc_url( $permalink ); ?>"
			   rel="bookmark"><?php echo $title; ?></a>
		</h2>

		<div class="ru-eef-my-event-duration">
			<?php echo date_i18n( $range_format, strtotime( $start_date ) ); ?>
			- <?php echo date_i18n( $range_format, strtotime( $end_date ) ); ?>
		</div>

	</div> <!-- .ru-eef-my-event-info -->

</div> <!-- .ru-eef-my-event-single -->
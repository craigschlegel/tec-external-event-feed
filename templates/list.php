<?php

?>
<div class="ru-eef-my-events" style="visibility: hidden;">
	<?php

	foreach ( $filtered_events as $event ) {
		include $single_template;
	}

	?>
</div>

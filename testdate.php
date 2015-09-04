<?php
	$todaysdate = date('Y-m-d H:i:s');
	$date = new DateTime($todaysdate);
	$date->modify("+7 day");
	echo $date->format("Y-m-d H:i:s");
?>
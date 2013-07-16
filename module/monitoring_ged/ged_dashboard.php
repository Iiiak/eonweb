<?php
/*
#########################################
#
# Copyright (C) 2013 EyesOfNetwork Team
# DEV NAME : Jean-Philippe LEVY
# VERSION 4.0
# APPLICATION : eonweb for eyesofnetwork project
#
# LICENCE :
# This program is free software; you can redistribute it and/or
# modify it under the terms of the GNU General Public License
# as published by the Free Software Foundation; either version 2
# of the License, or (at your option) any later version.
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
#########################################
*/
?>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
	<?php include("../../include/include_module.php"); ?>
	<?php include("../../include/report.php"); ?>
	<meta http-equiv=refresh content='<?php echo $refresh_time?>;URL=ged_dashboard.php' />
</head>
<body id="main">

<h1><?php echo $xmlmodules->getElementsByTagName("report_event")->item(0)->getAttribute("title")?></h1>
<div id="ged_messages" align="right">
        <i>screen refresh every <?php echo $refresh_time?> seconds</i>
        <div id="ged_filter" <?php if($_COOKIE["user_limitation"]==1) echo "style=\"display:none;\""; ?>>
        </div>
</div>

<?php
# --- Languages
$sla=$xmlmodules->getElementsByTagName("report_event")->item(0);
?>

<h1><?php echo $sla->getAttribute("active")?></h1><center>
<a href="/module/monitoring_ged/ged.php?q=active"><?php echo show_report("report_active_events_pie_by_state.xml",0,NULL,NULL,"active",false,true,true)?></a>
<br><br>

<a href="/module/monitoring_ged/ged.php?q=active"><?php echo show_report("report_active_events_by_group.xml",0,NULL,NULL,"active",false,true,true)?></a>
</center><br><br>

</body>
</html>

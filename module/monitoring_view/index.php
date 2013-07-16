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
<meta http-equiv=refresh content='<?php echo $refresh_time?>;URL=index.php' />
</head>

<body id="main">
<?php
// # Get language module
$dashboard = $xmlmodules->getElementsByTagName("dashboard");
?>
<h1><?php echo $dashboard->item(0)->getAttribute("title")?></h1>
<div id="ged_messages" align="right">
        <i>screen refresh every <?php echo $refresh_time?> seconds</i>
</div>
<br>

<center>
<table style="border:0px;margin:0;">
<tr> 
	<td align="center" style="border:0px;">
	<a href='<?php echo $path_nagios_cgi?>/status.cgi?hostgroup=all&style=hostdetail&hoststatustypes=12'><?php show_report("monitoring_view_pie_host.xml",0,NULL,NULL); ?></a>
	</td>
	<td align="center" style="border:0px;">
	<a href='<?php echo $path_nagios_cgi?>/status.cgi?host=all&servicestatustypes=28'><?php show_report("monitoring_view_pie_service.xml",0,NULL,NULL); ?></a>	
	</td>
</tr>
<tr>
	<td align="center" style="border:0px;">
	<a href='/module/monitoring_ged/ged.php?q=active'><?php show_report("monitoring_view_pie_event.xml",0,NULL,NULL); ?></a>
	</td>
	<td align="center" style="border:0px;">
	<a href='/module/monitoring_ged/ged.php?q=active'><?php show_report("monitoring_view_bar_event.xml",0,NULL,NULL); ?></a>
	</td>
</tr>
</table>
</center>

</body>
</html>

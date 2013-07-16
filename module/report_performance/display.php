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
</head>
<body id="main">
<h1> Performance report for ALL Host</h1>

<?php
	# --- TIMESPAN from now
        if(isset($_GET['date'])) $date = $_GET['date'];
        if(isset($_GET['title'])) $title = $_GET['title'];
        else message(0,"Could not get date value","critical");
        $end_date = time();

        switch($date)
        {
                case "today":
                        $start_date = $end_date - 12*60*60;
                        break;
                case "lastday":
                        $start_date = $end_date - 24*60*60;
                        break;
                case "lastweek":
                        $start_date = $end_date - 7*24*60*60;
                        break;
                case "last2week":
                        $start_date = $end_date - 2*7*24*60*60;
                        break;
                case "lastmonth":
                        $start_date = strtotime("-1 month");
                        break;
                case "last2month":
                        $start_date = strtotime("-2 months");
                        break;
                case "lastyear":
                        $start_date = strtotime("-1 year");
                        break;
                default:
                        $start_date = $end_date - 24*60*60;
        }

	if($title!="")
		 echo "<h2> Title : $title </h2><br>";

        echo "<h2> Periode :</h2>";
        echo "from <i>" . date("l d M Y - h:i A",intval($start_date)) . "</i> to <i>" .  date("l d M Y - h:i A",intval($end_date)) . "</i><br><br>";
	
	# --- For each host
	$result_host=sqlrequest($database_cacti,"select id,hostname from host");
	$nbr_ligne_host = mysql_num_rows($result_host);
	if($nbr_ligne_host == 0) message(0,"No host find in database","critical");
	for($j=0;$j<$nbr_ligne_host;$j++)
	{
		# -- Get the infos
		$hostname=mysql_result($result_host,$j,"hostname");
		$hostid=mysql_result($result_host,$j,"id");
		
		# --- Get the graph id from the host id
	        $result_graph=  sqlrequest($database_cacti,"SELECT graph_local.id FROM graph_local,graph_templates_graph WHERE host_id='$hostid' and graph_templates_graph.local_graph_id=graph_local.id and graph_templates_graph.title like '%$title%' ");
	        $nbr_ligne_graph = mysql_num_rows($result_graph);

		# --- Display info
		if($nbr_ligne_graph != 0) {
        		echo "<br><br><h1>$hostname</h1>";

			# --- For each graph of the host
			echo "<center>";
	        	for ($i=0;$i<$nbr_ligne_graph;$i++)
		        {	
		        	# --- Print the graph
	            		$graph_id = mysql_result($result_graph,$i,"id");
	            		echo "<img src='../../cacti/graph_image.php?local_graph_id=$graph_id&rra_id=1&graph_height=100&graph_width=300&graph_nolegend=true&graph_start=$start_date&graph_end=$end_date' border='0'>&nbsp";
	        	}
			echo "</center>";
		}
	}
?>
</body>
</html>

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
	<script type="text/javascript" src="/js/jquery.js"></script>
	<script type="text/javascript" src="/js/jquery.autocomplete.js"></script>
	<?php include("../../include/report.php"); ?>
</head>
<body id="main">

<h1><?php echo $xmlmodules->getElementsByTagName("report_event")->item(0)->getAttribute("title")?></h1><br>

<?php

# --- Type of report (events, sla, ...)
if(isset($_GET["type"]))
        $type="?type=".$_GET["type"];
else
        $type="";

# --- Languages
$sla=$xmlmodules->getElementsByTagName("report_event")->item(0);

# --- If Display
if(isset($_POST["display"])) {

    	# --- Search filters
        if(isset($_POST["value"])){
                if($_POST["value"]!=""){
                        $myfilter["field"]=$_POST["field"];
                        $myfilter["value"]=$_POST["value"];
			echo "<h2>".$myfilter["field"]." : ".$myfilter["value"]."</h2><br>";
                }
                else
                        $myfilter=false;
        }
        else
                $myfilter=false;

	# --- Display reports
	if($type!="") {

                echo "<h1>".$sla->getAttribute("sla")."</h1><center>";
                show_report("report_history_events_pie_sla.xml",0,NULL,NULL,"history",$myfilter);
                show_report("report_history_events_bar_sla.xml",0,NULL,NULL,"history",$myfilter);
                echo "</center><br><br>";

		if(isset($_POST["by_day"])) {
        	        echo "<h1>".$sla->getElementsByTagName("by_day")->item(0)->nodeValue."</h1>";
			echo "<i>".date("d/m/Y H:i:s",strtotime("- 1 day"))." - ".date("d/m/Y H:i:s",time())."</i><center>";
	                show_report("report_history_events_pie_sla.xml",1,strtotime("- 1 day"),time(),"history",$myfilter);
	                //show_report("report_history_events_bar_sla.xml",1,strtotime("- 1 day"),time(),"history",$myfilter);
	                echo "</center><br><br>";
		}

		if(isset($_POST["by_week"])) {
	                echo "<h1>".$sla->getElementsByTagName("by_week")->item(0)->nodeValue."</h1>";
			echo "<i>".date("d/m/Y H:i:s",strtotime("- 1 week"))." - ".date("d/m/Y H:i:s",time())."</i><center>";
	                show_report("report_history_events_pie_sla.xml",2,strtotime("- 1 week"),time(),"history",$myfilter);
	                //show_report("report_history_events_bar_sla.xml",2,strtotime("- 1 week"),time(),"history",$myfilter);
	                echo "</center><br><br>";
		}

		if(isset($_POST["by_month"])) {
	                echo "<h1>".$sla->getElementsByTagName("by_month")->item(0)->nodeValue."</h1>";
			echo "<i>".date("d/m/Y H:i:s",strtotime("- 1 month"))." - ".date("d/m/Y H:i:s",time())."</i><center>";
	                show_report("report_history_events_pie_sla.xml",3,strtotime("- 1 month"),time(),"history",$myfilter);
	                //show_report("report_history_events_bar_sla.xml",3,strtotime("- 1 month"),time(),"history",$myfilter);
	                echo "</center><br><br>";
		}

		if(isset($_POST["by_year"])) {
        	        echo "<h1>".$sla->getElementsByTagName("by_year")->item(0)->nodeValue."</h1>";
			echo "<i>".date("d/m/Y H:i:s",strtotime("- 1 year"))." - ".date("d/m/Y H:i:s",time())."</i><center>";
	                show_report("report_history_events_pie_sla.xml",4,strtotime("- 1 year"),time(),"history",$myfilter);
	                //show_report("report_history_events_bar_sla.xml",4,strtotime("- 1 week"),time(),NULL,"history",$myfilter);
	                echo "</center><br><br>";
		}
	}
	else {
	        echo "<h1>".$sla->getAttribute("active")."</h1><center>";
	        show_report("report_active_events_pie_by_state.xml",0,NULL,NULL,"active",$myfilter);
	        show_report("report_active_events_by_group.xml",0,NULL,NULL,"active",$myfilter);
	        echo "</center><br><br>";

	        echo "<h1>".$sla->getAttribute("history")."</h1><center>";
	        show_report("report_history_events_pie_by_state.xml",0,NULL,NULL,"history",$myfilter);
	        show_report("report_history_events_by_group.xml",0,NULL,NULL,"history",$myfilter);
	        echo "</center><br><br>";

                if(isset($_POST["by_day"])) {
                        echo "<h1>".$sla->getElementsByTagName("by_day")->item(0)->nodeValue."</h1>";
                        echo "<i>".date("d/m/Y H:i:s",strtotime("- 1 day"))." - ".date("d/m/Y H:i:s",time())."</i><center>";
                        show_report("report_history_events_pie_by_state.xml",1,strtotime("- 1 day"),time(),"history",$myfilter);
                        echo "</center><br><br>";
                }

                if(isset($_POST["by_week"])) {
                        echo "<h1>".$sla->getElementsByTagName("by_week")->item(0)->nodeValue."</h1>";
                        echo "<i>".date("d/m/Y H:i:s",strtotime("- 1 week"))." - ".date("d/m/Y H:i:s",time())."</i><center>";
                        show_report("report_history_events_pie_by_state.xml",2,strtotime("- 1 week"),time(),"history",$myfilter);
                        echo "</center><br><br>";
                }

                if(isset($_POST["by_month"])) {
                        echo "<h1>".$sla->getElementsByTagName("by_month")->item(0)->nodeValue."</h1>";
                        echo "<i>".date("d/m/Y H:i:s",strtotime("- 1 month"))." - ".date("d/m/Y H:i:s",time())."</i><center>";
                        show_report("report_history_events_pie_by_state.xml",3,strtotime("- 1 month"),time(),"history",$myfilter);
                        echo "</center><br><br>";
                }

                if(isset($_POST["by_year"])) {
                        echo "<h1>".$sla->getElementsByTagName("by_year")->item(0)->nodeValue."</h1>";
                        echo "<i>".date("d/m/Y H:i:s",strtotime("- 1 year"))." - ".date("d/m/Y H:i:s",time())."</i><center>";
                        show_report("report_history_events_pie_by_state.xml",4,strtotime("- 1 year"),time(),"history",$myfilter);
                        echo "</center><br><br>";
                }
	}

}
else {
?>

<div id="search">
<form action='index.php<?php echo $type?>' method='post'>
        <h2>Define your report :</h2>

	<br>

        <select id="field" name="field" onchange="$('#value').focus();">
        <?php
        for($i=0;$i<count($array_ged_filters);$i++)
                echo "<option>$array_ged_filters[$i]</option>";
        ?>
        </select>

        <input id="value" name="value" class="value" type="text" autocomplete="off" onFocus='$(this).autocomplete(<?php echo get_host_list_from_nagios();?>)' />

	| <?php echo $sla->getElementsByTagName("by_day")->item(0)->nodeValue?> <input type="checkbox" name="by_day" class="checkbox">
	| <?php echo $sla->getElementsByTagName("by_week")->item(0)->nodeValue?> <input type="checkbox" name="by_week" class="checkbox"> 
	| <?php echo $sla->getElementsByTagName("by_month")->item(0)->nodeValue?> <input type="checkbox" name="by_month" class="checkbox">
	| <?php echo $sla->getElementsByTagName("by_year")->item(0)->nodeValue?> <input type="checkbox" name="by_year" class="checkbox"> |

	<input class="button" type="submit" value="Display" name="display"></input>
<form>
</div>

<?php } ?>

</body>
</html>

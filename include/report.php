<?php
/*
#########################################
#
# Copyright (C) 2010 EyesOfNetwork Team
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

// include ezGraph autoload
require_once ("$path_ez/autoload.php");

// eonweb palette
class eonwebPalette extends ezcGraphPalette
{
	protected $axisColor = '#000000';
	protected $majorGridColor = '#000000BB';
 	protected $dataSetColor = array();
	protected $dataSetSymbol = array(
		ezcGraph::NO_SYMBOL,	
	);
	protected $fontName = 'sans-serif';
	protected $fontColor = '#555753';

        function setColor($color){
                $this->dataSetColor=$color;
        }

	function setSymbol($symbol){
                $this->dataSetSymbol=$symbol;
	}
}

// Display IMG function
function display_img($graph,$name,$surname,$show,$x=450,$y=200)
{
	//global value
	global $path_eonweb;
	global $dir_imgcache;

	// Create the image file
	$user=$_COOKIE['user_name'];
	$img="$path_eonweb/$dir_imgcache/$user-$name$surname";
	$pnglink="/cache/$user-$name$surname.png";
        $graph->render($x, $y, "$img.svg");

        exec("/usr/bin/rsvg '$img.svg' '$img.png'");
 	unlink("$img.svg");

	if($show)
		echo "<img src='$pnglink' style='margin:10px;'>";
}

// Graph PIE
function show_graph_pie($name,$surname,$data,$color,$graph_title,$show)
{
	global $path_eonweb;
	global $dir_imgcache;

	$graph = new ezcGraphPieChart();
	$graph->palette = new eonwebPalette();
	$graph->palette->setColor($color);
	$graph->palette->setSymbol(array(
		ezcGraph::BULLET,
	));
	$graph->title = $graph_title;
	$graph->options->label = '%2$d (%3$.1f%%)';
	$graph->data[$graph_title] = new ezcGraphArrayDataSet($data);
	$graph->renderer = new ezcGraphRenderer3d();
	$graph->renderer->options->moveOut = .2;
	$graph->renderer->options->pieChartOffset = 63;
	$graph->renderer->options->pieChartGleam = .3;
	$graph->renderer->options->pieChartGleamColor = '#FFFFFF';
//	$graph->renderer->options->pieChartShadowSize = 5;
//	$graph->renderer->options->pieChartShadowColor = '#000000';
	$graph->renderer->options->legendSymbolGleam = .5;
	$graph->renderer->options->legendSymbolGleamSize = .9;
	$graph->renderer->options->legendSymbolGleamColor = '#FFFFFF';
	$graph->renderer->options->pieChartSymbolColor = '#55575388';
	$graph->renderer->options->pieChartHeight = 5;
	$graph->renderer->options->pieChartRotation = .8;

	display_img($graph,$name,$surname,$show);
}

// Graph BAR EVENT
function show_graph_bar($name,$surname,$data,$color,$graph_title,$show)
{
	$graph = new ezcGraphBarChart();
	$graph->palette = new eonwebPalette();
        $graph->palette->setColor($color);
	$graph->title = $graph_title;

	foreach($data as $col => $val){
		$graph->data[$col] = new ezcGraphArrayDataSet($val); 	
	}

	$graph->renderer = new ezcGraphRenderer3d(); 
	$graph->legend = false;  
	display_img($graph,$name,$surname,$show,300,200);
}

// Get Report INFORMATIONS
function show_report($file,$surname,$start_date,$end_date,$event_state="active",$myfilter=false,$show=true,$filter=false)
{
	// Get global value
	global $path_reports;
	global $path_ged_bin;
	global $xmlmodules;
	global $database_host;
	global $database_username;
	global $database_password;
	global $database_ged;

	// Define the path file
	$pathfile="$path_reports/$file";

	// Test if report file exist
	if (!file_exists($pathfile)) message(0,"The XML file doesn't exist : $pathfile","critical");

	// Define the object
	$xml = simplexml_load_file($pathfile);

	// Parse XML File
	foreach($xml->zone as $zone) {
		
		// Define Array
		$data=array();
		$color=array();

		// Get the Value	
		foreach($zone->value as $value)
		{
			switch($value->source)
			{
				case "system" :
					// COMMAND RETURN : One number
					// exec the system command
					$result=false;
					exec("$value->get_value",$result);
	
	                                // Put the value into arrays
	                                $data["$value->legend"] = $result[0];
	                                $color = array_merge($color,array((string)$value->color));
					break;

				case "ged":
                                        # --- original request
                                        #
                                        // dates ranges
                                        if(isset($start_date) && isset($end_date))
                                                $request_where=$request_where." and o_sec >= $start_date and o_sec < $end_date";
					else
						$request_where="";

                                        // XML filters global options
					$file="../../cache/".$_COOKIE["user_name"]."-ged.xml";
                                        if(file_exists($file) && $filter==true){
                                                $xmlfilters = new DOMDocument("1.0","UTF-8");
                                                $xmlfilters->load($file);
                                                $g=$xmlfilters->getElementsByTagName("ged")->item(0);

                                                //Default filter detection
                                                $default=$g->getElementsByTagName("default")->item(0)->nodeValue;

                                                if($default!=""){
                                                        $xpath = new DOMXPath($xmlfilters);
                                                        $g_filters = $xpath->query("//ged/filters[@name='$default']/filter");
                                                        $or=0;

                                                        foreach($g_filters as $g_filter){
                                                          $or++;
                                                          if($or>1)
                                                            $request_filter=$request_filter." or (".$g_filter->getAttribute("name")." like '%".($g_filter->nodeValue)."%')";
                                                          else
                                                            $request_filter=" and ((".$g_filter->getAttribute("name")." like '%".($g_filter->nodeValue)."%')";
                                                        }
							if($or>0)	
                                                          $request_where=$request_where.$request_filter.")";
                                                }
                                        }

                                        // if filter search is set
                                        if($myfilter)
                                                $request_where=$request_where." and ".$myfilter['field']." like '".$myfilter['value']."'";

					// loop on each ged packet type
					$connect=mysql_connect($database_host,$database_username,$database_password);
					mysql_select_db($database_ged,$connect);	
					$result=mysql_query("select pkt_type_name from pkt_type where pkt_type_id!='0' AND pkt_type_id<'100';",$connect);
					$nbr=0;
					while($i=mysql_fetch_row($result)){
	                                  // for bar graphs
                                          if("$zone->display_type"=="bar")
                                          {
                                                $diffold=time();
                                                foreach($zone->int as $int)
                                                {
                                                   $diff=strtotime("$int->time");
			                                             $request="select count(id) from ".$i[0]."_queue_".$event_state." where state='".$value->get_value."' and queue='".substr($event_state{0},0,1)."' and o_sec >= $diff and o_sec < $diffold ".$request_where.";";
                                                   $diffold=$diff;
						                                       $count=mysql_query($request,$connect);
                                                	 $count=mysql_fetch_array($count);
                                                   $data["$value->legend"]["$int->name"] += $count[0];
                                                }
						$request="select count(id) from ".$i[0]."_queue_".$event_state." where state='".$value->get_value."' and queue='".substr($event_state{0},0,1)."' and o_sec < $diff ".$request_where.";";
						$count=mysql_query($request,$connect);
                                                $count=mysql_fetch_array($count);
                                                $data["$value->legend"]["more"] += $count[0];
                                          }
					  // for pie graph
                                          else {
                                                $request="select count(id) from ".$i[0]."_queue_".$event_state." where state='".$value->get_value."' and queue='".substr($event_state{0},0,1)."'".$request_where.";";
						$count=mysql_query($request,$connect);
                                        	$count=mysql_fetch_array($count);
	                                	$data["$value->legend"] += $count[0];
					  }
					}
					mysql_close($connect);
					$color = array_merge($color,array((string)$value->color));
					break;

				case "sla":
					# --- original request
					#
					// do not select OK states
                                        $request_where=" and state!='0'";
					
					// dates ranges
                                        if(isset($start_date) && isset($end_date))
						$request_where=$request_where." and o_sec >= $start_date and o_sec < $end_date";

                                        // if filter search is set
                                        if($myfilter)
                                                $request_where=$request_where." and ".$myfilter['field']." like '".$myfilter['value']."'";

                                        // for bar graphs
                                        if(isset($durationold))
                                                $durationold=$duration;
                                        else
                                                $durationold=0;

                                        $duration="$value->get_value";

                                        // loop on each ged packet type
                                        $connect=mysql_connect($database_host,$database_username,$database_password);
                                        mysql_select_db($database_ged,$connect);
                                        $result=mysql_query("select pkt_type_name from pkt_type where pkt_type_id!='0' AND pkt_type_id<'100';",$connect);
                                        $nbr=0;
                                        while($i=mysql_fetch_row($result)){
                                          // for bar graphs
                                          if("$zone->display_type"=="bar")
                                          {
                                                $diffold=time();
                                                foreach($zone->int as $int)
                                                {
                                                        $diff=strtotime("$int->time");
                                                        $request="select count(id) from ".$i[0]."_queue_".$event_state." where queue='".substr($event_state{0},0,1)."' and o_sec >= $diff and o_sec < $diffold and a_sec-o_sec < $duration and a_sec-o_sec >= $durationold ".$request_where.";";
                                                        $diffold=$diff;
                                                        $count=mysql_query($request,$connect);
                                                        $count=mysql_fetch_array($count);
                                                        $data["$value->legend"]["$int->name"] += $count[0];
                                                }
                                                $request="select count(id) from ".$i[0]."_queue_".$event_state." where queue='".substr($event_state{0},0,1)."' and o_sec < $diff and a_sec-o_sec < $duration and a_sec-o_sec >= $durationold ".$request_where.";";
                                                $count=mysql_query($request,$connect);
                                                $count=mysql_fetch_array($count);
                                                $data["$value->legend"]["more"] += $count[0];
                                          }
                                          // for pie graph
                                          else {
                                                $request="select count(id) from ".$i[0]."_queue_".$event_state." where queue='".substr($event_state{0},0,1)."' and a_sec-o_sec < $duration and a_sec-o_sec >= $durationold ".$request_where.";";
                                                $count=mysql_query($request,$connect);
                                                $count=mysql_fetch_array($count);
                                                $data["$value->legend"] += $count[0];
                                          }
                                        }
                                        mysql_close($connect);
                                        $color = array_merge($color,array((string)$value->color));
                                        break;

				default:
					message(0," Could not source type in xml file : $pathfile","critical");
			}
		}

                // Languages
                $graphs = $xmlmodules->getElementsByTagName("graphs");
                $title = $graphs->item(0)->getElementsByTagName($zone->display_title);

		// test if there is no data in array	
		if($data == array())
		{
			message(9,"No data to display for graph $title","warning");
		}
		else
		{
			// Languages
			$xpath = new DOMXPath($xmlmodules);
                        $menutabs = $xpath->query("//graphs/".$zone->display_title);
			$title = $menutabs->item(0)->getAttribute("title");

			// Display the DATA
			switch($zone->display_type)
			{
                        	case "pie" :
					// Add all the data to test for the pie
                			$test=0;
			                foreach($data as $val)
                			{
                        			$test=$test+$val;
                			}
					if($test==0){
						$data=false;
						$data["nothing"]=1;
						$color[0]="#00EE00";
					}
					show_graph_pie($zone->display_title,$surname,$data,$color,$title,$show);
					break;
				case "bar" :
					show_graph_bar($zone->display_title,$surname,$data,$color,$title,$show);
					break;
				default :
					message(0," Could not get data area type in xml file : $pathfile","critical");
			}
		}
	}
}

?>

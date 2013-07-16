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

// Display Error Message 
function message($id, $text,$type){

	// Get the global value
	global $array_msg;
	$tempid=$array_msg[$id];

        // Display the message
	echo "<ul class='ul'>";
	switch($type)
	{
		case "critical":
		        echo "<li class='msg_title'>Message EON - $id </li>";
		        echo "<li class='msg'> $tempid $text</li>";
		        echo "</ul></body></html>";
		        die();
			break;
		case "warning":
       		 	echo "<li class='msg_title_warning'> $tempid $text</li>";
       		 	echo "</ul>";
			break;
   		case "ok":
	                echo "<li class='msg_title_success'> $tempid $text</li>";
	                echo "</ul>";
			break;
		default:
                        echo "<li class='msg_title_success'> $tempid $text</li>";
                        echo "</ul>";
			break;			
	}
}


// Connect to Database
function sqlrequest($database,$sql,$id=false){

	// Get the global value
	global $database_host;
	global $database_username;
	global $database_password;

	$connexion = mysql_connect($database_host, $database_username, $database_password);
	if (!$connexion) {
		echo "<ul>";
		echo "<li class='msg_title'>Alert EyesOfNetwork - Message EON-database connect</li>";
		echo "<li class='msg'> Could not connect to database : $database ($database_host)</li>";
		echo "</ul>";
		exit(1);
	}

	$db = mysql_select_db($database, $connexion);
	if (!$db){
		echo "<ul>";
		echo "<li class='msg_title'>Alert EyesOfNetwork - Message EON-database </li>";
		echo "<li class='msg'> Could not open database : $database ($database_host)</li>";
		echo "</ul>";
		exit(1);
	}

	$result=mysql_query("$sql",$connexion);

	if($id==true)
		$result=mysql_insert_id();
		
	mysql_close($connexion);
	return $result;
}

// Display array value
function display_value($value, $key) {
	echo "$value\n";
}

// Function Edit and Modify a file
function filemodify($path,$get=false) {
	if(is_writable($path)) {
	
		// Test If Update or Display.
		if (isset($_POST['maj'])) {
			if (!$fconf = fopen($path, "w")) message(2,$path,"critical");
			// Write the change
			if (fwrite ($fconf, str_replace("\r\n", "\n", $_POST['maj'])) === FALSE) message(3,$path,"critical");
			else { 
				message(6," : File updated","ok");
				echo "<br>";
			}
			fclose ($fconf);
			if (!$fconf = fopen($path, "r")) message(2,$path,"critical");
		}
		else if (!$fconf = fopen($path, "r")) message(2,$path,"critical");

		// Display the Text Area and button
		echo "<form method='post' action='./index.php";
		if($get)
			echo "?file=$get";
		echo "'>";
		echo "<textarea cols='100' rows='25' name='maj' scrolling='no'>";
			print file_get_contents($path);
		echo "</textarea><br>";
		echo "<input class='button' type='submit' value='Update'>";
		echo "</form>";
		fclose ($fconf);
	}
	else message(3,$path,"critical");
}

// Host List form Nagios
function get_host_list_from_nagios($field=false) {
	global $database_lilac;
	$hosts=array();

	if($field){
		$request="SELECT name FROM nagios_$field ORDER BY name";
	}
	else {
		$request="SELECT name FROM nagios_host
		UNION SELECT name from nagios_hostgroup
		UNION SELECT name from nagios_service_group
		ORDER BY name";
	}

	$result=sqlrequest($database_lilac,$request);
 	while ($line = mysql_fetch_array($result)){ 
		$hosts[]=$line[0];
	}
	echo json_encode($hosts);
}

//Host and Address list from nagios. //TODO send the adress
function get_host_list() {
	global $database_lilac;
	$hosts=array();

	$result=sqlrequest($database_lilac,"SELECT name,address FROM nagios_host ORDER BY name");

 	while ($line = mysql_fetch_array($result)){ 
		$hosts[]=$line[0];
		$hosts[]=$line[1];
	}
	echo json_encode($hosts);
}

function get_host_listbox_from_nagios(){
	global $database_lilac;
	
	echo "<h2>host : </h2>";
	$result=sqlrequest($database_lilac,"SELECT name,address FROM nagios_host ORDER BY name");
	$mapage='toto';
	echo "<SELECT name='host_list' class='select' size=10 style='width:250px;'>";
	while ($line = mysql_fetch_array($result))
	{
		echo "<OPTION value='$line[0],$line[1]'>&nbsp;$line[0]</OPTION>";
	}
	print "</SELECT><br>";

}

// Host list from CACTI
function get_title_list_from_cacti() {
        global $database_cacti;
        $titles=array();
        $request="SELECT DISTINCT graph_templates_graph.title FROM graph_local,graph_templates_graph WHERE graph_templates_graph.local_graph_id=graph_local.id ORDER BY title";
        $result=sqlrequest($database_cacti,$request);
        while ($line = mysql_fetch_array($result)){
		$line[0]=str_replace("|host_description| - ","",$line[0]);
                $titles[]=$line[0];
        }
        echo json_encode($titles);
}

function get_host_listbox_from_cacti(){
        
	global $database_cacti;

	$result=sqlrequest($database_cacti,"SELECT DISTINCT host.id,hostname,description FROM host INNER JOIN graph_local ON host.id = graph_local.host_id ORDER BY hostname ASC");
        print "<SELECT name='host' class='select' size=15 style='width:250px;'>";
        while ($line = mysql_fetch_array($result))
        {
                print "<OPTION value='$line[0]'>&nbsp;$line[1] ($line[2])&nbsp;</OPTION>";
        }
        print "</SELECT><br>";
}


// system function : CUT
function cut($string, $width, $padding = "...") {
    return (strlen($string) > $width ? substr($string, 0, $width-strlen($padding)).$padding : $string);
} 

// Get graph from CACTI
function get_graph_listbox_from_cacti(){
	
	global $database_cacti;
	
        $result=sqlrequest($database_cacti,"SELECT DISTINCT graph_templates.id,name FROM graph_templates INNER JOIN graph_local ON graph_local.graph_template_id = graph_templates.id ORDER BY name ASC");
        print "<SELECT name='graph' class='select' size=15 style='width:250px;'>";
        while ($line = mysql_fetch_array($result))
        {
		print "<OPTION value='$line[0]'>&nbsp;$line[1]&nbsp;</OPTION>\n";
        }
        print "</SELECT><br>";
}

// Display TOOL list
function get_tool_listbox(){
	echo "<h2>tool : </h2>";	
	// Get the global table
	global $array_tools;

        // Get the first array key
        reset($array_tools);

	// Display the list of tool
	echo "<SELECT name='tool_list' class='select' size=4 style='width:250px;'>";
 	while (list($tool_name, $tool_url) = each($array_tools)) 
	{
		echo "<OPTION value='$tool_url'>&nbsp;$tool_name</OPTION>";
        }
	echo "</SELECT><br>";
}

// Display min and max port value for show port tool
function get_toolport_ports(){
	global $default_minport;
	global $default_maxport;

	echo "<h2>port min - port max</h2>";
	echo "(show port only) :<br>";
	echo "<input type=text name='min_port' value=$default_minport size='8'> - <input type=text name='max_port' value=$default_maxport size=8 >";
}

// Display User list
function get_user_listbox(){
	echo "<h2>Select user : </h2>";
        global $database_eonweb;

        $result=sqlrequest($database_eonweb,"SELECT DISTINCT user_name,user_id,group_id,user_descr FROM users ORDER BY user_name");
        print "<SELECT name='users_list' class='select' size=15>";
        while ($line = mysql_fetch_array($result))
        {
                print "<OPTION value='$line[1]'>$line[0] : $line[3]</OPTION>";
        }
        print "</SELECT>";
}

// Retrive form data
function retrieve_form_data($field_name,$default_value)
{
	if (!isset ($_GET[$field_name]))
		if (!isset ($_POST[$field_name]))
			return $default_value;
		else
			return $_POST[$field_name];	
	else 
		return $_GET[$field_name];
}

// Delete eccents
function stripAccents($str, $charset='utf-8')
{
    $str = htmlentities($str, ENT_NOQUOTES, $charset);

    $str = preg_replace('#\&([A-za-z])(?:acute|cedil|circ|grave|ring|tilde|uml)\;#', '\1', $str);
    $str = preg_replace('#\&([A-za-z]{2})(?:lig)\;#', '\1', $str); 
    $str = preg_replace('#\&[^;]+\;#', '', $str); 

    return $str;
}

// Add Logs
function logging($module,$command,$user=false)
{
	global $database_eonweb;
	global $dateformat;
	if($user)
		sqlrequest($database_eonweb,"insert into logs values ('','".time()."','$user','$module','$command','".$_SERVER["REMOTE_ADDR"]."');");
	else
		sqlrequest($database_eonweb,"insert into logs values ('','".time()."','".$_COOKIE['user_name']."','$module','$command','".$_SERVER["REMOTE_ADDR"]."');");
}


// Time
function getmtime()
{
  
    $temps = microtime();
    $temps = explode(' ', $temps);
    return $temps[1] + $temps[0];
 
}

//Get the informations of nagios' config's file.
function getBpProcess() {
	
	global $path_nagiosbpcfg ;
	global $path_nagiosbpcfg_lock ;

	wait($path_nagiosbpcfg_lock);	//Wait for the file to not be in use.
	$fp=@fopen($path_nagiosbpcfg_lock,"w");	//Lock the file
    fputs($fp,getmypid());
    fclose($fp);

    $tabProcess = array() ;
	$lines = file($path_nagiosbpcfg);
	if (!$lines) {
		unlink($path_nagiosbpcfg_lock);	//Unlock the file
		message(2,$path_nagiosbpcfg,"critical");
	}
	foreach( $lines as $line) {
		
		if ( trim($line) == "# Fin def") {	//End of definition
			$tabProcess[] = $tabProp ;
			$tabProp = null ;
		}
		elseif ( preg_match("/^# (ET|OU|MIN)$/", $line, $match)) {
			$tabProp['type'] = $match[1];	//Get the type of the process
		}
		elseif ( preg_match("/^display (\d)*/", $line, $match)) {	//Get the prirority
			$tabProp['prio'] = $match[1] ;
			$tab = explode(";",$line);
			$tabProp['pnom'] = $tab[2];
		}
		elseif ( strpos($line,"info_url") !== false) {	//Get the link
			$tab = explode(";", $line);
			$tabProp['url'] = $tab[count($tab)-1] ;
		}
		elseif ( strpos($line,"external_info") !== false) {	//Get the command
			$tab = explode(";", $line);
			$tabProp['cmd'] = $tab[count($tab)-1] ;
		}
		elseif ( strpos($line,"=") !== false) {	//Get the name, the minimun, and the services
			$tab = explode("=", $line);
			$tabProp['nom'] = trim($tab[0]);
			if ($tabProp['type'] == "MIN") {
				$tabProp['min'] = (int)trim($tab[1]);
				$tab = explode(":",$tab[1]);
				$tabProp['serv'] = $tab[1];
			}
			else $tabProp['serv'] = $tab[1];
		}
	}

	unlink($path_nagiosbpcfg_lock);	//Unlock the file
	return $tabProcess ;
}

//Wait the end of modification of a file
function wait($file){
	$retry = 0 ;

	while (file_exists($file)){
		if($retry>20) { die ("$file is already in use!"); }
        $retry++;
        sleep(1);
	}
}

//Insert a value in an array
function array_push_after($src,$in,$pos){
    if(is_int($pos)) $R=array_merge(array_slice($src,0,$pos+1), $in, array_slice($src,$pos+1));
    else{
        foreach($src as $k=>$v){
            $R[$k]=$v;
            if($k==$pos)$R=array_merge($R,$in);
        }
    }return $R;
}

//Format the nagios.conf file
function formatFile(){
	global $path_nagiosbpcfg;
	global $path_nagiosbpcfg_lock;
	global $database_nagios;

	wait($path_nagiosbpcfg_lock);	//Wait for the file to not be in use.
	$fp=@fopen($path_nagiosbpcfg_lock,"w");	//Lock the file.
	fputs($fp,getmypid());
	fclose($fp);

	$lines = file($path_nagiosbpcfg);
	$file[0] = "# Checked\n";
	if ( empty($lines) || trim($lines[0]) != "# Checked"){	//Not checked. Let's read it !

		write_file($path_nagiosbpcfg,array_merge($file,$lines),"w"," : File updated");

		sqlrequest($database_nagios,"DELETE FROM bp");
		sqlrequest($database_nagios,"DELETE FROM bp_services");
		sqlrequest($database_nagios,"DELETE FROM bp_links");
		$tabName = array();
		$tabDef = array();			

		foreach($lines as $i => $line){
			if ($line[0] == "#"){
				unset($lines[$i]); continue;	//A commented line. Delete.
			}
			if (($posComment = strpos($line,"#")) !== false){	//Found a commentary. Delete.
				$line = substr($line,0,$posComment);	
			}
			//No more commentary in the file

			if (strpos($line,"=") !== false){	//Found a name
				$tab = explode("=",$line);
				$tabName[] = trim($tab[0]);	//Keep the name
				$vals = explode("=",$line);
				$tabDef[] = $vals[1];	//Keep the whole line
				unset($lines[$i]);
			}
		}

		//There we got all the names.
		$serv = null;
		foreach($tabName as $i => $name){
			$type = $prio = $url = $cmd = $val = $desc = "" ;
			//Try to get the type. Default ET
			if ( strpos($tabDef[$i], ":")){
				$vals = explode("of :",$tabDef[$i]);
				$val = trim($vals[0]);
				$type = "MIN";
				$serv = $vals[1];
			}
			else {
				if ( strpos($tabDef[$i], "&")) $type = "ET";
				elseif ( strpos($tabDef[$i], "|")) $type = "OU";
				else $type = "ET";
				$serv = $tabDef[$i];
			}
			
			foreach($lines as $j=>$line){
				if (strpos($line,"$name;") !== false){	//We found a name
					if ( preg_match("/^display (\d)+/", $line,$match)){
						$prio = $match[1];
						$vals = explode(";",$line);
						$desc = trim($vals[2]);
					} 
					elseif ( strpos($line,"info_url") !== false) {
						$vals = explode(";",$line);
						$url = trim($vals[1]);
					}
					elseif ( strpos($line,"external_info") !== false) {
						$vals = explode(";",$line);
						$cmd= trim($vals[1]);
					}
					unset($lines[$j]);
				}
			}

			if ($prio == "" ) $prio = "null";
			sqlrequest($database_nagios,"INSERT INTO bp VALUES('$name','$desc','$prio','$type','$cmd','$url','$val','1')");

			switch ($type){
				case "ET": $vals = explode("&",$serv);
					break;
				case "OU": $vals = explode("|",$serv);
					break;
				case "MIN": $vals = explode("+",$serv);
					break;
			}
			foreach ($vals as $v) {
				if ( strpos($v,";") !== false ){
					$val = explode(";",$v); $host=trim($val[0]); $service=trim($val[1]);
					sqlrequest($database_nagios,"INSERT INTO bp_services VALUES('','$name','$host','$service')");
				}
				else sqlrequest($database_nagios,"INSERT INTO bp_links VALUES('','$name','".trim($v)."')");
			}
		}
		message(6," : Database updated with configuration file","ok");
	}
	unlink($path_nagiosbpcfg_lock);
}

//Write in a file, with error or succes message
function write_file($file,$contenu,$mode,$message = null){
	if(is_writable($file)){
		$error = 0 ;
		if (!$fconf = fopen($file, $mode)) message(2,$file,"critical");
		
		if ( is_array($contenu)){
			foreach ($contenu as $line) {
				if (fwrite ($fconf, $line) === FALSE) $error = 1 ;
			}
		}
		else if (fwrite ($fconf, $contenu) === FALSE) $error = 1 ;

		if ($error) message(3,$file,"critical");
		else if ( $message != null )message(6,$message,"ok");
		fclose ($fconf);
	}
	else 
		message(3,$file,"critical");
}

function sqlArrayNagios($request) {
	global $database_nagios;
	$result = sqlrequest($database_nagios,$request);
	$values = array();
	for ($i=0; $i<mysql_num_rows($result); ++$i) $values[] = mysql_fetch_assoc($result);
	return $values ;
}

function backup_file($start){
	global $path_nagiosbpcfg;
	global $path_nagiosbpcfg_bu;

	for ($i = $start; $i > 0; $i--){
		if ( file_exists($path_nagiosbpcfg_bu.$i)){
			if ( $i == $start) unlink($path_nagiosbpcfg_bu.$i);
			else {
				rename($path_nagiosbpcfg_bu.$i,$path_nagiosbpcfg_bu.($i+1));
			}
		}
	}
	copy($path_nagiosbpcfg,$path_nagiosbpcfg_bu.'1');
}

function buildFile(){

	global $path_nagiosbpcfg_lock;
	wait($path_nagiosbpcfg_lock);	//Wait for the file to not be in use.
	$fp=@fopen($path_nagiosbpcfg_lock,"w");	//Lock the file.
	fputs($fp,getmypid());
	fclose($fp);

	global $max_bu_file;
	backup_file($max_bu_file);
	global $path_nagiosbpcfg;
	$request = "SELECT * FROM bp WHERE `name` NOT IN (SELECT bp_name FROM bp_links) AND `is_define`='1'";
	$values = sqlArrayNagios($request);
	$prevRequest = str_replace("*","name",$request); 

	$file[] = "# Checked";
	foreach( $values as $metier){
		$writenBP[] = $metier['name'];
	  	switch( $metier['type']) {
	   		case "ET" : $sep = "&";break;
	   		case "OU" : $sep = "|";break;
	   		case "MIN" : $sep = "+";break;
	   	}
	   	$result = sqlArrayNagios("SELECT host,service FROM bp_services WHERE bp_name='$metier[name]'");
	   	$strServ = $string = null;
	   	foreach($result as $serv){
	   		if ( is_null($strServ) ) {
	   			$string = "\n#\n# Name : $metier[name]\n# Type : $metier[type]\n$metier[name] = ";
	   			if ( $metier['type'] == "MIN") $string .= "$metier[min_value] of: ";
	   			$strServ = "$serv[host];$serv[service]";
	   		}
	   		else $strServ .= " $sep $serv[host];$serv[service]";
	   	}
	   	$string .= $strServ."\n";
	   	if ( $metier['priority'] != "null") $string .= "display $metier[priority];$metier[name];$metier[description]\n";
	   	if ( $metier['command'] != "") $string .= "external_info $metier[name];$metier[command]\n";
	   	if ( $metier['url'] != "") $string .= "info_url $metier[name];$metier[url]\n";
	   	$file[] = $string;
    }

	if ( $values ) build($prevRequest,$file,$writenBP);
    write_file($path_nagiosbpcfg,$file,"w"," : File updated");
    unlink($path_nagiosbpcfg_lock);
}

function build($pRequest,&$file,$pWritenBP){

	$values = sqlArrayNagios($pRequest);
	unset($r);
	foreach( $values as $v){
		if ( !isset($r) ) $r = "SELECT bp_name FROM bp_links WHERE (bp_link='$v[name]' ";
		else $r .= " OR bp_link='$v[name]'";
	}
	$values = sqlArrayNagios($r.")");

	if ($values){
		unset($r);
		foreach ($values as $v) {
			if ( !isset($r) ) $r = "SELECT * FROM bp WHERE (name='$v[bp_name]' ";
			else $r .= " OR name='$v[bp_name]'";
		}
		$values = sqlArrayNagios($r.") AND `is_define`='1'");
		/*$request = "SELECT * FROM bp WHERE `name` IN (SELECT bp_name FROM bp_links WHERE bp_link IN ($pRequest)) AND `is_define`='1'";
		$values = sqlArrayNagios($request);
		sql takes to much time with this type of request. We split it in multiple request instead.*/
		$prevRequest = str_replace("*","name",$r.") AND `is_define`='1'");
		$writenBP = $pWritenBP;


		foreach ($pWritenBP as $r) {
			if ( !isset($reqC)) $reqC = "SELECT COUNT(bp_name) AS nbr FROM bp_links WHERE (bp_link='$r' ";
			else $reqC .= " OR bp_link='$r'";
		}

		foreach( $values as $metier){
			if (in_array($metier, $pWritenBP)) continue;
			
			$requestC = $reqC.") AND bp_name='$metier[name]'";
			$count = sqlArrayNagios($requestC);
			$cnt = sqlArrayNagios("SELECT COUNT(bp_name) AS nbr FROM bp_links WHERE bp_name='$metier[name]'");
			
			if ( $count[0]['nbr'] == $cnt[0]['nbr']){
				$writenBP[] = $metier['name'];
			  	switch( $metier['type']) {
			   		case "ET" : $sep = "&";break;
			   		case "OU" : $sep = "|";break;
			   		case "MIN" : $sep = "+";break;
			   	}
			   	$result = sqlArrayNagios("SELECT host,service FROM bp_services WHERE bp_name='$metier[name]'");
			   	$strServ = $string = null;

			   	foreach($result as $serv){
			   		if ( is_null($strServ) ) {
			   			$string = "\n#\n# Name : $metier[name]\n# Type : $metier[type]\n$metier[name] = ";
			   			if ( $metier['type'] == "MIN") $string .= "$metier[min_value] of: ";
			   			$strServ = "$serv[host];$serv[service]";
			   		}
			   		else $strServ .= " $sep $serv[host];$serv[service]";
			   	}
			   	$result = sqlArrayNagios("SELECT bp_link FROM bp_links WHERE bp_name='$metier[name]'");
			   	foreach($result as $serv){
			   		if ( is_null($strServ) ) {
			   			$string = "\n#\n# Name : $metier[name]\n# Type : $metier[type]\n$metier[name] = ";
			   			if ( $metier['type'] == "MIN") $string .= "$metier[min_value] of: ";
			   			$strServ = "$serv[bp_link]";
			   		}
			   		else $strServ .= " $sep $serv[bp_link]";
			   	}
			   	$string .= $strServ."\n";
			   	if ( $metier['priority'] != "null") $string .= "display $metier[priority];$metier[name];$metier[description]\n";
			   	if ( $metier['command'] != "") $string .= "external_info $metier[name];$metier[command]\n";
			   	if ( $metier['url'] != "") $string .= "info_url $metier[name];$metier[url]\n";
			   	$file[] = $string;
			}
	    }

	    build($prevRequest,$file,$writenBP);
	}
}
?>

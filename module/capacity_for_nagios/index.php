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

include("../../include/include_module.php");
$cactiurl="http://".$_SERVER["SERVER_NAME"]."/cacti";

###########
# Main Code
###########

# Get the IP ip oh the host (GET method)
if (isset($_GET["ip"])) {
        $ip = $_GET["ip"];
        $address = gethostbyname($ip);  # (We already sanitize it in "checkip" function)
} else {
        message(0," : Incorrect IP or hostname","critical");
}

# Connect to the Cacti DB
$link = mysql_connect($database_host, $database_username, $database_password)
    or message(1,"$database_cacti","critical");
mysql_select_db($database_cacti)
        or message(1,"$database_cacti","critical");

# Build and execute the SQL request
$query = "SELECT graph_local.id AS local_graph_id, host.id AS host_id, host.hostname AS hostname "
        ."FROM (graph_local, host) "
	."WHERE graph_local.host_id=host.id AND host.hostname LIKE '".$ip."' OR host.hostname LIKE '".$address."' OR host.description LIKE '".$ip."'";

$result = mysql_query($query)
        or message(0," : Query failed","critical");

# Get the result (the last one)
if (mysql_num_rows($result) > 0) {
        while ($row = mysql_fetch_assoc($result)) {
                $action_url = $cactiurl."/graph_view.php?action=preview&host_id=".$row["host_id"]."&graph_template_id=0&filter=";
        }
    header("Location: ".$action_url);
}
else {
    message(0," : Host not found","critical");
}

# Close the DB session
mysql_free_result($result);
mysql_close($link);

?> 

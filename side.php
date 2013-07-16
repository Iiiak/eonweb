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
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>

<title>menus</title>

<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<?php include("./include/include.php"); ?> 
<script type="text/javascript" src="./js/jquery.cookie.js"></script>

</head>

<body>

<script type="text/javascript">

$(document).ready(function(){
        $("#leftmenu").sortable({
		axis: 'y',
		handle: 'dt.handleitem',
		items: 'dl.sortableitem',
		opacity: '0.5'
	});
	return false;
});

function setLink(headernav,side_url){
	$('#zone_header',top.document).find('#headernav').html(headernav);
	$.cookie('active_page',side_url);
	return false;
}

function animateMenu(image,menu){
        var men = document.getElementById(menu);
        $(men).slideToggle();
        var img = document.getElementById(image);
        var url = location.protocol+"//"+location.host+"/images/actions/minus.gif";

        if(img.src == url)
                img.src = "./images/actions/plus.gif";
        else 
                img.src = "./images/actions/minus.gif";     

	return false;
}

</script>

<?php

// Check POST : active menutab 
if(!isset($_GET['tabid'])) $tmpid=$defaulttab;
else $tmpid=$_GET['tabid'];

$cookie_time = ($cookie_time=="0") ? 0 : time() + $cookie_time;
setcookie("active_tab",$tmpid,$cookie_time);

// Get information for menus in xml file
$xpath = new DOMXPath($xmlmenus);
$menutabs = $xpath->query("//menutab[@id='$tmpid']");
$tab_id = $menutabs->item(0)->getAttribute("id");
$tab_name = $menutabs->item(0)->getAttribute("name");

// Create the Menu
echo "<div id='leftmenu'>";
	
?>

<div id="leftmenutitle">
	<?php echo $tab_name?>
</div>

<form method="get" action="<?php echo $path_nagios_cgi?>/status.cgi" target="main">
  <center>
  <input name="s0_op" value="~" id="s0_to" type="hidden">
  <input name="s0_type" value="search" type="hidden">
  <input name="s0_value" id="s0_value" type="text" value="<?php echo $xmlmenus->getElementsByTagName("search")->item(0)->nodeValue;?>" onclick="if(this.value=='<?php echo $xmlmenus->getElementsByTagName("search")->item(0)->nodeValue?>') this.value=''" onFocus='$("input[name]=host").autocomplete(<?php echo get_host_list_from_nagios();?>)' autocomplete="off" />
  <input type="hidden" name="navbarsearch" value="1" />
  </center>
</form>

<?php	
// Display Left Menu
$menusubtabs = $menutabs->item(0)->getElementsByTagName("menusubtab");
foreach($menusubtabs as $menusubtab){
	(!isset($i)) ? $i=0 : $i++;
	$subtab_name = $menusubtab->getAttribute("name");
?>

<dl id="item_<?php echo $i?>" class="sortableitem">
  <dt class="handleitem">
    <img src="/images/actions/minus.gif" id="image_<?php echo $i?>" alt="" onclick="animateMenu('image_<?php echo $i?>','handle_<?php echo $i?>')" /><?php echo $subtab_name?>
  </dt>
  <dd>
    <ul id="handle_<?php echo $i?>" class="ul">
<?php 
// Get information for side menu in database
foreach($menusubtab->getElementsByTagName("link") as $link){
        $side_name = $link->getAttribute("name");
        $side_url = $link->getAttribute("url");
        $side_target = $link->getAttribute("target");
	$headernav="&nbsp;<b>".ucfirst($tab_name)." -> <i>".ucfirst($subtab_name)."</b> --> ".str_replace("'","\'",$side_name)."</i>&nbsp;";
	?>
	<li>
	  <a href="<?php echo $side_url?>" target="<?php echo $side_target?>" <?php if($side_target!="_blank") { ?>onclick="setLink('<?php echo $headernav?>','<?php echo $side_url?>');" <?php } ?>><?php echo $side_name?></a>
	</li>
	<?php
}
?>
    </ul>
  </dd>
</dl>

<?php } ?>

</div>

</body>

</html>

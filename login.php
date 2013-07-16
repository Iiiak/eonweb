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

<title>EyesOfNetwork</title>

<?php include("./include/include.php"); ?>

<script type="text/javascript">
$(document).ready(function(){
	$("#login").draggable({
		handle: '.handle',
		opacity: '0.5',
		containment: 'document'
	});
	$("input:text").focus();
});
</script>

</head>
<?php

// Display login Form
function display_login(){
	
	global $xmlmenus;
	
	echo "<script>if (window !=top ) {top.location=window.location;}</script>";
        echo "<body id='main'>";
	echo "<div id='login'>";
	echo "<div>";
	echo "<p> ".$xmlmenus->getElementsByTagName("login")->item(0)->nodeValue." </p>";
        echo "<form action='login.php' method='post'>";
	echo "<table class='table'>";
        echo "<tr><td class='blanc'>".$xmlmenus->getElementsByTagName("login")->item(0)->getAttribute("login")." :</td><td class='blanc'><input type='text' name='login' style='width:150px;' /></td></tr>";
        echo "<tr><td class='blanc'>".$xmlmenus->getElementsByTagName("login")->item(0)->getAttribute("password")." :</td><td class='blanc'><input type='password' name='mdp' style='width:150px;' /></td></tr>";
        echo "<tr><td class='blanc'></td><td class='blanc'><input type='submit' class='button' value='".$xmlmenus->getElementsByTagName("login")->item(0)->getAttribute("connect")."' /></td></tr>";
	echo "</table>";
        echo "</form>";
	echo "</div>";
	echo "</div>";
        echo "</body>";
}

	if(isset($_COOKIE['user_name'])){
		?>
		<script type="text/javascript">
			document.write("<body id='main'>");
			if (window!=top){
			        $("body").append("<ul><li class='msg_title'>Message EON - Security : not allowed</li></ul>");
			}
			else{
				$("body").append("<div id='login'><div><p><?php echo $xmlmenus->getElementsByTagName("login")->item(0)->nodeValue;?></p><br><h2><?php echo $xmlmenus->getElementsByTagName("login")->item(0)->getAttribute("yet")?> : <br><a href='index.php'><?php echo $_COOKIE["user_name"]?></a></h2><br><a href='logout.php'><?php echo $xmlmenus->getElementsByTagName("header")->item(0)->getAttribute('logout')?></a></div></div>");
			}
			document.write("</body>");
		</script>
		<?php
	}
	else {
		if(isset($_POST['login']) && isset($_POST['mdp'])){
			// Get login information
			$login=$_POST['login'];
               	 	$mdp=$_POST['mdp'];
			$_POST[]=array();
	
			if(strstr($login,"'")){
				display_login();
				exit;
			}

			$usersql=sqlrequest($database_eonweb,"select * from users where user_name = '$login'");
			$username = mysql_result($usersql,0,"user_name");

		        if ($login != $username) {
				display_login();
			}
			else {
				// IF LDAP USER
				if(mysql_result($usersql,0,"user_type")=="1"){
					$ldapsql=sqlrequest($database_eonweb,"select * from auth_settings");
					$ldap_ip=mysql_result($ldapsql,0,"ldap_ip");
					$ldap_port=mysql_result($ldapsql,0,"ldap_port");
					$ldap_rdn=mysql_result($ldapsql,0,"ldap_rdn");
					$ldap_search=mysql_result($ldapsql,0,"ldap_search");
					$user_location=str_replace("\\\\","\\",mysql_result($usersql,0,"user_location"));

					$ldapconn=ldap_connect($ldap_ip,$ldap_port);
					ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);
					ldap_set_option($ldapconn, LDAP_OPT_REFERRALS, 0);

                			$ldapbind=ldap_bind($ldapconn, $user_location, $mdp);

					if($ldapbind && !empty($mdp))		
						$LOGIN=true;
				}
				// IF NOT A LDAP USER
				else{
					$userpasswd = mysql_result($usersql,0,"user_passwd");
					$mdp=md5($mdp);

					if($userpasswd == $mdp)
						$LOGIN=true;
				}

				// LOGIN OK
				if(isset($LOGIN)){

					// Get user & group ids + filter
		          		$grpid = mysql_result($usersql,0,"group_id");
                                        $usrid = mysql_result($usersql,0,"user_id");
					$usrlimit = mysql_result($usersql,0,"user_limitation");

					// Create session ID
					$sessid=rand();
					sqlrequest($database_eonweb,"INSERT INTO sessions (session_id,user_id) VALUES ('$sessid','$usrid')");

					// Send cookie
					$cookie_time = ($cookie_time=="0") ? 0 : time() + $cookie_time;
					setcookie("session_id",$sessid,$cookie_time);
					setcookie("user_name",$login,$cookie_time,"/",$cookie_domain);
					setcookie("user_id",$usrid,$cookie_time);
					setcookie("user_limitation",$usrlimit,$cookie_time);
					setcookie("group_id",$grpid,$cookie_time);

					// Go to the main page
					logging("login","User logged in",$login);
					echo "<meta http-equiv='Refresh' content='0;URL=index.php' />";
				}
				// LOGIN FAILED
				else {
					display_login();
				}
			}	
		}
		else display_login();
	}

?>

</html>

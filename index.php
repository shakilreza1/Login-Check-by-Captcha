<?php
session_start();
$message="";
$captcha = true;

if(count($_POST)>0 && isset($_POST["captcha_code"]) && $_POST["captcha_code"]!=$_SESSION["captcha_code"])
 {
$captcha = false;
$message = "Enter Correct Captcha Code";
}
$mysqli = new mysqli('localhost','root','','blog_examples');

$ip = $_SERVER['REMOTE_ADDR'];

$result = $mysqli->query("SELECT count(ip_address) AS failed_login_attempt FROM failed_login WHERE ip_address = '$ip'  AND date BETWEEN DATE_SUB( NOW() , INTERVAL 1 DAY ) AND NOW()");

$row  = $result->fetch_array();

$failed_login_attempt = $row['failed_login_attempt'];
$result->free();


if(count($_POST)>0 && $captcha == true) 
{
	$result = $mysqli->query("SELECT * FROM users WHERE user_name='" . $_POST["user_name"] . "' and password = '". $_POST["password"]."'");

	$row  = $result->fetch_array();

	$result->free();

	if(is_array($row)) 
	{
		$_SESSION["user_id"] = $row["id"];
		$_SESSION["user_name"] = $row["user_name"];
		$mysqli->query("DELETE FROM failed_login WHERE ip_address = '$ip'");
	}
	 else 
	{
		$message = "Invalid Username or Password!";
		if ($failed_login_attempt < 3) 
		{
			$mysqli->query("INSERT INTO failed_login (ip_address,date) VALUES ('$ip', NOW())");
		} 
		else
		 {
			$message = "You have tried more than 3 invalid attempts. Enter captcha code.";
		}
	}
}

if(isset($_SESSION["user_id"])) 
{
header("Location:user_dashboard.php");
}
?>
<html>
<head>
<title>User Login</title>
<link rel="stylesheet" type="text/css" href="styles.css" />
</head>
<body>
<form name="frmUser" method="post" action="">
<div class="message">

<?php if($message!="") 
{ 
	echo $message;
	 } 
?>
</div>
<table border="0" cellpadding="10" cellspacing="1" width="500" align="center">
<tr class="tableheader">
<td align="center" colspan="2">Enter Login Details</td>
</tr>
<tr class="tablerow">
<td align="right">Username</td>
<td><input type="text" name="user_name"></td>
</tr>
<tr class="tablerow">
<td align="right">Password</td>
<td><input type="password" name="password"></td>
</tr>

<?php
 if (isset($failed_login_attempt) && $failed_login_attempt >= 3) 
 	{ 
 		?>
<tr class="tablerow">
<td align="right"></td>

<td><input  type="text" name="captcha_code"><br><br><img src="captcha_code.php" /></td>
</tr>

<?php
 }

  ?>
<tr class="tableheader">
<td align="center" colspan="2"><input type="submit" name="submit" value="Submit"></td>
</tr>
</table>
</form>
</body>
</html>
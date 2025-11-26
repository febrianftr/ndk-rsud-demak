<?php

session_start();

require 'koneksi/koneksi.php';

if ($_SESSION['level'] == "admin" || $_SESSION['level'] == "superadmin") {
	header("location:admin/index.php");
} else if ($_SESSION['level'] == "superadmin") {
	header("location:superadmin/index.php");
} else if ($_SESSION['level'] == "radiology") {
	header("location:radiology/index.php");
} else if ($_SESSION['level'] == "radiographer") {
	header("location:radiographer/index.php");
} else if ($_SESSION['level'] == "refferal") {
	header("location:refferal/workload.php");
} else {
	header("location:login.php");
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<title>Menu</title>
	<link rel="stylesheet" href="css/style.css" type="text/css">
</head>

<body>
</body>

</html>
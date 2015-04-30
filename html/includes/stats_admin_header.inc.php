<!DOCTYPE html>
<html lang='en'>
<header>
<title>EFI-EST Statistics</title>
<link rel="stylesheet" type="text/css"
	<?php if (file_exists("../includes/bootstrap/css/bootstrap.min.css")) {
		echo "href='../includes/bootstrap/css/bootstrap.min.css'>";
	}
	elseif (file_exists("includes/bootstrap/css/bootstrap.min.css")) {
		echo "href='includes/bootstrap/css/bootstrap.min.css'>";
	}
	?>
</header>

<body>
<div class='navbar navbar-inverse'>
                <div class='navbar-inner'>
			<a class='brand' href='#'><?php echo __TITLE__; ?></a>
			<ul class='nav'>
                                        <li><a href='index.php'>Generate Stats</a></li>
					<li><a href='analysis_stats.php'>Analysis Stats</a></li>
                                        <li><a href='generate.php'>Generate Jobs</a></li>
					<li><a href='analyse.php'>Analyse Jobs</a></li>
                                </ul>

                </div>
</div>

<div class='container-fluid'>
<div class='span12'>


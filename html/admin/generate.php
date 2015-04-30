<?php

include_once '../includes/stats_main.inc.php';
include_once '../includes/stats_admin_header.inc.php';

$month = date('n');
if (isset($_GET['month'])) {
        $month = $_GET['month'];
}
$year = date('Y');
if (isset($_GET['year'])) {
        $year = $_GET['year'];
}
$generate_page = functions::get_web_root() . "/stepc.php";
$jobs = statistics::get_generate($db,$month,$year);

$generate_html = "";
foreach ($jobs as $job) {
	$get_array = array('id'=>$job['Generate ID'],'key'=>$job['Key']);
	$url = $generate_page . "?" . http_build_query($get_array);
	$generate_html .= "<tr>";
	if (time() < $job['Time Completed'] + __RETENTION_DAYS__) {
		$generate_html .= "<td>&nbsp</td>";
	}
	else {
		$generate_html .= "<td><a href='" . $url ."'><i class='icon-share'></i></a></td>";
	}
	$generate_html .= "<td>" . $job['Generate ID'] . "</td>\n";
	$generate_html .= "<td>" . $job['Email'] . "</td>\n";
	$generate_html .= "<td>" . $job['Option Selected'] . "</td>\n";
	if ($job['Blast']) { 
		$generate_html .= "<td><a href='blast.php?blast=" . $job['Blast'] . "' target='_blank' ><i class='icon-pencil'></i></a>";
		$generate_html .= "</td>\n";
	}
	else {
		$generate_html .= "<td>&nbsp</td>\n";
	}
	$generate_html .= "<td>" . $job['Families'] . "</td>\n";
	$generate_html .= "<td>" . $job['Time Submitted'] . "</td>\n";
	$generate_html .= "<td>" . $job['Time Started'] . "</td>\n";
	$generate_html .= "<td>" . $job['Time Completed']  ."</td>\n";
	$generate_html .= "<td>" . $job['Status'] . "</td>\n";
	$generate_html .= "</tr>";

}

$month_html = "<select class='input-small' name='month'>";
for ($i=1;$i<=12;$i++) {
        if ($month == $i) {
                $month_html .= "<option value='" . $i . "' selected='selected'>" . date("F", mktime(0, 0, 0, $i, 10)) . "</option>\n";
        }
        else {
                $month_html .= "<option value='" . $i . "'>" . date("F", mktime(0, 0, 0, $i, 10)) . "</option>\n";
        }
}
$month_html .= "</select>";

$year_html = "<select class='input-small' name='year'>";
for ($i=2014;$i<=date('Y');$i++) {
        if ($year = $i) {
                $year_html .= "<option selected='selected' value='" . $i . "'>". $i . "</option>\n";
        }
        else {
                $year_html .= "<option value='" . $i . "'>". $i . "</option>\n";
        }

}
$year_html .= "</select>";

$monthName = date("F", mktime(0, 0, 0, $month, 10));
?>
<h3>EFI-EST Generate Jobs - <?php echo $monthName . " - " . $year; ?></h3>

<form class='form-inline' method='get' action='<?php echo $_SERVER['PHP_SELF']; ?>'>
<?php echo $month_html; ?>
<?php echo $year_html; ?>
<input class='btn btn-primary' type='submit'
                name='get_jobs' value='Submit'>

</form>
<h4>Generate Step</h4>
<table class='table table-condensed table-bordered'>
<tr>
	<th>&nbsp</th>
	<th>EFI-EST ID</th>
	<th>Email</th>
	<th>Type</th>
	<th>Blast</th>
	<th>Family</th>
	<th>Time Submitted</th>
	<th>Time Started</th>
	<th>Time Finished</th>
	<th>Status</th>
</tr>
<?php echo $generate_html; ?>
</table>



<?php include_once '../includes/stats_footer.inc.php' ?>

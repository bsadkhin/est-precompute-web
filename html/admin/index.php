<?php
include_once '../../conf/settings.inc.php';
include_once '../../libs/database.class.inc.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
<!DOCTYPE html>
    <html lang='en'>
    <head>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
<script src='//cdn.datatables.net/1.10.5/js/jquery.dataTables.min.js'></script>
<link rel='stylesheet'  href='//cdn.datatables.net/1.10.5/css/jquery.dataTables.min.css'>

<script src="https://code.highcharts.com/highcharts.js"></script>
<script src="https://code.highcharts.com/modules/data.js"></script>
<script src="https://code.highcharts.com/modules/exporting.js"></script>

<script type='text/javascript'>
$(document).ready(function() {

  /*  
        $('#table').highcharts({
            data: {
                table: 'datatable'
            },
            chart: {
                type: 'column'
            },
            title: {
                text: 'Data extracted from a HTML table in the page'
            },
            yAxis: {
                allowDecimals: false,
                    title: {
                        text: 'Units'
                    }
            },
                tooltip: {
                    formatter: function () {
                        return '<b>' + this.series.name + '</b><br/>' +
                            this.point.y + ' ' + this.point.name.toLowerCase();
                    }
                }
        });
 */ 
    $('table').DataTable();


} );

</script>


    <title>EFI-Precompute Statistics</title>
    <link rel="stylesheet" type="text/css"
    href='../includes/bootstrap/css/bootstrap.min.css'></head>

    <body>
    <div class='navbar navbar-inverse'>
    <div class='navbar-inner'>
    <a class='brand' href='#'>EFI-EST-Precompute</a>
    <ul class='nav'>
    <li><a href='index.php'>Interproscan Jobs</a></li>
    <li><a href='index.php?p=filter'>Filter Jobs</a></li>
    </ul>

    </div>
    </div>
<div class='container-fluid'>
<div class='span12'>


<?php
main();
function main(){
    $i = isset($_GET['p'])?$_GET['p']:null;
    switch ($i) {
    case null:
        echo "iprscan";
        get_iprscan_jobs();
        break;
    case 'filter':
        echo 'filter';
        get_filter_jobs();
        break;
    }

}

function get_iprscan_jobs(){
    $cols = array('job_id','job_flag','job_release','job_email','job_sequence','job_time_submit','job_time_start','job_time_end','job_session','job_pbs_id','job_ip');
    generateTable($cols,'iprscan','stepb');
}
function get_filter_jobs(){
    $cols = array('job_id','job_flag','job_release','job_email','job_family',
        'job_network_type','job_network_name',
        'job_expect_cutoff','job_pid_cutoff','job_min_length','job_max_length',
        'job_time_submit','job_time_start','job_time_end','job_session','job_pbs_id','job_ip');
    generateTable($cols,'filter','stepe');

}

function generateTable($cols,$type,$result){
    $header="";

    $db = new database();
    $sql_rows =($db->getJobsByType($type));
    foreach($cols as $th){
        $header .= "<th>$th</th>";
    }
    $header = "<tr>$header</tr>";

    $rows = "";
    foreach($sql_rows as $sql_row){
        $row = "";

        $id = $sql_row['job_id'];
        $session = $sql_row['job_session'];
        $email = $sql_row['job_email'];
        $link = "../$result.php?id=$id&session=$session&email=$email";

        foreach($cols as $col){
            $value = $sql_row[$col];
            $value = modifyValue($value);
            if($col == 'job_id'){
                $value = "<a href='$link'>$value</a>";
            }
            $row .= "<td>$value</td>";
        }
        $rows .= "<tr>$row</tr>";
    }

    echo "<table id='table' class='table'>
        <thead>$header</thead>
        <tbody>$rows</tbody>
        </table> " . getChart();
}

function getChart(){
    return '<div id="container" style="min-width: 310px; height: 400px; margin: 0 auto">Hi</div>';


}

#checks to see if entry is longer than 35 chars
function modifyValue($value){
    if(strlen($value) > 35){
        return "<input type='text' value='$value'></input>";
    }
    return $value;
}


/*
$month = date('n');
if (isset($_POST['month'])) {
    $month = $_POST['month'];
}
$year = date('Y');
if (isset($_POST['year'])) {
    $year = $_POST['year'];
}

$graph_type = "generate_daily_jobs";
$get_array  = array('graph_type'=>$graph_type,
                'month'=>$month,
                'year'=>$year);
$graph_image = "<img src='../daily_graph.php?" . http_build_query($get_array) . "'>";

$generate_per_month = statistics::num_generate_per_month($db);
$generate_per_month_html = "";
foreach ($generate_per_month as $value) {
    $generate_per_month_html .= "<tr><td>" . $value['month'] . "</td>";
    $generate_per_month_html .= "<td>" . $value['year'] . "</td>";
    $generate_per_month_html .= "<td>" . $value['count'] . "</td>";
    $generate_per_month_html .= "<td>" . $value['num_success_option_a'] . "</td>";
    $generate_per_month_html .= "<td>" . $value['num_failed_option_a'] . "</td>";
    $generate_per_month_html .= "<td>" . $value['num_success_option_b'] . "</td>";
    $generate_per_month_html .= "<td>" . $value['num_failed_option_b'] . "</td>";
    $generate_per_month_html .= "<td>" . $value['num_failed_seq_option_b'] . "</td>";
    $generate_per_month_html .= "<td>" . $value['total_time'] . "</td>";
    $generate_per_month_html .= "</tr>";

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
?>



<h3>EFI-EST Generate Statistics</h3>

<h4>Generate Step</h4>
<table class='table table-condensed table-bordered span8'>
<tr>
    <th>Month</th>
    <th>Year</th>
    <th>Total Jobs</th>
    <th>Successful Option A Jobs</th>
    <th>Failed Option A Jobs</th>	
    <th>Successful Option B Jobs</th>
    <th>Failed Option B Jobs</th>
    <th>Failed Option B Jobs (> <?php echo __MAX_SEQ__; ?> Sequences)</th>
    <th>Total Time</th>	
</tr>
<?php echo $generate_per_month_html; ?>
</table>

<form class='form-inline' method='post' action='report.php'>
                <select name='report_type' class='input-medium'>
                <option value='xls'>Excel 2003</option>
                <option value='xlsx'>Excel 2007</option>
                <option value='csv'>CSV</option>
        </select> <input class='btn btn-primary' type='submit'
                name='create_user_report' value='Download User List'>
</form>

<br>
<br>
<hr>
<form class='form-inline' method='post' action='report.php'>
                <select name='report_type' class='input-medium'>
                <option value='xls'>Excel 2003</option>
                <option value='xlsx'>Excel 2007</option>
                <option value='csv'>CSV</option>
        </select> 
    <?php echo $month_html; ?>
    <?php echo $year_html; ?>
<input class='btn btn-primary' type='submit'
                name='create_job_report' value='Download Job List'>

</form>
<hr>
<form class='form-inline' method='post' action='<?php echo $_SERVER['PHP_SELF']; ?>'>
                <select name='report_type' class='input-medium'>
                <option value='xls'>Excel 2003</option>
                <option value='xlsx'>Excel 2007</option>
                <option value='csv'>CSV</option>
        </select> 
        <?php echo $month_html; ?>
        <?php echo $year_html; ?>

<input class='btn btn-primary' type='submit'
                name='create_user_report' value='Get Daily Graph'>

<br>
<hr>
<?php echo $graph_image; ?>

 */ ?>
</div>
</div>
<?php include_once '../includes/stats_footer.inc.php'; ?>

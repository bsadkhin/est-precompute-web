<body>

<div class='content_holder'>
<table id='bob'>
<thead>
<tr ><th><font color='black'>PFAM</font></th><th>Plots</th><th>XGMML</th><th><font color='black'>Count</font></th></tr>

</thead>
<?php
include_once 'includes/main.inc.php';
include_once 'includes/header.inc.php';
include_once 'includes/quest_acron.inc';





error_reporting(E_ALL);
ini_set('display_errors', '1');


$dir = "/home/groups/efi/est-precompute48/generated_data/pfam";
$list_of_incomplete = "$dir/incompleted";
$list_of_incomplete = "incomplete_pfams";
$handle = fopen("$list_of_incomplete", "r");
$db = new database();

if ($handle) {
    while (($line = fgets($handle)) !== false) {
        $pfam = rtrim($line);


        $count = $db->getFamilyCount($pfam,'pfam','48');
        $plots = check_plots($pfam,$dir);
        print_plots($plots,$pfam); 
        $xgmml = check_xgmml($pfam,$dir);
        print_xgmml($xgmml,$pfam,$count);
        if($pfam == 'PF01473'){

            print "Dying on PF01473";
die;
}      
    }

    fclose($handle);
} else {
    // error opening the file.
} 


function print_plots($plots,$pfam){
    print "<tr><td>$pfam</td>";
    $td= "<td><font color='lime'>complete</font></td>";
    foreach($plots as $plot => $plo){
        if($plots[$plot] == 0){
            $td= "<td><font color='red'>incomplete</font></td>";
        }
    }
    print $td;

}

function print_xgmml($plots,$pfam,$count){
    $td= "<td><font color='lime'>complete</font></td>";
    foreach($plots as $plot => $plo){
        if($plots[$plot] == 0){
            $td= "<td><font color='red'>incomplete</font></td>";
        }
    }
    print $td;
    print "<td>$count</td>";
    print "</tr>";
}

function check_xgmml($pfam,$dir){
    $x['40'] = 0;
    $x['45'] = 0;
    $x['50'] = 0;
    $x['55'] = 0;
    $x['60'] = 0;
    $x['65'] = 0;
    $x['70'] = 0;
    $x['75'] = 0;
    $x['80'] = 0;
    $x['85'] = 0;
    $x['90'] = 0;
    $x['95'] = 0;
    $x['98'] = 0;
    $x['100'] = 0;
    $x['full'] = 0;

    foreach(array_keys($x) as $xgmml){
        if(file_exists("$dir/$pfam/xgmml/original/$pfam-$xgmml.xgmml.gz")){
            $x[$xgmml] = 1;
        }
    }
    return $x;



}

function check_plots($pfam,$dir){
    $plots['r_hist_edges.png'] = 0;
    $plots['r_hist_length.png'] = 0;
    $plots['r_quartile_align.png'] = 0;
    $plots['r_quartile-perid.png'] = 0;

    foreach(array_keys($plots) as $png){
        if(file_exists("$dir/$pfam/plots/$png")){
            $plots[$png] = 1;
        }
        else{
        }
    }
    return $plots;
}




?>
</table>
</div>
</body>
</html>


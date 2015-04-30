<?php 
include_once 'includes/main.inc.php';
include_once 'includes/header.inc.php'; 
include_once 'includes/quest_acron.inc';

error_reporting(E_ALL);
ini_set('display_errors', 1);

#$parameters = array();

main();
function main(){
    if(isset($_GET['name']) && isset($_GET['type']) ){
        $name = $_GET['name'];
        $type = $_GET['type'];
        $parameters=getUnfilteredNetwork($name,$type);
        emitTables($name,$type,null,null);
    }
    else if(isset($_GET['session']) && isset($_GET['id']) ){
        $session = $_GET['session'];
        $job_id = $_GET['id']; 
        if(!isset($session) || !isset($job_id)){
            echo "<br>Error no job id or session found <br>";
            exit;
        }
        $parameters=getFilteredNetwork($job_id,$session);
        emitTables(null,null,$job_id,$session);
    }
    else{
            echo "<br>Error. Please go back and try again! <br>";
            exit;
    } 
}

function emitTables($name,$type,$id,$session){
    echo '<img src="images/quest_stages_e.jpg" width="990" height="119" alt="stage 1"><hr>';
    echo '<h3> DOWNLOAD NETWORK FILES </h3>';

    if(isset($name) && isset($type)){
        $precomputed = new precomputed($name,$type);
        $base_path = $precomputed->getBaseDirXgmml() ;  #ssnsjuly/clans/$type/$name/xgmml
        $parameters = getParameters($name,$base_path);
        $parameters['job_family'] = $name;
        emitTablesHelper($parameters);
    }
    elseif(isset($id) && isset($session)){
        $filtered_xgmml = new filtered_xgmml($id,$session);
        $filtered_parameters = $filtered_xgmml->getParameters();
        $base_path = "{$filtered_parameters['base_path']}/$id/$session";
        $name = $filtered_parameters['job_family'];

        $parameters = getParameters($name,$base_path);

        $parameters['job_family'] = $name;
        #Load job cutoffs from DB
        foreach(array('job_pid_cutoff','job_expect_cutoff','job_min_length','job_max_length','job_network_name') as $key ){
            if(isset($filtered_parameters[$key]) && strlen($filtered_parameters[$key]) > 0 && $filtered_parameters[$key] > 0){
                $parameters[$key] = $filtered_parameters[$key];
            }
        }
        $parameters['filtered_params'] = getFilteredFilename($parameters);
        emitTablesHelper($parameters);
    }
}

#Emit the tables for filtered and unfiltered networks, depending on the _GET
#Input : Modified $parameters hash, with job family added
#Input : filtered_network true/false
#Output: No return. Echos the tables!
function emitTablesHelper($parameters){

    echo
        "<h4>Full Network". '<a href="http://efi.igb.illinois.edu/efi-est/tutorial_download.php" class="question" target="_blank">?</a>' . "</h4>".
        "<p>Each node in the network is a single protein from the data set. Large files (>500MB) may not open.</p>"; 
    echo  getTable($parameters,'full');

    echo "<h4>Representative Node Networks". '<a href="http://efi.igb.illinois.edu/efi-est/tutorial_download.php" class="question" target="_blank">?</a>' . "</h4>";
    echo "Each node in the network represents a collection of proteins grouped according to percent identity.";
    echo getTable($parameters,'repnode_network'); 

}


#Generate xgmml table for a rep/full network
#input $dbrow
#input 'repnode_network' or 'full_network'
#return xgmml download table
function getTable($parameters,$type){
    $id_header = $type == 'full' ?  '' : '% ID';
    $th = "<thead><tr>
        <th> Filename </th>
        <th> $id_header </th>
        <th> # Nodes </th>
        <th> # Edges </th>
        <th> XGMML Size </th>

        <th>Zipped</th></tr></thead>";
    $rows = "";
    foreach(array('full','40','45','50','55','60','65','70','75','80','85','90','95','98','100') as $ext){
        #Dont include full in repnode table
        $id = $ext=='full' ? 'Full' : $ext;
        if($type =='repnode_network' && $ext == 'full'){
            continue; 
        } 
        #Generate rows
        $base_path = $parameters['base_path'];
        $name = "{$parameters['job_family']}-$ext";
        $filtered_params = isset($parameters['filtered_params']) ? $parameters['filtered_params'] : '';
        $filename = "$name.xgmml.gz";
        $download = "$name$filtered_params.xgmml.gz";
        $link = "<a href='$base_path/$name.xgmml.gz' class='view_download' download='$download'>Download $name.xgmml.gz</a>";
    
        $nodes = $parameters[$ext]['nodes'];
        $edges = $parameters[$ext]['edges'];
        $filesize = $parameters[$ext]['filesize'];
        $filesize_compressed = $parameters[$ext]['filesize_compressed'];

        if($nodes ==0){
            $filesize_compressed = $filesize = $edges = $id = $nodes = '';
            $link = "<a class='view_download'> Too Many Edges. Unavailable for Download </a>";
        }

        $rows .= 
            "<tr>".
            "<td>$link</td>".
            "<td>$id</td>".
            "<td>$nodes</td>".
            "<td>$edges</td>".
            "<td>$filesize</td>".
            "<td>$filesize_compressed</td>".
            "</tr>";

        #If not full, exit already!
        if($type =='full'){
            break;
        }
    }
    return "<table width='100%' border=1>$th $rows </table>";

}

#Append filtered parameters to xgmml filename
#input = db row
#output = filtered_parameters string
function getFilteredFilename($parameters){
    $name ="";
    $filters=array('job_expect_cutoff'=>'score',
        'job_pid_cutoff'=>'pid',
        'job_min_length'=>'minL',
        'job_max_length'=>'maxL');

    foreach(($filters) as $key=>$val){
        if(isset($parameters[$key]) && strlen($parameters[$key]) > 0 && $parameters[$key] > 0){
            $name .= "-$val$parameters[$key]";
        } 

    }    #print "About to return $name\n";
    return $name;

}


#all of this needs to go into xgmml class
function getUnFilteredNetwork($name,$type){
    $precomputed = new precomputed($name,$type);
    $base_path = $precomputed->getBaseDirXgmml() ;  #ssnsjuly/clans/$type/$name/xgmml
    $parameters = getParameters($name,$base_path);
    return $parameters;
}

#needs to go into xgmml class
function getfilteredNetwork($job_id,$session){
    $filtered_xgmml = new filtered_xgmml($job_id,$session);
    $job_parameters = $filtered_xgmml->getParameters();
    $base_path = $job_parameters['base_path'] . "/$job_id/$session";
    $name = $job_parameters['job_family'];
    $type = $job_parameters['job_network_type'];
    return getParameters($name,$base_path);
}

function getParameters($name,$base_path){
    $list = array(.4, .45, .5, .55, .6, .65, .7, .75, .8, .85, .9, .95, .98, 1 );
    $list = array(40,45,50,55,60,65,70,75,80,85,90,95, 98 , 100);
    $parameters = array();
    $parameters['name'] = $name;
    $stats = getStats("$base_path/$name-full.xgmml");

    $parameters['full']['filename'] = "$base_path/$name-full.xgmml";
    $parameters['full']['nodes'] = $stats['nodes'];
    $parameters['full']['edges'] = $stats['edges']; 
    $parameters['full']['filesize'] =  $stats['filesize'];
    $parameters['full']['filesize_compressed'] =  $stats['filesize_compressed'];


    foreach ($list as $pid){
        $filename_prefix =  "$base_path/$name-$pid";
        $filename = "$filename_prefix.xgmml";
        $stats = getStats($filename);

        $parameters[$pid]['filename'] = $filename;
        $parameters[$pid]['nodes'] = $stats['nodes'];
        $parameters[$pid]['edges'] = $stats['edges'];
        $parameters[$pid]['filesize'] = $stats['filesize'];
        $parameters[$pid]['filesize_compressed'] = $stats['filesize_compressed'];
    }
    $parameters['base_path'] = $base_path;
    return $parameters;
}

function getStats($filename){
    if(file_exists("$filename.stats") ){
        $line = file_get_contents("$filename.stats");;
        $edges_nodes_size = explode("\t",$line);
        $size['edges'] = number_format($edges_nodes_size[0]);
        $size['nodes'] = number_format($edges_nodes_size[1]);
        $size['filesize'] = human_filesize($edges_nodes_size[2] * 1024);
        $size['filesize_compressed'] = human_filesize(filesize("$filename.gz"));
        return $size;
    }
    else{
        return null;
    }


}
function human_filesize($bytes, $decimals = 2) {
    $sz = 'BKMGTP';
    $factor = floor((strlen($bytes) - 1) / 3);
    return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$sz[$factor];
}




function getFullNetwork($parameters,$path =null){
    $nodes = $parameters['full']['nodes'];
    $edges = $parameters['full']['edges'];
    $size = $parameters['full']['filesize'];
    $sizec = $parameters['full']['filesize_compressed'];
    $filename = $parameters['full']['filename'];

    $basename = basename($filename);

    $xgmml_file = "$filename.gz";
    $content = "<td><a href='$xgmml_file' class ='view_download' target='_self' >Download $basename</a></td>";
    if(! file_exists($xgmml_file) || ($nodes ==0 && $edges ==0)){
        $content = "<td><a class ='view_download' target='_blank' >Unavailable </a></td>";
        $edges = $nodes = $size = $sizec = "";
    }
    if($nodes == 0 && $edges ==0){
        $content = "<td><a class ='view_download' target='_blank' >Unavailable </a></td>";
    }

    $content .= "<td>$nodes</td>";
    $content .= "<td>$edges</td>";
    $content .= "<td>$size</td>";
    $content .= "<td>$sizec</td>";
    $row = "<tr>$content</tr>";
    return $row;
}
function getRepnodeNetwork($parameters,$path = null){
    #$repnodes = array(.40 , .45, .5, .55, .6, .65, .7, .75, .8, .85, .9, .95, 1);

    $repnodes = array(40,45,50,55,60,65,70,75,80,85,90,95,98,100);

    $rows = "";
    foreach($repnodes as $rep){

        $filename = $parameters[$rep]['filename'];
        $basename = basename($filename);
        $nodes = $parameters[$rep]['nodes'];
        $edges = $parameters[$rep]['edges'];
        $size  = $parameters[$rep]['filesize'];
        $sizec  = $parameters[$rep]['filesize_compressed'];


        $content = "<td>
            <a href='$filename.gz' class ='view_download' target='_self'>Download $basename</a>

            </td> ";
        $content .= "<td>$rep</td>";
        $content .= "<td>$nodes</td>";
        $content .= "<td>$edges</td>";
        $content .= "<td>$size</td>";
        $content .= "<td>$sizec</td>";
        $rows .=  "<tr>$content</tr>";

    }

    return $rows;

}



/*


if ((isset($_GET['id'])) && (is_numeric($_GET['id']))) {
    $generate = new stepa($db,$_GET['id']);
    if ($generate->get_key() != $_GET['key']) {
        echo "No EFI-EST Selected. Please go back";
        exit;
}
$analysis_id = $_GET['analysis_id'];
$analysis = new analysis($db,$analysis_id);

if (time() < $analysis->get_time_completed() + __RETENTION_DAYS__) {
    echo "Your job results are only retained for a period of " . __RETENTION_DAYS__;
    echo "<br>Please go back to the <a href='" . functions::get_server_name() . "'>homepage</a>";
    exit;
}
$stats = $analysis->get_network_stats();
$rep_network_html = "";
$full_network_html = "";

for ($i=0;$i<count($stats);$i++) {
    if ($i == 0) {
        $path = functions::get_web_root() . "/results/" . $analysis->get_output_dir() . "/" . $analysis->get_network_dir() . "/" . $stats[$i]['File'];
        $full_network_html = "<tr>";
        $full_network_html .= "<td style='text-align:center;'><a href='" . $path . "'><button>Download</button></a></td>";
        $full_network_html .= "<td style='text-align:center;'>" . number_format($stats[$i]['Nodes'],0) . "</td>";
        $full_network_html .= "<td style='text-align:center;'>" . number_format($stats[$i]['Edges'],0) . "</td>";
        $full_network_html .= "<td style='text-align:center;'>" . functions::bytes_to_megabytes($stats[$i]['Size'],0) . " MB</td>";
        $full_network_html .= "</tr>";
}
else {
    $percent_identity = substr($stats[$i]['File'],strpos($stats[$i]['File'],'-')+1);
    $percent_identity = substr($percent_identity,0,strrpos($percent_identity,'.'));
    $percent_identity = str_replace(".","",$percent_identity);
    $path = functions::get_web_root() . "/results/" . $analysis->get_output_dir() . "/" . $analysis->get_network_dir() . "/" . $stats[$i]['File'];
    $rep_network_html .= "<tr>";
    $rep_network_html .= "<td style='text-align:center;'><a href='" . $path . "'><button>Download</button></a></td>";
    $rep_network_html .= "<td style='text-align:center;'>" . $percent_identity . "</td>";
    $rep_network_html .= "<td style='text-align:center;'>" . number_format($stats[$i]['Nodes'],0) . "</td>";
    $rep_network_html .= "<td style='text-align:center;'>" . number_format($stats[$i]['Edges'],0) . "</td>";
    $rep_network_html .= "<td style='text-align:center;'>" . functions::bytes_to_megabytes($stats[$i]['Size'],0) . " MB</td>";
    $rep_network_html .= "</tr>";
}
}

}

else {

    echo "No EFI-EST Select.  Please go back";
    #        exit;

}

 */
?>	
<!--
    <img src="images/quest_stages_e.jpg" width="990" height="119" alt="stage 1">
    <hr>

    <h3>Download Network Files </h3>
    <p>&nbsp;</p>
    <h4>Full Network <a href="http://efi.igb.illinois.edu/efi-est/tutorial_download.php" class="question" target="_blank">?</a></h4>
    <p>Each node in the network is a single protein from the data set. Large files (&gt;500MB) may not open.</p>

    <table width="100%" border="1">
    <tr>
    <th></th>
    <th># Nodes</th>
    <th># Edges</th>
    <th>XGMML Size </th>
    <th>Zipped</th>
    </tr>
    <?php echo $full_network_html; ?>
</table>

    <p>&nbsp;</p>
    <div class="align_left">
    <h4>Representative Node Networks <a href="http://efi.igb.illinois.edu/efi-est/tutorial_download.php" class="question" target="_blank">?</a></h4>
    <p>Each node in the network represents a collection of proteins grouped according to percent identity.</p>
    </div>
    <table width="100%" border="1">
    <tr>
    <th></th>
    <th>% ID</th>
    <th># Nodes</th>
    <th># Edges</th>
    <th>XGMML Size</th>
    <th>Zipped </th>
    </tr>

    <?php echo $rep_network_html; ?>
</table>

-->
    <hr>

    </form>
    </div>

    <p style='text-align: center;text-decoration:underline'>
    <a href='http://enzymefunction.org/resources/tutorials/efi-and-cytoscape3'>New To Cytoscape?</a>
    </p>    
<?php include_once 'includes/footer.inc.php'; ?>


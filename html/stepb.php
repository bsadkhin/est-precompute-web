<?php
include_once 'includes/main.inc.php';
include_once 'includes/header.inc.php';
include_once 'includes/quest_acron.inc'; 

$html = null;

$token = isset($_GET['session']) ? $_GET['session'] : null; 
$job   = isset($_GET['id'])   ? $_GET['id']   : null; 
$email = isset($_GET['email']) ? $_GET['email'] : null; 

if(isset($token) && isset($job) && isset($email)){
    $html = generateResults($token,$job,$email);
}
else{
}


function generateResults($token,$job,$email){
    $iprscanResult = __PRECOMPUTE_JOBS_DIR__ . "/$job/$token/$token";
    if(file_exists($iprscanResult) && filesize($iprscanResult) > 0){
        return generateResultsHelper($iprscanResult,$email);    
    }
   print "FAILURE<br> $iprscanResult does not exist! <br>Jobs are only kept for 30 days"; 
}

function generateResultsHelper($file,$email){
    $th = "<tr><th>Annotation Type</th> <th>ID</th ><th>Description</th><th>Expect</th></tr>";
    $rows = "";
    $fh = fopen($file, "r");
    while(!feof($fh)){
        $line = explode("\t",fgets($fh));
        if(count($line) != 4){
            continue; #Skip bad lines
        }
        $type = $line[0];
        $id = $line[1];
        $idlink = returnURL(strtolower($type),$id,$email);
        if(!$idlink){
        $idlink = $id;
        }   
        $desc = $line[2];;
        if(strlen($desc) ==0){
            $desc = getLink($type,$id);
        }
        $score= $line[3];
            
        $row = "<td>$type</td><td>$idlink</td><td>$desc</td><td>$score</td>";
        
        $rows .= "<tr>$row</tr>";
    }
    fclose($fh);
    return "<table> $th $rows </table>";
}

function returnURL($annotation_type,$annotation_id,$email){
    if($annotation_type == 'pfam'){
        $url = "stepc.php?type=$annotation_type&name=$annotation_id&email=$email";
         #echo "Returned $url\n<br>";
         return "<a href='$url' target='_blank'>$annotation_id</a>";
    }
}
function getLink($annotation_type,$annotation_id){
    if(strtolower($annotation_type) == 'gene3d'){
        $annotation_id = htmlspecialchars($annotation_id);
        return "<a href='http://www.ebi.ac.uk/interpro/signature/$annotation_id'>Link Out</a>";
    }
    return "ERROR";
}


?>





<img src="images/quest_stages_b.jpg" width="990" height="119" alt="stage B">
   <hr>

    <h3>Completing Generate Data Set</h3>
    <p>&nbsp;</p>

<?php
if (!isset($html)){   
    echo "<p>An email will be sent when your data set generation is complete.</p>\n";
}
else{
    echo $html;
}
?>
        <p>&nbsp;</p>
    <p>&nbsp;</p>
    <p></p>
    <p>&nbsp;</p>
  </div>

  <div class="clear"></div>

</div>


<?php include_once 'includes/footer.inc.php'; ?>

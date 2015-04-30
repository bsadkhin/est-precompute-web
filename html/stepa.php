<?php 
include_once 'includes/main.inc.php';
include_once 'includes/header.inc.php'; 
error_reporting(E_ALL);
ini_set('display_errors', '1');

main();
function main(){
    disable_site();
}

function disable_site(){
    $enabled = __ENABLE_WEBSITE__;
    if($enabled == False){
        die( " EST-PRECOMPUTE is down for upgrade for a few hours today, Jan 13. Will be up by 5pm!\n");
    }
}
function returnListOfPfams_html($type){
    if($type == 'pfam'){
        $filename='pfam';
    } 
    else if($type == 'clan'){
        $filename ='clan';
    } 
    else{
        exit;
    }
    $file = fopen($filename, "r");
    if(! file_exists($file)){
        echo         ("Can't open file of Pfams\n");
    }
    while(!feof($file)){
        $line = fgets($file);
        $selects .= "<option name='$type'>$line</option>";   
    }
    fclose($file);
    return $selects;

}
function verify_email($email) {
    $email = strtolower($email);
    $hostname = "";
    if (strpos($email,"@")) {
        list($prefix,$hostname) = explode("@",$email);
    }

    $valid = 1;
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $valid = 0;
    }
    elseif (($hostname != "") && (!checkdnsrr($hostname,"ANY"))) {
        $valid = 0;
    }
    return $valid;
}


$email   = isset($_GET['email'])   ? $_GET['email']    : null;
$optionA = isset($_GET['sequence'])? $_GET['sequence'] : null;
$optionB = null;
$pfam_input = isset($_GET['families_input'])? $_GET['families_input'] : null;
foreach ($_GET as &$var) {
    $var = trim(rtrim($var));
}

if (isset ($_GET['submit'] )){
    //If you entered both blast and pfam/interpro, fail
    $optionB = $pfam_input;
    $type = 'pfam';
    if ( strlen($optionA) > 0 && strlen($optionB) > 0  ){ # || array_key_exists ($optionA,$_GET) && array_key_exists($optionB,$_GET) ) {
        $message = "<br><b>Please select only Option A or Option B $optionB</b></br>";
        #$message .= "input = $optionA $optionB";
    }
    else if ( strlen($optionA) ==0 && strlen($optionB) == 0  ) {
        $message = "<br><b>Please select Option A or Option B</b></br>";
        $a = strlen($optionA);
        $b = strlen($optionB);
        $message .= "You did not enter any input ";
    }
    else{
        if( $email == "Enter your email address" || strlen($email) < 5  || !verify_email($email)){
            $message ="Please enter a valid email address";

        }
        else if(strlen($optionA) > 1){
            $iprscan = new iprscan($_GET['email'],$_GET['sequence']);
            $message =  $iprscan->getError();
            echo "did not iprscan $optionA" . strlen($optionA);
        }
        else if(strlen($optionB) > 1){
            $get =  http_build_query( array("name" => "$optionB","email" => $email , "type" => "$type") );
            $message = "Error, couldn't go to step C";
            header("Location:" .  "stepc.php?" . $get);
        }
    }
    #}
}

include_once 'includes/quest_acron.inc';



?>



<script>
(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
    (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
        m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
})(window,document,'script','//www.google-analytics.com/analytics.js','ga');

ga('create', 'UA-51157342-1', 'illinois.edu');
ga('send', 'pageview');

</script>

<h3>Start With...    </h3>
<h4>An Introduction</h4>
<p>Start here if you are new to the &quot;Sequence Similarity Networks Tools&quot;.</p>
<form name="Introduction" action="http://enzymefunction.org/resources/tutorials/est-precompute" method="post">
<input type="submit" name="submit" value="GO TO TUTORIAL" class="css_btn_class">
</form>
<hr>
<img src="images/quest_stages_a.jpg" width="990" height="119" alt="stage 1">
<hr>
<h4>Input<a href="../efi-est/tutorial_startscreen.php" class="question" target="_blank">?</a></h4>
<form name="step1" method="get" action="<?php echo $_SERVER['PHP_SELF']; ?>">
<p>Option A: Generate data set of close relatives via InterProScan.  Enter only protein sequence.  Do not enter any fasta header information. (Max sequence length 65535) </p> 

<textarea class="blast_inputs" name='sequence'><?php if (isset($_GET['sequence'])) { echo $_GET['sequence']; } ?></textarea>
<br>
<hr>
Option B: Generate data set using Pfam IDs<br><hr>
<br>Database Release Interpro <?php echo __INTERPRO_VERSION__; ?>
<div><br>
<?php

if(!isset($_GET['type'])){
    $family_table = getTable('pfam');
}
else{
    $family_table = getTable($type);
}

echo $family_table,"<br><br>";

#Open a file containing a list of names, seperated by \n
function getTable($type){
    if(! file_exists($type)){
        return "";
    }   
    $FH = fopen($type, "r") or die("Couldn't open $type");  
    $html = "<div class='family_div' style='margin-left:20% ; margin-right:20% ' ><table name='$type' class='family_table' ><thead>
        <tr style='background: #25383C'>
        
        <th> ". ucfirst($type) . " Identifier </th>
        <th> Number of Sequences </th>
        </tr></thead><tbody>";
    if($FH){
        $DB = new database();
        $families_and_counts = $DB->getFamiliesAndCounts($type,__INTERPRO_VERSION__);
        #So we have a file of available pfams... So we check it against this list!
        while (($family = fgets($FH)) !== false) {
            $family = rtrim($family);
            $seq = $families_and_counts[$family];
            $html .= "<tr>
                <td><label>
                <input type='radio' id='$type' name='families_input' value='$family'></input>$family</label>
                </td>
                <td>
                $seq
                </td>
                </tr>\n";
        } 
    }
    $html .= "</tbody></table></div>";
    fclose($FH);
    return ($html);
}

?>



</select>

<!--<select class='chosen-select' name='clans_input' id='' style='width:700px' data-placeholder='Clan (448/514)'  >".
<option ></option>
<?php

#echo returnListOfPfams_html('clan');
?>
</select>
<br><br>
-->
<p>

<p>
<input type="text" name='email' value='<?php if (isset($_GET['email'])) { echo $_GET['email']; } else { echo "Enter your email address"; } ?>' 
class="sequences email" id='email' onfocus="if(!this._haschanged){this.value=''};this._haschanged=true;"><br>
<span class="smalltext">Used for data retrieval only</span>
</p>
<?php if (isset($message)) { echo "<b style='color: red;'>" . $message . "</b>"; } ?>       
<hr>
<input type="submit" name="submit" value="GO" class="css_btn_class" >

</form>




<?php include_once 'includes/footer.inc.php'; ?>

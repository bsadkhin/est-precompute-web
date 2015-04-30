<?php 
include_once 'includes/main.inc.php';
include_once 'includes/header.inc.php'; 

error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);
error_reporting(E_ALL);
ini_set('display_errors', '1');

//Some basic checking
$email = (isset($_GET['email'])? $_GET['email'] : null);
$name = (isset($_GET['name'])? $_GET['name'] : null);
$type = (isset($_GET['type'])? $_GET['type'] : null);
$message = null;

//Second submit of form, this is an artifact of retrofitting the website.
if(isset($_POST)){
    $email = (isset($_POST['email']) ? $_POST['email']   : $email);
    $name  = (isset($_POST['name'])  ? $_POST['name']    : $name);
    $type  = (isset($_POST['type'])  ? $_POST['type']    : $type);

}
//Prevent user from accessing this page without input
if($name == null || $type == null || $email == null){
    include_once 'includes/quest_acron.inc';
    echo "Please go to <a href='index.php'>Step A</a> and choose a precomputed family <br>";
   # echo "name=$name type=$type email=$email\n";
    exit; 

}
//Parse request and submit it

if(isset($name) && isset($type) && isset($email) && count($_GET) ==0){

    $parameters['job_email'] = $email;
    $parameters['job_type'] = 'filter';
    $parameters['job_family'] = $name; 
    $parameters['job_network_type'] = $type;
    $parameters['job_network_name'] = $_POST['network_name'];
    $parameters['job_expect_cutoff'] = $_POST['evalue'];
    $parameters['job_pid_cutoff'] = $_POST['pid'];
    $parameters['job_min_length']   = $_POST['min_length'];
    $parameters['job_max_length']   = $_POST['max_length'];

  #  print "Submtting a filterjob . " . $parameters['job_expect_cutoff'];
    $filter_job = new filterjob($parameters);
    $message = $filter_job->getError();
    if($message){
        #print "Something went wrong!";
    }
}

include_once 'includes/quest_acron.inc';
?>

<img src="images/quest_stages_c.jpg" width="990" height="119" alt="stage 1">
<hr>



<?php
$db = new database();
$family_count = $db->getFamilyCount($name,$type,__INTERPRO_VERSION__);
#$description = $db->getFamilyDescription($name,$type,__INTERPRO_VERSION__);
$description = 'none';

$heading =
    '<h3>View Plots /  Filter Network for '."$type $name".'</h3>
    <p>&nbsp;</p>
    <table>
    <thead><tr>
    <th> Name </th>
  <!--  <th>&nbspDescription&nbsp</th> -->
    <th> Sequence Count </th><tr></thead>

        <tbody>
        <tr>
        <td>'.$name.'</td>
   <!--     <td>'. $description . '</td> -->
        <td>'. $family_count .'</td>
        </tr>
        </tbody>
        </table>
   '."<p style='color:red;text-align:center'><b>$message</b></p>" .' 
        <h4>1: Analyze your data set<a href="http://efi.igb.illinois.edu/efi-est/tutorial_analysis.php" class="question" target="_blank">?</a></h4> 
    <p><strong>Important! </strong>View plots and histogram to determine the appropriate lengths and alignment score before continuing.</p>
    ';



$precomputed = new precomputed($name,$type);
$filepaths = $precomputed->getPlotFilepaths();
sort($filepaths);

$table ="";

foreach ($filepaths as $key =>$value){
    $path = $filepaths[$key]['path'];
    $bitscore_path = $filepaths[$key]['bitscore_path'];
    $title = $filepaths[$key]['title'];

    $title_html  = "<td><p>$title</p></td>";
    $view_button = "<td><a href='$path' class='view_download' target='_blank'>View</a></td>";
    $view_button2 = "<td><a href='$bitscore_path' class='view_download' target='_blank'>View Bitscore</a></td>";
    $download_button = "<td><a href='$path' class ='view_download' target='_blank' download='$title'>Download </a></td>";
    $download_button2 = "<td><a href='$bitscore_path' class ='view_download' target='_blank' download='$title'>Download 2 </a></td>";

        #bitscore
   # if(! file_exists($bitscore_path)){
        $download_button2 = ""; $view_button2 = "";
   # }
    if(! file_exists($path)){
        $view_button =   "<td><a class='view_download' target='_blank'>Unavailable  </a></td>"; 
        $download_button = "<td><a class ='view_download' target='_blank' download='$title'>N/A</a></td>";
        $download_button = "";
    }
    $row = "<tr>$title_html $view_button $view_button2  $download_button  $download_button2</tr>";

    $table .= $row;
}

$table = "<table>$table</table>";

echo $heading;
echo $table;


?>


<hr><p><br></p>
<h4>2: Choose alignment score cutoff to  filter network
<a href="http://efi.igb.illinois.edu/efi-est/tutorial_analysis.php" class="question" target="_blank">?</a>
<span style='color:red'>Recommended</span></h4>
<p>Select a threshold alignment score for output files. Default is 5 <p> <!-- You will input an integer which represents the exponent of 10<sup>-X</sup> where X is the integer.</p> -->
<form name="define_length" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" class="align_left">

<p><input type="text" name="evalue" placeholder='5' 
<?php if (isset($_POST['evalue'])) { 
    echo "value='" . $_POST['evalue'] ."'"; }
?>
    > alignment score</p>
    
  <!-- 
 <h4>3: Define length range<a href="tutorial_analysis.php" class="question" target="_blank">?</a>
    <span style='color:red'>Optional</span></h4>
    <p>If protein length needs to be restricted.</p>

    <p>
-->
<?php
    $question = '<a href="http://efi.igb.illinois.edu/efi-est/tutorial_analysis.php" class="question" target="_blank">?</a>';

    $form['min_length'] ="";
    $form['max_length'] ="";
    $form['pid']        =""; 
    if( isset($_POST['min_length'] )){
        $form['min_length'] = $_POST['min_length'];
    }
    if( isset($_POST['max_length'] )){
        $form['max_length'] = $_POST['max_length'];
    }
    if( isset($_POST['pid'] )){
        $form['pid']  = $_POST['pid'];
    }
    $min_length_input = "<input type='text' name='min_length' maxlength='20' value='{$form['min_length']}'>" . " Minimum Protein Length";
    $max_length_input = "<input type='text' name='max_length' maxlength='20' value='{$form['max_length']}'>" . " Maximum Protein Length";; 
    $pid_input        = "<input type='text' name='pid' maxlength='20' value='{$form['pid']}'>" . " Minimum % Identity ";
    
    echo 
    "<hr>
    <h4>3: Optional Filtering for Protein Scores $question
    <span style='color:red'>Optional</span></h4>
    <br>

        <b>Define Length Range if protein length needs to be restricted.</b><br><br>
        <p>$min_length_input</p< <br><br> <p>$max_length_input</p>
             <b>Choose Protein Percent Identity Cutoff to Remove edges by a percent identity</b><br><br>
    <p>$pid_input</p>";
    

?>
          <hr>  <h4>4: Provide Network Name <span style='color:red'>Optional</span></h4><br>
<?php
        $network_value = isset($_POST['network_name']) ? $_POST['network_name'] :'';
    echo "<p><input type='text' name='network_name' value='$network_value' maxlength='25'> </p>";

?>


<p>

<?php
    $url = "\"stepe.php?type=$type&name=$name\"";

    echo"

        <input type='hidden' name='type' value='$type'>
        <input type='hidden' name='email' value='$email'>
        <input type='hidden' name='name' value='$name'>
        <input type='hidden' name='id' value='none'>
        <input type='submit' name='analyze_data' value='Filter SSN' class='css_btn_class_recalc'></input>
        <input type='button' name='unfiltered_data' onclick='window.open($url)' value=\"See Unfiltered SSN with Minimum Score of 5\" class='css_btn_class_recalc'> </input><br>
        <p style='color:red;text-align:center'><b>$message</b></p>
        ";
?>
</p>

</form>
<div> <?php include_once 'includes/footer.inc.php'; ?>

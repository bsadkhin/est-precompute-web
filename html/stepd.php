<?php 
include_once 'includes/main.inc.php';
include_once 'includes/header.inc.php'; 
include_once 'includes/quest_acron.inc'; 


?>	
    <img src="images/quest_stages_d.jpg" width="990" height="119" alt="stage 1">
   <hr>


    <h3>Generating Network XGMML file...</h3>
    <p>&nbsp;</p>
<?php
if(isset($_GET['email'])){
    echo "  <p>An email will be sent when your files are complete. ({$_GET['email']})"; 
}
else{
    echo "Please go <a href='index.php'> Step A </a> and input your query. ";
}

?>
 </p>
    <p>&nbsp;</p>
    <p>Thank you. You may now close the window.</p>
    <p>&nbsp;</p>
    <p>&nbsp;</p>
    <p></p>
    <p>&nbsp;</p>
  </div>

<?php include_once 'includes/footer.inc.php'; ?>

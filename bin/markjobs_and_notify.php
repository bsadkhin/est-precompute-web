#!/usr/bin/env php
<?php
include_once '../html/includes/main.inc.php';


main();
//Check to see if job running
//Check to see if file exists
//
function main(){
    #checkLockFile();


    $db = new database();
    $iprscan_jobs = $db->getJobsWithFlag("iprscan",__RUNNING__);
    $filter_jobs = $db->getJobsWithFlag("filter",__RUNNING__);
    $job_rows = array_merge($iprscan_jobs,$filter_jobs);
    $directory = __PRECOMPUTE_JOBS_DIR__;

    foreach($job_rows as $row){

        //Get pertinent information
        $job_id = $row['job_id'];
        $job_pbs_id = $row['job_pbs_id'];
        $session = $row['job_session'];
        $email = $row['job_email'];
        $path = "$directory/$job_id/$session";
        $job_type = $row['job_type'];


        //Check if completed
        //Check to see if job is running and if it passed/failed
        $success = null;

        if(jobRunning("$job_pbs_id") == false){
            print "Checking $job_pbs_id is not running . . .  ";
            if($job_type == 'iprscan'){
                $filename = "$path/$session";
                $success = checkIPRSCANjob($filename);
            }
            else if($job_type == 'filter'){
                $success = checkXGMMLjob($path);
            }

        //Check to see if the email that was sent was a success or a fail!
            $status = sendEmail($job_id,$session,$email,$job_type,$success ,$row);
            $flag = "FINISHED";
            if($status == 0){
                $flag = "FAILED";
            }
            $db->updateFlag($job_id,$flag,'stop');
        }
        else{
            continue;
        }
    }
    $db = null;
}

function checkIPRSCANjob($filename){
    if(file_exists($filename) && filesize($filename) > 0){
        if( preg_match("/\t/",file_get_contents($filename)))
        {
            return true;
        }
    }
    return false;
}

function checkXGMMLjob($filepath){
    $fileExists = 0;
    $ls = explode("\n",`ls $filepath | grep "\.xgmml\$"`);
    if(count($ls) < 13){
       # print "Count of $ls = " . count($ls) . "\n";
        return false;
    }
    foreach ($ls as $xgmml){
        if(file_exists($filepath) && filesize($filepath) > 0){
            return true;
        }
    }
    return false;


}



/*Get PBS JOB ID
 *Check to see if running
 *Return : true/false
 */
function jobRunning2($PBS_JOBID){
    $running_command = "qstat -u " . __CLUSTER_USER__ . " | grep '$PBS_JOBID'"; 
    $running = `$running_command`;
    $running_status = strlen($running_command) > 1;
    $len = strlen($running_command);
    echo $running_command . " status=[$running_status] len[$len]\n";
    return strlen($running) > 1  ? true : false;
}

function jobRunning($PBS_JOBID){
    $command = "qstat -u " . __CLUSTER_USER__ . " | grep '$PBS_JOBID'"; 
    $descriptorspec = array(
        0 => array("pipe", "r"),  // stdin
        1 => array("pipe", "w"),  // stdout
        2 => array("pipe", "w"),  // stderr
    );
    $process = proc_open("$command", $descriptorspec, $pipes, dirname(__FILE__), null);
    $stdout = stream_get_contents($pipes[1]);
    fclose($pipes[1]);
    $stderr = stream_get_contents($pipes[2]);
    fclose($pipes[2]);

    if(isset($stdout) && strlen($stdout)>0){
        # print "Job $PBS_JOBID is running! Stdout is set[$stdout] \n $command";
        return true;
    }
    else{
        # print "Job $PBS_JOBID is NOT RUNNING stderr[$stderr] stdout[$stdout] $command";
        return false;
    }




}


function sendEmail($job_id,$session,$email, $job_type,$status, $row){
    $success = 1;
    $step = $job_type=='iprscan' ? 'stepb.php' : 'stepe.php';
    $status_title = $status == true ? 'Success' : 'Failure';
    #Get url from configuration file
    $folder = "est-precompute49";
    $url = "http://efi.igb.illinois.edu/$folder/$step?id=$job_id&session=$session&email=$email";
    $message = "Please find the results of your $job_type job at the following url: $url \n\n";
    $message .= "Job Type: $job_type \n";
    #$message .= "Session: $session \n";

    $message .= emailHelper($row);
    $subject = "Completion of $job_type JobID[$job_id] Status[$status_title]";
    if($status == false){
        $message = "Hello.\n Sorry, your [$job_type] job[$job_id][$session] did not have any results or failed. ";
        if($job_type =='iprscan'){
            $message .= "\n Please visit http://efi.igb.illinois.edu/efi-est/ and create a custom network. \n";
        }
        $success= 0;
    }
    print " Sent email with status [$success] for $job_id ";
    mail($email,$subject,$message);
    return $success;
}

function emailHelper($row){
    $message = "";
    foreach ( array('job_family','job_release','job_network_type','job_network_name','job_expect_cutoff',
        'job_pid_cutoff', 'job_min_length', 'job_max_length', 'job_sequence') as $col){


            $title = explode("_", $col);
            $title = ucfirst(implode("_", array_slice($title,1)));
            $item = $row[$col];

            if($title == 'expect_cutoff'){
                $title = "Alignment_score_cutoff"; 
            }
            if($title == 'release'){
                $title = 'Interpro Database Release';
            }
            if(isset($item) && strlen($item) > 0 ){
                if(is_numeric($item) && $item ==0){ #So this won't work if your job name is 0
                    continue;
                }
                else{
                    $message .= "$title : $item \n";
                }
            }
        }
return $message;
}



?>




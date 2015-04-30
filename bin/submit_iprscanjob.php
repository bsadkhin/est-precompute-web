#!/usr/bin/env php
<?php

$dir = dirname(__FILE__);
include_once "$dir/../html/includes/main.inc.php";

error_reporting(E_ERROR | E_PARSE);
main();
//Submits Jobs for IPR Scan
//Uses the database object to create a connection
//Gew new jobs
//create directories for each job, one by one
//copy the sequence fasta into a file_put_contents
//create a qsub script for the job, based on a template in a qsub object?
//submit the job
//let other scripts take care of errors and emailing
function main(){

    $db = new database();
    $job_rows = $db->getJobsWithFlag('iprscan',__NEW__);
    $new_jobs_count = count($job_rows);
    #Check for new jobs
    if($new_jobs_count ==0) {

        exit("No new jobs to submit\n");
    }

    $submitted_job_count = 0;
    foreach($job_rows as $row){
        print "Working on job $job_id\n";

        $job_id = $row['job_id'];
        $email = $row['job_email'];
        $token = $row['job_session'];
        $sequence = $row['job_sequence'];

        $dir = __PRECOMPUTE_JOBS_DIR__ . "/$job_id/$token"; 
        if(file_exists($dir)){
            print "Couldn't create $dir . It already exists. jobid[$job_id]\n";
        }
        else{
            $mkdir = mkdir($dir,0775,true);
        }
        if($mkdir == false){
            print "Uh Oh: Couldn't create folder [$dir] for job due to permissions: [$job_id]\n";
        }
        if(! file_exists($dir)){
            print "Skipping $dir\n";
            continue;
        }
        //Copy Sequence to a file
        file_put_contents ("$dir/interproscan.input",">IPRSCAN\n$sequence");
        createQsub($dir,$job_id,$token);
        $result  = submitQsub($dir,$job_id);
        if(isset($result['error'])){
            echo "Error = {$result['error']}"; #print this to a log
            exit;
        }
        else{
            $flag = "RUNNING";
            print "Updating $job_id to $flag\n";
            $db->updateFlag($job_id,$flag,null,$result['id']);
        }
        $submitted_job_count+=1;
        print "Successfully created directory for qsub [$dir] and submitted it. \n";
    }
    $db = null;
    print "Submitted $submitted_job_count of $new_jobs_count\n";
}

function createQsub($dir,$job_id,$token){
    $queue = __CLUSTER_QUEUE__;
    $account = __CLUSTER_ACCOUNT__;
    $qsub =
        "
        #!/bin/bash

        # ----------------QSUB Parameters----------------- #
        #PBS -S /bin/bash
        #PBS -N iprscan_[$job_id]
        #PBS -q $queue
        #PBS -l nodes=1:ppn=1
        #PBS -A $account
        # ----------------Load Modules-------------------- #
        module load iprscan/5.7-48
        # ----------------Your Commands------------------- #
        cd $dir
        FILENAME=iprscan.out
        TOKEN=$token
        echo \$PBS_JOBID  > PBS_JOBID
        interproscan.sh -f tsv -i interproscan.input -o \$FILENAME > interproscan.STDOUT
        cut -f 4-6,9 \$FILENAME | grep -P 'Pfam|Gene3D' | sort -u -k1,2 | sort -n -k4  > \$TOKEN
        ";

    file_put_contents("$dir/iprscan_qsub.sh",$qsub); #Submits job and dumps


}
function submitQsub($dir){
    $command = "qsub $dir/iprscan_qsub.sh";
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

    $return['error'] = null;
    if(isset($stdout) && strlen($stdout) > 0){
        $id = explode(".", $stdout);
        $return['id'] = $id[0];
    }
    else if(isset($stderr) && $strlen($stderr) > 0){
        $return['error'] = $stderr;
    }
    else{
        $return['error'] = 'Some other error';
    }
    return($return);
}

?>




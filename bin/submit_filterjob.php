#!/usr/bin/env php
<?php
$pwd = dirname(__FILE__);
include_once "$pwd/../html/includes/main.inc.php";

error_reporting(E_ERROR | E_PARSE);
main();
//Submits Jobs for Filtering XGMML
//Uses the database object to create a connection
//Gew new jobs
//create directories for each job, one by one
//copy the sequence fasta into a file_put_contents
//create a qsub script for the job, based on a template in a qsub object?
//submit the job
//let other scripts take care of errors and emailing
function main(){

    $db = new database();
    $job_rows = $db->getJobsWithFlag('filter',__NEW__);
    $new_jobs_count = count($job_rows);

    if($new_jobs_count ==0) {
        print "No new jobs to submit\n";
        exit;
    }

    $submitted_job_count = 0;

    foreach($job_rows as $row){
        $job_id = $row['job_id'];
        $email = $row['job_email'];
        $token = $row['job_session'];
        $data_dir = __PRECOMPUTE_DIR__ ;
        $output_dir = __PRECOMPUTE_JOBS_DIR__ ."/$job_id/$token"; #GET FROM CONFIG FILE
        $mkdir = mkdir($output_dir,0775,true);
        if($mkdir == false){
            print "JOB_ID:$job_id\t Error[Couldn't create folder [$output_dir] for job: [$job_id] ]\n";
            if(! file_exists($output_dir)){
                continue;
            }
        }
        createJobList($data_dir,$output_dir,$row);
        createQsub($job_id,$output_dir);
        $return  = submitQsub($output_dir);
        if(isset($return['error'])){
            echo "Queue is full or some other error {$return['error']}\n"; #print this to a log
        }
        else{
            $flag = "RUNNING";
            $db->updateFlag($job_id,$flag,null,$return['id']);
            $submitted_job_count+=1;
            print "Successfully created directory for qsub [$output_dir] and submitted it. \n";
        }
    }
    $db = null;
    print "Submitted $submitted_job_count of $new_jobs_count\n";
}




function createJobList($data_dir,$output_dir,$parameters){

    $name = $parameters['job_family'];
    $type = $parameters['job_network_type'];
    $cutoffs = '';
    $expect = $parameters['job_expect_cutoff'];
    $pid = $parameters['job_pid_cutoff'];
    $min = $parameters['job_min_length'];
    $max = $parameters['job_max_length'];

    if(isset($expect) && strlen($expect) > 0 && $expect !=0){
        $cutoffs .= ' -expect_cutoff ' . $expect;
    }
    if(isset($pid) && strlen($pid) > 0 && $pid !=0){
        $cutoffs .= ' -pid_cutoff ' . $pid;
    }
    if(isset($min) && strlen($min) > 0 && $min !=0){
        $cutoffs .= ' -length_min ' . $min;
    }
    if(isset($max) && strlen($max) > 0 && $max !=0){
        $cutoffs .= ' -length_max ' . $max;
    }

    $inputs = array(40,45,50,55,60,65,70,75,80,85,90,95,98,100,'full' );

    foreach ($inputs as $xgmml_name){
        $commands[] = "filter_xgmml.pl ".
            " -dir $data_dir/$type/$name ".
            " -output_dir $output_dir ".
            " -xgmml_name $xgmml_name ".
            $cutoffs;
    }    
    $commands_string= implode("\n", $commands);
    file_put_contents("$output_dir/xgmml.list", "$commands_string\n");

} 

function createQsub($job_id,$dir){
    $queue = __CLUSTER_QUEUE__;
    $account = __CLUSTER_ACCOUNT__;
    $module = __EFI_MODULE__;
    $job_list = "$dir/xgmml.list";    
    $qsub =
        "
        #!/bin/bash
        # ----------------QSUB Parameters----------------- #
        #PBS -S /bin/bash
        #PBS -N filter_xgmml[$job_id]
        #PBS -q $queue
        #PBS -l nodes=1:ppn=1
        #PBS -j oe
        #PBS -t 1-15
        #PBS -A $account
        # ----------------Load Modules-------------------- #
        module load $module
        # ----------------Your Commands------------------- #
        cd $dir
        JOB_LIST=$job_list
        command=`sed -n \${PBS_ARRAYID}p \$JOB_LIST`
        \$command > \$PBS_ARRAYID.log\n";
    file_put_contents("$dir/filter_xgmml.sh",$qsub); #Prints job 
}

function submitQsub($dir){
    $command = "qsub $dir/filter_xgmml.sh";
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
        $id = explode("[", $stdout);
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



function submitQsub_old($dir){
    $command = "qsub $dir/filter_xgmml.sh &> $dir/PBS_JOBID ";
    $error = rtrim(shell_exec($command));

    if(strpos($error,"cannot be loaded") != false){
        print "ERROR[$error]\n";
        return $error;
    }


    /*  #error = system ($command)
        if($error){
        return $error;
        }
        else{
        return null;
     */

    #$error = system($command);
    #if($error){
    #    print "to log, error, couldn't submit";
    #}
    #else{

    #if(return of command is not "Cluster is full" and job was able to submit){
    #  updateDB($job_id,$session,$flag);

    #} 
    # system($command);
}

?>




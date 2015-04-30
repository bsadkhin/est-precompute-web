<?php
/* Author : Boris Sadkhin
 * Purpose: Provide a way to add jobs to the database from the web interface
 * Date Created: July 29, 2014
 *
 */

class database{
    private $username;
    private $password;
    private $hostname;
    private $database;
    private $db;

    function __construct() {
        $this->username = __MYSQL_USER__;
        $this->password = __MYSQL_PASSWORD__;
        $this->hostname = __MYSQL_HOST__;
        $this->database = __MYSQL_DATABASE__;

        $this->db = new PDO("mysql:host={$this->hostname};dbname={$this->database}", $this->username, $this->password);
    }

    function close(){
        $this->db = null;
    }

    /*
     * Return job parameters by type
     */
    function getJobsByType($job_type){
        try{

            $stmt = $this->db->prepare("SELECT * FROM jobs WHERE job_type= :job_type");
            $stmt->bindParam(':job_type', $job_type);
            $stmt->execute();
            return  $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        catch (PDOException $e)
        {
            echo "Failure, could not retrieve data to db\n";
            return array("Couldn't retrieve data '$e'");
        }
    }
    /*
     * Update flag given a job id

    /*
     * Return job parameters by session/id
     */
    function getFilteredParameters($job_id,$session){
        try{

            $stmt = $this->db->prepare("SELECT * FROM jobs WHERE job_session= :session AND job_id= :job_id");
            $stmt->bindParam(':session', $session);
            $stmt->bindParam(':job_id', $job_id);
            $stmt->execute();
            $return = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $return[0];
        }
        catch (PDOException $e)
        {
            echo "Failure, could not retrieve data to db\n";
            return array("Couldn't retrieve data '$e'");
        }
    }
    /*
     * Update flag given a job id
     * Inputs: job_id and flag
     * 
     */
    function updateFlag($job_id,$flag,$start_stop='start',$PBS_ID=null){
        try{
            $time_stamp =  date("Y-m-d h:i:s"); 
            $update = "UPDATE jobs  SET job_flag= :flag , job_time_start= :time where job_id = :job_id";
            #print "Updating job[$job_id] to $flag\n";
            $stmt = $this->db->prepare($update); 
            if(isset($start_stop) && $start_stop == 'stop'){
                $update = "UPDATE jobs  SET job_flag= :flag , job_time_end= :time where job_id = :job_id";    
                $stmt = $this->db->prepare($update);
                #print "updated flag to stop";
            }
            $stmt->bindParam(':job_id', $job_id);
            $stmt->bindParam(':flag', $flag);
            $stmt->bindParam(':time', $time_stamp);
            $stmt->execute();

            #Update PBS JOB
            if(isset($PBS_ID)){
                $update="UPDATE jobs SET job_pbs_id = :PBS_ID where job_id= :job_id";
                $stmt = $this->db->prepare($update);
                $stmt->bindParam(':job_id', $job_id);
                $stmt->bindParam(':PBS_ID',$PBS_ID);
                $stmt->execute();
            }


        }
        catch (PDOException $e)
        {
            echo "Failure, could not add data to db\n";
            return "Couldn't update flag to $flag for $job_id error: '$e'";
        }


    }

    /*
     * Return all new jobs with a flag of NEW
     */
    function getJobsWithFlag($type,$flag){
        try{

            print "Getting jobs for type[$type] flag[$flag]\n";
            # $stmt = $this->db->prepare("SELECT * from job where job_type=$type AND job_flag=".$flag);
            $stmt = $this->db->prepare("SELECT * FROM jobs WHERE job_type= :type AND job_flag= :flag");
            $stmt->bindParam(':type', $type);
            $stmt->bindParam(':flag', $flag);
            $stmt->execute();
            #print "SELECT * FROM jobs WHERE job_type= :type AND job_flag= :flag";
            return  $stmt->fetchAll(PDO::FETCH_ASSOC);


        }
        catch (PDOException $e)
        {
            echo "Failure, could not add data to db\n";
            return "Couldn't insert data '$e'";
        }


    }


    #Input: Parameter Object
    #Returns an error code or nothing if successful
    #Emails user that their job was successfully added
    function addJob($parameters){
        $job_type = $parameters['job_type'];
        $job_flag = __NEW__;
        $job_release = __INTERPRO_VERSION__;
        $job_session = $this->generate_key();
        $job_email = $parameters['job_email'];
        $job_ip = $_SERVER['REMOTE_ADDR'];

        if($job_type == 'iprscan'){
            $job_sequence = $parameters['job_sequence'];
            $columns =  array('job_flag', 'job_type', 'job_release', 'job_email', 'job_sequence', 'job_session','job_ip');
            $inputs = array($job_flag,$job_type,$job_release,$job_email,$job_sequence,$job_session,$job_ip);
        }
        else if ($job_type =='filter'){
            $job_min_length = isset($parameters['job_min_length'])? $parameters['job_min_length']:NULL;
            $job_max_length = isset($parameters['job_max_length'])? $parameters['job_max_length']:NULL;
            $job_expect_cutoff = isset($parameters['job_expect_cutoff'])? $parameters['job_expect_cutoff']:NULL;
            $job_pid_cutoff = isset($parameters['job_pid_cutoff'])? $parameters['job_pid_cutoff']:NULL;
            $job_network_name = isset($parameters['job_network_name'])? $parameters['job_network_name']:NULL;
            $job_family = isset($parameters['job_family'])? $parameters['job_family']:NULL;
            $job_network_type = isset($parameters['job_network_type']) ? $parameters['job_network_type'] : NULL;
            $columns =  array('job_flag', 'job_type', 'job_release', 'job_email',
                'job_session','job_ip','job_family','job_network_name',
                'job_network_type','job_expect_cutoff','job_pid_cutoff',
                'job_min_length','job_max_length');
            $inputs = array($job_flag,$job_type,$job_release,$job_email,
                $job_session,$job_ip,$job_family,$job_network_name,
                $job_network_type,$job_expect_cutoff,$job_pid_cutoff,
                $job_min_length,$job_max_length);
        }
        $error= $this->insertJob($columns,$inputs);
        if($error == null){
            $this->notifyUser($job_type,$job_email);
            print "Erorr is null and notifyUserFailed with $job_type $job_email";
        }
        else{
            echo "error = '$error'";
        }
    }

    #Helper method for addjob
    function insertJob($columns,$inputs){
        $prepared = implode(",",array_fill(0,count($columns),'?'));
        $columns = implode (",",$columns);
        try{
            $stmt = $this->db->prepare("INSERT INTO jobs($columns) VALUES($prepared)");
            if($stmt->execute($inputs)){
                return null;
            }
            else{
                echo "\nPDOStatement::errorCode(): ";
                print $stmt->errorCode();
                print "FAILURE!";
                print $stmt->debugDumpParams();
                return $stmt->errorCode();
            }
        }
        catch (PDOException $e)
        {
            echo "Failure, could not add data to db\n";
            echo 'Connection failed: ' . $e->getMessage();
            return "Couldn't insert data '$e'";
        }
        return null;
    }

    /* Sends you to the splash page after submitting the job. Called by addJob automatically
     *
     */ 
    private function notifyUser($type,$email){
        if($type =='iprscan'){
            $query = header("Location: stepb.php" ."?email=$email");
        }
        else if($type =='filter'){
            $query = header("Location: stepd.php" ."?email=$email");
        }
        else{
            echo "Something went wrong: notifyUser";
        }
    }

    private function generate_key() {
        $key = uniqid (rand (),true);
        $hash = sha1($key);
        return $hash;
    }
    
    #these functions are used by precompute
    public function getFamilyCount($name,$type,$release){
        try{
            $stmt = $this->db->prepare("select counts from family where family= :name AND type = :type AND interpro_release= :release");
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':type', $type);
            $stmt->bindParam(':release', $release);
            $stmt->execute();
            $results =   $stmt->fetchAll();
            return $results[0][0];
        }
        catch (PDOException $e)
        {
            echo "Failure, could not retrieve data to db\n";
            return array("Couldn't retrieve data '$e'");
        }
    }

    public function getFamiliesAndCounts($type,$release){
        try{
            $stmt = $this->db->prepare("select family as NAME,counts as VALUE from family where type = :type AND interpro_release= :release");
            $stmt->bindParam(':type', $type);
            $stmt->bindParam(':release', $release);
            $stmt->execute();
            $results =   $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
            return $results;
        }
        catch (PDOException $e)
        {
            echo "Failure, could not retrieve data to db\n";
            return array("Couldn't retrieve data '$e'");
        }
    }

    #Tehse functions are used by admin



    #These functions are used by xgmml




}



?>

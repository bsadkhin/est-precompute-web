<?php
#Simple class to wrap filter xgmml call
class filterjob{

    private $error;
    function getError(){
        return $this->error;
    }

    function print_var_name($var) {
        foreach($GLOBALS as $var_name => $value) {
            if ($value === $var) {
                return $var_name;
            }
        }

        return false;
    }

    function checkInput($parameters){
        #Verify Parameters
        $network_name = strlen($parameters['job_network_name']) > 0? $parameters['job_network_name'] : null;
        $alignment_score = strlen($parameters['job_expect_cutoff']) > 0 ? $parameters['job_expect_cutoff'] : null;
        $pid = strlen($parameters['job_pid_cutoff']) > 0? $parameters['job_pid_cutoff'] : null;
        $min = strlen($parameters['job_min_length']) > 0? $parameters['job_min_length'] : null;
        $max = strlen($parameters['job_max_length'])> 0? $parameters['job_max_length'] : null;


        #Check to see if at least one parameter is input
        $options= 0;
        foreach (array($pid,$min,$max,$alignment_score,$network_name) as $filter){
            if(isset($filter) && strlen($filter) > 0){
                $options++;
            }
        }
        if($options ==0){
            return "You did not choose any filtering options. Please choose one or view the unfiltered network.";
        }
        //Name
        #if(strlen($network_name) < 1){
        #    return "Please provide name for generating the network. \n";
        #}
        if(strlen($network_name) > 25){
            return "Please limit name to only 25 characters";
        }
        $parameters['job_network_name'] = str_replace("/[^0-9A-Za-z]/","_",$network_name);

        //Score and length
        #   if(strlen($alignment_score) < 1){
        #       return "Please provide an alignment score cutoff \n";
        #   }
        if(preg_match("/[^0-9]/",$alignment_score)){
            return "Please provide an integer for alignment score threshold. You provided '$alignment_score'";
        }
        if(preg_match("/[^0-9]/",$min)){
            return "Please provide an integer for minimum sequence length. You provided '$min'";
        }
        if(preg_match("/[^0-9]/",$max)){
            return "Please provide an integer for maximum sequence length. You provided '$max'";
        }
        if(preg_match("/[^0-9]/",$pid)){
            return "Please provide an integer for percent identity. You provided '$pid'";
        }
        if($pid > 100){
            $pid = 100;
        }
        if( isset($alignment_score) && $alignment_score <= 5){
            $alignment_score = 5;
            return  "No filtering required, network already at score of 5. Choose the unfiltered network button.";
        }
        $parameters['job_type'] = 'filter';
        return null;
    }

    function __construct($parameters) {
        $this->error = $this->checkInput($parameters); 
        $db = new database();

        if(!isset($this->error)){
            $this->error = $db->addJob($parameters);
        }
        else{
            return $this->error;
        }
    }



}

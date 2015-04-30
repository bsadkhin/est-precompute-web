<?php
#Simple class to wrap IPRSCAN call


class iprscan{

    private $error;
    function getError(){
        return $this->error;
    }



    function checkInput($email,$sequence){
        #Verify email


        //Verify Sequence
        #This won't work unless you POST, otherwise GET is only 8kb
        if(strlen($sequence) > 65535){
            return "Error: Sequence Length too long";
        }
        if(strlen($sequence) < 9){
            return "Sequence length less than 9 (" . strlen($sequence) . " Amino Acids)";
        }
        $sequence = strtoupper($sequence);
        $sequence = trim($sequence);
        if(preg_match("/^>/",$sequence)){
            return "Error: Please remove fasta header information";
        }
        if(preg_match("/[^A-Z]/",$sequence)){
            return "Error: Please remove all non protein characters";
        }


        return null;


    }




    function __construct($email,$sequence,$parameters=null) {
        $db = new database();
        $sequence =  str_replace(array("\r\n", "\r", "\n" , ' '), "", $sequence);
        $this->error = $this->checkInput($email,$sequence); 
        if(!isset($this->error)){
            $parameters['job_ip'] = $_SERVER['REMOTE_ADDR'];
            $parameters['job_email'] = $email;
            $parameters['job_sequence'] = $sequence;
            $parameters['job_type'] = 'iprscan';
            $this->error = $db->addJob($parameters);
        }
    }



}

<?php


// Function : given a pfam/gene3d/ssf , etc, create a set of filenames to associated files
// Author : Boris Sadkhin
// Date : July , 2014


class filtered_xgmml{

    ////////////////Private Variables//////////
    private $name;
    private $parent;
    private $basedir = "data/precompute"; #A constant in a config file    
    private $parameters ; 
    ///////////////Public Functions///////////

    public function __construct($job_id,$session) {
        $db = new database();
        $parameters = $db->getFilteredParameters($job_id,$session);
        $parameters['base_path'] = $this->basedir;
        $this->parameters = $parameters;
    }

    public function getParameters(){
        return $this->parameters;
    }

    public function getBaseDir(){
        return $this->basedir;
    }
    public function getBaseDirXgmml(){
        return $this->basedir . "/xgmml";
    }

    public function __destruct() {
    }




}


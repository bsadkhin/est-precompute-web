<?php


// Function : given a pfam/gene3d/ssf , etc, create a set of filenames to associated files
// Author : Boris Sadkhin
// Date : July , 2014


class precomputed{

    ////////////////Private Variables//////////
    private $name;
    private $parent;
    private $basedir = "data/release48"; #A constant in a config file    

    ///////////////Public Functions///////////

    public function __construct($name,$type) {
        $base_directory =  $this->basedir;
        if($type == "clan"){
            $base_directory .= "/clans/$name";
        }
        else if($type == "pfam"){
            $base_directory .= "/pfam/$name";
        }
        else if($type == "gene3d"){
            $base_directory .= "/gene3d/$name";
        }
        else if($type == "ssf"){
            $base_directory .= "/ssf/$name";
        }
        else{
            echo "Improper type: $type";
            exit;
        }
        $this->basedir = $base_directory;
    }

    public function getBaseDir(){
        return $this->basedir;
    }
    public function getBaseDirXgmml(){
        return $this->basedir . "/xgmml/original";
    }


    public function returnHello(){
        return "hello";

    }


    public function getPlotFilepaths(){
        $paths = array();

        $basedir =  $this->basedir;

        $paths['length_histogram']['path']  = $basedir . "/quartiles/length_histogram.png"; 
        $paths['alignment_length']['path']  = $basedir . "/quartiles/alignment_length.png"; 
        $paths['number_of_edges']['path']   = $basedir . "/quartiles/number_of_edges.png";
        $paths['percent_identity']['path']  = $basedir . "/quartiles/percent_identity.png"; 
    
        
        $paths['length_histogram']['path']  = $basedir . "/plots/r_hist_length.png"; 
        $paths['alignment_length']['path']  = $basedir . "/plots/r_quartile_align.png"; 
        $paths['number_of_edges']['path']   = $basedir . "/plots/r_hist_edges.png";
        $paths['percent_identity']['path']  = $basedir . "/plots/r_quartile-perid.png"; 


        $paths['length_histogram']['bitscore_path']  = $basedir . "/quartiles/bitscore/length_histogram.png"; 
        $paths['alignment_length']['bitscore_path']  = $basedir . "/quartiles/bitscore/alignment_length.png"; 
        $paths['number_of_edges']['bitscore_path']   = $basedir . "/quartiles/bitscore/number_of_edges.png";
        $paths['percent_identity']['bitscore_path']  = $basedir . "/quartiles/bitscore/percent_identity.png"; 


        $paths['alignment_length']['title']  = "Alignment Length Quartile Plot" ; 
        $paths['number_of_edges']['title']   = "Number of Edges Histogram";
        $paths['length_histogram']['title']  = "Length Histogram"; //Number of Edges Histogram";
        $paths['percent_identity']['title']  = "Percent Identity Quartile Plot"; 


        return $paths;

    }


    public function __destruct() {
    }




}


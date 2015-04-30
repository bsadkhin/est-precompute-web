#!/usr/bin/env perl
# Extract matching PFAMS
# Author Boris Sadkhin
#
use strict;
my $filename = shift @ARGV;
if(not -s $filename){
    die "No iprscan output file provided";
}

sub generateFileName{
    return "$_[0].PotentialFamilies";

}

my $call = "cut -f 4-6,9 $filename | grep Pfam | sort -u -k1,2 | sort -n -k4 | tee " . generateFileName($filename);
#my $call = "cut -f 4-6,9 $filename | grep Pfam | tee  " . generateFileName($filename);
print "$call\n";
system($call);



#!/usr/bin/env perl
use strict;
use File::Basename;
my $dir = dirname(__FILE__);

chomp(my $whoami=`whoami`);
if($whoami ne "efi-precompute"){
    die "Must be run as efi-precompute";
}


if(-s "$dir/lockfile"){
    die "Already running submissions script";
}
else
{
    `touch $dir/lockfile`;
    if ($?){
        die "Couldn't create lock file\n";
    }
}


print `pwd`;
chdir("$dir/../") or die $!;
print `pwd`;

print "Submitting iprscanjobs:\n";
system("php submit_iprscanjob.php");
sleep 5;

print "Submitting filterjobs:\n";
system("php submit_filterjob.php");
sleep 5;

print "Marking jobs as completed and sending emails: \n";
system("php markjobs_and_notify.php");
sleep 5;

unlink("cron/lockfile");

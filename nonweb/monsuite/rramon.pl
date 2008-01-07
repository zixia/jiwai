#!/usr/bin/perl -w

use strict;
use Net::Telnet;

sub genRraPath {
    my $robot = shift;

    my $rraPath = '/opt/rra/';

    my %rraMoMt = (
        'gtalk' => 'robot_mo_609.rrd',
        'msn'   => 'robot_mo_611.rrd',
        'qq'    => 'robot_mo_613.rrd',
        'skype' => 'robot_mo_615.rrd',
        'sms'   => 'robot_mo_617.rrd',
        'fetion'=> 'robot_mo_619.rrd',
    );

    return ($rraMoMt{$robot}) ? $rraPath . $rraMoMt{$robot} : undef;
}

sub fetchCountFromRra {
    my ($rraFile, $interval) = @_;
    my ($mo, $mt)   = (0, 0);

    open FD, "rrdtool fetch --start='-" . $interval ."' $rraFile AVERAGE |" or die "$!";

    while (<FD>) {
        chomp;
        if (m/^\d+:\s+(\d.*?)\s+(\d.*?)$/si) {
            if (($mo > 0 and $mt > 0) and (int($1) > 10 or int($2) > 10)) {
                next;
            }
            $mo += int($1 * 300 + .5);
            $mt += int($2 * 300 + .5);
        }
    }

    close FD;

    return ($mo, $mt);
}

sub sendAlert {
    my ($robot, $reason) = @_;
    $reason = "NoMoreMoReceived" unless defined $reason;

    my ($ip, $port) = ('60.28.194.36', '50020');
    `wget http://$ip:$port/alert/$robot/$reason`;
}

my $interval = 60 * 30; ## half an hour
my ($lower, $upper) = (1, 0);   ## between lower and upper
my ($silentStart, $silentStop) = (0, 8);    ## silent between start and stop

my $hourNow = `date +%H`; chomp $hourNow;
if ($hourNow > $silentStart and $hourNow < $silentStop) {
    die "Shh... Genius at sleep";
}

my @robots = ('gtalk', 'msn', 'qq', 'fetion', 'sms');

for my $robot (@robots) {
    my $rra = genRraPath($robot);
    my ($mo, $mt) = fetchCountFromRra($rra, $interval);

    if ($mo < $lower) {
        warn "$robot received no more than $lower status(es) in the last $interval sec(s).";
        sendAlert($robot);
    }
}

0;

#
# Jiwai::Censor.pm - NetService Censors
#
# Purpose: Provide a convenient way for detecting
#          network services
#
# Copyright (c) 2000-2007 Wang Hongwei <glinus |AT| gmail.com>.
# All Rights Reserved. Std. disclaimer applies.
# Artificial License, same as perl itself. Have fun.
#
# namespace
package Jiwai::Censor;

use strict;
use warnings;
use Net::Telnet;
use Sys::Syslog;

use Carp;
use Exporter;

$Jiwai::Censor::VERSION = 1.00;

use vars  qw(@ISA @EXPORT_OK);
use base qw(Exporter);
@EXPORT_OK = qw(DetectSsh DetectHttp);

=pod
Subroutine using spawn and IO::Pty to establish a real connection to SSH Servers
While the server response with the pattern /password/, it returns 1, 0 otherwise
=cut
sub DetectSsh {
    my ($host, $port, $user) = @_;
    my ($pty, $ssh, @lines);
    my $prompt = '/home/wanghw:~> $/';

    $port = 22 unless defined $port;
    $user = 'wanghw' unless defined $user;

    $pty = &_spawn("ssh", "-l", $user, $host, "-p", $port);  # spawn() defined below

    $ssh = new Net::Telnet (-fhopen => $pty,
                            -prompt => $prompt,
                            -telnetmode => 0,
                            -cmd_remove_mode => 1,
                            -output_record_separator => "\r",
                            -timeout => 2);

    unless ($ssh->waitfor(-match => '/password/i', -errmode => "return")) {
            warn "problem connecting to host: ", $ssh->lastline;
            return 0;
    }

    1;
}

=pod
Internal Subroutine, fork a pty for usage
=cut
sub _spawn {
    my(@cmd) = @_;
    my($pid, $pty, $tty, $tty_fd);

    ## Create a new pseudo terminal.
    use IO::Pty ();
    $pty = new IO::Pty
        or die $!;

    ## Execute the program in another process.
    unless ($pid = fork) {  # child process
        die "problem spawning program: $!\n" unless defined $pid;

        ## Disassociate process from existing controlling terminal.
        use POSIX ();
        POSIX::setsid
            or die "setsid failed: $!";

        ## Associate process with a new controlling terminal.
        $tty = $pty->slave;
        $tty_fd = $tty->fileno;
        close $pty;

        ## Make stdio use the new controlling terminal.
        open STDIN, "<&$tty_fd" or die $!;
        open STDOUT, ">&$tty_fd" or die $!;
        open STDERR, ">&STDOUT" or die $!;
        close $tty;

        ## Execute requested program.
        exec @cmd
            or die "problem executing $cmd[0]\n";
    } # end child process

    $pty;
}

sub DetectHttp {
    my ($host, $port, $uri, $pattern) = @_;

    $port = 80 unless defined $port;
    $uri  = '/' unless defined $uri;
    $pattern = 'jiwai.de' unless defined $pattern;

    my %headers = (
        'Host'  => $host,
        'User-Agent'    => 'JWMonAlertBeta',
    );

    my $http = new Net::Telnet (
        Host    => $host,
        Port    => 80,
        Timeout => 2);

    ## HTTP Protocol
    $http->print('GET '. $uri . ' HTTP/1.0');

    while (my ($k, $v) = each %headers) {
        $http->print($k. ': '.$v);
    }

    $http->print("");
    ## END HTTP Protocol

    unless ($http->waitfor(-match => "/$pattern/i", -errmode => "return")) {
            warn "problem connecting to host: ", $http->lastline;
            return 0;
    }

    1;
}

# keep this one
1;
__END__


#
# Jiwai::Sms.pm - SMS Module
#
# Purpose: Provide a convenient way for sending 
#          short message via TTY device
#
# Copyright (c) 2000-2007 Wang Hongwei <glinus |AT| gmail.com>.
# All Rights Reserved. Std. disclaimer applies.
# Artificial License, same as perl itself. Have fun.
#
# namespace
package Jiwai::Sms;

use strict;
use warnings;
use Device::Modem;
use Sys::Syslog;

use Carp;
use Exporter;

$Jiwai::Sms::VERSION = 1.00;

use constant NEWMSG  => 'CMTI';

use vars  qw(@ISA @EXPORT_OK);
use base qw(Exporter);
@EXPORT_OK = qw(openDevice sendSms cleanStorage waitReply closeDevice);

sub new {
    my ($device) = @_;
    my $modem;
    bless {
        _device => $device, _modem => $modem
    }, ;
}

sub openDevice {
    my ($obj) = @_;
    $obj->{_modem} = new Device::Modem( port => $obj->{_device} , log => 'File,/tmp/modem.log' );

    die "no device specified" unless defined $obj->{_device};

    $obj->{_modem}->connect(
        baudrate    => 9600,
        databits    => 8,
        parity      => 0,
        stopbits    => 1
    ) or die "failed open $obj->{_device} : $!";

    syslog("info", "successful open $obj->{_device}");

    $obj->{_modem}->echo(0);

    $obj->{_modem}->atsend( 'AT' . Device::Modem::CR);
    $obj->{_modem}->atsend('AT+CMGF=1' . Device::Modem::CR);

    1;
}

sub closeDevice {
    my ($obj) = @_;
    $obj->{_modem}->disconnect();

    syslog("info", "successful close $obj->{_device}");

    1;
}

sub sendSms {
    my ($obj, $msg, $pPhones) = @_;
    die "no message body specified" unless defined $msg;

    my @phones = @$pPhones;
    my $endOfMsg = '';
    my ($answer, $token);

    $obj->{_modem}->atsend('AT+CMGW=1' . Device::Modem::CR);
    $answer = $obj->{_modem}->answer('> ', 1);

    $obj->{_modem}->atsend($msg . $endOfMsg);
    $answer = waitReply($obj, 5, "CMGW");

## AT+CMGW, STORE THE MSG TO MODEM FOR FURTHER USE 
    if (defined $answer and $answer =~ /\+CMGW:\s+(\d+)/i) {
        $token = $1;
    } else {
        warn "error store the msg to modem";
        return 0;
    }

## LOOP WITH AT+CMSS TO SEND THE MSG
    for my $phone (@phones) {
        $obj->{_modem}->atsend('AT+CMSS=' . $token . ',' . $phone. ',129' . Device::Modem::CR);
        $answer = waitReply($obj, 5, "CMSS");
        (defined $answer) ? syslog("info", "sendto $phone") : syslog("err", "failed sendto $phone");
    }

## CLEAN THE STORAGE WITH AT+CMGD
    if (defined $token) {
        $obj->{_modem}->atsend('AT+CMGD=' . $token . Device::Modem::CR);
    }

    1;
}

sub waitReply {
    my ($obj, $timeout, $pattern) = @_;

    while ($timeout) {
        my $answer = $obj->{_modem}->answer($pattern, 1);
        if (defined $answer and $answer=~/$pattern/i) {
            return $answer;
        }
        --$timeout;
        sleep 1;
    }

    return undef;
}

=pod
flag:lengthOfSmsBody:SmsBody:CellPhone1:CellPhone2:...:CellPhoneX, eg.
s:3:Or2:13520805254:10086

flag    - the FLAG could be one of 's'(Simplex), 'd'(Duplex)
length  - the length of sms body in bytes
smsbody - content to send
cell    - phone number(s)

sub dispatchSms {
    my ($line, $modem) = @_;
    die "no line specified" unless defined $line;
    die "no modem specified" unless defined $modem;

    my @lineItems = split(':', $line);

    my $lex = shift @lineItems;
    if ($lex ne 's' and $lex ne 'd') 
    {
        warn "protocol do not match : $line";
        return 0;
    }

    my $len = shift @lineItems;
    my $msg = shift @lineItems;
    my $answer;

    if ($len != length($msg)) {
        warn "length do not match : $line";
        return 0;
    }

    sendSms($modem, $msg, \@lineItems);

    if ($lex eq 'd') {
        $answer = waitReply($modem, 20, "CMTI");
        unless (defined $answer) {
            warn "no reply received";
            return 0;
        }

        print $answer, "\n";
    }

    1;
}

sub smsServer {
    my ($fifo, $modem) = @_;
    die "no fifo specified" unless defined $fifo;
    die "no modem specified" unless defined $modem;

    unless( -p $fifo) {
        unlink $fifo;
        require POSIX;
        POSIX::mkfifo($fifo, 0700) or die "can't mkfifo $fifo: $!";
    }

    open (FIFO, "<", $fifo) or die "can't open $fifo: $!";

    my $loop = 1;

    while ($loop) {
        while (<FIFO>) {
            if (m@bye@i) { $loop=0; last; }
            chomp;
            syslog("info", "recv $_");
            print FIFO dispatchSms($_, $modem);
        }
    }

    close FIFO;

    1;
}
=cut

sub cleanStorage {
    my $obj = shift;

    $obj->{_modem}->atsend("AT+CMGD=1,4" . Device::Modem::CR);
}

# keep this one
1;
__END__


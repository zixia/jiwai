#!/usr/bin/perl

use strict;
use warnings;

#########################################################
# JiWai.de queue(mo/mt)
#########################################################
my $IM_QUEUE='/var/cache/tmpfs/jiwai/queue/';
my $MSN_QUEUE=$IM_QUEUE . 'msn/';

my $IDLE_CIRCLE=1;
my $IDLE_CIRCLE_MAX=64;

use Time::HiRes qw/gettimeofday/;
use Data::Dumper;

my $is_connected = 0;

sub jiwai_queue_mt {
	return unless ( $is_connected );
	my $MAX_RETURN = 100;

#XXX
	print STDERR ".";

	my $queue_dir = $MSN_QUEUE.'mt/';
	if ( ! opendir(DIR, $queue_dir) ){
		print STDERR "open dir[$queue_dir] error $!\n";
		return;
	}

	my @MTs;

	my $counter = 0;
	while ( $_=readdir(DIR) ){
		next unless /^msn_/;
		
		my $file = $queue_dir . $_;

		#print "\nprocessing: $file\n"; 

		my ( $email, $msg );
		if ( open(FD,"<$file") ){
			my $file_content = join('',<FD>);
			close FD;
			
			if ( $file_content=~m#(.+?)\n\n(.+)#s ){
				my ($head,$body) = ($1,$2);
				my $email = $1 if $head=~m#^ADDRESS: msn://(\S+)#i;

				my @mt = ($email,$body, $file);
				push ( @MTs, \@mt );

				$counter++;
				last if $counter > $MAX_RETURN;
			}
		}
	}
	closedir DIR;

	if ( ! $counter ){
		if ( $IDLE_CIRCLE > $IDLE_CIRCLE_MAX ){
			$IDLE_CIRCLE = $IDLE_CIRCLE_MAX;
		}else{
			$IDLE_CIRCLE *= 2;
		}
		my $sleep_sec = 0.001 * $IDLE_CIRCLE++;

		select (undef,undef,undef,$sleep_sec) ;
		return;
	}

	$IDLE_CIRCLE = 0;
	return @MTs;
}

sub jiwai_queue_mo {
	my ( $email, $msg ) = @_;

	return unless ( $is_connected );

	return unless ( $email && $msg );

	return if ( $email eq 'thw416@hotmail.com' );

	my $queue_file;

	do {
		my ($s, $usec) = gettimeofday();
		$queue_file = $MSN_QUEUE . 'mo/msn__' . $email . "__${s}_$usec";
	}while( -e $queue_file );

	if ( open (FD, ">$queue_file") ){
		print FD "ADDRESS: msn://$email\n\n$msg";
		close FD;
	}else{
		print STDERR "open file $!\n";
		return;
	}
	return 1;
}
#########################################################
# JiWai.de queue(mo/mt)
#########################################################

unshift(@INC,'.');
use Data::Dumper;

use Net::MSN;
use IO::Select;
use POSIX;

use Data::Dumper;

my $handle = 'wo@jiwai.de';
my $password = 'Beta@JiWai741de';

my $D = 0;
my $PIDFile = './msn-client.pid';
my $LogFile = './msn-client.log';
my $timeout = 0.01;
my $s;


my %admin = (
  'zixia@zixia.net' => 1
);

if (defined $ARGV[0]) {
  if ($ARGV[0] =~ /\-v/i) {
    print "Net::MSN Version: ". $Net::MSN::VERSION. "\n";
    exit;
  } elsif ($ARGV[0] =~ /\-d/i) {
    $D = 1;
  }
}

if ($D == 1) {
  &demonize_me();
} else {
  $s = IO::Select->new();
  $s->add(\*STDIN);
}

my $debug = 1;

my $client;

if ( ! $debug ){
	$client = new Net::MSN(
  Debug           =>  0,
  Debug_Lvl       =>  0,
  Debug_STDERR    =>  0,
  Debug_LogCaller =>  0,
  Debug_LogTime   =>  0,
  Debug_LogLvl    =>  0,
  Debug_Log       =>  $LogFile
	);
}else{
	$client = new Net::MSN(
  Debug           =>  1,
  Debug_Lvl       =>  3,
  Debug_STDERR    =>  1,
  Debug_LogCaller =>  1,
  Debug_LogTime   =>  1,
  Debug_LogLvl    =>  1,
  Debug_Log       =>  $LogFile
	);
}

$client->set_event(
  on_connect => \&on_connect,
  on_status  => \&on_status,
  on_answer  => \&on_answer,
  on_message => \&on_message,
  on_join    => \&on_join,
  on_bye     => \&on_bye,
  auth_add   => \&auth_add
);

print STDERR "Try to connect...\n";
$client->connect($handle, $password);
print STDERR "Connecting...\n";


while (1) {
  my $ret = $client->check_event();
  if ( $ret ){
	print STDERR "*";
  	$IDLE_CIRCLE = 0;
  }
  &checkSTDIN() unless ($D == 1);
  &checkQueue();
}
die("Robot quit\n");

sub checkQueue {
	my @MTs = jiwai_queue_mt();

	foreach my $mt ( @MTs ){
		my ($email,$msg,$file)  = @$mt;
print "\nmt: $email [$msg] of $file\n";
        my $r = $client->sendmsg($email, $msg);
        if (defined $r && $r){
			unlink $file;
		}else{
			# FIXME move to retry queue, to provent full fill the mt queue, which can lead to deadlock(starve)
 	      	print STDERR $email . " is not online or not on your contact list\n";
			
			if ( $file=~/([^\/]+)$/ ) {
				my $retry_file_name = $MSN_QUEUE . $1;
				my $quarantine_dir = $MSN_QUEUE . "quarantine";
				mkdir $quarantine_dir if ( ! -e $quarantine_dir );
				link $file, $quarantine_dir;
			}

			unlink $file;
		}
	}
}

sub checkSTDIN {
  if (my @r = $s->can_read($timeout)) {
    foreach my $fh (@r) {
      my $input = <$fh>;
      print '> '. $input;
      chomp($input);
      
      my ($cmd, @data) = split(/ /, $input);

      next unless (defined $cmd && $cmd);

      if ($cmd eq 'call') {
        if (defined $data[0]) {
          unless ($client->call($data[0])) {
            print $data[0]. " is not online or not on your contact list\n";
          }
        } else {
          print "no party specified to call!\n";
        }
      } elsif ($cmd eq 'msg') {
        my $calling = shift @data;
        my $message = join(' ', @data);
        my $r = $client->sendmsg($calling, $message);
        print $calling. " is not online or not on your contact list\n"
          unless (defined $r && $r);
      } elsif ($cmd eq 'list') {
        $client->send('LST', 'RL');
      } elsif ($cmd eq 'quit') {
        $client->disconnect();
        exit;
      } elsif ($cmd eq 'ping') {
        $client->sendnotrid('PNG');
      } elsif ($cmd eq 'who') {
        my $calling = shift @data;
        my $response = &who();
        $client->sendmsg($calling, $response);
      } elsif ($cmd =~ /die/) {
        die;
      } elsif ($cmd eq 'send') {
        my ($command, @payload) = @data;
        my $payload = '';
        if (@payload && @payload >= 1) {
          $payload =  join(' ', @payload);
        }
        print STDERR "SEND: ". $command. ' '. $payload. "\n";
        $client->send($command, $payload);
      } elsif ($cmd eq 'dump') {
        use Data::Dumper;
        print '$client = '. Dumper($client). "\n";
      }
    }
  }
}

sub on_connect {
  $client->{_Log}("Connected to MSN @ ". $client->{_Host}. ':'. 
    $client->{Port}. ' as: '. $client->{ScreenName}. 
    ' ('. $client->{Handle}. ")", 3);
  # by zixia: set online flag
  # FIXME: set offline flag;
  print STDERR "\nConnected to MSN @ ". $client->{_Host}. ':'. 
    $client->{Port}. ' as: '. $client->{ScreenName}. 
    ' ('. $client->{Handle}. ")\n\n";
  $is_connected = 1;
}

sub on_status {
  # FIXME
}

sub on_message {
  my ($sb, $chandle, $friendly, $message) = @_;

  print "\n$friendly <$chandle>: [". $message. "]\n" unless ($D == 1); 

#<msnobj Creator="callow819@yahoo.com.cn" Size="24758" Type="2" Location="TFRE.dat" Friendly="AAA=" SHA1D="q2x6DeSxjSJvn8WUUDg7HHqXz38=" SHA1C="3S8QlkpsiW5dearxAvLz2toQ6AM="/>
# <msnobj Creator="jessicazhuzhu@hotmail.com" Type="2" SHA1D="WWQ5uZ36cljMBjyHNa6Q/jg5i4I=" Size="4443" Location="0" Friendly="AjACMAIwAAA="/>  3 分钟前  来自于 msn  Icon_star_empty   
#<msnobj Creator="callow819@yahoo.com.cn" Size="220" Type="2" Location="TFR3.dat" Friendly="AAA=" SHA1D="s5JHL/zEfso5pIeAvv99HUnzUvY=" SHA1C="9YTwtENnHd1TIGnWOviynn4B/Eo="/>

#[Client-Name: Gaim/2.0.0beta6
#Chat-Logging: Y
#]
# Client-Name: Miranda IM 0.6.8 Unicode (MSN v.0.6.0.2)
  return if $message=~m#^Client-Name:[\w\d\s\/\-:\.]+#sig ;

# MIME-Version: 1.0
#Content-Type: text/x-bobo
#sv: 4.2.28.25
#mv: 8.1.178.0
#state: ssShellHandShake1
#bobo1: 4157841205731911335491593.2343381297336730900564481
	return if $message=~m#^MIME-Version: #i;

# FIXME: we ignore the full msg that include msn emote icon now.
#  $message=~s#<msnobj\s+Creator="[^"]+"\s+\S+="[^"]+"\s+.+?/>##ig;
  return if $message=~m#<msnobj\s+Creator="[^"]+"\s+\S+="[^"]+"\s+.+?/>#si;


  return jiwai_queue_mo ($chandle,$message);

  if ($message =~ /^reply/i) {
    $sb->sendmsg('yes, what would you like?');
  } elsif ($message =~ /^call\s*(.+?)$/i) {
    $client->call($1);
  } elsif ($message =~ /^calc([^\s])*\s*(.+?)$/) {
    my $sum = $2;
    if ($sum =~ /^[0-9 \-\+\*\/\(\)\^]*$/) {
      $sum = '$ans = '. $sum;
      my $ans;
      eval $sum;
      if (my $err = $@) {
        chomp($err);
        $sb->sendmsg('Error: '. $err);
      } else {
        $sb->sendmsg('Result: '. $ans);
      }
    } else {
       $sb->sendmsg('Error: Syntax Invalid');
    }
  } elsif ($message =~ /^who$/i) {
    my $response = &who($chandle);
    $sb->sendmsg($response);
  } elsif ($message =~ /self\s*destruct/) {
    if ($admin{$chandle} == 1) {
      $sb->sendmsg('YAY, Ive been waiting so long!');
      $sb->sendmsg('Self Destruct Sequence Initiated');
      $sb->sendmsg(5);
      sleep 1;
      $sb->sendmsg(4);
      sleep 1;
      $sb->sendmsg(3);
      sleep 1;
      $sb->sendmsg(2);
      sleep 1;
      $sb->sendmsg(1);
      $sb->sendmsg('*BOOM*');
      $client->disconnect();
      sleep 1;
      die "Self Destructed\n";
    } else {
      $sb->sendmsg('Your not my master!');
    }
  } elsif ($message =~ /^msg\s+([^\s]*)\s+(.+?)$/i) {
    unless ($client->sendmsg($1, $chandle. '> '. $2)) {
      $sb->sendmsg($1. 
        ' is either not online, or not on my contact list');
    }
  } elsif ($message =~ /help/i) {
    $sb->sendmsg(&help);
  } else {
    $sb->sendmsg('I dont know, what you say?? "'. $message. '"');
  }
}

sub help {
  return "msn-client's Command List\n\n".
    "Standard Commands:\n".
    " reply       - msn-client will send a message to you\n".
    " calc        - calculate an expression, ie 'calc 1 * 9'\n".
    " who         - shows whos online on msn-client's contact list\n".
    " msg msnname - message someone on msn-client's list\n\n".
    "Admin Commands:\n".
    " self destruct - cause msn-client to quit\n";
}

sub on_bye {
  my ($chandle) = @_;

  $client->{_Log}($chandle. " has left the conversation (switch board)", 3);  
}

sub on_join {
  my ($sb, $chandle, $friendly) = @_;

  $client->{_Log}($chandle. " has joined the conversation (switch board)", 3);  
}

sub on_answer {
  my $sb = shift;

  #print "Answer() called with parameters:\n";
  #print "   " . join(", ", @_), "\n";
}

sub auth_add {
  my ($chandle, $friendly) = @_;

  $client->{_Log}('recieved authorisation request to add '. $chandle. ' ('.
    $friendly. ')', 3);

# 我们设置了任何人都可以加我为好友，不需要认证通过了。但是应会收到通知
# Update: 好像不行？
  return 1;
}

sub who {
  my ($requestor) = @_;
  $requestor = $requestor || '';

  return 'Sorry, nobody is online :('
    unless (defined $client->{Buddies} &&
    ref $client->{Buddies} eq 'HASH');

  my $response;
  foreach my $username (keys %{$client->{Buddies}}) {
    next unless ($client->{Buddies}->{$username}->{StatusCode} eq 'NLN');
    #next if ($username eq $requestor);
    $response .= '* '. $username. ' ('. 
      $client->{Buddies}->{$username}->{DisplayName}.
      ') is '. $client->{Buddies}->{$username}->{Status}. "\n";
  }

  chomp($response);

  return (defined $response && $response) ?
     $response : 'Sorry, nobody is online :(';
}

sub demonize_me ($) {
  print "Daemonizing msn-client ...\n";
  defined (my $pid = fork) or die "Can't fork: $!";
  if ($pid) {
    # close parent process.
    exit;
  } else {
    # use the child process
    if (defined $PIDFile){
      die "ERROR: I Died! Another copy of msn-client seems to be running. ".
        "Check ". $PIDFile. "\n" if (&is_running());
      open(PIDFILE,">$PIDFile") or warn "creating $PIDFile: $!\n";
      print PIDFILE "$$\n";
      close PIDFILE;
    }
    POSIX::setsid or die "Can't start a new session: $!";
    open (STDOUT,'>>'. $LogFile)
      or die "ERROR: Redirecting STDOUT to ". $LogFile. ': '. $!;
    open (STDERR,'>>'. $LogFile)
      or die "ERROR: Redirecting STDERR to ". $LogFile. ': '. $!;
    open (STDIN, '</dev/null') 
      or die "ERROR: Redirecting STDIN from /dev/null: $!";
  }
}
                                                                                                                           
sub is_running {
  if (-f $PIDFile) {
    my $pid = `cat $PIDFile`; chomp($pid);
    my @ps = `ps auxw | grep $pid | grep -v grep`;
    return 1 if ($ps[0]);
  }
  return 0;
}


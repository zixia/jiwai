#!/usr/bin/perl -w
# by zixia@zixia.net 2007-04-17

use strict;
use POSIX;
use Sys::Syslog;
use Linux::Inotify2;
use URI::Escape;
use IPC::Open2;

use Data::Dumper;

die("You must run me by root. I need root privilege, coz I need to chroot & setuid to apache.\n") unless 0==$>;

my $QUEUE_ROOT='/var/cache/tmpfs/jiwai/queue/';
my $QUARANTINE_MT = '/var/cache/tmpfs/jiwai/quarantine/sms/mt';


openlog('JWSmsRobot', "ndelay,pid,cons", 'local0');
syslog('info', "started...");


#
# get apache uid before change root.
#
my $apache_uid = getpwnam('apache');


#
# open PHP sms mt system
#
my($sms_mt_chld_out, $sms_mt_chld_in);

# 直接执行 /proc/$$/cwd/sms_mt.php 会有一定概率系统说无法执行，没有找到原因，替换为绝对路径
#my $sms_mt_pid = open2($sms_mt_chld_out, $sms_mt_chld_in, "/proc/$$/cwd/sms_mt.php") or die "open2 $!";

my $cwd = readlink "/proc/$$/cwd";
my $sms_mt_pid = open2($sms_mt_chld_out, $sms_mt_chld_in, "$cwd/sms_mt.php") or die "open2 $!";

syslog('info', "sms_mt pid $sms_mt_pid opened.");


#
# chroot
#
syslog('info', "chroot to $QUEUE_ROOT");
chroot($QUEUE_ROOT);
chdir '/';


#
# setuid - drop root, I'm apache now.
#
$< = $> = $apache_uid;
setuid($apache_uid);
syslog('info', "setuid-ed to apache: $apache_uid");


#
# create a inotify object
#
my $inotify = new Linux::Inotify2
	or die "Unable to create new inotify object: $!" ;


my $mo_dir = "sms/mo";
my $mt_dir = "sms/mt";

(-d  $mo_dir) && (-d $mt_dir)
	or die "sms doesn't contain mo/mt sub-dir!\n";

my @exist_old_msgs;

#
# create watch
# JiWai Robot, jiwai mt -> sms mt
#
$inotify->watch ("$mt_dir", IN_CREATE)
		or die "watch creation failed in [$mt_dir] $!" ;


opendir(DIR, $mt_dir) or die "can't opendir $mt_dir: $!";
@exist_old_msgs = grep (/^sms__/, readdir(DIR));
closedir DIR;


#
# delive old exist msgs...
#
sms_deliver_file("$mt_dir/$_",$sms_mt_chld_in,$sms_mt_chld_out) foreach (@exist_old_msgs);


syslog('info', "starting watch new file in $mt_dir");
syslog('info', "Entering the main loop...");

my $need_exit = 0;

while ( ! $need_exit ) {
		my @events = $inotify->read;

		unless (@events > 0) 
		{
				syslog('info', "read error: $!");
				next;
		}

		foreach my $event (@events) {
			#print Dumper($_);
			my $new_msg_file_fullname 		= $event->fullname();
			my $new_msg_file_relaname 	= $event->name();

			if ( $new_msg_file_relaname=~/^sms__/ )
			{
				sms_deliver_file(	$new_msg_file_fullname
									, $sms_mt_chld_in
									, $sms_mt_chld_out);
			}
			else
			{
				syslog('err', "Skip new file $new_msg_file_fullname");
			}
		}
}

close($sms_mt_chld_in);
close($sms_mt_chld_out);

waitpid($sms_mt_pid, WNOHANG);
syslog('info', "sms_mt pid $sms_mt_pid closed.");
syslog('info', "end...");

closelog();
exit(0);

########################################
# JiWai.de robot deliver logic here
##########################################
sub sms_deliver_file {
	my $new_msg_file = shift;
	my ($chld_in, $chld_out) = @_;

#print Dumper($chld_in);
#print Dumper($chld_out);
	if ( ! -f $new_msg_file )
	{
		syslog('err', "file [$new_msg_file] not exist?");
		return;
	}

	#file name like this: sms/mt/sms__13911833788__1176864357_517124
	unless ( $new_msg_file=~m#^(\w+)/(\w+)/(\w+?)__([\+\d]+)__(\S+)# )
	{
		syslog('err', "file [$new_msg_file] can't be parsed!");
		return;
	}

	my ($mobile_no,$file_name_tailer) = ($4,$5);

	if (!open(FD, "<$new_msg_file"))
	{
		syslog('err', "can't open file[$new_msg_file] to read");
		return;
	}

	my $file_content = join('',<FD>);
	close FD;

	unless ( $file_content=~m#(.+?)\n\n(.+)#s ){
		syslog('err', "parse file[$new_msg_file] err for content[$file_content]");

		link($new_msg_file, "$QUARANTINE_MT/sms__${mobile_no}__$file_name_tailer")
				&& unlink($new_msg_file);

		return;
	}

	my ($head, $body) = ($1,$2);
	
	# TODO: really MT

	my $encode_msg = uri_escape($body);

	my $retval;

	#print "$mobile_no $encode_msg\n";

	#print("/var/www/vhost/jiwai.de/beta/robot/sms_mt.php $mobile_no \"$encode_msg\"");

	#
	# print sms mt to JWSms PHP class
	#
	print $chld_in "$mobile_no $encode_msg\n";

	$retval = <$chld_out>;

	if ( !defined $retval ){
		syslog('err', 'chld_out no data! mt process died? I must exit!');
		exit(-1);
	}

	chomp $retval;



	if ('OK' eq $retval)
	{
		syslog('info', "MT OK: $new_msg_file");
		unlink($new_msg_file);
	}
	else
	{
		syslog('err', "MT FAIL: $new_msg_file");
	}
}



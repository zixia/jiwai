#!/usr/bin/perl -w
# by zixia@zixia.net 2007-04-17

use strict;
use POSIX;
use Sys::Syslog;
use Linux::Inotify2;

use Data::Dumper;


openlog('JWDeliveRobot', "ndelay,pid,cons", 'local0');

# get apache uid before change root.
my $apache_uid = getpwnam('apache');


# chroot
my $QUEUE_ROOT='/var/cache/tmpfs/jiwai/queue/';
syslog('info', "chroot to $QUEUE_ROOT");
chroot($QUEUE_ROOT);
chdir '/';


# setuid - drop root, I'm apache now.
$< = $> = $apache_uid;
setuid($apache_uid);
syslog('info', "setuid-ed to apache: $apache_uid");


opendir(DIR, '/') or die "can't opendir /: $!";
my @device_dirs = grep (/^[^\.]/, readdir(DIR));
closedir DIR;


#print Dumper(@device_dirs);
die "Directory $QUEUE_ROOT (after chroot /) didn't containt a 'robot' sub-dir?\n" unless grep (/robot/, @device_dirs );


# create a new object
my $inotify = new Linux::Inotify2
or die "Unable to create new inotify object: $!" ;

# create watch
foreach my $device_dir ( @device_dirs )
{
	my $mo_dir = "$device_dir/mo";
	my $mt_dir = "$device_dir/mt";

	(-d  $mo_dir) && (-d $mt_dir)
			or die "$device_dir doesn't contain mo/mt sub-dir!\n";

	my @exist_old_msgs;

	if ( 'robot' eq $device_dir )
	{	
		# JiWai Robot, jiwai mt -> sms/im mt
		$inotify->watch ("$mt_dir", IN_CREATE)
			or die "watch creation failed in [$device_dir] $!" ;

		opendir(DIR, $mt_dir) or die "can't opendir $mt_dir: $!";
		@exist_old_msgs = grep (/^(\w+)__/, readdir(DIR));
		closedir DIR;

		deliver_file("$mt_dir/$_") foreach ( @exist_old_msgs );

		syslog('info', "starting watch new file in $mt_dir");
	}
	else
	{	
		# SMS / IM Robot, sms/im mo -> jiwai mt
		$inotify->watch ("$mo_dir", IN_CREATE)
			or die "watch creation failed in [$device_dir] $!" ;

		opendir(DIR, $mo_dir) or die "can't opendir $mt_dir: $!";
		@exist_old_msgs = grep (/^(\w+)__/, readdir(DIR));
		closedir DIR;

		deliver_file("$mo_dir/$_") foreach ( @exist_old_msgs );


		syslog('info', "starting watch new file in $mo_dir");
	}
}


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
			deliver_file($event->fullname());
		}
}

closelog();
exit(0);

########################################
# JiWai.de robot deliver logic here
##########################################
sub deliver_file {
	my $new_msg_file = shift;

	if ( ! -f $new_msg_file )
	{
		syslog('err', "file [$new_msg_file] not exist?");
		return;
	}

	#file name like this: msn/mo/msn__niuniu0328@hotmail.com__1176864357_517124
	#file name like this: robot/mt/msn__niuniu0328@hotmail.com__1176864357_517124
	unless ( $new_msg_file=~m#^(\w+)/(\w+)/(\w+?)(__\S+)# )
	{
		syslog('err', "file [$new_msg_file] can't be parsed!");
		return;
	}

	my ($from,$direction,$msg_type,$file_name_tailer) = ($1,$2,$3,$4);

	#print join(',',($from,$direction,$msg_type,$file_name_tailer)) . "\n";

	if ( 'robot' eq $from )
	{	# JWrobot/mt is wrote by robot, then we need to deliver robot mt to im/sms mt.
		# JWrobot/mo is wrote by self(deliver), so we doesnt watch robot/mo.
		if ( 'mt' eq $direction )
		{
			my $dest_file = "$msg_type/$direction/$msg_type$file_name_tailer";

			# XXX
			syslog('info', "move $new_msg_file to $dest_file");
			link($new_msg_file, $dest_file)
				&& unlink($new_msg_file);
		}
	}
	else
	{	# not JWrobot is sms/msn/qq/gtalk/jabber/
		# their mo directory is wrote by themselves, then we need to deliver their mt to JWrobot mt.
		# their mt directory is managed by themselves, which we do not care.
		if ( 'mo' eq $direction )
		{
			my $dest_file = "robot/$direction/$msg_type$file_name_tailer";

			# XXX
			syslog('info', "move $new_msg_file to $dest_file");
			link($new_msg_file, $dest_file)
				&& unlink($new_msg_file);
		}
	}
}



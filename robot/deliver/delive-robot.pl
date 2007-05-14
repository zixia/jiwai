#!/usr/bin/perl -w
# by zixia@zixia.net 2007-04-17

use strict;
use POSIX;
# use Fcntl qw (:flock); # import LOCK_* constants
use Sys::Syslog;
use Linux::Inotify2;

use Data::Dumper;

die("You must run me by root. I need root privilege, coz I need to chroot & setuid to apache.\n") unless 0==$>;

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

			syslog('info', "move $new_msg_file to $dest_file");
			link($new_msg_file, $dest_file)
				&& unlink($new_msg_file);
		}
	}
}

=pod=
Inotify 会在文件 inode 建立的时候出发 event，这个核心的事件是如此的快，
以至于这个时候 PHP 刚刚 open 文件，甚至连 flock 都没有来得及调用
所以无法通过锁来确认。

目前的解决方案是，JWRobotMsg::Save 的时候，先写入一个临时文件，然后再link过来。
sub wait_file_ready()
{
	my $file_path_name = shift;

	syslog('info', "opening file...");
	if ( open ( FD, "<$file_path_name" ) )
	{
		# inotify 得到的文件可能还没有写完，这里配合 JWRobotMsg::Save 进行等待排它锁的释放
		syslog('info', "getting lock...");
		flock (FD,LOCK_SH);
		syslog('info', "got lock.");
		close FD;
	}
	else
	{
		syslog('err', "fopen [$file_path_name] failed, can't test flock sh");
	}
}
=cut=

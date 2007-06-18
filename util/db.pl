#!/usr/bin/perl

use DBI;

$database 	= 'jiwai';
$hostname 	= 'localhost';
$port		= 3306;

$db_user	= 'root';
$db_pass	= '';

$dsn = "DBI:mysql:database=$database;host=$hostname;port=$port";
$dbh = DBI->connect($dsn, $db_user, $db_pass);

my $sth = $dbh->prepare("select * from Picture");

$sth->execute();

$n = 0;
while (my $ref = $sth->fetchrow_hashref()) {
	$file_type = $ref->{'fileExt'};
	$src_dir = "/vhost/jiwai.de/domain/asset/system/user/$ref->{'idUser'}/profile_image";
	$dst_dir = "/750/reiserfs/jiwai/picture/$ref->{'id'}";

	print "Found a row: id = $ref->{'id'}, idUser = $ref->{'idUser'}\n";

	mkdir $dst_dir if ( ! -e $dst_dir );
		
	$cmd = "cp $src_dir/*.$file_type $dst_dir -v";

	system($cmd);

	#last if $n++ > 50;
}
$sth->finish();

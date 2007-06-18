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

	$picture_id = $ref->{'id'};

	my ($d1,$d2,$d3);
	$d1 = int( $picture_id/(1000*1000) );
	$d2 = int( ($picture_id%(1000*1000))/1000 );
	$d3 = $picture_id % 1000;

	$dst_dir = "/750/reiserfs/jiwai/picture/";

	$dst_dir .= "$d1/";
	mkdir $dst_dir if ( ! -e $dst_dir );
	$dst_dir .= "$d2/";
	mkdir $dst_dir if ( ! -e $dst_dir );
	$dst_dir .= "$d3/";
	mkdir $dst_dir if ( ! -e $dst_dir );

	if ( ! -e $dst_dir )
	{
		die("FT");
	}

	print "Found a row: id = $ref->{'id'}, idUser = $ref->{'idUser'}\n";

		
	$cmd = "cp $src_dir/*.$file_type $dst_dir -v";

	system($cmd);

	#print $cmd, "\n";
	#last if $n++ > 5;
}
$sth->finish();

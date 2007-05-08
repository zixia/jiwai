#!/usr/bin/perl -w
use strict;

use DBI;

my $database	= "jiwai";
my $hostname	= "localhost";

my $user		= "root";
my $password	= "";

my $dsn = "DBI:mysql:database=$database;host=$hostname";

my $dbh = DBI->connect($dsn, $user, $password) or die "connect $!";

my $sth = $dbh->prepare("SELECT * FROM Picture");
$sth->execute;
my @pictures	= {};
while (my $ref = $sth->fetchrow_hashref()) 
{
	print "Found a row: id = $ref->{id}, name = $ref->{photoInfo}\n";
	my $picture = {};

	die "parse error!'" unless ( $ref->{'photoInfo'}=~/^(\d+)\|([^.]+)\.([^.]+)$/ );
		
	$picture->{'idUser'}	= $ref->{'id'};
	$picture->{'class'}		= 'ICON';
	$picture->{'fileName'}	= $2;
	$picture->{'fileExt'}	= $3;

	push(@pictures, $picture);
}
$sth->finish;

#use Data::Dumper;
#print Dumper(@pictures);

foreach my $picture ( @pictures )
{
	my $sql = <<_SQL_;
UPDATE	 User
SET      idPicture=$picture->{idUser}
        ,class='ICON'
        ,fileName='$picture->{fileName}'
        ,fileExt='$picture->{fileExt}'
        ,timeCreate=NOW()
_SQL_
	print $sql, "\n";
	$dbh->do($sql);
}


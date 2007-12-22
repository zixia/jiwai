#!/usr/bin/perl -w

use strict;
use Mail::Bulkmail;
use Mail::Bulkmail::Server;
use MIME::Base64;
use Getopt::Std;
use DBI;
use DBD::mysql;

=pod
http://www.faqs.org/rfcs/rfc2047
http://blog.roodo.com/rocksaying/archives/2950655.html
    return $subject;
=cut
sub _buildSubject {
    my ($subject, $charset) = @_;
    my $b64subject = encode_base64($subject);
    chomp $b64subject;
    return '=?' . $charset . '?B?' . $b64subject . '?=';
}

sub _buildServer {
    my ($smtp, $port, $domain) = @_;
    my $server = Mail::Bulkmail::Server->new(
        'Smtp' => $smtp,
        'Port' => $port
    ) or die Mail::Bulkmail::Server->error();

    $server->Tries(5);
    $server->Domain($domain);
    $server->connect or die $server->error();
    return $server;
}

sub _buildMail {
    my ($server, $list, $from, $subject, $message, $charset) = @_;
    $subject = _buildSubject($subject, $charset);
    my $bulk = Mail::Bulkmail->new(
        'LIST'      => $list,
        'From'      => $from,
        'Subject'   => $subject,
        'message_from_file' => 1,
        'Message'   => $message,
        'servers'   => [$server, ],
    ) or die Mail::Bulkmail->error();

    $bulk->header("MIME-Version", "1.0");
    $bulk->header("Content-Type", 'multipart/related; type="multipart/alternative"; boundary="----=_NextPart_000_000E_01C836A5.7F4E22B0"');
=pod
    $bulk->header("Content-Transfer-Encoding", "quoted-printable");
=cut
    return $bulk;
}

sub _buildList {
    my ($db, $table) = ('jiwai', 'User');
    my ($host, $port, $user, $pass) = ('db-master-01.jw', '3306', 'root');
    my $dsn = "DBI:mysql:database=$db;host=$host;port=$port";
    my $dbh = DBI->connect($dsn, $user, $pass,
        {'RaiseError' => 1}
    ) or die "unable to connect : $DBI::errstr\n";

    my $newlist = '/tmp/bulkmail.' . $$;
    open FD, ">", $newlist;
    ##unlink $newlist;

    my $sth = $dbh->prepare("SELECT email FROM " . $table);
    $sth->execute();

    my ($ref, $entry) = ();
    while ($ref = $sth->fetchrow_hashref()) {
        $entry = $ref->{'email'};
        chomp $entry;
	$entry = reverse $entry;
        if ($entry=~m!.*?@.*?!) {
            print FD $entry, "\n";
        }       
    }

    $sth->finish();
    close FD;
    $dbh->disconnect();

    return $newlist;
}

my %opts = ();
my $forceCharset = 'gb2312';
my ($charset, $subject, $message) = ($forceCharset);
my ($smtpServer, $smtpPort, $smtpDomain) = ('127.0.0.1', 25, 'jiwai.de');
my ($mailList, $mailFrom) = ('/tmp/bulkmail.list', 'wo@jiwai.de');

getopts('c:s:f:l:o:', \%opts);

=pod
Getopt
=cut
$charset = $opts{'c'} if defined $opts{'c'};
$subject = $opts{'s'} if defined $opts{'s'};
$message = $opts{'f'} if defined $opts{'f'};
$mailList= $opts{'l'} if defined $opts{'l'};
$mailFrom= $opts{'o'} if defined $opts{'o'};

if ($mailList eq 'db') {
    $mailList = _buildList();
}

my $server = _buildServer($smtpServer, $smtpPort, $smtpDomain);
my $bulk = _buildMail($server, $mailList, $mailFrom, $subject, $message, $forceCharset);
$bulk->bulkmail() or die $bulk->error;

0;

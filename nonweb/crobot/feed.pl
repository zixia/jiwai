#!/usr/bin/perl -w

## $Id$
use XML::RSS::Parser;
use FileHandle;
use Data::Dumper;
use Fcntl ':flock';

=pod
use strict;

my %feedMap = (
    'newsmth'   => {
        'nameScreen'=> 'newsmth',
        'nameFull'  => '[水木社区]',
        'feedUrl'   => 'http://www3.newsmth.net/rssi.php?h=1',
    },
    'jwblog'    => {
        'nameScreen'=> 'newsmth',
        'nameFull'  => '[叽歪博客]',
        'feedUrl'   => 'http://feed.blog.jiwai.de/',
    },
);
=cut

require '/opt/jiwai.de/nonweb/crobot/feed.inc.pl';

sub _getFeedPathFromName {
    my $name = shift;
    my $basepath = '/tmp/feed/';
    return $basepath . $name . '.rss';
}

sub _getCachePathFromName {
    my $name = shift;
    my $basepath = '/tmp/feed/';
    return $basepath . $name . '.cache';
}


sub _getRedirectUrl {
    my $origin = shift;
    open CURL, "curl -s --head $origin |" or die "$!";

    while (<CURL>) {
        chomp;
        if (m/^Location:\s+(.*?)$/gi) {
            my $redirected = $1; chomp $redirected;;
            return $redirected;
        }   
    }   
    close CURL;
    $origin;
}

sub getFeedItemsFromName {
    my ($name, $options) = @_;
    my %options = %$options;
    my ($filter, $version, $redirect, $roi) = ($options{'filter'}, $options{'version'}, $options{'redirect'}, $options{'roi'});
    die "no name specifed" unless defined $name;

    my %feedItems = ();
    my $feedn  = _getFeedPathFromName($name);


    if (defined $version and $version eq 1) {
        use XML::FeedPP;
        my $feed = XML::FeedPP->new( $feedn);
        foreach my $item ( $feed->get_item() ) {
            my ($title, $link) = ($item->title(), $item->link());

            if (defined $roi) {
                my $line = $item->description();
                $line =~ s/<[^>]+>//gi;
                $line =~ s/[\n]//gi;
                $line =~ s/<[^>]+>//gi;
                ($line) = split("。", $line);
                $title .= " $line";
            }

            $title =~ s/,/，/gi;

            if (defined $filter) {
                eval '$link=~'.$filter;
            }
            if (defined $redirect and $redirect eq 'feedburner') {
                $link = $item->get("feedburner:origLink");
            }

            $feedItems{$title} = $link;
        }
    } else {
        my $p = XML::RSS::Parser->new;
        my $fh = FileHandle->new($feedn, "< :utf8") or die "failed to open: $!";
        my $feed = $p->parse_file($fh);

        foreach my $i ( $feed->query('//item') ) { 
            my $title = $i->query('title');
            $title = $title->text_content;
            $title =~ s/,/，/gi;

            my $link = $i->query('link');
            $link = $link->text_content;

            if (defined $filter) {
                eval '$link=~'.$filter;
            }
            if (defined $redirect) {
                $link = _getRedirectUrl($link);
            }

            $feedItems{$title} = $link;
        }
    }

    return %feedItems;
}

sub _loadCache {
    my $name = shift;
    my %cachedItems = ();
    my $cache = _getCachePathFromName($name);

    open FH, "<$cache" or return;

    while (<FH>) {
        chomp;
        my ($title, @link) = split(",", $_);
        my $link = join(',', @link);
        $cachedItems{$link} = $title;
    }

    close FH;

    return %cachedItems;
}

sub _urlencode {
    my $text = shift;
    $text =~ s/([^A-Za-z0-9])/sprintf("%%%02X", ord($1))/seg;
    return $text;
}

sub _postItem {
    my ($user, $pass, $text) = @_;
    $text = _urlencode($text);
    my $err = `curl --connect-timeout 3 -s -u "$user:$pass" -FidPartner=10044 -Fstatus="$text" http://api.jiwai.de/statuses/update.json`;
}

# buddy encoding issue, this ugly version works properly
sub _postStrFactory {
    my ($u, $t, $l) = @_;
    my $str = "";
    open FH, ">", \$str;
    print FH $u,$t," ",$l;
    close FH;
    return $str;
}

# main
sub _mainLoop {
    my $key = shift;
    die "no feed specified" unless defined $feedMap{$key};

# variables
    my $nameScreen = $feedMap{$key}{'nameScreen'};
    my $nameFull = $feedMap{$key}{'nameFull'};
    my $password = $feedMap{$key}{'password'};
    my $filter = $feedMap{$key}{'filter'};
    my $version = $feedMap{$key}{'version'};
    my $ratelimit = $feedMap{$key}{'ratelimit'};
    my $sofar = 0;
    $password = $nameScreen.'bbsdem1ma' unless defined $password;

# fetch feed
    my $feedUrl = $feedMap{$key}{'feedUrl'};
    my $feedPath = _getFeedPathFromName($key);
    `curl --connect-timeout 3 -k -A 'Mozilla/5.0 (Windows; U; Windows NT 5.1; zh-CN; rv:1.8.1.12) Gecko/20080201 Firefox/2.0.0.12' -s \'$feedUrl\' -o $feedPath`;

    my %items = getFeedItemsFromName($key, $feedMap{$key});
    my %omits = _loadCache($key);

    my $cache = _getCachePathFromName($key);
    open CACHE, ">>$cache" or die "failed write: $!";
    flock(CACHE,LOCK_EX);

# post items
    while (my($k,$v) = each %items) {
        if (defined $omits{$v}) {
            print "[DUP]", $v, "\n";
            next;
        }
        if (defined $ratelimit and $sofar eq $ratelimit) {
            print "[IGN]", $v, "\n";
            next;
        }
        ++$sofar;
        my $str = _postStrFactory($nameFull,$k,$v);
        if (defined $ENV{'RSS_NONPOST'} and $ENV{'RSS_NONPOST'}) {
            print "[HLD]", $v, "\n";
        } else {
            _postItem($nameScreen, $password, $str);
            print "[INF]", $v, "\n";
            print CACHE "$k,$v\n";
        }
    }

    flock(CACHE,LOCK_UN);
    close CACHE;
}

while (my $key = shift @ARGV) {
    _mainLoop($key);
}

0;

=pod
my $p = XML::RSS::Parser->new;
my $fh = FileHandle->new('newsmth.rss');
my $feed = $p->parse_file($fh);

# output some values 
my $feed_title = $feed->query('/channel/title');
my $count = $feed->item_count;
foreach my $i ( $feed->query('//item') ) { 
my $node = $i->query('link');
print $node->text_content,"\n";
}
=cut


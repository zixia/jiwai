#!/usr/bin/perl -w

use strict;
use Data::Dumper;
use Text::Iconv;

my %channelMap = (
    'CCTV亚洲' => 'CCTV4',
    'CCTV奥运' => 'CCTV5',
    'CCTV紫微' => 'CCTV5',
    'CCTV新闻' => 'CCTV13',
    'CCTV少儿' => 'CCTV15',
    'CCTV音乐' => 'CCTV16',
);

my %userMap = (
    'CCTV1'     => 'cctv1',
    'CCTV2'     => 'cctv2',
    'CCTV3'     => 'cctv3',
    'CCTV4'     => 'cctv4',
    'CCTV5'     => 'cctv5',
    'CCTV6'     => 'cctv6',
    'CCTV7'     => 'cctv7',
    'CCTV8'     => 'cctv8',
    'CCTV9'     => 'cctv9',
    #'CCTV10'    => 'cctv10',
    #'CCTV11'    => 'cctv11',
    #'CCTV12'    => 'cctv12',
    #'CCTV13'    => 'cctv13',
    #'CCTV15'    => 'cctv15',
    #'CCTV16'    => 'cctv16',
);

sub getTVGuideCacheByChannel {
    my $channel = shift;
    my $timestamp = `date +%Y%m%d -d "4 hours ago"`; chomp $timestamp;

    if (defined $channelMap{$channel}) { $channel = $channelMap{$channel}; }
    return "/tmp/cctv/$channel.$timestamp.cache";
}

sub getTVGuideByChannel {
    my $channel = shift;
    my $url = getTVGuideUrlByChannel($channel);
    my @guide = ();
    my $raw = `wget -U "Googlebot" -q -O - $url`;
    my $today = `date +%Y-%m-%d`; chomp $today;
    my $tomorrow = `date +%Y-%m-%d -d tomorrow`; chomp $tomorrow;

    my $converter = Text::Iconv->new("gbk", "utf-8");
    my $converted = $converter->convert($raw);

    open HTTP, "<", \$converted;
    my ($roi, $across, $hourSoFar, $hourNow) = (0, 0, 0, 0);

    while (<HTTP>) {
        my ($time, $show) = ();
        chomp;
        if (m#<div\s+id="pg">#i) {$roi = 1;}
        next if ($roi eq 0);
        $_ =~ s#<a\s+href=\".*?\">##i;
        $_ =~ s#<\/a>##i;
        if (m#<div\s+id="pgrow"><font.*?>([^<> ]+)<\/font>\s+<div.*?>([^<>]+)\s+#i) {
            ($time, $show) = ($1, $2);
            ($hourNow) = split(":", $1);
        } elsif (m#<div\s+id="pgrow"><font.*?>([^<> ]+)<\/font>.*?([^<>]+)\s+<\/div>#i) {
            ($time, $show) = ($1, $2);
            ($hourNow) = split(":", $1);
        }
        if (($across eq 0) and ($hourNow < $hourSoFar)) {
            $across = 1;
        }
        next unless defined $time;
        $hourSoFar = $hourNow;
        if ($across eq 1) {
            push(@guide, "$tomorrow $time;$show;0");
        } else {
            push(@guide, "$today $time;$show;0");
        }
    }

    close HTTP;
    return @guide;
}

sub getTVGuideUrlByChannel {
    my $channel = shift;
    die "no channel specified" unless defined $channel;
    if (defined $channelMap{$channel}) {$channel = $channelMap{$channel};};

=pod
    http://epg.tvsou.com/programys/TV_1/Channel_%Channel%/W%indexOfWeek%.htm
    my $retstr = 'http://epg.tvsou.com/programys/TV_1/Channel_0Channel0/W0indexOfWeek0.htm';
=cut

    ##http://www.tvmao.com/program/CCTV-CCTV1-w3.html
    my $retstr = 'http://www.tvmao.com/program/CCTV-0Channel0-w0indexOfWeek0.html';
    my $indexOfWeek = `date +%u`; chomp $indexOfWeek;
    $indexOfWeek = 7 if ($indexOfWeek eq 0);

    $retstr =~ s/0Channel0/$channel/si;
    $retstr =~ s/0indexOfWeek0/$indexOfWeek/si;

    return $retstr;
}

sub TVGuideFactory {
    my $channel = shift;
    die "no channel specified" unless defined $channel;

    my @tvguide = getTVGuideByChannel($channel);
    return @tvguide;
}

sub writeCache {
    my ($channel, @guide) = @_;

    my $cache = getTVGuideCacheByChannel($channel);

    open CACHE, ">$cache" or warn "$cache: $!";
    for my $entry (@guide) {
        chomp $entry;
        print CACHE $entry, "\n";
    }
    close CACHE
}

sub postTVGuide {
    my ($channel) = @_;
    die "no channel specified" unless defined $channel;

    if (defined($channelMap{$channel})) {$channel = $channelMap{$channel};}
    my ($username, $password) = ($userMap{$channel}, $userMap{$channel} . 'epgdem1ma');

    my $cache = getTVGuideCacheByChannel($channel);

    open CACHE, "<$cache" or warn "$cache: $!";
    my @guide = <CACHE>;
    close CACHE;

    if (!@guide) {
        @guide = TVGuideFactory($channel);
        writeCache($channel, @guide);
    }

    my ($lower, $upper) = (60 * 15, 60 * 30);
    my $len = @guide;
    for my $i (0 .. $len - 1) {
        my $entry = $guide[$i]; chomp $entry;
        my ($time, $show, $f) = split(/;/, $entry);
        my $tsNow = `date +%s`; chomp $tsNow;
        my $tsShow= `date +%s -d "$time"`; chomp $tsShow;
        my $tsDiff = int($tsShow - $tsNow);
        if ($tsDiff > $lower and $tsDiff < $upper and $f eq 0) {
            print "$channel with $tsDiff\n";
            `curl -u "$username:$password" -Fstatus="$channel $time $show" http://api.jiwai.de/statuses/update.json`;
            $guide[$i] = "$time;$show;1";
        }
    }
    writeCache($channel, @guide);
}

for my $channel (keys %userMap) {
    postTVGuide($channel);
}

#!/usr/bin/perl -w

use strict;
use Data::Dumper;
use Text::Iconv;

my %channelMap = (
    'GDTV1'    => '广东卫视',
    'GDTV2'    => '广东电视台珠江频道',
    'GDTV3'    => '广东电视台体育频道',
    'GDTV4'    => '广东电视台公共频道',
    'GDTV5'    => '广东电视台珠江频道海外版',
    'GDTV6'    => '广东电视台新闻频道',
    'GDTV7'    => '嘉佳卡通频道',
);

my %userMap = (
    'GDTV1'    => 'gdtv1',
    'GDTV2'    => 'gdtv2',
    'GDTV3'    => 'gdtv3',
    'GDTV4'    => 'gdtv4',
    'GDTV5'    => 'gdtv5',
    'GDTV6'    => 'gdtv6',
    'GDTV7'    => 'gdtv7',
);

sub getTVGuideCacheByChannel {
    my $channel = shift;
    my $timestamp = `date +%Y%m%d -d "6 hours ago"`; chomp $timestamp;
    return "/tmp/gdtv/$channel.$timestamp.cache";
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
        #$_ =~ s#<a\s+href=.*?>##gi;
        #$_ =~ s#<\/a>##gi;
        #$_ =~ s#<img\s+.*?>##gi;
        $_ =~ s#<img[^>]+>##gi;
        $_ =~ s#<a[^>]+>##gi;
        $_ =~ s#<\/a>##gi;
        $_ =~ s#<div\s+style.*?>##gi;
        $_ =~ s#<div[^>]+>##gi;
        $_ =~ s#<\/div>##gi;
        if (m#<li.*?>([^\s]+)\s+([^<>]*?)</li>#i) {
            ($time, $show) = ($1, $2);
            ($hourNow) = split(":", $1);
        } elsif (m#<div\s+id="pgrow">.*?<font.*?>([^<> ]+)<\/font>\s+<div.*?>([^<>]*?)\s+#i) {
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

    ##http://www.tvmao.com/program/GDTV-GDTV1-w4.html
    my $retstr = 'http://www.tvmao.com/program/GDTV-0Channel0-w0indexOfWeek0.html';
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
        $show =~ s#^\s+##gi;
        my $tsNow = `date +%s`; chomp $tsNow;
        my $tsShow= `date +%s -d "$time"`; chomp $tsShow;
        my $tsDiff = int($tsShow - $tsNow);
        if ($tsDiff > $lower and $tsDiff < $upper and $f eq 0) {
            print "$channel $time $show $tsDiff\n";
            `curl -s -u "$username:$password" -Fstatus="$channelMap{$channel} $time $show" http://api.jiwai.de/statuses/update.json`;
            $guide[$i] = "$time;$show;1";
        }
    }
    writeCache($channel, @guide);
}

sub createAccountByChannel {
    my $channel = shift;
    die "no channel specified" unless defined $channel;

    my ($username, $password) = ($userMap{$channel}, $userMap{$channel} . 'epgdem1ma');

    `curl -s -Fname_screen=$username -Fpass=$password -Femail=$username\@jiwai.de -Fapikey=4107f1349979cc9ed2951fb82b6105d4 http://api.jiwai.de/account/new.json`;
}

for my $channel (keys %channelMap) {
    postTVGuide($channel);
}

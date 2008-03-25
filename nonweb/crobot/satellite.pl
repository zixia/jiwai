#!/usr/bin/perl -w

use strict;
use Data::Dumper;
use Text::Iconv;

my %channelMap = (
    'AHTV1'     => '安徽卫视',
    'BTV1'      => '北京卫视',
    'FJTV2'     => '东南卫视',
    'GSTV1'     => '甘肃卫视',
    'GDTV1'     => '广东卫视',
    'NANFANG2'  => '南方卫视',
    'GUANXI1'   => '广西卫视',
    'GUIZOUTV1' => '贵州卫视',
    'TCTC1'     => '旅游卫视',
    'HEBEI1'    => '河北卫视',
    'HLJTV1'    => '黑龙江卫视',
    'HNTV1'     => '河南卫视',
    'PHOENIX1'  => '凤凰卫视',
    'HUBEI1'    => '湖北卫视',
    'HUNANTV1'  => '湖南卫视',
    'JSTV1'     => '江苏卫视',
    'JXTV1'     => '江西卫视',
    'JILIN1'    => '吉林卫视',
    'LNTV1'     => '辽宁卫视',
    'NMGTV1'    => '内蒙卫视',
    'NXTV2'     => '宁夏卫视',
    'SXTV1'     => '山西卫视',
    'SDTV1'     => '山东卫视',
    'DONGFANG1' => '东方卫视',
    'SHXITV1'   => '陕西卫视',
    'SCTV1'     => '四川卫视',
    'XJTV1'     => '新疆卫视',
    'YNTV1'     => '云南卫视',
    'ZJTV1'     => '浙江卫视',
);

my %userMap = (
    'AHTV1'     => '安徽卫视',
    'BTV1'      => '北京卫视',
    'FJTV2'     => '东南卫视',
    'GSTV1'     => '甘肃卫视',
    'GDTV1'     => '广东卫视',
    'NANFANG2'  => '南方卫视',
    'GUANXI1'   => '广西卫视',
    'GUIZOUTV1' => '贵州卫视',
    'TCTC1'     => '旅游卫视',
    'HEBEI1'    => '河北卫视',
    'HLJTV1'    => '黑龙江卫视',
    'HNTV1'     => '河南卫视',
    'PHOENIX1'  => '凤凰卫视',
    'HUBEI1'    => '湖北卫视',
    'HUNANTV1'  => '湖南卫视',
    'JSTV1'     => '江苏卫视',
    'JXTV1'     => '江西卫视',
    'JILIN1'    => '吉林卫视',
    'LNTV1'     => '辽宁卫视',
    'NMGTV1'    => '内蒙卫视',
    'NXTV2'     => '宁夏卫视',
    'SXTV1'     => '山西卫视',
    'SDTV1'     => '山东卫视',
    'DONGFANG1' => '东方卫视',
    'SHXITV1'   => '陕西卫视',
    'SCTV1'     => '四川卫视',
    'XJTV1'     => '新疆卫视',
    'YNTV1'     => '云南卫视',
    'ZJTV1'     => '浙江卫视',
);

sub getTVGuideCacheByChannel {
    my $channel = shift;
    my $timestamp = `date +%Y%m%d -d "4 hours ago"`; chomp $timestamp;

    return "/tmp/satellite/$channel.$timestamp.cache";
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
        $_ =~ s#<img[^>]+>##gi;
        $_ =~ s#<a[^>]+>##gi;
        $_ =~ s#<\/a>##gi;
        if (m#<div\s+id="pgrow"><font.*?>([^<> ]+)<\/font>\s+<div.*?>([^<>]*?)\s+#i) {
            ($time, $show) = ($1, $2);
            ($hourNow) = split(":", $1);
        } elsif (m#<div\s+id="pgrow"><font.*?>([^<> ]+)<\/font>.*?([^<>]*?)\s+<\/div>#i) {
            ($time, $show) = ($1, $2);
            ($hourNow) = split(":", $1);
        }
        if (($across eq 0) and ($hourNow < $hourSoFar)) {
            $across = 1;
        }
        next unless defined $time;
        $show =~ s#分集剧情##gi;
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

=pod
    http://epg.tvsou.com/programys/TV_1/Channel_%Channel%/W%indexOfWeek%.htm
    my $retstr = 'http://epg.tvsou.com/programys/TV_1/Channel_0Channel0/W0indexOfWeek0.htm';
=cut

    ##http://www.tvmao.com/SATELLITE/DONGFANG1/w2/
    my $retstr = 'http://www.tvmao.com/SATELLITE/0Channel0/w0indexOfWeek0/';
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

    my ($username, $password) = ($userMap{$channel}, 'satellitedem1ma');

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
    my %posted = ();
    for my $i (0 .. $len - 1) {
        my $entry = $guide[$i]; chomp $entry;
        my ($time, $show, $f) = split(/;/, $entry);
        $show =~ s#分集剧情##gi;
        my $tsNow = `date +%s`; chomp $tsNow;
        my $tsShow= `date +%s -d "$time"`; chomp $tsShow;
        my $tsDiff = int($tsShow - $tsNow);
        if ($tsDiff > $lower and $tsDiff < $upper and $f eq 0) {
            if (!defined $posted{$time}) {
                print "[INF]$channel $time $show $tsDiff\n";
                `curl -s -u "$username:$password" -Fstatus="$channelMap{$channel} $time $show" http://api.jiwai.de/statuses/update.json`;
                $posted{$time} = $show;
            } else {
                print "[DUP]$channel $time $show $tsDiff\n";
            }
            $guide[$i] = "$time;$show;1";
        }
    }
    writeCache($channel, @guide);
}

for my $channel (keys %userMap) {
    postTVGuide($channel);
}

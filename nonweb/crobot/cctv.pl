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
    'CCTV10'    => 'cctv10',
    'CCTV11'    => 'cctv11',
    'CCTV12'    => 'cctv12',
    'CCTV13'    => 'cctv13',
    'CCTV15'    => 'cctv15',
    'CCTV16'    => 'cctv16',
);

sub getTVGuideByChannel {
    my $channel = shift;
    my $url = getTVGuideUrlByChannel($channel);
    my @guide = ();
    my $raw = `wget -U "Googlebot" -q -O - $url`;

    my $converter = Text::Iconv->new("gbk", "utf-8");
    my $converted = $converter->convert($raw);

    open HTTP, "<", \$converted;
    my $roi = 0;

    while (<HTTP>) {
        chomp;
        if (m#<div\s+id="pg">#i) {$roi = 1;}
        next if ($roi eq 0);
        $_ =~ s#<a\s+href=\".*?\">##i;
        $_ =~ s#<\/a>##i;
        if (m#<div\s+id="pgrow"><font.*?>([^<> ]+)<\/font>\s+<div.*?>([^<>]+)\s+#i) {
            push(@guide, "$1-$2");
        } elsif (m#<div\s+id="pgrow"><font.*?>([^<> ]+)<\/font>.*?([^<>]+)\s+<\/div>#i) {
            push(@guide, "$1-$2");
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
    return join(" ", @tvguide);
}

sub postTVGuide {
    my ($channel) = @_;
    die "no channel specified" unless defined $channel;

    my $guide = TVGuideFactory($channel);
    warn "no guide founded" unless $guide;

    my ($username, $password) = ($channel, $channel . 'dem1ma');
    `curl -A "Googlebot" -u "$username:$password" -Fstatus="$guide" http://api.jiwai.de/statuses/update.json`;
}

for my $channel (keys %userMap) {
    postTVGuide($channel);
}

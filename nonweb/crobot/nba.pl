#!/usr/bin/perl -w

use strict;
use Data::Dumper;

my %teamMap = (
    '波士顿凯尔特人'=> {
        'id'    => 'celtics',
    },
    '新泽西网'      => {
        'id'    => 'nets',
    },
    '纽约尼克斯'    => {
        'id'    => 'knicks',
    },
    '费城76人'      => {
        'id'    => '76ers',
    },
    '多伦多猛龙'    => {
        'id'    => 'raptors',
    },
    '芝加哥公牛'    => {
        'id'    => 'bulls',
    },
    '克里夫兰骑士'  => {
        'id'    => 'cavaliers',
    },
    '底特律活塞'    => {
        'id'    => 'pistons',
    },
    '印第安纳步行者'=> {
        'id'    => 'pacers',
    },
    '密尔沃基雄鹿'  => {
        'id'    => 'bucks',
    },
    '亚特兰大鹰'    => {
        'id'    => 'hawks',
    },
    '夏洛特山猫'    => {
        'id'    => 'bobcats',
    },
    '迈阿密热'      => {
        'id'    => 'heat',
    },
    '奥兰多魔术'    => {
        'id'    => 'magic',
    },
    '华盛顿奇才'    => {
        'id'    => 'wizards',
    },
    '达拉斯小牛'    => {
        'id'    => 'mavericks',
    },
    '休斯敦火箭'    => {
        'id'    => 'rockets',
    },
    '孟菲斯灰熊'    => {
        'id'    => 'grizzlies',
    },
    '新奥尔良黄蜂'  => {
        'id'    => 'hornets',
    },
    '圣安东尼奥马刺'=> {
        'id'    => 'spurs',
    },
    '丹佛掘金'      => {
        'id'    => 'nuggets',
    },
    '明尼苏达森林狼'=> {
        'id'    => 'timberwolves',
    },
    '波特兰开拓者'  => {
        'id'    => 'blazers',
    },
    '西雅图超音速'  => {
        'id'    => 'supersonics',
    },
    '犹他爵士'      => {
        'id'    => 'jazz',
    },
    '金州勇士'      => {
        'id'    => 'warriors',
    },
    '洛杉矶快船'    => {
        'id'    => 'clippers',
    },
    '洛杉矶湖人'    => {
        'id'    => 'lakers',
    },
    '菲尼克斯太阳'  => {
        'id'    => 'suns',
    },
    '萨克拉门托国王'=> {
        'id'    => 'kings',
    },
);

sub urlFactory {
    my ($key, $options) = @_;
    my %options = %$options;

    return 'http://china.nba.com/'. $options{$key}{'id'} .'/';
}

sub convertToAbsUrl {
    my ($relative) = @_;
    return 'http://china.nba.com' . $relative;
}

sub getItems {
    my ($url) = @_;
    my ($roiBegin, $roiEnd) = ('-球队新闻', '-\/球队新闻');

    my $withRoi = 0;
    my %items = ();

    open PAGE, "wget -A 'Googlebot' -q $url -O - |iconv -f gbk -t utf-8 |" or die "$!";

    while (<PAGE>) {
        chomp;
        if (m/$roiBegin/) {
            $withRoi = 1;
        } elsif (m/$roiEnd/) {
            $withRoi = 0;
        }
        next if $withRoi eq 0;

        my ($roi, $pattern) = ($_, qr/<a\s+href=\"(.*?)\"\s+target=\"_blank\">([^<]+)/);
        while ($roi =~ m/$pattern/) {
            my ($link, $title) = ($1, $2);
            $title =~ s/,/，/gi;
            $items{$link} = $title;
            $roi =~ s/$pattern//i;
        }
    }

    close PAGE;

    return %items;
}

sub _urlencode {
    my $text = shift;
    $text =~ s/([^A-Za-z0-9])/sprintf("%%%02X", ord($1))/seg;
    return $text;
}

sub _postItem {
    my ($user, $pass, $text) = @_;
    $text = _urlencode($text);
    my $err = `curl -s -u "$user:$pass" -Fstatus="$text" http://api.jiwai.de/statuses/update.json`;
}

sub _postStrFactory {
    my ($t, $l) = @_;
    my $str = "";
    open FH, ">", \$str;
    print FH $t," ",$l;
    close FH;
    return $str;
}

sub _loadCache {
    my ($cache) = @_;
    my %cachedItems = ();

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

my $cache = '/tmp/feed/nba.cache';
my $sleep = 2;
my %cachedItems = _loadCache($cache);
open CACHE, ">>$cache" or die "failed write: $!";

foreach my $key (keys %teamMap) {
    my $url = urlFactory($key, \%teamMap);
    my %items = getItems($url);
    while (my($link, $title) = each %items) {
        my $str = _postStrFactory($title, convertToAbsUrl($link));
        if (defined $cachedItems{$link}) {
            print "[DUP]", $str, "\n";
        } elsif (defined $ENV{'RSS_NONPOST'} and $ENV{'RSS_NONPOST'} eq 1) {
            print "[HLD]", $str, "\n";
        } else {
            print CACHE $title,",",$link,"\n";
            _postItem($key, 'sportdem1ma', $str);
            print "[INF]", $str, "\n";
        }
    }
    sleep $sleep;
}

close CACHE;

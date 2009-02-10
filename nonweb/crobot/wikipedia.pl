#!/usr/bin/perl -w

use strict;
use Data::Dumper;

## http://zh.wikipedia.org/w/index.php?title=首页&variant=zh-cn

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

=pod
    'news'  => {
        'username'  => 'wiki.news@jiwai.de',
        'password'  => 'geekdem1ma',
        'roiBegin'  => '新闻动态</span>',
        'roiEnd'    => '更多新闻',
        'withinRoi' => 0,
        'match'     => '^<li>(.*?)</li>$',
        'filter'    => 's/<[^<>]+>//g',
        'prefix'    => '[新闻]',
    },
=cut
my %roi = (
    'history'   => {
        'username'  => 'day.in.history@jiwai.de',
        'password'  => 'geekdem1ma',
        'roiBegin'  => '历史上的今天</span>',
        'roiEnd'    => '更多历史事件',
        'withinRoi' => 0,
        'match'     => '^<li>(.*?)</li>$',
        'filter'    => 's/<[^<>]+>//g',
        'prefix'    => '[历史]',
    },
);

my $url = 'http://zh.wikipedia.org/w/index.php?title=首页&variant=zh-cn';
open PAGE, "wget -A 'Googlebot' -q \'$url\' -O - |" or die "$!";

while (<PAGE>) {
    chomp;
    foreach my $r (keys %roi) {
        if (m#$roi{$r}{'roiBegin'}#gi) {
            $roi{$r}{'withinRoi'} = 1;
        } elsif (m#$roi{$r}{'roiEnd'}#gi) {
            $roi{$r}{'withinRoi'} = 0;
        }
    }

    foreach my $r (keys %roi) {
        if (1 eq $roi{$r}{'withinRoi'}) {
            if (m#$roi{$r}{'match'}#) {
                my $c = $1;
                eval '$c=~'.$roi{$r}{'filter'};
                print "[INF]", $c, "\n";
                _postItem($roi{$r}{'username'},
                        $roi{$r}{'password'},
                        $roi{$r}{'prefix'} . $c);
            }
        }
    }
}

close PAGE;

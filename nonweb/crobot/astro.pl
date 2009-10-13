#!/usr/bin/perl -w

use strict;
use Data::Dumper;
use Text::Iconv;

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

sub _postUrlFactory {
  my ($offset) = @_;
  my $url = "http://astro.sina.com.cn/pc/west/frame0_$offset.html";
  print $url,"\n";
  return $url;
}

sub _postItemsFactory {
  my ($astro, $offset) = @_;
  my $url = _postUrlFactory($offset);
  my %items = ();
  my %result = ();
  my $converter = Text::Iconv->new("gbk", "utf-8");

  open PAGE, "wget -A 'Googlebot' -q \"$url\" -O - |" or die "$!";

  my $roi = 0;
  while (<PAGE>) {
    my $line = $converter->convert($_);
    if ($line =~ m#<div class="tab"><h4>([^<;]+)</h4><p>([^<;]+)</p></div>#i) {
      $items{$1} = $2;
    } elsif ($line =~ m#<div class="tab"><h4>([^<;]+)</h4><p>(<img.*)</p>#i) {
      my $row = $2; my $key = $1;
      $row =~ s/<img\s+src.*?>/★/gi;
      $items{$key} = $row;
    } elsif ($line =~ m#<div class="lotconts">#i) {
      $roi = 1;
    } elsif ($roi and $line =~ m#(.*?)</div>#i) {
      $items{'今日概述'} = $1;
      $roi = 0;
    }
  }

  close PAGE;

  $result{'废话很多'} = sprintf("[%s]综合运势:%s 爱情运势:%s 工作状况:%s 理财投资:%s 健康指数:%s 商谈指数:%s 幸运颜色:%s 幸运数字:%s 速配星座:%s",
      $astro,
      $items{'综合运势'},
      $items{'爱情运势'},
      $items{'工作状况'},
      $items{'理财投资'},
      $items{'健康指数'},
      $items{'商谈指数'},
      $items{'幸运颜色'},
      $items{'幸运数字'},
      $items{'速配星座'},
      );
  $result{'废话概述'} = sprintf("[%s]今日概述: %s",
      $astro,
      $items{'今日概述'},
      );

  return %result;
}

my @astros = (
    '白羊座',
    '金牛座',
    '双子座',
    '巨蟹座',
    '狮子座',
    '处女座',
    '天秤座',
    '天蝎座',
    '射手座',
    '摩羯座',
    '水瓶座',
    '双鱼座',
    );

my $offset = 0;
for my $astro (@astros) {
  my %result = _postItemsFactory($astro, $offset);
  print "[INF]$result{'废话很多'}", "\n";
  _postItem($astro . '运程', 'bulkdem1ma', $result{'废话很多'});
  print "[INF]$result{'废话概述'}", "\n";
  _postItem($astro . '运程', 'bulkdem1ma', $result{'废话概述'});
  $offset += 1;
}

exit 0;


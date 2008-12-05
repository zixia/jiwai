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
  my ($astro) = @_;
  my $converter = Text::Iconv->new("utf-8", "gbk");
  my $converted = $converter->convert($astro);
  $astro = _urlencode($converter->convert($astro));
  my $url = 'http://appastro.qq.com/cgi-bin/astro_newluckday11?astro=' . $astro .  '&type=today';
  return $url;
}

sub _postItemsFactory {
  my $astro = shift;
  my $url = _postUrlFactory($astro);
  my %items = ();
  my %result = ();
  my $converter = Text::Iconv->new("gbk", "utf-8");

  open PAGE, "wget -A 'Googlebot' -q \"$url\" -O - |" or die "$!";

  while (<PAGE>) {
    my $line = $converter->convert($_);
    if ($line =~ m#⊙(.*?)：(.*?)\<#i) {
      $items{$1} = $2;
    } elsif ($line =~ m#bluedzi\s+kong\s+h24">(.*?)<#i) {
      $items{'今日概述'} = $1;
    }
  }

  close PAGE;

  $result{'废话很多'} = sprintf("[%s]综合指数:%s 爱情指数:%s 工作指数:%s 财运指数:%s 健康指数:%s 幸运色:%s 幸运数字:%s",
      $astro,
      $items{'综合指数'},
      $items{'爱情指数'},
      $items{'工作指数'},
      $items{'财运指数'},
      $items{'健康指数'},
      $items{'幸运色'},
      $items{'幸运数字'},
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
    '天枰座',
    '天蝎座',
    '射手座',
    '摩羯座',
    '水瓶座',
    '双鱼座',
    );

for my $astro (@astros) {
  my %result = _postItemsFactory($astro);
  print "[INF]$result{'废话很多'}", "\n";
  _postItem('sohu.astro@jiwai.de', 'geekdem1ma', $result{'废话很多'});
  print "[INF]$result{'废话概述'}", "\n";
  _postItem('sohu.astro@jiwai.de', 'geekdem1ma', $result{'废话概述'});
}

exit 0;


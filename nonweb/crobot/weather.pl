#!/usr/bin/perl -w

use strict;
use Data::Dumper;
use Text::Iconv;

my %userMap = (
    '北京'  => 'bj',
    '上海'  => 'sh',
    '广州'  => 'gz',
    '武汉'  => 'wh',
    '杭州'  => 'hz',
    '天津'  => 'tj',
    '深圳'  => 'sz',
);

my %cityMap = (
    '北京'  => 125 ,
    '广州'  => 292 ,
    '上海'  => 252 ,
    '天津'  => 127 ,
    '重庆'  => 212 ,
    '沈阳'  => 115 ,
    '南京'  => 244 ,
    '武汉'  => 211 ,
    '成都'  => 166 ,
    '西安'  => 186 ,
    '石家庄'    => 82 ,
    '太原'  => 84 ,
    '郑州'  => 189 ,
    '长春'  => 103 ,
    '哈尔滨'    => 17 ,
    '呼和浩特'  => 69 ,
    '济南'  => 140 ,
    '合肥'  => 248 ,
    '杭州'  => 255 ,
    '福州'  => 276 ,
    '厦门'  => 287 ,
    '长沙'  => 218 ,
    '深圳'  => 296 ,
    '南宁'  => 295 ,
    '桂林'  => 232 ,
    '南昌'  => 264 ,
    '贵阳'  => 227 ,
    '香港'  => 1 ,
    '澳门'  => 2 ,
    '昆明'  => 179 ,
    '台北'  => 280 ,
    '拉萨'  => 150 ,
    '海口'  => 303 ,
    '兰州'  => 57 ,
    '银川'  => 78 ,
    '西宁'  => 56 ,
    '乌鲁木齐'  => 28 ,
);

=pod
http://weather.news.qq.com/inc/07_ss252.htm
http://weather.news.qq.com/inc/dc252.htm
=cut
sub getWeatherUrlByCity {
    my ($city) = @_;
    my $prefix = 'http://weather.news.qq.com/inc/07_ss';
    my $suffix = '.htm';
    my $code = $cityMap{$city};

    return $prefix . $code . $suffix;
}

sub getWeatherReportByCity {
    my ($city) = @_;
    if (not defined $cityMap{$city}) {$city = "北京"; }

    my $url = getWeatherUrlByCity($city);
    my %weather = ('city' => $city);
    my $raw = `wget -U "Googlebot" -q -O - $url`;

    my $converter = Text::Iconv->new("gbk", "utf-8");
    my $converted = $converter->convert($raw);

    open HTTP, "<", \$converted;
    my $roi = 0;

    while (<HTTP>) {
        if (m/<td height="77" class="wht2 lk37">/si) {
            $roi = 1;
        }
        next if ($roi eq 0);

        if (m/^<div\s+class="txbd">(.*?)<\/div>(.*?)$/si) {
            $weather{'desc'} = $1;
            $weather{'temp'} = $2;
        } elsif (m/风力：([^<]+)/si) {
            $weather{'wind'} = $1;
        } elsif (m/紫外线强度：([^<]+)/si) {
            $weather{'ultraviolet'} = $1;
        } elsif (m/空气质量：([^<]+)$/si) {
            $weather{'air'} = $1;
            chomp $weather{'air'};
        }
    }

    close HTTP;
    return %weather;
}

sub weatherReportFactory {
    my $city = shift;
    die "no city specified" unless defined $city;

    my %weather = getWeatherReportByCity($city);

    my $today = `date +%m月%d日`; chomp $today;
    my $tomorrow = `date +%m月%d日 -d tomorrow`; chomp $tomorrow;
    my $dayInWeek = `date +%u`; chomp $dayInWeek;
    my @weekday = (
    '星期日', '星期一', '星期二', '星期三', '星期四', '星期五', '星期六', '星期日', 
    );
    $dayInWeek = $weekday[$dayInWeek];

    my $retstr = join(" ", ("今天", $city,
    $today ."-". $tomorrow,
    $dayInWeek,
    $weather{'desc'},
    $weather{'temp'},
    $weather{'wind'}));

    return $retstr;
}

sub postWeatherReport {
    my ($city) = @_;
    die "no city specified" unless defined $city;
    die "no city founded" unless defined $userMap{$city};

    my $weather = weatherReportFactory($city);
    warn "no weather founded" unless $weather;

    my ($username, $password) = ($city . '天气', $userMap{$city} . 'weatherdem1ma');

    `curl -A "Googlebot" -u "$username:$password" -Fstatus="$weather" http://api.jiwai.de/statuses/update.json`;
}

for my $city (keys %userMap) {
    postWeatherReport($city);
}


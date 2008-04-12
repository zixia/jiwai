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
    '重庆'  => 'cq',
    '沈阳'  => 'sy',
    '南京'  => 'nj',
    '成都'  => 'cd',
    '西安'  => 'xa',
    '石家庄'    => 'sjz',
    '太原'  => 'ty',
    '郑州'  => 'zz',
    '长春'  => 'cc',
    '哈尔滨'    => 'heb',
    '呼和浩特'  => 'hhht',
    '济南'  => 'jn',
    '合肥'  => 'hf',
    '福州'  => 'fz',
    '厦门'  => 'xm',
    '长沙'  => 'cs',
    '南宁'  => 'nn',
    '桂林'  => 'gl',
    '南昌'  => 'nc',
    '贵阳'  => 'gy',
    '香港'  => 'xk',
    '澳门'  => 'am',
    '昆明'  => 'km',
    '台北'  => 'tb',
    '拉萨'  => 'ls',
    '海口'  => 'hk',
    '兰州'  => 'lz',
    '银川'  => 'yc',
    '西宁'  => 'xn',
    '乌鲁木齐'  => 'wlmq',
);

my %cityMap = (
    '北京'  => 125 ,
    '上海'  => 252 ,
    '广州'  => 292 ,
    '武汉'  => 211 ,
    '杭州'  => 255 ,
    '天津'  => 127 ,
    '深圳'  => 296 ,
    '重庆'  => 212 ,
    '沈阳'  => 115 ,
    '南京'  => 244 ,
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
    '福州'  => 276 ,
    '厦门'  => 287 ,
    '长沙'  => 218 ,
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
    my $prefix = 'http://weather.news.qq.com/inc/07_dc';
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
    my ($roi, $day) = (0, -1);

    while (<HTTP>) {
        if (m/72小时天气预报/si) {
            $roi = 1;
        }
        next if ($roi eq 0);

        if (m#EEEEEE.*?>([^>+])#si) {
            ++$day;
        } elsif(m#<td height="57" align="center" bgcolor="\#EEF3F8">(.*?)<br>(.*)#si) {
            $weather{$day}{'desc'} = $1; chomp $weather{$day}{'desc'};
            $weather{$day}{'temp'} = $2; chomp $weather{$day}{'temp'};
        } elsif (m/<br>([^<]+)<\/td>/si) {
            $weather{$day}{'wind'} = $1; chomp $weather{$day}{'wind'};
        }
    }

    close HTTP;
    return %weather;
}

sub weatherReportFactory {
    my ($city, $f) = @_;
    die "no city specified" unless defined $city;

    my %weather = getWeatherReportByCity($city);
    my $retstr = '';

    my $today = `date +%m月%d日`; chomp $today;
    my $tomorrow = `date +%m月%d日 -d tomorrow`; chomp $tomorrow;
    my $dayAfterTomorrow = `date +%m月%d日 -d "+2 days"`; chomp $dayAfterTomorrow;
    my $dayInWeek = `date +%u`; chomp $dayInWeek;
    my $dayInWeekTomorrow = `date +%u -d tomorrow`; chomp $dayInWeekTomorrow;
    my @weekday = (
    '星期日', '星期一', '星期二', '星期三', '星期四', '星期五', '星期六', '星期日', 
    );
    $dayInWeek = $weekday[$dayInWeek];
    $dayInWeekTomorrow = $weekday[$dayInWeekTomorrow];

    if (defined $f) {
        $retstr = join(" ", ("明天", $city,
        $tomorrow ."-". $dayAfterTomorrow,
        $dayInWeekTomorrow,
        $weather{1}{'desc'},
        $weather{1}{'temp'},
        $weather{1}{'wind'}));
    } else {
        $retstr = join(" ", ("今天", $city,
        $today ."-". $tomorrow,
        $dayInWeek,
        $weather{0}{'desc'},
        $weather{0}{'temp'},
        $weather{0}{'wind'}));
    }

    return $retstr;
}

sub postWeatherReport {
    my ($city) = @_;
    die "no city specified" unless defined $city;
    die "no city founded" unless defined $userMap{$city};

    my $hourNow = `date +%H`; chomp $hourNow;
    my $weather = '';

    if ($hourNow > 15) {
        $weather = weatherReportFactory($city, 1);  ## tomorrow
    } else {
        $weather = weatherReportFactory($city); ## today
    }
    print $weather, "\n";
    warn "no weather founded" unless $weather;

    my ($username, $password) = ($city . '天气', $userMap{$city} . 'weatherdem1ma');
    `curl -s -u "$username:$password" -Fstatus="$weather" http://api.jiwai.de/statuses/update.json`;
}

for my $city (keys %userMap) {
    postWeatherReport($city);
}


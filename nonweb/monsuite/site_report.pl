#!/usr/bin/perl -w

use strict;

my $yesterday = `date +%m%Y%d -d yesterday`;
my $yesterday2= `date +%Y%m%d -d yesterday`;
chomp $yesterday;
chomp $yesterday2;

=pod
GET Gadget Statistics from AWStats
=cut
my $cmdAwstats = '/usr/local/awstats/wwwroot/cgi-bin/awstats.pl';
my $optionUpdate = ' --config=gadget -databasebreak=day -update';
my $outAwstats = '/opt/awstats';

`$cmdAwstats $optionUpdate`;

my $yesterdayReport = $outAwstats.'/awstats'.$yesterday.'.gadget.txt';

my %stats;
my %momt;
my %cluster;

if (-e $yesterdayReport) {
    open FD, "<$yesterdayReport" or die "$!";

    while (<FD>) {
        chomp;
        if ($_=~m/^TotalVisits\s+(\d+)/) {
            $stats{'nv'} = $1;
        } elsif ($_=~m/^TotalUnique\s+(\d+)/) {
            $stats{'uv'} = $1;
        } elsif ($_=~m/^ip\s+(\d+)\s+(\d+)\s+/) {
            $stats{'pv'} = $1;
        }
    }

    close FD;
} else {
    die "non exist of $yesterdayReport";
}

=pod
Get Mo/Mt Count from RRD
=cut
my @robots = ('gtalk', 'msn', 'qq', 'skype', 'sms');
for my $robot (@robots) {
    my $rra = genRraPath($robot);
    ($momt{$robot}{'mo'}, $momt{$robot}{'mt'}) = fetchCountFromRra($rra);
}

my $t1 = getResponseTime();
my $t2 = getResponseTime();
my $t3 = getResponseTime();

$cluster{'response'} = sprintf("%.2f", ($t1 + $t2 + $t3) / 3);

my @contacts = (
    'glinus@jiwai.com',
    'rannie@jiwai.com',
);

my %errorDef = (
    '400'   => 'Bad Request',
    '401'   => 'Unauthorized',
    '402'   => 'Payment Required',
    '403'   => 'Forbidden',
    '404'   => 'Not Found',
    '405'   => 'Method Not Allowed',
    '406'   => 'Not Acceptable',
    '407'   => 'Proxy Authentication Required',
    '408'   => 'Request Timeout',
    '409'   => 'Conflict',
    '410'   => 'Gone',
    '411'   => 'Length Required',
    '412'   => 'Precondition Failed',
    '413'   => 'Request Entity Too Large',
    '414'   => 'Request-URI Too Long',
    '415'   => 'Unsupported Media Type',
    '416'   => 'Requested Range Not Satisfiable',
    '417'   => 'Expectation Failed',
    '500'   => 'Internal Server Error',
    '501'   => 'Not Implemented',
    '502'   => 'Bad Gateway',
    '503'   => 'Service Unavailable',
    '504'   => 'Gateway Timeout',
    '505'   => 'HTTP Version Not Supported',
);

my %errorCnt = (
    '400'   => 0,
    '401'   => 0,
    '402'   => 0,
    '403'   => 0,
    '404'   => 0,
    '405'   => 0,
    '406'   => 0,
    '407'   => 0,
    '408'   => 0,
    '409'   => 0,
    '410'   => 0,
    '411'   => 0,
    '412'   => 0,
    '413'   => 0,
    '414'   => 0,
    '415'   => 0,
    '416'   => 0,
    '417'   => 0,
    '500'   => 0,
    '501'   => 0,
    '502'   => 0,
    '503'   => 0,
    '504'   => 0,
    '505'   => 0,
);

my $halog = '/opt/log/syslog-ng/haproxy.log';
open HALOG, "<$halog" or warn "$!";

while (<HALOG>) {
    chomp;
    if (m/^(\S+\s+){10}([4|5]\d+)/) {
        ++$errorCnt{$2};
    }
}

close HALOG;

my $message = "
本站(http://jiwai.de) $yesterday2的统计(第二部分)

->窗可贴统计

| 用户数(UV)： $stats{'uv'}
| 人次数(NV)： $stats{'nv'}
| 展示数(PV)： $stats{'pv'}

->消息发送统计(Mo/Mt = 上行/下行)
| GTALK:  $momt{'gtalk'}{'mo'} / $momt{'gtalk'}{'mt'}
| MSN  :  $momt{'msn'}{'mo'} / $momt{'msn'}{'mt'}
| QQ   :  $momt{'qq'}{'mo'} / $momt{'qq'}{'mt'}
| SKYPE:  $momt{'skype'}{'mo'} / $momt{'skype'}{'mt'}
| SMS  :  $momt{'sms'}{'mo'} / $momt{'sms'}{'mt'}

->页面相应速度测试(Currency=1, Count=100)
| 每次请求时间：    $cluster{'response'}(ms)

->错误页面统计
| 400($errorDef{'400'}) $errorCnt{'400'}
| 401($errorDef{'401'}) $errorCnt{'401'}
| 402($errorDef{'402'}) $errorCnt{'402'}
| 403($errorDef{'403'}) $errorCnt{'403'}
| 404($errorDef{'404'}) $errorCnt{'404'}
| 405($errorDef{'405'}) $errorCnt{'405'}
| 406($errorDef{'406'}) $errorCnt{'406'}
| 407($errorDef{'407'}) $errorCnt{'407'}
| 408($errorDef{'408'}) $errorCnt{'408'}
| 409($errorDef{'409'}) $errorCnt{'409'}
| 410($errorDef{'410'}) $errorCnt{'410'}
| 411($errorDef{'411'}) $errorCnt{'411'}
| 412($errorDef{'412'}) $errorCnt{'412'}
| 413($errorDef{'413'}) $errorCnt{'413'}
| 414($errorDef{'414'}) $errorCnt{'414'}
| 415($errorDef{'415'}) $errorCnt{'415'}
| 416($errorDef{'416'}) $errorCnt{'416'}
| 417($errorDef{'417'}) $errorCnt{'417'}
| 500($errorDef{'500'}) $errorCnt{'500'}
| 501($errorDef{'501'}) $errorCnt{'501'}
| 502($errorDef{'502'}) $errorCnt{'502'}
| 503($errorDef{'503'}) $errorCnt{'503'}
| 504($errorDef{'504'}) $errorCnt{'504'}
| 505($errorDef{'505'}) $errorCnt{'505'}

";

=pod
HTTP Response Statistics
=cut

## Time per request:       91.644 [ms] (mean)
sub getResponseTime {
    my $ret = 0;
    open FD, "ab -c 1 -n 100 http://jiwai.de/ |" or die "$!";
    while (<FD>) {
        chomp;
        if (m/Time per request:\s+([\d\.]+).*?\(mean\)/) {
            $ret = $1;
        }
    }
    close FD;
    return $ret;
}

=pod
Send Mail
=cut
use MIME::Lite;

for my $contact (@contacts) {
    my $msg = MIME::Lite->new(
            From     =>'site-report@jiwai.de',
            To       =>$contact,
            Subject  =>'jiwai.de statistics - part 2',
            Data     =>$message,
            );
    $msg->attr("content-type"         => "text/plain");
    $msg->attr("content-type.charset" => "utf-8");
    $msg->send();
}

sub genRraPath {
    my $robot = shift;

    my $rraPath = '/opt/rra/';

    my %rraMoMt = (
        'gtalk' => 'robot_mo_609.rrd',
        'msn'   => 'robot_mo_611.rrd',
        'qq'    => 'robot_mo_613.rrd',
        'skype' => 'robot_mo_615.rrd',
        'sms'   => 'robot_mo_617.rrd',
    );

    return ($rraMoMt{$robot}) ? $rraPath . $rraMoMt{$robot} : undef;
}

sub fetchCountFromRra {
    my $rraFile     = shift;
    my ($mo, $mt)   = (0, 0);

    open FD, "rrdtool fetch $rraFile AVERAGE |" or die "$!";

    while (<FD>) {
        chomp;
        if (m/^\d+:\s+(\d.*?)\s+(\d.*?)$/si) {
## work around with unusual peeks caused by reset
            if (($mo > 0 and $mt > 0) and (int($1) > 10 or int($2) > 10)) {
                next;
            }
            $mo += int($1 * 300 + .5);
            $mt += int($2 * 300 + .5);
        }
    }

    close FD;

    return ($mo, $mt);
}

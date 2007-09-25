<?php
    header('Content-type: text/html; charset=UTF-8');
    $message = $this->GetRedirectMessage();
    $user = $this->GetUser();
      $site_base = $this->GetConfigValue("base_url");
      if ( substr_count($site_base, 'wikka.php?wakka=') > 0 ) $site_base = substr($site_base,0,-16);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
    <title><?php echo "叽歪de帮助: ".$this->PageTitle(); ?></title>
    <base href="<?php echo $site_base ?>" />
    <?php if ($this->GetMethod() != 'show' || $this->page["latest"] == 'N' || $this->page["tag"] == 'SandBox') echo "<meta name=\"robots\" content=\"noindex, nofollow, noarchive\" />\n"; ?>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="keywords" content="<?php echo $this->GetConfigValue("meta_keywords") ?>" />
    <meta name="description" content="<?php echo $this->GetConfigValue("meta_description") ?>" />
    <link rel="stylesheet" type="text/css" href="css/<?php echo $this->GetConfigValue("stylesheet") ?>" />
    <link href="css/jiwai-screen.css" media="screen, projection" rel="Stylesheet" type="text/css" />
    <link rel="shortcut icon" href="http://asset1.jiwai.de/img/favicon.ico?1183979714" type="image/icon" />
    <script type="text/javascript" src="js/jiwai.js"></script>
    <script type="text/javascript" src="js/mootools.v1.11.js"></script>
    <link rel="start" href="http://JiWai.de/" title="叽歪de首页" />
    <meta name="ICBM" content="40.4000, 116.3000" />
    <meta name="DC.title" content="叽歪de" />
    <meta name="copyright" content="copyright 2007 http://jiwai.de" />
    <meta name="robots" content="all" />
<?php
if ($this->GetMethod() != 'edit') {
    $rsslink  = '   <link rel="alternate" type="application/rss+xml" title="'.$this->GetWakkaName().': revisions for '.$this->tag.' (RSS)" href="'.$this->Href('revisions.xml', $this->tag).'" />'."\n";
    $rsslink .= '   <link rel="alternate" type="application/rss+xml" title="'.$this->GetWakkaName().': recently edited pages (RSS)" href="'.$this->Href('recentchanges.xml', $this->tag).'" />'."\n";
    echo $rsslink;  
}
?>
</head>
<script type="text/javascript">
  function onLJLoading() {
    new Effect.Highlight('lj_details');
    $('newsmth_details').innerHTML = '<img alt="Icon_throbber" src="http://asset.jiwai.de/img/icon_throbber.gif?1176324540" /> &nbsp; 找朋友中……朋友越多，需要的时间会越长。';
  }

  function onEmailChange() {
    text = $('emails').value;

    text = text.replace(/[,;，；、]/,',');

    pieces = text.split(',');  
    
    Element.extend({ visible: function() { return this.style.display != 'none'; }});

    if ( (pieces.length > 1) && (pieces[1].trim() != '') && !($('mutualrow').visible())) {
      $('mutualrow').style.display = "";
      JiWai.Yft('#mutualrow');
    } else if (pieces.length <= 1) {
      $('mutualrow').style.display = "none";
    }
  }
</script>

<body <?php echo $message ? "onLoad=\"alert('".$message."');\" " : "" ?> class="account" id="help" >
<ul id="accessibility">
<li> <a href="#navigation" accesskey="2">跳转到导航目录</a> </li>
<li> <a href="#side">跳转到功能目录</a> </li>
</ul>

<?php JWTemplate::accessibility() ?>
<?php JWTemplate::header() ?>

<div id="container">
<h1><span>
<a href="<?php echo $this->config["root_page"]?>">叽歪de帮助中心</a> :: <a href="/FAQ">常见问题</a> :: <a href="/JiWaiHelpList">帮助文档</a>
</span>叽歪de帮助中心 : JiWaiHelp </h1>
<table width="100%" border="0" cellspacing="10" cellpadding="0">
  <tr>
    <th width="170"><h3>目录</h3>
      <?php 
      $tobeHighlight = $this->tag;
      $navigateBar = array(
              'JiWaiHelp'     => '帮助中心首页',
              'FAQ'           => array (
                  'title'     => '常见问题(FAQ)',
                  'MobileFAQ' => '手机',
                  'IMFAQ'     => '聊天软件',
                  ),
              'JiWaiHelpList' => array (
                  'title' => '帮助文档',
                  'GettingStart'  => '开始使用',
                  'SettingsAndManagement' => '设置与管理',
                  'JiWaiFeatures' => '叽歪特色',
                  ),
              );
        echo '<ul>';
        foreach ($navigateBar as $wiki=>$title) {
            if (is_array($title)) {
                if (strcasecmp($wiki, $tobeHighlight) == 0) {
                    echo '<li><a href="'.
                        $this->config['base_url'] . $wiki . '">' .
                        '<font color="#FF830B">'. $title['title'] . '</font></a>';
                } else {
                    echo '<li><a href="'.
                        $this->config['base_url'] . $wiki . '">' .
                        $title['title'] ;
                }
                if (strcasecmp($wiki, $tobeHighlight) == 0 || 
                    array_key_exists($tobeHighlight, $title))
                {
                        foreach ($title as $subWiki=>$subTitle) {
                        if ($subWiki == 'title') continue;
                        if (strcasecmp($subWiki, $tobeHighlight) == 0) {
                            echo '<li style="text-indent:2em;"><a href="'. $this->config['base_url'] . $subWiki . '">' .
                                '<font color="#FF830B">'. $subTitle . '</font>' .
                                '</a></li>';
                        } else {
                            echo '<li style="text-indent:2em;"><a href="'. $this->config['base_url'] . $subWiki . '">' . $subTitle . '</a></li>';
                        }
                    }
                }
                echo '</li>';
            } else {
                if (strcasecmp($wiki, $tobeHighlight) == 0) {
                    echo '<li><a href="'. $this->config['base_url'] . $wiki . '">' .
                        '<font color="#FF830B">'. $title . '</font>' .
                        '</a></li>';
                } else {
                    echo '<li><a href="'. $this->config['base_url'] . $wiki . '">' . $title . '</a></li>';
                }
            }
            echo "\n";
        }
        ?>
      </ul>
      </div>    <!-- menu -->
      </th>
    <td>

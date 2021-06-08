<?php
require __DIR__ . "/function.php";
#注册插件
RegisterPlugin("mz_CoinsGame", "ActivePlugin_mz_CoinsGame");

function ActivePlugin_mz_CoinsGame()
{
  Add_Filter_Plugin('Filter_Plugin_Cmd_Ajax', 'mz_CoinsGame_Ajax');
  Add_Filter_Plugin('Filter_Plugin_ViewAuto_Begin', 'mz_CoinsGame_ViewPage');
  Add_Filter_Plugin('Filter_Plugin_Zbp_BuildTemplate', 'mz_CoinsGame_GenTpl');
}
function mz_CoinsGame_Ajax($src)
{
  if ("mz_CoinsGame" !== $src) {
    return;
  }
  $act = GetVars("mcg_act", "GET");
  if ($act === "buy") {
    $id = GetVars("id", "GET");
    $jwtData = null;
    $rltCode = mz_CoinsGame_BuyAct($id, 1, $jwtData);
    $rltData = array("id" => $id, "rltcode" => $rltCode, "coins" => $jwtData->coins);
    switch ($rltCode) {
      case 0:
        $rltData["msg"] = "余额不足";
        break;
      case 1:
        $rltData["msg"] = "购买成功";
        break;
      case 2:
        $rltData["msg"] = "已购买";
        break;
      default:
        # code...
        break;
    }
    header('Content-type: application/json');
    echo json_encode($rltData);
    die();
  }
}
function mz_CoinsGame_ViewPage($inpurl, $url)
{
  global $zbp;
  $path = $zbp->Config('mz_CoinsGame')->path;
  $r = "/^{$path}\/?([^\/]*)$/i";
  // echo $r;
  // echo "<br />";
  // echo $url;
  $m = array();
  if (preg_match($r, $url, $m) == 1) {
    // 提交处理
    $tip = mz_CoinsGame_Post();
    // 读取JWT信息
    $dataJWT = mz_CoinsGame_core::GetJWTData();
    // 面板地址，用于接受post
    $board = mz_CoinsGame_Path("board", "host");
    $isSign = $dataJWT->isSign;
    $isFeed = $dataJWT->isFeed;
    $zbp->template->SetTags('tip', $tip);
    $zbp->template->SetTags('jwt', $dataJWT);
    $zbp->template->SetTags('isSign', $isSign);
    $zbp->template->SetTags('isFeed', $isFeed);
    $zbp->template->SetTags('board', $board);
    $content = $zbp->template->Output("plugin_mz_CoinsGame_board");
    mz_CoinsGame_GenPage($content);
    die();
  }
  return;
}
function mz_CoinsGame_GenPage($content)
{
  global $zbp;
  // 引入文件
  $style = mz_CoinsGame_Path("mz-css", "host");
  $zbp->header .= "<link rel=\"stylesheet\" href=\"{$style}\">";
  $style = mz_CoinsGame_Path("css-plugin", "host");
  $zbp->header .= "<link rel=\"stylesheet\" href=\"{$style}\">";
  $script = mz_CoinsGame_Path("js-plugin", "host");
  $zbp->footer .= "<script src=\"{$script}\"></script>";

  $article = new Post;
  $article->Type = ZC_POST_TYPE_PAGE;
  $article->IsLock = true;
  $article->Title = "积分一览";
  $article->Content = $content;
  $zbp->template->SetTemplate($article->Template);
  $zbp->template->SetTags('type', 'page');
  $zbp->template->SetTags('title', $article->Title);
  $zbp->template->SetTags('article', $article);
  $zbp->template->Display();
}
function mz_CoinsGame_GenTpl(&$templates)
{
  global $zbp;
  // $help = $zbp->Config('mz_CoinsGame')->help;
  $filePath = mz_CoinsGame_Path("tpl/help.php");
  $fileCon  = file_get_contents($filePath);
  $help = $fileCon;
  $tplCont = file_get_contents(mz_CoinsGame_Path("tpl-board"));
  $tplCont = str_replace("-help-", $help, $tplCont);
  $templates["plugin_mz_CoinsGame_board"] = $tplCont;
}
function mz_CoinsGame_Post()
{
  global $zbp;
  $act = GetVars("act", "post");
  $tip = (object) array("on" => "hidden", "info" => "");
  switch ($act) {
    case 'update':
      mz_CoinsGame_core::Update($_POST);
      $tip->info = "操作成功";
      break;
    case 'sign':
      $bol = mz_CoinsGame_SignAct();
      $tip->info = $bol ? "签到成功" : "已签到";
      break;
    default:
      # code...
      break;
  }
  if (!empty($act)) {
    setcookie("mz_tips", json_encode($tip), 2000000000, $zbp->cookiespath, '', false, false);
    Redirect($zbp->currenturl);
  }
  if (isset($_COOKIE['mz_tips'])) {
    setcookie("mz_tips", json_encode($tip), 2000000000, $zbp->cookiespath, '', false, false);
    $tip = json_decode(GetVars('mz_tips', 'COOKIE'));
  }
  return $tip;
}
function mz_CoinsGame_Path($file, $t = 'path')
{
  global $zbp;
  $result = $zbp->$t . 'zb_users/plugin/mz_CoinsGame/';
  switch ($file) {
    case 'tpl-board':
      return $result . 'tpl/board.php';
      break;
    case 'js-plugin':
      return $result . 'from/plugin.js';
      break;
    case 'css-plugin':
      return $result . 'from/plugin.css';
      break;
    case 'data-info':
      return $result . 'from/data-info.json';
      break;
    case 'mz-css':
      return $result . 'mz-ui/mizu.css';
      break;
    case 'usr':
      return $result . 'usr/';
      break;
    case 'var':
      return $result . 'var/';
      break;
    case 'board':
      return $zbp->host . $zbp->Config("mz_CoinsGame")->path . "/";
      break;
    case 'main':
      return $result . 'main.php';
      break;
    default:
      return $result . $file;
  }
}
function InstallPlugin_mz_CoinsGame()
{
  global $zbp;
  if (!$zbp->HasConfig('mz_CoinsGame')) {
    $zbp->Config('mz_CoinsGame')->version = 1;
    $zbp->Config('mz_CoinsGame')->path = "CoinsGame";
    $zbp->SaveConfig('mz_CoinsGame');
  }
  // if (!$zbp->Config('mz_CoinsGame')->HasKey("help")) {
  //   $filePath = mz_CoinsGame_Path("tpl/help.php");
  //   $fileCon  = file_get_contents($filePath);
  //   $zbp->Config('mz_CoinsGame')->help = $fileCon;
  //   $zbp->SaveConfig('mz_CoinsGame');
  // }
  $zbp->BuildTemplate();
}
// 插件升级时执行
function UpdatePlugin_mz_CoinsGame()
{
  InstallPlugin_mz_CoinsGame();
}
// 旧版兼容
function mz_CoinsGame_Updated()
{
  UpdatePlugin_mz_CoinsGame();
}
function UninstallPlugin_mz_CoinsGame()
{
}

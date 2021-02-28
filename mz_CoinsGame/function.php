<?php
class mz_CoinsGame_core
{
  public static $objJWT = null;

  public static $isLoaded = false;
  public static $dataInfo = null;
  public static function ReadJWT()
  {
    self::$objJWT = new StorJWT("CoinsGame");
    self::$objJWT->Unit = 86400; // 时间间隔基数，单位为秒，用于和Save()方法的参数相乘；
    if (!self::$objJWT->Load()) {
      self::InitJWT();
    }
    mz_CoinsGame_core::$isLoaded = true;
  }
  public static function InitJWT()
  {
    $dataInfo = self::$dataInfo;
    foreach ($dataInfo as $key => $value) {
      self::$objJWT->$key = $value;
    }
    self::$objJWT->jti = mz_CoinsGame_GenJTI();
    self::$objJWT->aud = "usr";
    self::$objJWT->Save(0, 37);
  }
  public static function GetJWTData()
  {
    $rltData = self::$objJWT->GetData(1);
    $rltData->token = self::$objJWT->Token;
    $rltData->periods = mz_CoinsGame_GenJTI("periods");
    $rltData->isSign = mz_CoinsGame_IsSign($rltData);
    $rltData->isFeed = StorJWT_PubGet("isFeed");
    return $rltData;
  }
  public static function Update($dataInfo)
  {
    $flag = false;
    foreach ($dataInfo as $key => $value) {
      if (substr($key, 0, 4) == 'jwt-') {
        $name = substr($key, 4 - strlen($key));
        if (self::$objJWT->$name == $value) {
          continue;
        }
        // 跳过非预置的字段
        if (isset(self::$dataInfo->$name)) {
          self::$objJWT->$name = $value;
          $flag = true;
        }
      }
    }
    if ($flag)
      self::$objJWT->Save(0, 37);
  }
}

if (!mz_CoinsGame_core::$isLoaded) {
  $dataInfoFile = mz_CoinsGame_Path("data-info");
  mz_CoinsGame_core::$dataInfo = json_decode(file_get_contents($dataInfoFile));
  mz_CoinsGame_core::ReadJWT();
}

// 判断购买
function mz_CoinsGame_IsBot($PostID, &$jti = null)
{
  $data = mz_CoinsGame_core::GetJWTData();
  $jti = mz_CoinsGame_GenJTI("bot");
  $intQEV = ($PostID % 37) + $jti;
  if (!in_array($intQEV, $data->log->bot)) {
    $jti = $intQEV;
    return false;
  }
  return true;
}

/**
 * 购买
 *
 * @param integer $PostID
 * @param integer $need 定价
 * @param mixed $data 用于回显余额等
 * @return int 0|1|2 失败|成功|已购买
 */
function mz_CoinsGame_BuyAct($PostID, $need = 1, &$data = null)
{
  $data = mz_CoinsGame_core::GetJWTData();
  $jti = null;
  // 已购买
  if (mz_CoinsGame_IsBot($PostID, $jti)) {
    return 2;
  }
  // 剩余积分不够
  if ($data->coins < $need) {
    return 0;
  }
  $data->log->bot[] = $jti;
  $data->coins -= $need;
  $rltData = array("jwt-log" => $data->log, "jwt-coins" => $data->coins);
  mz_CoinsGame_core::Update($rltData);
  return 1;
}

// 签到判断
function mz_CoinsGame_IsSign($data, &$jti = null)
{
  $jti = mz_CoinsGame_GenJTI("sign");
  if (!in_array($jti, $data->log->sign)) {
    return false;
  }
  return true;
}

// 签到
function mz_CoinsGame_SignAct()
{
  $data = mz_CoinsGame_core::GetJWTData();
  $jti = null;
  if (mz_CoinsGame_IsSign($data, $jti)) {
    return false;
  }
  $data->log->sign[] = $jti;
  $coins = $data->coins * 10;
  $add = 10;
  if ($data->isFeed) {
    $add += 3;
  }
  $data->coins = (intval($coins) + $add) / 10;
  $rltData = array("jwt-log" => $data->log, "jwt-coins" => $data->coins);
  mz_CoinsGame_core::Update($rltData);
  return true;
}

function mz_CoinsGame_GenJTI($type = "usr")
{
  $time = time();
  switch ($type) {
    case 'rss':
    case 'qq':
    case 'sign':
      return intval($time / 86400);
      break;
    case 'bot':
    case 'periods':
      return intval($time / 86400 / 37);
      break;
    case 'usr':
    default:
      return intval($time / 300);
      break;
  }
}

function mz_CoinsGame_a($href, $title, $text = "")
{
  if (empty($text)) {
    $text = $href;
  }
  return "<a class=\"\" target=\"_blank\" href=\"{$href}\" title=\"{$title}\">$text</a>";
}

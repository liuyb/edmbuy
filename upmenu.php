<?php
/**
 * 更新微信菜单
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
//~ require init.php
require (__DIR__.'/core/init.php');

SimPHP::I()->boot();

if (!defined('BR')) {
  define('BR', IS_CLI ? "\n" : "<br>");
}

if (!IS_CLI) {
  echo "该脚本请在服务器运行";
  exit;
}

$json =<<<HEREDOC
{
	"button" :
	[
		{
			"type" : "view",
			"name" : "多米年货",
			"url"  : "http://m.edmbuy.com/"
		},
		{
      "name" : "米商",
      "sub_button" : [
      	{
    			"type" : "view",
    			"name" : "米商计划",
    			"url"  : "http://m.edmbuy.com/riceplan"
        },
      	{
    			"type" : "view",
    			"name" : "推广二维码",
    			"url"  : "http://m.edmbuy.com/comeon"
        },
      	{
    			"type" : "view",
    			"name" : "米商中心",
    			"url"  : "http://m.edmbuy.com/partner"
        }
      ]
		},
    {
      "name" : "我的",
      "sub_button" : [
        {
    			"type" : "view",
    			"name" : "会员中心",
    			"url"  : "http://m.edmbuy.com/user"
        },
        {
    			"type" : "view",
    			"name" : "个人信息",
    			"url"  : "http://m.edmbuy.com/user/setting"
        },
        {
    			"type" : "view",
    			"name" : "我的订单",
    			"url"  : "http://m.edmbuy.com/trade/order/record"
        },
        {
    			"type" : "view",
    			"name" : "常见问题",
    			"url"  : "http://m.edmbuy.com/about"
        },
        {
    			"type" : "click",
    			"name" : "官方客服",
    			"key"  : "300"
        }
      ]
		}
	]
}
HEREDOC;

$menuConfig = json_decode($json, TRUE);
config_set('wxmenu', $menuConfig, 'J');

$msg = 'update menu fail'.BR;
if((new Weixin([],'edmbuy'))->createMenu($menuConfig)){
  $msg = 'update menu success'.BR;
}

echo $msg;

/*----- END FILE: upmenu.php -----*/
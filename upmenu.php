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
			"name" : "小蜜商城",
			"url"  : "http://m.fxmgou.com/"
		},
		{
      "name" : "我的",
      "sub_button" : [
        {
    			"type" : "click",
    			"name" : "最新文章",
    			"key"  : "200"
        },
        {
    			"type" : "view",
    			"name" : "我的订单",
    			"url"  : "http://m.fxmgou.com/trade/order/record"
        },
        {
    			"type" : "view",
    			"name" : "我的收藏",
    			"url"  : "http://m.fxmgou.com/user/collect"
        },
        {
    			"type" : "view",
    			"name" : "意见反馈",
    			"url"  : "http://m.fxmgou.com/user/feedback"
        }
      ]
		},
    {
      "name" : "使用帮助",
      "sub_button" : [
        {
    			"type" : "click",
    			"name" : "关于小蜜",
    			"key"  : "300"
        },
        {
    			"type" : "click",
    			"name" : "联系小蜜",
    			"key"  : "301"
        }
      ]
		}
	]
}
HEREDOC;

$menuConfig = json_decode($json, TRUE);
config_set('wxmenu', $menuConfig, 'J');

$msg = 'update menu fail'.BR;
if((new Weixin([],'fxmgou'))->createMenu($menuConfig)){
  $msg = 'update menu success'.BR;
}

echo $msg;

/*----- END FILE: upmenu.php -----*/
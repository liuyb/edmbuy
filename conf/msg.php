<?php
/**
 * 第三方接口配置
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

return [
    'sms' => [
        'forgetPwd' => '【益多米】验证码%s,尊敬的用户您好,您正在找回益多米商城登录密码,动态密码为:%s',
        'merchant_reg' => '【益多米】验证码%s,尊敬的商家您好,您正在注册益多米商城,动态密码为:%s',
    ],
    'stmp' => [
        'merchant_reg' => '【益多米】,尊敬的商家您好,恭喜您成功注册益多米商城登录地址：%s (请在电脑端登录),您的登录帐号为:%s，
                            初始密码为：%s (为了您账户的安全尽快到益都米商家页面更改密码)'
                            ,
    ]

];

/*----- END FILE: port.php -----*/
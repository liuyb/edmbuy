<?php
/**
 * 第三方接口配置
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
defined('IN_SIMPHP') or die('Access Denied');

return [
    'sms' => [
        'forget_pwd' => '【益多米】尊敬的用户您好,您正在找回益多米商城登录密码,您本次手机动态密码为:%s',
        'merchant_reg' => '【益多米】尊敬的商家您好,您正在注册益多米商城,您本次手机动态密码为:%s',
        'bind_bank' => '【益多米】尊敬的商家您好,您正在进行绑定银行卡操作,您本次手机动态密码为:%s',
        'reg_success' => '【益多米】尊敬的商家您好,恭喜您成功注册益多米商城,登录地址：%s (请在电脑端登录),您的登录账号为:%s，初始密码为：%s (为了您账户的安全请尽快到益多米商家页面更改密码)',
		'reg_account' => '【益多米】您正在注册益多米账号，验证码：%s，工作人员不会向您索取，请勿泄露。',
		'reg_eqx'     => '【益多米】您正在注册一起享账号，验证码：%s，工作人员不会向您索取，请勿泄露。',
    ],
    'stmp' => [
        'merchant_reg' => '【益多米】验证码%s,尊敬的商家您好,恭喜您成功注册益多米商城,登录地址：%s (请在电脑端登录),您的登录账号为:%s，
                            初始密码为：%s (为了您账户的安全请尽快到益多米商家页面更改密码)',
    ]

];

/*----- END FILE: port.php -----*/
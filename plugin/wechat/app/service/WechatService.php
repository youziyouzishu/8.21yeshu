<?php

namespace plugin\wechat\app\service;

use EasyWeChat\Factory;
use EasyWeChat\OfficialAccount\Application;
use plugin\admin\app\model\Option;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

class WechatService
{
    public static function instance(): Application
    {
        $config = Option::where('name', 'wechat_config')->value('value');
        if (empty($config)) {
            throw new \InvalidArgumentException('请先前往后台[微信管理]->[参数配置]界面配置微信公众号信息');
        }

        $app = Factory::officialAccount(json_decode($config, true));
        if ($request = request()) {
            $symfony_request = new SymfonyRequest($request->get(), $request->post(), [], $request->cookie(), [], [], $request->rawBody());
            $symfony_request->headers = new HeaderBag($request->header());
            $app->rebind('request', $symfony_request);
        }
        return $app;
    }

    public static function share_js(): string
    {
        $share = Option::where('name', 'wechat_share')->value('value');
        if (empty($share = json_decode($share, true)) or empty($share['url']) or empty($share['message_img']) or empty($share['timeline_img'])) {
            throw new \InvalidArgumentException('请先前往后台[微信管理]->[分享设置]界面配置分享信息');
        }
        $app = self::instance();
        $app->jssdk->setUrl($share['url'] . request()->path());
        $json = $app->jssdk->buildConfig(['updateAppMessageShareData', 'updateTimelineShareData'], (bool)$share['debug']);
        $js = "<script src=\"//res.wx.qq.com/open/js/jweixin-1.6.0.js\" type=\"text/javascript\" charset=\"utf-8\"></script>" . PHP_EOL;
        $js .= "<script type=\"text/javascript\">" . PHP_EOL;
        $js .= "wx.config($json);" . PHP_EOL;
        $js .= "wx.ready(function () {" . PHP_EOL;
        $js .= "    wx.updateAppMessageShareData({" . PHP_EOL;
        $js .= "        title: document.title||'" . ($share['message_title'] ?: '分享标题') . "'," . PHP_EOL;
        $js .= "        desc: document.querySelector('meta[name=\"description\"]').getAttribute('content')||'" . ($share['message_description'] ?: '分享描述') . "'," . PHP_EOL;
        $js .= "        link: window.location.href," . PHP_EOL;
        $js .= "        imgUrl: '{$share['message_img']}'," . PHP_EOL;
        $js .= "        success: function () {}" . PHP_EOL;
        $js .= "    });" . PHP_EOL;
        $js .= "    wx.updateTimelineShareData({" . PHP_EOL;
        $js .= "        title: document.title||'" . ($share['timeline_title'] ?: '分享标题') . "'," . PHP_EOL;
        $js .= "        link: window.location.href," . PHP_EOL;
        $js .= "        imgUrl: '{$share['timeline_img']}'," . PHP_EOL;
        $js .= "        success: function () {}" . PHP_EOL;
        $js .= "    });" . PHP_EOL;
        $js .= "});" . PHP_EOL;
        $js .= "</script>";
        return $js;
    }
}
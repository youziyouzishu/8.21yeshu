<?php

namespace plugin\wechat\app\admin\controller;

use EasyWeChat\Kernel\Messages\Article;
use plugin\admin\app\controller\Crud;
use plugin\wechat\app\service\WechatService;
use support\exception\BusinessException;
use support\Request;
use support\Response;

class ArticleController extends Crud
{
    /**
     * 浏览
     * @return Response
     * @throws Throwable
     */
    public function index(): Response
    {
        return raw_view('article/insert');
    }

    /**
     * 插入
     * @param Request $request
     * @return Response
     * @throws BusinessException
     */
    public function insert(Request $request): Response
    {

        if ($request->method() === 'POST') {
            $title = $request->post('title');
            $thumb_media_id = $request->post('thumb_media_id');
            $content = $request->post('content');
            $thumb_media_id = public_path(str_replace('/app/admin/', '', $thumb_media_id), 'admin');


            $thumb_media_id = WechatService::instance()->material->uploadThumb($thumb_media_id);
            $res = WechatService::instance()->draft->add([
                'articles'=>[
                    [
                        'article_type' => 'news',
                        'author' => '椰树长寿泉',
                        'digest' => '测试',
                        'content_source_url' => 'https://www.baidu.com',
                        'title' => $title,
                        'thumb_media_id' => $thumb_media_id['media_id'],
                        'content' => $content,
                        'need_open_comment' => 0,
                        'only_fans_can_comment' => 0,
                    ]
                ],
            ]);

            $res = WechatService::instance()->broadcasting->sendNews($res['media_id']);
            return json(['code' => 0, 'msg' => '发送成功']);

        }
        return view('article/insert');
    }

}
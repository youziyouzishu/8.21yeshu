<?php

namespace plugin\wechat\app\admin\controller;

use app\admin\model\User;
use EasyWeChat\Kernel\Messages\Article;
use plugin\admin\app\controller\Crud;
use plugin\wechat\app\service\WechatService;
use support\exception\BusinessException;
use support\Request;
use support\Response;

class TemplateController extends Crud
{
    /**
     * 查询
     * @param Request $request
     * @return Response
     * @throws BusinessException
     */
    public function select(Request $request): Response
    {

        $res = WechatService::instance()->template_message->getPrivateTemplates();
        $data = [];
        foreach ($res['template_list'] as $key => $item){
            if ($key == 0){
                continue;
            }
            $data[] = $item;
        }
        return $this->success('成功',$data);
    }


    /**
     * 浏览
     * @return Response
     */
    public function index(): Response
    {
        return view('template/index');
    }

    /**
     * 发送消息
     * @param Request $request
     * @return Response
     * @throws BusinessException
     */
    public function insert(Request $request): Response
    {
        if ($request->method() === 'POST') {
            $template_id = $request->post('template_id');
            $user_id = $request->post('user_id');
            $pagepath = $request->post('pagepath');
            $key = $request->post('key');
            $value = $request->post('value');
            $mergedArray = [];

            foreach ($key as $index => $id) {
                $mergedArray[] = array(
                    'key' => $id,
                    'value' => $value[$index]
                );
            }
            $user = User::find($user_id);
            if ($user->client_type == 'user'){
                $appid = config('wechat.UserMiniApp.app_id');
            }elseif ($user->client_type == 'driver'){
                $appid = config('wechat.DriverMiniApp.app_id');
            }else{
                $appid = config('wechat.TransportMiniApp.app_id');
            }
            $openid = $user->openid;
            $formattedData = [];
            foreach ($mergedArray as $item) {
                $formattedData[$item['key']] = $item['value'];
            }
            $sendData = [
                'touser' => $openid,
                'template_id' => $template_id,

                'miniprogram' => [
                    'appid' => $appid,
                    'pagepath' => $pagepath
                ],
                'data' => $formattedData,
            ];
            $res = WechatService::instance()->template_message->send($sendData);
            return $this->success();
        }
        return view('template/insert');
    }

    /**
     * 更新
     * @param Request $request
     * @return Response
     * @throws BusinessException
     */
    public function update(Request $request): Response
    {
        if ($request->method() === 'POST') {
            return parent::update($request);
        }
        return view('template/update');
    }

    /**
     * 删除
     * @param Request $request
     * @return Response
     * @throws BusinessException
     */
    public function delete(Request $request): Response
    {
        $template_id = $request->input('template_id');
        if (empty($template_id)) {
            return $this->fail('请选择要删除的模板');
        }
        $res = WechatService::instance()->template_message->deletePrivateTemplate($template_id);
        return $this->json(0);
    }

}
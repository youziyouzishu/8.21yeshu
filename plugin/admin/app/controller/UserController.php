<?php

namespace plugin\admin\app\controller;

use plugin\admin\app\model\User;
use support\exception\BusinessException;
use support\Request;
use support\Response;
use Throwable;

/**
 * 用户管理 
 */
class UserController extends Crud
{
    
    /**
     * @var User
     */
    protected $model = null;

    /**
     * 构造函数
     * @return void
     */
    public function __construct()
    {
        $this->model = new User;
    }

    /**
     * 浏览
     * @return Response
     * @throws Throwable
     */
    public function index(): Response
    {
        return raw_view('user/index');
    }

    /**
     * 插入
     * @param Request $request
     * @return Response
     * @throws BusinessException|Throwable
     */
    public function insert(Request $request): Response
    {
        if ($request->method() === 'POST') {
            return parent::insert($request);
        }
        return raw_view('user/insert');
    }

    /**
     * 更新
     * @param Request $request
     * @return Response
     * @throws BusinessException|Throwable
     */
    public function update(Request $request): Response
    {
        $id = $request->input('id');
        $user = \app\admin\model\User::find($id);
        if ($request->method() === 'POST') {
            $status = $request->post('status');
            $warehouse_id = $request->post('warehouse_id');
            if ($user->client_type == 'transport' && empty($warehouse_id)){
                return $this->fail('请选择所属仓库');
            }
            if ($user->status == 0 && $status == 1){
                //禁用->正常

            }
            return parent::update($request);
        }
        return raw_view('user/update',['client_type' => $user->client_type]);
    }

}

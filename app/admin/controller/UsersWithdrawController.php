<?php

namespace app\admin\controller;

use app\admin\model\User;
use support\Request;
use support\Response;
use app\admin\model\UsersWithdraw;
use plugin\admin\app\controller\Crud;
use support\exception\BusinessException;

/**
 * 提现管理 
 */
class UsersWithdrawController extends Crud
{
    
    /**
     * @var UsersWithdraw
     */
    protected $model = null;

    /**
     * 构造函数
     * @return void
     */
    public function __construct()
    {
        $this->model = new UsersWithdraw;
    }

    /**
     * 查询
     * @param Request $request
     * @return Response
     * @throws BusinessException
     */
    public function select(Request $request): Response
    {
        [$where, $format, $limit, $field, $order] = $this->selectInput($request);
        $query = $this->doSelect($where, $field, $order)->with(['user','bankcard']);
        return $this->doFormat($query, $format, $limit);
    }
    
    /**
     * 浏览
     * @return Response
     */
    public function index(): Response
    {
        return view('users-withdraw/index');
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
            return parent::insert($request);
        }
        return view('users-withdraw/insert');
    }

    /**
     * 更新
     *
     *
     * @param Request $request
     * @return Response
     * @throws BusinessException
    */
    public function update(Request $request): Response
    {
        if ($request->method() === 'POST') {
            $status = $request->post('status');
            $id = $request->post('id');
            $row = $this->model->find($id);
            if ($row->status == 0 && $status == 2){
                //驳回
                User::money($row->withdraw_amount, $row->user_id, '驳回提现');
            }
            return parent::update($request);
        }
        return view('users-withdraw/update');
    }

}

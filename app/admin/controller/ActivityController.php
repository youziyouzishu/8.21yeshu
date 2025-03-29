<?php

namespace app\admin\controller;

use support\Request;
use support\Response;
use app\admin\model\Activity;
use plugin\admin\app\controller\Crud;
use support\exception\BusinessException;

/**
 * 活动管理 
 */
class ActivityController extends Crud
{
    
    /**
     * @var Activity
     */
    protected $model = null;

    /**
     * 构造函数
     * @return void
     */
    public function __construct()
    {
        $this->model = new Activity;
    }
    
    /**
     * 浏览
     * @return Response
     */
    public function index(): Response
    {
        return view('activity/index');
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
        $query = $this->doSelect($where, $field, $order)->with(['coupon']);
        return $this->doFormat($query, $format, $limit);
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
        return view('activity/insert');
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
        return view('activity/update');
    }

}

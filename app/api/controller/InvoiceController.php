<?php

namespace app\api\controller;

use app\admin\model\UsersInvoice;
use app\api\basic\Base;
use support\Db;
use support\Request;

class InvoiceController extends Base
{
    function insert(Request $request)
    {
        $type = $request->post('type');
        $name = $request->post('name');
        $taxpayer = $request->post('taxpayer');
        $address = $request->post('address');
        $mobile = $request->post('mobile');
        $bank = $request->post('bank');
        $bank_account = $request->post('bank_account');
        UsersInvoice::create([
            'user_id' => $request->user_id,
            'type' => $type,
            'name' => $name,
            'taxpayer' => $taxpayer,
            'address' => $address,
            'mobile' => $mobile,
            'bank' => $bank,
            'bank_account' => $bank_account,
        ]);
        return $this->success();
    }

    function detail(Request $request)
    {
        $id = $request->post('id');
        $row = UsersInvoice::where(['id' => $id])->first();
        return $this->success('成功',$row);
    }

    function update(Request $request)
    {
        $id = $request->post('id');
        $type = $request->post('type');
        $name = $request->post('name');
        $taxpayer = $request->post('taxpayer');
        $address = $request->post('address');
        $mobile = $request->post('mobile');
        $bank = $request->post('bank');
        $bank_account = $request->post('bank_account');
        $row = UsersInvoice::find($id);
        if (!$row) {
            return $this->fail('发票不存在');
        }
        // 使用事务管理
        Db::connection('plugin.admin.mysql')->transaction(function () use ($request, $row, $type, $name, $taxpayer, $address, $mobile, $bank, $bank_account) {
            // 删除旧记录并创建新记录
            $row->delete();
            UsersInvoice::create([
                'type' => $type,
                'name' => $name,
                'taxpayer' => $taxpayer,
                'address' => $address,
                'mobile' => $mobile,
                'bank' => $bank,
                'bank_account' => $bank_account,
            ]);
        }, 3); // 设置重试次数以应对死锁等异常情况
        return $this->success();
    }

    function delete(Request $request)
    {
        $ids = $request->post('ids');
        UsersInvoice::destroy($ids);
        return $this->success();
    }

    function select(Request $request)
    {
        $rows = UsersInvoice::where(['user_id' => $request->user_id])
            ->latest()
            ->paginate()
            ->items();
        return $this->success('成功', $rows);
    }

    function setDefault(Request $request)
    {
        $id = $request->post('id');
        UsersInvoice::where(['user_id' => $request->user_id])->update(['default' => 0]);
        UsersInvoice::where(['id' => $id])->update(['default' => 1]);
        return $this->success();
    }

    /**
     * 获取默认
     */
    function getDefault(Request $request)
    {
        $row = UsersInvoice::where(['user_id' => $request->user_id, 'default' => 1])->first();
        return $this->success('成功', $row);
    }
}

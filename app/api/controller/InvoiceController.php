<?php

namespace app\api\controller;

use app\admin\model\UsersInvoice;
use app\api\basic\Base;
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
        UsersInvoice::where(['id' => $id])->update([
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

    function delete(Request $request)
    {
        $id = $request->post('id');
        UsersInvoice::where(['id' => $id])->delete();
        return $this->success();
    }

    function select(Request $request)
    {
        $rows = UsersInvoice::where(['user_id' => $request->user_id])
            ->orderByDesc('id')
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
}

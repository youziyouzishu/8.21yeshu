<?php

namespace app\api\controller;

use app\admin\model\PrivacyService;
use app\api\basic\Base;
use support\Request;

class ServiceController extends Base
{

    #提交服务
    function insert(Request $request)
    {
        $type = $request->post('type');#类型:1=水机维修,2=水机清洗
        $address_id = $request->post('address_id');
        $visit_time = $request->post('visit_time');
        $image = $request->post('image');
        $mark = $request->post('mark');
        PrivacyService::create([
            'user_id' => $request->user_id,
            'address_id' => $address_id,
            'type' => $type,
            'visit_time' => $visit_time,
            'image' => $image,
            'mark' => $mark,
        ]);
        return $this->success();
    }

    #获取服务列表
    function select(Request $request)
    {
        $type = $request->post('type');#类型:0=全部,1=水机维修,2=水机清洗
        $rows = PrivacyService::where(['user_id' => $request->user_id])
            ->with(['address'=>function ($query) {
                $query->withTrashed();
            }])
            ->when(!empty($type), function ($query) use ($type) {
                $query->where('type', $type);
            })
            ->orderByDesc('id')
            ->paginate()
            ->items();
        return $this->success('成功', $rows);
    }

    function cancel(Request $request)
    {
        $id = $request->post('id');
        $row = PrivacyService::find($id);
        if (empty($row)) {
            return $this->fail('服务不存在');
        }
        if ($row->status != 1) {
            return $this->fail('服务已受理');
        }
        $row->status = 3;
        $row->save();
        return $this->success();
    }


}

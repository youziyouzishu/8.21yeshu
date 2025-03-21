<?php

namespace app\api\controller;

use app\admin\model\Area;
use app\admin\model\DeliveryConfig;
use app\admin\model\UsersAddress;
use app\admin\model\Warehouse;
use app\api\basic\Base;
use support\Request;

class AddressController extends Base
{

    /**
     * 添加地址
     */
    function insert(Request $request)
    {
        $name = $request->post('name');
        $mobile = $request->post('mobile');
        $province = $request->post('province');
        $city = $request->post('city');
        $region = $request->post('region');
        $detail = $request->post('detail');
        $default = $request->post('default', 0);
        $lat = $request->post('lat');
        $lng = $request->post('lng');
        $data = [
            'user_id' => $request->user_id,
            'name' => $name,
            'mobile' => $mobile,
            'province' => $province,
            'city' => $city,
            'region' => $region,
            'detail' => $detail,
            'default' => $default,
            'lat'=> $lat,
            'lng'=> $lng,
        ];

        if ($data['default'] == 0) {
            $existingDefault = UsersAddress::where(['user_id' => $request->user_id, 'default' => 1])->first();
            if (!$existingDefault) {
                $data['default'] = 1;
            }
        } else {
            UsersAddress::where(['user_id' => $request->user_id, 'default' => 1])->update(['default' => 0]);
        }

        UsersAddress::create($data);
        return $this->success();
    }

    /**
     * 设置默认地址
     */
    function setDefault(Request $request)
    {
        $address_id = $request->post('address_id');
        UsersAddress::where(['user_id' => $request->user_id])->update(['default' => 0]);
        UsersAddress::where(['id' => $address_id])->update(['default' => 1]);
        return $this->success();
    }

    /**
     * 获取默认地址
     */
    function getDefault(Request $request)
    {
        $row = UsersAddress::where(['user_id' => $request->user_id, 'default' => 1])->first();
        return $this->success('成功', $row);
    }

    /**
     * 获取指定地址
     */
    function get(Request $request)
    {
        $address_id = $request->post('address_id');
        $row = UsersAddress::find($address_id);
        if (!$row) {
            return $this->fail('地址不存在');
        }
        return $this->success('成功', $row);
    }

    /**
     * 编辑地址
     */
    function update(Request $request)
    {
        $address_id = $request->post('address_id');
        $name = $request->post('name');
        $mobile = $request->post('mobile');
        $province = $request->post('province');
        $city = $request->post('city');
        $region = $request->post('region');
        $detail = $request->post('detail');
        $default = $request->post('default', 0);
        $lat = $request->post('lat');
        $lng = $request->post('lng');


        $row = UsersAddress::find($address_id);
        if (!$row) {
            return $this->fail('地址不存在');
        }

        $fieldsToUpdate = [
            'name' => $name,
            'mobile' => $mobile,
            'province' => $province,
            'city' => $city,
            'region' => $region,
            'detail' => $detail,
            'default' => $default,
            'lat'=> $lat,
            'lng'=> $lng,
        ];

        if ($fieldsToUpdate['default'] == 1) {
            UsersAddress::where(['user_id' => $request->user_id])->update(['default' => 0]);
        }

        $row->fill($fieldsToUpdate);
        $row->save();
        return $this->success();
    }

    /**
     * 删除地址
     */
    function delete(Request $request)
    {
        $address_id = $request->post('address_id');
        $row = UsersAddress::where(['user_id' => $request->user_id])->find($address_id);
        if (!$row) {
            return $this->fail('地址不存在');
        }
        $row->delete();
        return $this->success();
    }

    /**
     * 地址列表
     */
    function select(Request $request)
    {
        $warehouses = Warehouse::all();
        if ($warehouses->isEmpty()) {
            return $this->fail('没有可用的仓库');
        }

        $minDistance = PHP_FLOAT_MAX;
        $maxDeliveryDistance = DeliveryConfig::max('end'); // 最大配送距离
        if (!$maxDeliveryDistance) {
            return $this->fail('未配置最大距离');
        }
        $rows = UsersAddress::where(['user_id' => $request->user_id])
            ->orderByDesc('id')
            ->paginate()
            ->items();
        foreach ($rows as $row){
            $closestWarehouse = null;
            foreach ($warehouses as $warehouse) {
                $distance = Area::getDistanceFromLngLat($row->lng, $row->lat, $warehouse->lng, $warehouse->lat);
                if ($distance < $minDistance && $distance <= $maxDeliveryDistance) {
                    $minDistance = $distance;
                    $closestWarehouse = $warehouse;
                }
            }
            if (!$closestWarehouse) {
                $row->lock = true;
            }else{
                $row->lock = false;
            }
        }
        return $this->success('成功', $rows);
    }

}

<?php

namespace app\api\controller;

use app\admin\model\Area;
use app\admin\model\DeliveryConfig;
use app\admin\model\User;
use app\admin\model\UsersCollect;
use app\admin\model\Warehouse;
use app\api\basic\Base;
use support\Log;
use support\Request;
use Tinywan\Jwt\JwtToken;

class UserController extends Base
{

    /**
     * 用户详情
     * @param Request $request
     * @return \support\Response
     */
    function getUserInfo(Request $request)
    {
        $user_id = $request->post('user_id');
        if (!empty($user_id)) {
            $request->user_id = $user_id;
        }
        $row = User::find($request->user_id);
        if (!$row) {
            return $this->fail('用户不存在');
        }
        return $this->success('成功', $row);
    }

    function bindMobile(Request $request)
    {
        $code = $request->post('code');
        $client_type =  $request->client_type;
        if ($client_type == 'user'){
            $config = config('wechat.UserMiniApp');
        }elseif ($client_type == 'transport'){
            $config = config('wechat.UserMiniApp');
        }elseif ($client_type == 'driver'){
            $config = config('wechat.UserMiniApp');
        }else{
            return $this->fail('客户端类型错误');
        }
        try {
            $app = new \EasyWeChat\MiniApp\Application($config);
            $api = $app->getClient();
            $ret = $api->postJson('/wxa/business/getuserphonenumber', [
                'code' => $code
            ]);
            $ret = json_decode($ret);
            if ($ret->errcode != 0) {
                throw new \Exception($ret->errmsg);
            }
            $mobile = $ret->phone_info->phoneNumber;
            $user = User::find($request->user_id);
            $user->mobile = $mobile;
            $user->save();
        }catch (\Throwable $e){
            Log::error('获取手机号失败');
            Log::error($e->getMessage());
            return $this->fail('获取手机号失败');
        }
        $token = JwtToken::generateToken([
            'id' => $user->id,
            'openid' => $user->openid,
            'client_type' => $client_type,
            'client' => JwtToken::TOKEN_CLIENT_MOBILE
        ]);
        return $this->success('成功', ['user' => $user, 'token' => $token]);
    }

    function getCollectList(Request $request)
    {
        $warehouses = Warehouse::all();
        if ($warehouses->isEmpty()) {
            return $this->fail('没有可用的仓库');
        }
        $closestWarehouse = null;
        $minDistance = PHP_FLOAT_MAX;
        $maxDeliveryDistance = DeliveryConfig::max('end'); // 最大配送距离
        if (!$maxDeliveryDistance) {
            return $this->fail('未配置最大距离');
        }
        foreach ($warehouses as $warehouse) {
            $distance = Area::getDistanceFromLngLat($request->lng, $request->lat, $warehouse->lng, $warehouse->lat);
            if ($distance < $minDistance && $distance <= $maxDeliveryDistance) {
                $minDistance = $distance;
                $closestWarehouse = $warehouse;
            }
        }

        if ($closestWarehouse) {
            $distance = round($minDistance, 2);
            $freight = DeliveryConfig::where('start', '<=', $distance)->where('end', '>=', $distance)->first()->price;
        } else {
            $freight = null;
        }

        $rows = UsersCollect::where(['user_id'=>$request->user_id])
            ->with(['goods'])
            ->latest()
            ->paginate()
            ->getCollection()
            ->each(function (UsersCollect $item)use($freight) {
                $item->setAttribute('freight',$freight);
            });
        return $this->success('成功', $rows);
    }


}

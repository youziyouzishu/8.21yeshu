<?php
/**
 * This file is part of webman.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author    walkor<walkor@workerman.net>
 * @copyright walkor<walkor@workerman.net>
 * @link      http://www.workerman.net/
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */

namespace support;

/**
 * Class Request
 * @package support
 * @property int $user_id 用户ID
 * @property string $lat 当前纬度
 * @property string $lng 当前经度
 * @property string $client_type 客户端类型 #user=用户端,transport=配送端,driver=司机端
 * @property string $openid 微信唯一标识
 */
class Request extends \Webman\Http\Request
{


    /**
     * 设置$request数据，自动覆盖更新
     * @param string $method
     * @param array $data
     *
     */
    function setParams(string $method, array $data)
    {
        $method = strtolower($method);
        if (!isset($this->data[$method])) {
            if ($method == 'post'){
                $this->parsePost();
            }
            if ($method == 'get'){
                $this->parseGet();
            }
        }
        $rawData = $this->data ?: [];// 获取原数据
        $newData = $rawData; // 复制原始数据
        $newData[$method] = array_merge($newData[$method] ?? [], $data); // 合并特定方法的数据
        $this->data = $newData; // 更新对象数据
    }
}
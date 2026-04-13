<?php

namespace App\Controller\Api;

use App\Controller\AbstractController;
use App\Exception\AppException;
use App\Library\MessageAlert;
use Psr\Http\Message\ResponseInterface;

class AlertController extends AbstractController
{
    /**
     * @throws AppException
     * @throws \WLib\Exception\AppException
     */
    public function alert(): ResponseInterface
    {
        $json = $this->request->getParsedBody();
        $country = arr_get($this->request->getQueryParams(), 'country');
        $country = !empty($country) ? $country : null;
        if (empty($country)) {
            throw new AppException("请选择告警国家");
        }
        if (empty($json) || empty($json['alarm'])) {
            throw new AppException("告警参数错误");
        }
        $alarm = $json['alarm'];
        $endTime = !empty($alarm['recover_time']) ? $alarm['recover_time'] : $alarm['last_eval_time'];
        $status = $alarm['status'];//状态 alerting,recovered
        $confirmTime = $alarm['confirmState']['confirmActionTime'];//认领时间
        $confirmUserName = $alarm['confirmState']['confirmUsername'];//认领人
        // 只有当前为alerting且已认领时才标识为处理中
        if ($alarm['confirmState']['isOk'] && $status == 'alerting') {
            $status = "processing";//处理中
        }
        $alertData = [
            'country' => $country,//所在国家
            'type' => $alarm['is_recovered'] === true ? 'recovered' : 'alert',
            'status' => $status,//状态
            'name' => $alarm['rule_name'],//规则名称
            'rule_id' => $alarm['rule_id'],//规则ID
            'rule' => $alarm['annotations'],//规则详情
            'pattern' => $alarm['searchQL'],//触发条件
            'level' => $alarm['severity'],//等级
            'value' => arr_get($alarm, 'labels.value'),//当前值
            'start_time' => date('Y-m-d H:i:s', $alarm['first_trigger_time']),//触发时间
            'end_time' => date('Y-m-d H:i:s', $endTime),//持续时间/恢复时间
            'confirm_time' => !empty($confirmTime) ? date('Y-m-d H:i:s', $confirmTime) : '',//认领时间
            'confirm_username' => !empty($confirmUserName) ? $confirmUserName : '',//认领人
            'duration' => $this->formatDuration($endTime - $alarm['first_trigger_time']),//持续时长
        ];
        $result = MessageAlert::send("INDICATOR_ALERT", $alertData);
        return $this->success($result);
    }

    /**
     * @param $seconds
     * @return string
     */
    protected function formatDuration($seconds): string
    {
        if ($seconds >= 3600) {
            // 1. 超过一小时
            $h = floor($seconds / 3600);
            $m = floor(($seconds % 3600) / 60);
            return $m > 0 ? "{$h}小时{$m}分钟" : "{$h}小时";
        } elseif ($seconds >= 60) {
            // 2. 超过一分钟但不到一小时
            $m = floor($seconds / 60);
            $s = $seconds % 60;
            return $s > 0 ? "{$m}分钟{$s}秒" : "{$m}分钟";
        } else {
            // 3. 不满一分钟
            return "{$seconds}秒";
        }
    }
}
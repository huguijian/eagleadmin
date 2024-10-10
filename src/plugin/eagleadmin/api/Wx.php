<?php

namespace plugin\eagleadmin\api;

use app\api\Auth;
use jwt\JwtInstance;
use plugin\eagleadmin\app\admin\logic\auth\DepartmentLogic;
use plugin\eagleadmin\app\constant\Constant;
use plugin\eagleadmin\app\home\logic\DeviceLogic;
use plugin\eagleadmin\app\model\EmsConfig;
use plugin\eagleadmin\app\model\EmsDevice;
use plugin\eagleadmin\app\model\EmsDeviceFailureCause;
use plugin\eagleadmin\app\model\EmsDeviceGroupUserMap;
use plugin\eagleadmin\app\model\EmsDeviceLog;
use plugin\eagleadmin\app\model\EmsDevicePlanField;
use plugin\eagleadmin\app\model\EmsDevicePlanInventory;
use plugin\eagleadmin\app\model\EmsDevicePlanTask;
use plugin\eagleadmin\app\model\EmsDeviceRepair;
use plugin\eagleadmin\app\model\EmsDistrict;
use plugin\eagleadmin\app\model\EmsNotice;
use plugin\eagleadmin\app\model\EmsNotOrderTime;
use plugin\eagleadmin\app\model\EmsUser;
use plugin\eagleadmin\app\model\EmsUserCreditScoreLog;
use plugin\eagleadmin\app\model\EmsUserOrder;
use plugin\eagleadmin\app\model\EmsUserRegister;
use plugin\eagleadmin\app\service\DeviceService;
use support\Db;
use support\Log;
use support\Redis;
use Tinywan\Jwt\JwtToken;
use DateTime;
use plugin\eagleadmin\app\home\logic\IrmsApiLogic;

class Wx
{
    public static function Login($userName,$password,&$msg)
    {
        $code = 0;
        //模型打印sql语句
//        Db::connection()->enableQueryLog();
        $userInfo = EmsUser::where('user_name', $userName)->first();
//        var_dump(Db::getQueryLog());
        if (empty($userInfo)) {
            $msg = '帐号未注册，请注册后再登录!';
            return false;
        }

        if (!password_verify($password, $userInfo["password"])) {
            $msg = '用户名或密码错误';
            return false;
        }
        if ($userInfo["is_audit"] != 1) {
            $msg = '该用户待审核';
            return false;
        }
        if ($userInfo["status"] != 1) {
            $msg = '该用户禁止登陆';
            return false;
        }
        $userInfo = collect($userInfo)->toArray();
        $token = JwtToken::generateToken($userInfo);

        $userInfo = EmsUser::with('roles')->where("id",$userInfo['id'])->first();
        $expire = config('plugin.ems.app.ems_token_expire');
        return [
            'token'     => $token,
            'nick_name' => $userInfo["nick_name"],
            'user_info' => $userInfo,
            'expire' => $expire,
        ];
    }

    /**
     * 微信登录
     * @param $userName
     * @param $msg
     * @return array|false
     */
    public static function wxLogin($userName,&$msg)
    {
        $code = 0;
        //模型打印sql语句
//        Db::connection()->enableQueryLog();
        $userInfo = EmsUser::where('user_name', $userName)->first();
//        var_dump(Db::getQueryLog());
        if (empty($userInfo)) {
            $msg = '帐号未注册，请注册后再登录!';
            return false;
        }

        if ($userInfo["is_audit"] != 1) {
            $msg = '该用户待审核';
            return false;
        }

        if ($userInfo["status"] != 1) {
            $msg = '该用户禁止登陆';
            return false;
        }

        $userInfo = collect($userInfo)->toArray();
        $token = JwtToken::generateToken($userInfo);

        $userInfo = EmsUser::with('roles')->where("id",$userInfo['id'])->first();
        $expire = config('plugin.ems.app.ems_token_expire');
        return [
            'token'     => $token,
            'nick_name' => $userInfo["nick_name"],
            'user_info' => $userInfo,
            'expire' => $expire,
        ];
    }


    /**
     * 微信注册
     * @return void
     */
    public static function wxRegister($phone,$nickName,$verifyCode,$password,$departmentId,&$msg)
    {
        $userInfo = EmsUser::where('user_name', $phone)->first();
        if ($userInfo) {
            $msg = '帐号已存在!';
            return false;
        }

        if (!$password) {
            $msg = '密码不能为空';
            return false;
        }

        $rVerifyCode = Redis::get('phone_'.$phone);
        if (!$rVerifyCode || $rVerifyCode != $verifyCode) {
            $msg = '请输入正确验证码!';
            return false;
        }

        EmsUser::insert([
            'user_name' => $phone,
            'phone' => $phone,
            'password' => password_hash($password, PASSWORD_BCRYPT, ["cost" => 12]),
            'status'   => 0,
            'is_audit' => 0,
            'nick_name' => $nickName,
            'department_id' => $departmentId ?: 0,
            'source' => '微信小程序',
            'credit_score' => 100
        ]);

        return true;
    }


    /**
     * 获取手机号验证码
     * @param $phone
     * @param $verifyCode
     * @param $msg
     * @return bool
     */
    public static function getPhoneVerifyCode($phone,$verifyCode,&$msg)
    {
        $userInfo = EmsUser::where('user_name', $phone)->first();
        if ($userInfo) {
            $msg = '此帐号已存在!';
            return false;
        }else{
            Redis::set('phone_'.$phone,$verifyCode,'EX',300);
        }

        return true;
    }


    /**
     * 获取设备列表
     * @param $userId
     * @param $params
     * @return array
     */
    public static function getDeviceList($data=[])
    {
        $userId = $data['data']['user_id'];
        $params = $data['data'];
        $EmsDevice = EmsDevice::query();
        if (isset($params["device_name"])) {
            $EmsDevice->where("device_name","like","%".$params["device_name"]."%");
        }

        $authorDevice = EmsDeviceGroupUserMap::where("user_id",$userId)->pluck("device_group_id");
        $authorDevice = collect($authorDevice)->toArray();

        $pageSize = 10;
        $paginator = $EmsDevice->orderBy("id", "desc")
            ->orderBy("sort","asc")
            ->paginate($pageSize, $columns = ['*'], $pageName = 'page', $params['page']);
        $list = $paginator->items() ?? [];
        foreach ($list as &$item) {
            if (in_array($item["device_group_id"],$authorDevice)) {
                $item["is_device_author"] = 1;
            }else{
                $item["is_device_author"] = 0;
            }
            // $deviceImg = $item['device_info'] ?? '';
            // $item['device_img'] = $deviceImg ? 'http://'.env('WECHAT_TCP_SERVER_HOST', '') . $deviceImg : '';
            //if (!empty($item['device_img'])) {
            //    if (is_file(public_path($item['device_img']))) {
            //        /*
            //        $ext = pathinfo($item['device_img'],PATHINFO_EXTENSION);
            //        $ext = strtolower($ext);
            //        if (in_array($ext,['jpg','jpeg'])) {
            //            $pre = 'data:image/jpeg;base64,';
            //        }else{
            //            $pre = 'data:image/png;base64,';
            //        }
            //        $item['device_img'] = $pre.base64_encode(file_get_contents(public_path($item['device_img'])));
            //    }else{
            //        $item['device_img'] = '';
            //    }
            //}
        }
        $res['total'] = $paginator->total();
        $res['items'] = $list;
        return $res;
    }


    /**
     * 获取设备详情
     * @param int $deviceId
     * @param string $msg
     * @return array|false
     * @throws \Exception
     */
    public static function getDeviceDetail($deviceId,$deviceNo,$userId,&$msg): bool|array
    {
        $res = DeviceLogic::getDeviceDetail($deviceId,$deviceNo,$userId,$msg);
        return $res;
    }


    /**
     * 获取部门列表
     * @return array
     */
    public static function getDepartmentList()
    {
        return DepartmentLogic::getDepartmentList([]);
    }

    /**
     * 用户预约
     * @param $params
     * @param $msg
     * @return bool
     */
    public static function userBooking($params,&$msg): bool
    {
        try {
            Db::beginTransaction();
            $date = $params['date']??"";
            $startTime = $params['start_time']??"";
            $endTime   = $params['end_time']??"";
            if (!$date) {
                $msg = '请选择预约日期!';
                Db::rollBack();
                return false;
            }

            $userInfo = EmsUser::where("id",$params['user_id'])->first();
            $config = EmsConfig::where("key","SYS_CONFIG")->first();
            $config = json_decode($config["value"],true);
            // 开启审核的状态为审核
            if ($config["is_audit"] == 1) {
                $status = EmsUserOrder::STATUS['待审核'];
            } else { // 未开启审核的直接审核通过
                $status = EmsUserOrder::STATUS['待上机'];
            }

            if (!Auth::isSupperAdmin($params['user_id'])) {
                if ($config["credit"]["status"]==1 && intval($userInfo["credit_score"])<intval($config["credit"]["score"])) {
                    $msg = "信用分低于{$config["credit"]["score"]}分，请联系管理员!";
                    Db::rollBack();
                    return false;
                }
            }

            $subjectId = $params["subject_id"];
            $deviceId  = $params['device_id'];
            $deviceInfo = EmsDevice::where("id",$deviceId)->first();

            $userOrder = [];

            $isRepeatBooking = $deviceInfo["is_double_booking"] === Constant::IS_REPEAT_BOOKING;


            $oderMiniTime = true;
            $oderMaxTime  = true;
            $flag = true;
            $sampleCount = true;
            $startTime = $date." ".$startTime;
            $endTime = $date." ".$endTime;
            if (!empty($deviceInfo['min_order_time'])) {
                $date1 = new \DateTime($startTime);
                $date2 = new \DateTime($endTime);

                if ($date2 < $date1) {
                    $msg = '结束时间不能小于开始时间！';
                    Db::rollBack();
                    return false;
                }

                $interval = $date1->diff($date2);
                $minutes = $interval->days * 24 * 60 + $interval->h * 60 + $interval->i;
                if ($minutes<$deviceInfo['min_order_time'] && $deviceInfo['min_order_time']<>0) {
                    $oderMiniTime = false;
                }
            }

            if (!empty($deviceInfo['max_order_time'])) {
                $date1 = new \DateTime($startTime);
                $date2 = new \DateTime($endTime);
                $interval = $date1->diff($date2);
                $minutes = $interval->days * 24 * 60 + $interval->h * 60 + $interval->i;
                if ($minutes>$deviceInfo['max_order_time'] && $deviceInfo['max_order_time']<>0) {
                    $oderMaxTime = false;
                }
            }

            if ($isRepeatBooking===false) {
                $count = EmsUserOrder::where('start_time','<',$endTime)
                    ->where('end_time','>',$startTime)
                    ->where('device_id',$deviceId)
                    ->whereIn('status', [
                        EmsUserOrder::STATUS['待审核'],
                        EmsUserOrder::STATUS['待上机'],
                        EmsUserOrder::STATUS['上机中'],
                    ])
                    ->count();

                if($count>0) {
                    $flag = false;
                }
            }

            if ($deviceInfo['is_sample']==1) {
                $params['sample_count'] = $params['sample_count']??0;
                if ($params['sample_count']>$deviceInfo['sample_count']) {
                    $sampleCount = false;
                }else{
                    (new DeviceService())->deviceSampleLog($deviceId,$params['user_id'],$deviceInfo['sample_count'],$params['sample_count'],"微信端-设备预约");
                    EmsDevice::where('id',$deviceId)->update([
                        'sample_count' => $deviceInfo['sample_count']-$params['sample_count']
                    ]);
                }
            }

            $userOrder[] = [
                'cost_type'  => $deviceInfo['cost_type'],
                'user_id'    => $userInfo['id'],
                'real_name'  => $userInfo['nick_name'],
                'subject_id' => $subjectId,
                'device_id'  => $deviceId,
                'order_time' => date('Y-m-d H:i:s',time()),
                'start_time' => $startTime,
                'end_time'   => $endTime,
                'device_ip'  => $deviceInfo["device_ip"],
                'status'   => $status,
                'terminal_ip'  => $deviceInfo["terminal_ip"],
                'experiment_theme'  => $params["experiment_theme"]??'',
                'sample_count' => $params["sample_count"]??0,
                'remark'  => $params["remark"]??'',
            ];

            if (false===$sampleCount) {
                $msg = '申领样本数量已超上限，请重新选择!';
                Db::rollBack();
                return false;
            }

            if (false===$flag) {
                $msg = "时间:".$startTime."~".$endTime."已在预约时间内!";
                Db::rollBack();
                return false;
            }

            if (false===$oderMiniTime){
                $msg = "时间:".$startTime."~".$endTime."小于最小预约时间!";
                Db::rollBack();
                return false;
            }

            if (false===$oderMaxTime){
                $msg = "时间:".$startTime."~".$endTime."大于最大预约时间!";
                Db::rollBack();
                return false;
            }

            foreach ($userOrder as $item) {
                Db::table('ems_user_order')->insertGetId($item);
            }
            Db::commit();
        }catch (\Exception $exception){
            $msg = $exception->getMessage();
            Db::rollBack();
            return false;
        }
        return true;
    }


    /**
     *  获取用户预约列表
     * @param $params
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public static function orderList($data)
    {
        $params = $data['data'];
        $model = EmsUserOrder::query();
        $model->with(['deviceInfo']);
        if (isset($params['device_name'])) {
            $model->whereHas('deviceInfo',function($query)use($params){
               $query->where('device_name','like','%'.$params['device_name'].'%');
            });
        }

        if (isset($params['status'])) {
            $model->where('status',$params['status']);
        }

        if (isset($params['user_id'])) {
            $model->where('user_id',$params['user_id']);
        }
        $list = $model->orderBy('create_time','desc')->get();
        //$list = collect($list)->toArray();
        // foreach ($list as &$item) {
        //     $deviceImg = $item['device_info']['device_img'] ?? '';
        //     $item['device_img'] = $deviceImg ? 'http://'.env('WECHAT_TCP_SERVER_HOST', '') . $deviceImg : '';
        //     //if (!empty($item['device_info'])) {
        //     //    if (is_file(public_path($item['device_info']['device_img']))) {
        //     //        $ext = pathinfo($item['device_info']['device_img'],PATHINFO_EXTENSION);
        //     //        $ext = strtolower($ext);
        //     //        if (in_array($ext,['jpg','jpeg'])) {
        //     //            $pre = 'data:image/jpeg;base64,';
        //     //        }else{
        //     //            $pre = 'data:image/png;base64,';
        //     //        }
        //     //        $item['device_info']['device_img'] = $pre.base64_encode(file_get_contents(public_path($item['device_info']['device_img'])));
        //     //    }else{
        //     //        $item['device_info']['device_img'] = '';
        //     //    }
        //     //}
        // }
        return collect($list)->toArray();
    }


    /**
     * 用户预约详情
     * @param $params
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|object|null
     */
    public static function orderDetail($params)
    {
        $res = EmsUserOrder::with(['deviceInfo','userInfo','subjectInfo'])->where("id",$params['id'])->first();
        // if (!empty($res['device_info'])){
        //     //if (is_file(public_path($res['device_info']['device_img']))) {
        //     //    $ext = pathinfo($res['device_img'],PATHINFO_EXTENSION);
        //     //    $ext = strtolower($ext);
        //     //    if (in_array($ext,['jpg','jpeg'])) {
        //     //        $pre = 'data:image/jpeg;base64,';
        //     //    }else{
        //     //        $pre = 'data:image/png;base64,';
        //     //    }
        //     //    $res['device_info']['device_img'] = $pre.base64_encode(file_get_contents(public_path($res['device_img'])));
        //     //}else{
        //     //    $res['device_info']['device_img'] = '';
        //     //}
        //     $deviceImg = $res['device_info']['device_img'] ?? '';
        //     $item['device_img'] = $deviceImg ? 'http://'.env('WECHAT_TCP_SERVER_HOST', '') . $deviceImg : '';
        // }
        return $res;
    }


    /**
     * 取消用户预约
     * @return \support\Response
     */
    public static function cancelUserBooking($params,&$msg)
    {
        $userOrderId = $params["id"];
        $userOrderInfo = EmsUserOrder::where("id",$userOrderId)->first();
        if (!$userOrderInfo) {
            $msg = '预约信息不存在!';
            return false;
        }
        if ($userOrderInfo["status"] > EmsUserOrder::STATUS['待上机']) {
            $msg = '只有待上机及以前的的预约单才能取消!';
            return false;
        }
        $sysCofnig = EmsConfig::where("key","SYS_CONFIG")->first();
        $sysCofnig["value"] = json_decode($sysCofnig["value"],true);

        if (!empty($sysCofnig)) {
            if ($sysCofnig["value"]["hm_cancel_order"]["status"]==1) {
                $calculateMinutesDiff = function($date1,$date2){
                    $minutesDiff = round(abs($date2 - $date1) / 60);
                    return $minutesDiff;
                };
                if (time()>strtotime($userOrderInfo["start_time"]) && ($calculateMinutesDiff(strtotime($userOrderInfo["start_time"]),time())>$sysCofnig["value"]["hm_cancel_order"]["min"])) {
                    $msg = '无法撤消预约，已超规定时间范围内';
                    return false;
                }
            }
        }

        EmsUserOrder::where('id',$userOrderId)->update(['status' => EmsUserOrder::STATUS['取消预约']]);
        return true;
    }


    /**
     * 审核列表
     * @param $param
     * @return array
     */
    public static function auditList($data): array
    {
        $params = $data['data'];
        $pageSize = 10;
        $paginator = EmsUserOrder::with('orderLog');
        $paginator->with(['deviceInfo']);
        if (isset($params['device_name'])) {
            $paginator->whereHas('deviceInfo',function($query)use($params){
                $query->where('device_name','like','%'.$params['device_name'].'%');
            });
        }
        $isAudit = $params['is_audit'] ?? null;

        if ($isAudit == EmsUserOrder::IS_AUDIT['待审核']) {
            $paginator->where('status', EmsUserOrder::STATUS['待审核']);
        } elseif ($isAudit == EmsUserOrder::IS_AUDIT['已审核']) {
            $paginator->where('status', '>=', EmsUserOrder::STATUS['待上机']);
        }
//        Db::connection()->enableQueryLog();
        $paginator = $paginator->orderBy('start_time','asc')->paginate($pageSize, $columns = ['*'], $pageName = 'page', $params['page']);
//        var_dump(Db::getQueryLog());
        $list = $paginator->items() ?? [];
        $list = collect($list)->toArray();
        // foreach ($list as &$item) {
        //     $deviceImg = $item['device_info']['device_img'] ?? '';
        //     $item['device_info']['device_img'] = $deviceImg ? 'http://'.env('WECHAT_TCP_SERVER_HOST', '') . $deviceImg : '';
        //     //if (!empty($item['device_info'])) {
        //     //    if (is_file(public_path($item['device_info']['device_img']))) {
        //     //        $item['device_info']['device_img'] = env('DEVICE_IMG_HOST', ''). $item['device_info']['device_img'];
        //     //        $ext = pathinfo($item['device_info']['device_img'],PATHINFO_EXTENSION);
        //     //        $ext = strtolower($ext);
        //     //        if (in_array($ext,['jpg','jpeg'])) {
        //     //            $pre = 'data:image/jpeg;base64,';
        //     //        }else{
        //     //            $pre = 'data:image/png;base64,';
        //     //        }
        //     //        $item['device_info']['device_img'] = $pre.base64_encode(file_get_contents(public_path($item['device_info']['device_img'])));
        //     //    }else{
        //     //        $item['device_info']['device_img'] = '';
        //     //    }
        //     //}
        // }
        $res['total'] = $paginator->total();
        $res['items'] = $list;
        return $res;
    }

    /**
     * 审核详情
     * @param $params
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|object|null
     */
    public static function auditDetail($params)
    {
        return EmsUserOrder::with(['deviceInfo','userInfo','subjectInfo'])->where("id",$params['id'])->first();
    }

    /**
     * 开始审核/通过/不通过
     * @param $params
     * @return int
     */
    public static function startAudit($params)
    {
        return EmsUserOrder::where('id',$params['id'])
            ->update([
                'status' => $params['status'],
                'audit_remark' => $params['audit_remark'] ?? '',
        ]);
    }


    /**
     * 设备预约历史记录
     * @param $params
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public static function orderHistory($data)
    {
        $params = $data['data'];
        return  EmsUserOrder::with(['userInfo', 'deviceInfo'])
            ->where('device_id',$params['device_id'])
            ->orderBy('create_time','desc')
            ->get();
    }


    /**
     * 工作台仪表盘
     * @return array
     */
    public static function dashboard($data)
    {
        $params = $data['data'];
        $assetTotalCount = EmsDevice::count();
        //待保养数量
        $waitMaintenanceCount = EmsDevice::whereHas('devicePlanTask.planInfo', function($query)use($params){
                $query->where('ems_device_plan.type',Constant::PLAN_TYPE_ONE)
                    ->where('ems_device_plan_task.status','pending')
                    ->where('ems_device_plan_task.task_uid',$params['user_id'])
                    ->where('ems_device_plan_task.starttime','<=',time())
                    ->where('ems_device_plan_task.duetime','>=',time());
            })
            ->count();
        //待巡检数量
        $waitInspectionCount = EmsDevice::whereHas('devicePlanTask.planInfo',function($query)use($params){
                $query->where('ems_device_plan.type',Constant::PLAN_TYPE_TWO)
                    ->where('ems_device_plan_task.status','pending')
                    ->where('ems_device_plan_task.task_uid',$params['user_id'])
                    ->where('ems_device_plan_task.starttime','<=',time())
                    ->where('ems_device_plan_task.duetime','>=',time());
            })
            ->count();

        //待盘点数量
        $waitInventoryCount = EmsDevice::whereHas('devicePlanTask.planInfo',function($query)use($params){
                $query->where('ems_device_plan.type',Constant::PLAN_TYPE_THREE)
                    ->where('ems_device_plan_task.status','pending')
                    ->where('ems_device_plan_task.task_uid',$params['user_id'])
                    ->where('ems_device_plan_task.starttime','<=',time())
                    ->where('ems_device_plan_task.duetime','>=',time());
            })
            ->count();


        //设备运行情况
        $normalCount = EmsDevice::where('work_status',Constant::DEVICE_WORK_STATUS_ONE)
            ->count();
        $repairCount = EmsDevice::where('work_status',Constant::DEVICE_WORK_STATUS_TWO)
            ->count();
        $scrapCount  = EmsDevice::where('work_status',Constant::DEVICE_WORK_STATUS_THREE)
            ->count();

        $waitRepairCount  = EmsDeviceRepair::where('delete_time','=',null)
            ->where('repair_uid',$params['user_id'])
            ->where('status',Constant::DEVICE_REPAIR_TWO)
            ->count();
        $waitOrderCount = EmsDeviceRepair::where('delete_time','=',null)
            ->where('status',Constant::DEVICE_REPAIR_ONE)
            ->count();
        $miCount = [];
        $deviceRepairCount = [];

        for ($i = 0; $i < 7; $i++) {
            $date = date("Y-m-d", strtotime("-$i day"));
            $mtCount  = EmsDeviceLog::where('add_uid',$params['user_id'])->where('type','=',Constant::RECORD_TYPE_ONE)->whereBetween('create_time',[$date.' 00:00:00',$date. ' 23:59:59'])->count();
            $insCount = EmsDeviceLog::where('add_uid',$params['user_id'])->where('type','=',Constant::RECORD_TYPE_TWO)->whereBetween('create_time',[$date.' 00:00:00',$date. ' 23:59:59'])->count();
            $miCount[] = [
                'date' => $date,
                'mt_count'=> $mtCount,
                'ins_count' => $insCount
            ];
            $deviceRepairCount[] = [
                'date' => $date,
                'count' => EmsDeviceRepair::where('repair_uid',$params['user_id'])->whereBetween('create_time',[$date.' 00:00:00',$date.' 23:59:59'])->count()
            ];
        }

        usort($miCount,function($a,$b){
            return (strtotime($a['date']) - strtotime($b['date']));
        });

        usort($deviceRepairCount,function($a,$b){
            return (strtotime($a['date']) - strtotime($b['date']));
        });

        $firstDayOfMonth = date('Y-m-01 00:00:00');
        $lastDayOfMonth =  date('Y-m-d 23:59:59');

        $failureCause = EmsDeviceFailureCause::pluck('name','id');
        $failure = EmsDeviceRepair::where('repair_uid',$params['user_id'])->where('delete_time','=',null)->whereBetween('create_time',[$firstDayOfMonth,$lastDayOfMonth])->select('failure_cause_id', DB::raw('count(failure_cause_id) as total'))->groupBy('failure_cause_id')->get();
        foreach ($failure as &$item) {
            $item['failure_cause_txt'] = $failureCause[$item['failure_cause_id']]??"";
        }

        //设备巡检保养计划
        $data = [
            'equipment_operation_status' => [
                'normal_count' => $normalCount,
                'repair_count' => $repairCount,
                'scrap_count' => $scrapCount,
            ],
            'mi_count' => $miCount,
            'main_work_count'   => $deviceRepairCount,
            'failure_cause'     => $failure,
            'wait_order_count'  => $waitOrderCount,//待接工单数量
            'wait_repair_count' => $waitRepairCount,//待维修工单数量
            'wait_maintenance_count' => $waitMaintenanceCount,//待保养设备数量
            'wait_inspection_count'  => $waitInspectionCount,//待巡检设备数量
            'wait_inventory_count'   => $waitInventoryCount,//待盘点数量
            'asset_total_count'      => $assetTotalCount,//资产总数
        ];
        return $data;
    }

    /**
     * 工单列表(我的工单/待接工单)
     * @param $params
     * @return array
     */
    public static function workOrder($params=[]): array
    {
        $pageSize = 10;
        //status:1待派单 2 待维修  status:3,4 已维修+停机报废
        $status = explode(',',$params['status']);

        $paginator = EmsDeviceRepair::query();
        $paginator->whereIn('status',$status)->orderBy('id','desc');
        if (isset($params['user_id']) && !empty($params['user_id'])) {
            $paginator = $paginator->where('repair_uid',$params['user_id'])->with(['deviceInfo','failureCauseInfo'])->paginate($pageSize, $columns = ['*'], $pageName = 'page', $params['page']);
        }else{
            $paginator = $paginator->with(['deviceInfo','failureCauseInfo'])->paginate($pageSize, $columns = ['*'], $pageName = 'page', $params['page']);
        }
        $list = $paginator->items() ?? [];
        $res['total'] = $paginator->total();
        $res['items'] = $list;
        return $res;
    }


    /**
     * 维修工单详情
     * @param $params
     * @return array|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|object
     */
    public static function workOrderDetail($params=[])
    {
        $info = EmsDeviceRepair::where('id',$params['id'])->with(['deviceInfo.documentInfo','repairUserInfo.userInfo','registeUserInfo','failureCauseInfo'])->first();
        $date1 = new DateTime($info['register_time']??'');
        $date2 = new DateTime($info['repair_time']??'');
        $interval = $date2->diff($date1);
        $info['repair_duration'] = $interval->format('%a 天 %h 小时 %i 分钟');
        return $info;
    }


    /**
     * 维修登记
     * @param $params
     * @return bool
     * @throws \support\exception\BusinessException
     */
    public static function repairRegister($params=[])
    {
        $params = inputFilter($params,EmsDeviceRepair::class);
        EmsDeviceRepair::where('id',$params['id'])->update([
            'status' => $params['status'],
            'repair_time' => date('Y-m-d H:i:s',time()),
            'failure_cause_id' => $params['failure_cause_id']??0,
            'repair_content' => $params['repair_content']??"",
            'repair_image' => $params['repair_image']??"",
        ]);

        $info = EmsDeviceRepair::where('id',$params['id'])->first();
        $name = '';
        if ($params['status']===Constant::DEVICE_REPAIR_THREE) {
            EmsDevice::where('id',$info['device_id'])->update([
                'work_status' => Constant::DEVICE_WORK_STATUS_ONE,
            ]);
            $name = '维修结果:已维修';
        }elseif($params['status']===Constant::DEVICE_REPAIR_FOUR){
            EmsDevice::where('id',$info['device_id'])->update([
                'work_status' => Constant::DEVICE_WORK_STATUS_THREE,
            ]);
            $name = '维修结果:停机报废';
        }

        EmsDeviceLog::insert([
            'device_id' => $info['device_id'],
            'relate_id' => $info['id'],
            'add_uid' => $info['repair_uid'],
            'type' => Constant::RECORD_TYPE_THREE,
            'name' => $name,
            'content' => '[]'
        ]);

        return true;
    }

    /**
     * 我要报修
     * @return void
     */
    public static function repairReport($params,&$msg)
    {
        Db::beginTransaction();
        try {
            $count = EmsDeviceRepair::count();
            $workNo = 'R'.date('Ymd',time()).'-'.str_pad($count,3,'0',STR_PAD_LEFT);
            EmsDeviceRepair::insert([
                'work_no' => $workNo,
                'device_id' => $params['device_id'],
                'register_uid' => $params['user_id'],
                'register_time' => date('Y-m-d H:i:s',time()),
                'content' => $params['content'],
                'register_image' => $params['register_image']??"",
            ]);
            EmsDevice::where('id',$params['device_id'])->update([
                'work_status' => Constant::DEVICE_WORK_STATUS_TWO,
            ]);
            Db::commit();
        }catch (\Throwable $exception) {
            Db::rollBack();
            $msg = $exception->getMessage();
            return false;
        }

        return true;
    }

    /**
     * 获取计划任务对应表单
     * @param $params
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public static function planTaskForm($params=[])
    {
        $taskInfo = EmsDevicePlanTask::where('id',$params['task_id'])->first();
        $form = EmsDevicePlanField::where('plan_id',$taskInfo['plan_id'])->get();
        foreach ($form as $item) {
            $item['options'] = json_decode($item['options'],true);
        }
        return $form;
    }


    /**
     * 记录-保养/巡检/盘点
     * @param $params
     * @return void
     */
    public static function planTaskLog($params,&$msg)
    {
        Db::beginTransaction();
        try {
            $taskInfo = EmsDevicePlanTask::with(['planInfo'])->where('id',$params['task_id'])->first()->toArray();
            $name = '';
            $logType = null;

            if ($taskInfo['plan_info']['type']==Constant::PLAN_TYPE_ONE) {
                $name = '保养结果:正常';
                $logType = Constant::RECORD_TYPE_ONE;

                $taskIds = EmsDevicePlanTask::where('plan_id',$taskInfo['plan_id'])
                    ->where('device_id',$taskInfo['device_id'])
                    ->pluck('id')->toArray();

                EmsDevicePlanTask::whereIn('id',$taskIds)->update([
                    'status' => 'normal',
                ]);
            }elseif($taskInfo['plan_info']['type']==Constant::PLAN_TYPE_TWO) {
                $name = '巡检结果:'.$params['content']['work_status'];
                $workStatus = 0;
                if ($params['content']['work_status']=='正常') {
                    $workStatus = Constant::DEVICE_WORK_STATUS_ONE;
                }elseif($params['content']['work_status']=='停机待修') {
                    $workStatus = Constant::DEVICE_WORK_STATUS_TWO;
                }elseif($params['content']['work_status']=='停机报废') {
                    $workStatus = Constant::DEVICE_WORK_STATUS_THREE;
                }

                EmsDevice::where('id',$taskInfo['device_id'])->update([
                    'work_status' => $workStatus,
                ]);
                $logType = Constant::RECORD_TYPE_TWO;

                EmsDevicePlanTask::where('id',$params['task_id'])->update([
                    'status' => 'normal',
                ]);
            }elseif($taskInfo['plan_info']['type']==Constant::PLAN_TYPE_THREE) {
                $name = '盘点结果:已盘';
                EmsDevicePlanInventory::where('plan_id',$taskInfo['plan_id'])->increment('complete_num',1);
                EmsDevicePlanInventory::where('plan_id',$taskInfo['plan_id'])->decrement('wait_num',1);
                $logType = Constant::RECORD_TYPE_FOUR;
                $taskIds = EmsDevicePlanTask::where('plan_id',$taskInfo['plan_id'])
                    ->where('device_id',$taskInfo['device_id'])
                    ->pluck('id')->toArray();

                EmsDevicePlanTask::whereIn('id',$taskIds)->update([
                    'status' => 'normal',
                ]);
            }

            EmsDeviceLog::insert([
                'device_id' => $taskInfo['device_id'],
                'relate_id' => $taskInfo['plan_id'],
                'add_uid' => $params['user_id'],
                'type' => $logType,
                'name' => $name,
                'content' => json_encode($params['content'])
            ]);
            Db::commit();
        }catch (\Throwable $exception) {
            Db::rollBack();
            $msg = $exception->getMessage();
            return false;
        }
       return true;
    }


    /**
     * 获取故障原因
     * @return array
     */
    public static function getFailurecause($params=[]): array
    {
        $list = EmsDeviceFailureCause::where('delete_time',null)->get();
        $list = collect($list)->toArray();
        return $list;
    }

    /**
     * 我要接单
     * @param $params
     * @return bool
     */
    public static function takeOrder($params)
    {
        $res = EmsDeviceRepair::where('id',$params['id'])->update([
            'status' => Constant::DEVICE_REPAIR_TWO,
            'repair_uid' => $params['user_id']
        ]);
        return $res;
    }

    /**
     * 获取设备详情
     * @param $params
     * @return array|mixed[]
     * @throws \Exception
     */
    public static function getDeviceInfo($params): array
    {
        $EmsDevice = EmsDevice::query();
        if (isset($params['device_no']) && !empty($params['device_no'])) {
            $EmsDevice->where('device_no',$params['device_no']);

        }elseif(isset($params['device_id']) && !empty($params['device_id'])) {
            $EmsDevice->where('id',$params['device_id']);
        }

        $info = $EmsDevice->with(['deviceSupplier','documentInfo'])->first();
        $district = EmsDistrict::pluck('name','id');
        $info['province'] = $district[$info['province_id']]??'';
        $info['city'] = $district[$info['city_id']]??'';
        $info['area'] = $district[$info['area_id']]??'';

        $history = EmsDeviceLog::with(['userInfo'])->where('device_id',$info['id'])->orderBy('id','desc')->get();
        $info['history'] = $history;

        $planTask = EmsDevicePlanTask::with(['planInfo'])
        ->where('device_id',$info['id'])
        ->where('task_uid',$params['user_id'])
        ->where('status','pending')
        ->where('duetime',">=",time())->where('starttime',"<=",time())->get();

        $repairInfo = EmsDeviceRepair::where('device_id',$info['device_id'])->where('status',Constant::DEVICE_REPAIR_TWO)->where('repair_uid',$params['user_id'])->first();
        $maintenance = [];
        $inspection  = [];
        $inventory   = [];

        $planTask = (collect($planTask)->toArray());

        foreach ($planTask as $item) {
            if ($item['plan_info']['type']==Constant::PLAN_TYPE_ONE) {//保养
                $maintenance = [
                    'id' => $item['id'],
                    'title' => $item['plan_info']['name'],
                    'periodicity' => $item['plan_info']['periodicity'],
                    'duetime' => date('Y-m-d H:i:s',$item['duetime']),
                ];
            }elseif($item['plan_info']['type']==Constant::PLAN_TYPE_TWO) {//巡检
                $inspection = [
                    'id' => $item['id'],
                    'title' => $item['plan_info']['name'],
                    'periodicity' => $item['plan_info']['periodicity'],
                    'duetime' => date('Y-m-d H:i:s',$item['duetime']),
                ];
            }elseif($item['plan_info']['type']==Constant::PLAN_TYPE_THREE) {//盘点
                $inventory = [
                    'id' => $item['id'],
                    'title' => $item['plan_info']['name'],
                    'periodicity' => 0,
                    'duetime' => date('Y-m-d H:i:s',$item['duetime']),
                ];
            }
        }


        $info = collect($info)->toArray();
        $info['todos'] = [];
        if (!empty($maintenance)) {
            $info['todos']['maintenance'] = $maintenance;
        }

        if (!empty($inspection)) {
            $info['todos']['inspection'] = $inspection;
        }

        if (!empty($repairInfo)) {
            $info['todos']['repair'] = $repairInfo;
        }

        if (!empty($inventory)) {
            $info['todos']['inventory']= $inventory;
        }
        return $info;
    }

    /**
     * 获取设备列表(停机报废、巡检、保养、盘点)
     * @param $params
     * @return array
     */
    public static function getDeviceListByType($data)
    {
        $params = $data['data'];
        $pageSize = 10;
        $paginator = EmsDevice::query();
        if (isset($params['search_type']) && $params['search_type']=='device_name') {
            $paginator->where('device_name','like','%'.$params['keyword'].'%');
        }
        if (isset($params['search_type']) && $params['search_type']=='device_no') {
            $paginator->where('device_no','like','%'.$params['keyword'].'%');
        }

        if (isset($params['work_status']) && $params['work_status']===Constant::DEVICE_WORK_STATUS_THREE) {
            $paginator->where('work_status',Constant::DEVICE_WORK_STATUS_THREE);
        }

        if (isset($params['plan_type']) && $params['plan_type']=='maintenance') {//设备保养
            $paginator->with(['devicePlanTask.planInfo'])->whereHas('devicePlanTask.planInfo',function($query)use($params){
                $query->where('ems_device_plan.type',Constant::PLAN_TYPE_ONE)->where('ems_device_plan_task.status','pending')->where('ems_device_plan_task.task_uid',$params['user_id'])->where('ems_device_plan_task.starttime','<=',time())->where('ems_device_plan_task.duetime','>=',time());
            });
        }

        if (isset($params['plan_type']) && $params['plan_type']=='inspection') {//设备巡检
            $paginator->whereHas('devicePlanTask.planInfo',function($query)use($params){
                 $query->where('ems_device_plan.type',Constant::PLAN_TYPE_TWO)->where('ems_device_plan_task.status','pending')->where('ems_device_plan_task.task_uid',$params['user_id'])->where('ems_device_plan_task.starttime','<=',time())->where('ems_device_plan_task.duetime','>=',time());
            });
        }

        if (isset($params['plan_type']) && $params['plan_type']=='inventory') {//设备盘点
            $paginator->with(['devicePlanTask.planInfo'])->whereHas('devicePlanTask.planInfo',function($query)use($params){
                $query->where('ems_device_plan.type',Constant::PLAN_TYPE_THREE)->where('ems_device_plan_task.status','pending')->where('ems_device_plan_task.task_uid',$params['user_id'])->where('ems_device_plan_task.starttime','<=',time())->where('ems_device_plan_task.duetime','>=',time());
            });
        }

        $paginator = $paginator->paginate($pageSize, $columns = ['*'], $pageName = 'page', $params['page']);
        $list = $paginator->items() ?? [];
        $district = EmsDistrict::pluck('name','id');
        foreach ($list as &$item) {
            $item['province'] = $district[$item['province_id']]??'';
            $item['city'] = $district[$item['city_id']]??'';
            $item['area'] = $district[$item['area_id']]??'';
        }
        $res['total'] = $paginator->total();
        $res['items'] = $list;
        return $res;
    }

    /**
     * 历史记录
     * @return void
     */
    public static function historyInfo($data)
    {
        $params = $data['data'];
        $EmsDeviceLog = EmsDeviceLog::query();
        if ($params['type']===Constant::RECORD_TYPE_ONE || $params['type']==Constant::RECORD_TYPE_TWO) {
            $info = $EmsDeviceLog->with(['deviceInfo','userInfo'])->where('id',$params['id'])->first();
        }elseif($params['type']==Constant::RECORD_TYPE_THREE) {
            $info = $EmsDeviceLog->with(['deviceInfo','userInfo','repairInfo.failureCauseInfo','repairInfo.registeUserInfo'])->where('id',$params['id'])->first();
        }elseif($params['type']==Constant::RECORD_TYPE_FOUR) {
            $info = $EmsDeviceLog->with(['deviceInfo','userInfo'])->where('id',$params['id'])->first();
        }
        $info['content'] = json_decode($info['content'],true);
        return $info;
    }


    /**
     * 获取用户信用记录
     * @param $params
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public static function getUserCreditScoreLog($params)
    {
        $list = EmsUserCreditScoreLog::where("user_id",$params['user_id'])->get();
        return $list;
    }

    /**
     * 检查是否应该显示上机及下机
     * @param $params
     *
     */
    public static function checkShowOnoff($params)
    {
        $userId = $params['user_id'];
        $deviceNo = $params['device_no'];
        $deviceId = EmsDevice::where('device_no', $deviceNo)->value('id');

        // 当前时间以前有未下机的。展示上下机按钮
        $res = EmsUserOrder::where(['device_id' => $deviceId, 'user_id' => $userId])
            ->whereNull('off_time')
            ->value('id');
        if ($res) {
            return ['show' => 1];
        }
        // 当前时间以后预约时间前
        $gap = env('SHOW_ON_OFF_GAP', 24 * 3600);
        $compareTime = date('Y-m-d H:i:s', time() + $gap);
        $res = EmsUserOrder::where([
                'device_id' => $deviceId,
                'user_id' => $userId
            ])
            ->where('start_time', '>=', $compareTime)
            ->value('id');
        if ($res) {
            return ['show' => 1];
        }
        return ['show' => 0];
    }

    /**
     * 上机
     * @param $data
     * @return null
     * @throws \plugin\eagleadmin\app\exception\BusinessException
     */
    public static function turnOn($data)
    {
        $params = $data['data'];
        $deviceId  = $params['device_id'];
        $deviceInfo = EmsDevice::where("id", $deviceId)
            ->first();
        if (!$deviceInfo) {
            tips('设备信息不存在！');
        }

        $userInfo = EmsUser::where("id", $params['user_id'])->first();

        // 查询设备状态，只有是待上机才能上机
        if ($deviceInfo['use_status'] == EmsDevice::USE_STATUS['上机使用中']) {
            $isSuperCard = EmsUser::where('id', $deviceInfo['device_useid'])->value('is_super_card');
            if ($isSuperCard) {
                tips('管理员正在使用中，请联系管理员');
            }
            tips('设备正在使用中,请耐心等待!');
        }

        return (new IrmsApiLogic())->powerSwipe(
            $userInfo['card_num'],
            $deviceInfo['terminal_no'],
            $deviceInfo['site_id'],
            '',
            true,
            0,
            $params['user_id'],
        );
    }


    /**
     * 下机
     * @param $data
     * @return null
     * @throws \plugin\eagleadmin\app\exception\BusinessException
     */
    public static function turnOff($data)
    {
        $params = $data['data'];
        $deviceId  = $params['device_id'];
        $deviceInfo = EmsDevice::where("id", $deviceId)
            ->first();
        if (!$deviceInfo) {
            tips('设备信息不存在！');
        }
        $userInfo = EmsUser::where("id", $params['user_id'])->first();
        // 查询设备状态，只有是上机中才能下机
        if ($deviceInfo['use_status'] != EmsDevice::USE_STATUS['上机使用中'] && $deviceInfo['device_type']==Constant::DEVICE_TYPE_FOUR) {
            tips('门禁设备使用结束!');
        } elseif($deviceInfo['use_status'] != EmsDevice::USE_STATUS['上机使用中']){
            tips('只有是上机中才能下机!');
        }

        return (new IrmsApiLogic())->powerSwipe(
            $userInfo['card_num'],
            $deviceInfo['terminal_no'],
            $deviceInfo['site_id'],
            '',
            true,
            0,
            $params['user_id'],
        );
    }

    /**
     * 获取公告信息
     * @return mixed
     */
    public static function getNotice($userId)
    {
        $notice = EmsNotice::orderBy('id','desc')->first();
        $isNotice = 0;
        if ($notice) {
            if ($notice['type']==2) {
                $isNotice = 1;
            }else{
                $userInfo = EmsUser::where('id',$userId)->first();
                $isNotice = $userInfo['is_notice'];
            }
        }

        return [
            'is_notice' => $isNotice,
            'notice_content' => $notice['content'] ?? ''
        ];
    }


    /**
     * 确认公告内容
     * @param $userId
     * @return bool
     */
    public static function knowNotice($userId)
    {
        EmsUser::where('id',$userId)->update([
            'is_notice' => 0
        ]);
        return true;
    }
}

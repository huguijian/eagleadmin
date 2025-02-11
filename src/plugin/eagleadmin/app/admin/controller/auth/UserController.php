<?php

namespace plugin\eagleadmin\app\admin\controller\auth;

use app\model\SsyDeviceAuditGroup;
use app\model\SsyUserCreditScoreLog;
use plugin\eagleadmin\app\admin\logic\auth\UserLogic;
use plugin\eagleadmin\app\admin\validate\auth\UserValidate;
use plugin\eagleadmin\app\BaseController;
use plugin\eagleadmin\app\model\EgUser;
use plugin\eagleadmin\app\model\EmsDepartment;
use plugin\eagleadmin\app\model\EmsDeviceAuditGroup;
use plugin\eagleadmin\app\model\EmsDeviceGroup;
use plugin\eagleadmin\app\model\EmsDeviceGroupUserMap;
use plugin\eagleadmin\app\model\EmsRole;
use plugin\eagleadmin\app\model\EmsUser;
use jwt\JwtInstance;
use plugin\eagleadmin\app\model\EmsUserCreditScoreLog;
use plugin\eagleadmin\app\model\EmsUserDepartment;
use plugin\eagleadmin\app\model\EmsUserRegister;
use plugin\eagleadmin\app\model\EmsUserRole;
use plugin\eagleadmin\app\model\EmsUserSubjectMap;
use support\Db;
use support\exception\BusinessException;
use support\Request;
use support\Response;
use Tinywan\Jwt\JwtToken;

class UserController extends BaseController
{

    protected array $noNeedAuth = ['getRole','userInfo'];

    protected $model = null;

    const NORMAL_USER = 'NORMAL_USER';

    const SUPER_USER_ID = 1;

    public function __construct()
    {
        $this->model = new EgUser();
    }

    public function select(Request $request): Response
    {
        $this->withArr = ['department'];
        [$where, $page_size, $order] = $this->selectInput($request);
        $search = $request->input('search');
        if (!empty($search)) {
            foreach ($search as $item) {
                if ($item['field']=='department_id' && !empty($item['val'])) {
                    $this->hasArr = [
                        [
                            'relation_name'=>'department',
                            'where'=>[
                                ['department_id','=',$item['val']]
                            ]
                        ],
                    ];
                }
            }
        }
        $model     = $this->selectMap($where,$order);
        $paginator = $model->paginate($page_size);
        $items = $paginator->items();

        $userIds = array_column($items,"id");
        // $departmentId = array_column($items,"department_id");
        // $departmentList = EmsDepartment::whereIn('id',$departmentId)->pluck('name','id');
        $userRole = UserLogic::getRoleListByUserIds($userIds);
        foreach ($items as &$item) {
            $dep = $item['department'] ?? [];
            $item['department_ids'] = $dep->pluck('id') ?? [];
            unset($item['department']);

            $item["role_info"] = implode(",",$userRole[$item["id"]]["name"]??[]);
            $item["role_ids"]  = $userRole[$item["id"]]["role_ids"]??"";
            $item["role_name"] = $userRole[$item["id"]]["name"]??'';
            //$item["department_name"] =  $departmentList[$item["department_id"]]??"";
            $item["subject_ids"] =  EmsUserSubjectMap::where('user_id',$item['id'])->pluck('subject_id');
            $item["device_group_id"] =  EmsDeviceGroupUserMap::where('user_id',$item['id'])->pluck('device_group_id');
        }

        return $this->success([
            'items' => $items,
            'total' => $paginator->total()
        ], 'ok');

    }

    /**
     * 添加用户
     * @throws BusinessException
     * @throws \Exception
     */
    public function insert(Request $request): \support\Response
    {
        $params = (new UserValidate())->isPost()->validate();
        $result = UserLogic::addUser($params,$msg);
        if (false === $result) {
            return $this->error($msg);
        }
        return $this->success([],"添加成功");
    }


    /**
     * 编辑用户
     * @param Request $request
     * @return Response
     * @throws \app\exception\BusinessException
     * @throws BusinessException
     */
    public function update(Request $request): \support\Response
    {
        if ($request->method()==='POST') {
            $params = (new UserValidate())->validate('EditUser');
            $id = $request->input('id');
            $departmentIds = $request->input('department_ids', []);
            $rs = [];
            foreach($departmentIds as $departmentId) {
                $rs[] = [
                    'department_id' => $departmentId,
                    'user_id' => $id,
                ];
            }
            try {
                Db::beginTransaction();
                // 先清空原有的
                EmsUserDepartment::where('user_id', $id)
                    ->delete();
                EmsUserDepartment::insert($rs);

                $result = UserLogic::editUser($params,$msg);

                Db::commit();
            } catch(\Throwable $e) {
                Db::rollback();
                throw $e;
            }

            if (false===$result) {
                return $this->error($msg);
            }
            return $this->success([]);
        } else {
            $this->withArr = ['department'];
            $this->callBack = function($item) {
                if ($item) {
                    $dep = $item['department'] ?? [];
                    $item['department_ids'] = $dep->pluck('id') ?? [];
                    unset($item['department']);
                }
                return $item;
            };
            return $this->info($request);
        }
    }


    /**
     * 删除用户
     * @throws \Exception
     */
    public function delete(Request $request): \support\Response
    {
        $params = (new UserValidate())->isPost()->validate('DelUser');
        $userIds = $params['id'];
        $result = UserLogic::deleteUsers($userIds,$msg);
        if (false===$result) {
            return $this->error($msg);
        }
        return $this->success([],"删除成功");
    }

    /**
     * 获取用户信息
     * @param Request $request
     * @return \support\Response
     * @throws \Exception
     */
    public function userInfo(Request $request): \support\Response
    {
        $userId = JwtToken::getCurrentId();
        $result = UserLogic::getUserInfo($userId,$msg);
        if (false===$result) {
            return $this->error($msg);
        }
        return $this->success($result);
    }


    /**
     * 修改密码
     * @param Request $request
     * @return Response
     * @throws \app\exception\BusinessException
     */
    public function changePw(Request $request): Response
    {
        $params = (new UserValidate())->isPost()->validate('ChangePw');
        $userId = JwtToken::getCurrentId();
        $result = UserLogic::changePw($userId,$params['password_old'],$params['password_new'],$msg);
        if (false===$result) {
            return $this->error($msg);
        }

        return $this->success([],"修改成功");
    }


    /**
     * 获取信用分记录
     * @param Request $request
     * @return \support\Response
     */
    public function getUserCreditScoreLog(Request $request)
    {
        $userId = $request->post("user_id");
        $list = EmsUserCreditScoreLog::where("user_id",$userId)->get();
        return $this->success($list);
    }

    /**
     * 预约设置
     * @throws \Throwable
     */
    public function setDeviceAppoint(Request $request)
    {
        $params = $request->all();
        $userId = $params['user_id'];
        $deviceGroupIds = $params['device_group_id'];
        $subjectIds = $params['subject_ids'];
        Db::beginTransaction();
        try {
            $creditScore = EmsUser::where('id',$userId)->first(['credit_score'])->toArray();
            $creditScore = $creditScore['credit_score'];
            $userInfo = inputFilter($params,EmsUser::class);
            EmsUser::where('id',$userId)->update($userInfo);
            //用户授权设备分组
            if (!empty($deviceGroupIds)) {
                EmsDeviceGroupUserMap::where('user_id',$userId)->delete();
                foreach ($deviceGroupIds as $item) {
                    EmsDeviceGroupUserMap::insert([
                        "device_group_id" => $item,
                        "user_id" => $userId,
                    ]);
                }
            }else{
                EmsDeviceGroupUserMap::where('user_id',$userId)->delete();
                $deviceGroup = EmsDeviceGroup::get();
                $deviceGroup = collect($deviceGroup)->toArray();
                foreach ($deviceGroup as $item) {
                    EmsDeviceGroupUserMap::insert([
                        "device_group_id" => $item['id'],
                        "user_id" => $userId,
                    ]);
                }
            }


            //用户关联课题
            EmsUserSubjectMap::where('user_id',$userId)->delete();
            foreach($subjectIds as $item){
                EmsUserSubjectMap::insert([
                    "user_id"    => $userId,
                    "subject_id" => $item
                ]);
            }

            $score = $params['credit_score']-$creditScore;
            if ($score>0) {
                $score = "+" . $score;
            }
            //记录信用分变化
            UserLogic::userCreditScoreLog($params['user_id'],$creditScore,$score,"用户预约设置");
            Db::commit();
        }catch (\Throwable $throwable) {
            Db::rollBack();
            throw $throwable;
        }

        return $this->success([]);
    }


    /**
     * 审核注册用户
     * @param Request $request
     * @return \support\Response
     * @throws \plugin\eagleadmin\app\exception\BusinessException
     * @throws \Exception
     */
    public function toExamine(Request $request): \support\Response
    {
        $all = $request->all();
        try {
            Db::beginTransaction();
            $userInfo = EmsUser::where('id',$all['id'])->first();
            if (!$userInfo) {
                return $this->error('用户不存在');
            }

            foreach ($all['id'] as $userId) {
                if ($userId==self::SUPER_USER_ID)
                    continue;

                EmsUser::where('id',$userId)->update([
                    'is_audit' => $all['is_audit'],
                    'status' => 1
                ]);

                $roleId = EmsRole::where('role_val',self::NORMAL_USER)->value('id');
                EmsUserRole::where('user_id',$userId)->delete();
                EmsUserRole::insert([
                    'user_id' => $userId,
                    'role_id' => $roleId ?? 0,
                ]);
            }


            Db::commit();
        }catch (\Exception $e) {
            Db::rollBack();
            throw $e;
        }

        return $this->success([]);

    }
}

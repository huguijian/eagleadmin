<?php
use Webman\Route;
Route::group('/core', function () {
    Route::any('/admin/get-captcha', [plugin\eagleadmin\app\controller\AdminController::class, 'getCaptcha']);
    Route::any('/admin/login', [plugin\eagleadmin\app\controller\AdminController::class, 'login']);
    Route::any('/auth/user/login-info', [plugin\eagleadmin\app\controller\auth\UserController::class, 'loginInfo']);

    //操作日志
    Route::any('/monitor/log/sysLog', [plugin\eagleadmin\app\controller\monitor\LogController::class, 'sysLog']);
    Route::any('/monitor/log/deleteOperLog', [plugin\eagleadmin\app\controller\monitor\LogController::class, 'deleteOperLog']);

    //岗位管理
    Route::any('/auth/post/select', [plugin\eagleadmin\app\controller\auth\PostController::class, 'select']);
    Route::any('/auth/post/recycle', [plugin\eagleadmin\app\controller\auth\PostController::class,'recycle']);
    Route::any('/auth/post/delete', [plugin\eagleadmin\app\controller\auth\PostController::class,'delete']);
    Route::any('/auth/post/insert', [plugin\eagleadmin\app\controller\auth\PostController::class,'insert']);
    Route::any('/auth/post/recovery', [plugin\eagleadmin\app\controller\auth\PostController::class,'recovery']);
    Route::any('/auth/post/update', [plugin\eagleadmin\app\controller\auth\PostController::class,'update']);
    Route::any('/auth/post/realDestroy', [plugin\eagleadmin\app\controller\auth\PostController::class,'realDestroy']);

    //角色管理
    Route::any('/auth/role/select', [plugin\eagleadmin\app\controller\auth\RoleController::class,'select']);
    Route::any('/auth/role/recycle', [plugin\eagleadmin\app\controller\auth\RoleController::class,'recycle']);
    Route::any('/auth/role/delete', [plugin\eagleadmin\app\controller\auth\RoleController::class,'delete']);
    Route::any('/auth/role/insert', [plugin\eagleadmin\app\controller\auth\RoleController::class,'insert']);
    Route::any('/auth/role/recovery', [plugin\eagleadmin\app\controller\auth\RoleController::class,'recovery']);
    Route::any('/auth/role/update', [plugin\eagleadmin\app\controller\auth\RoleController::class,'update']);
    Route::any('/auth/role/realDestroy', [plugin\eagleadmin\app\controller\auth\RoleController::class,'realDestroy']);
    Route::any('/auth/role/get-menu-by-role', [plugin\eagleadmin\app\controller\auth\RoleController::class,'getMenuByRole']);
    Route::any('/auth/role/update-menu-permission', [plugin\eagleadmin\app\controller\auth\RoleController::class,'updateMenuPermission']);
    Route::any('/auth/role/get-dept-by-role', [plugin\eagleadmin\app\controller\auth\RoleController::class,'getDeptByRole']);
    Route::any('/auth/role/update-data-permission', [plugin\eagleadmin\app\controller\auth\RoleController::class,'updateDataPermission']);

    //用户管理
    Route::any('/auth/user/select', [plugin\eagleadmin\app\controller\auth\UserController::class,'select']);
    Route::any('/auth/user/recycle', [plugin\eagleadmin\app\controller\auth\UserController::class,'recycle']);
    Route::any('/auth/user/delete', [plugin\eagleadmin\app\controller\auth\UserController::class,'delete']);
    Route::any('/auth/user/insert', [plugin\eagleadmin\app\controller\auth\UserController::class,'insert']);
    Route::any('/auth/user/recovery', [plugin\eagleadmin\app\controller\auth\UserController::class,'recovery']);
    Route::any('/auth/user/update', [plugin\eagleadmin\app\controller\auth\UserController::class,'update']);
    Route::any('/auth/user/realDestroy', [plugin\eagleadmin\app\controller\auth\UserController::class,'realDestroy']);
    Route::any('/auth/user/init-password', [plugin\eagleadmin\app\controller\auth\UserController::class,'initPassword']);
    Route::any('/auth/user/modifypassword', [plugin\eagleadmin\app\controller\auth\UserController::class,'modifyPassword']);
    Route::any('/auth/user/change-status', [plugin\eagleadmin\app\controller\auth\UserController::class,'changeStatus']);
    Route::any('/auth/user/getUserInfoByIds', [plugin\eagleadmin\app\controller\auth\UserController::class,'getUserInfoByIds']);
    Route::any('/auth/user/savepersonal', [plugin\eagleadmin\app\controller\auth\UserController::class,'savePersonal']);

    //附件管理
    Route::any('/data/attachment/select', [plugin\eagleadmin\app\controller\data\AttachmentController::class,'select']);
    Route::any('/data/attachment/delete', [plugin\eagleadmin\app\controller\data\AttachmentController::class,'delete']);
    Route::any('/data/attachment/downloadById', [plugin\eagleadmin\app\controller\data\AttachmentController::class,'downloadById']);

    //部门管理
    Route::any('/auth/dept/select', [plugin\eagleadmin\app\controller\auth\DeptController::class,'select']);
    Route::any('/auth/dept/recycle', [plugin\eagleadmin\app\controller\auth\DeptController::class,'recycle']);
    Route::any('/auth/dept/insert', [plugin\eagleadmin\app\controller\auth\DeptController::class,'insert']);
    Route::any('/auth/dept/recovery', [plugin\eagleadmin\app\controller\auth\DeptController::class,'recovery']);
    Route::any('/auth/dept/update', [plugin\eagleadmin\app\controller\auth\DeptController::class,'update']);
    Route::any('/auth/dept/realDestroy', [plugin\eagleadmin\app\controller\auth\DeptController::class,'realDestroy']);
    Route::any('/auth/dept/leaders', [plugin\eagleadmin\app\controller\auth\DeptController::class,'leaders']);
    Route::any('/auth/dept/delLeader', [plugin\eagleadmin\app\controller\auth\DeptController::class,'delLeader']);
    Route::any('/auth/dept/addLeader', [plugin\eagleadmin\app\controller\auth\DeptController::class,'addLeader']);
    Route::any('/auth/dept/delete', [plugin\eagleadmin\app\controller\auth\DeptController::class,'delete']);
    Route::any('/auth/dept/real-destroy', [plugin\eagleadmin\app\controller\auth\DeptController::class,'realDestroy']);

    //字典管理
    Route::any('/data/dict/select', [plugin\eagleadmin\app\controller\data\DictController::class,'select']);
    Route::any('/data/dict/delete', [plugin\eagleadmin\app\controller\data\DictController::class,'delete']);
    Route::any('/data/dict/insert', [plugin\eagleadmin\app\controller\data\DictController::class,'insert']);
    Route::any('/data/dict/update', [plugin\eagleadmin\app\controller\data\DictController::class,'update']);
    Route::any('/data/dict/changeStatus', [plugin\eagleadmin\app\controller\data\DictController::class,'changeStatus']);
    Route::any('/data/dictCategory/select', [plugin\eagleadmin\app\controller\data\DictCategoryController::class,'select']);
    Route::any('/data/dictCategory/insert', [plugin\eagleadmin\app\controller\data\DictCategoryController::class,'insert']);
    Route::any('/data/dict-category/data', [plugin\eagleadmin\app\controller\data\DictCategoryController::class,'data']);
    Route::any('/data/dictCategory/update', [plugin\eagleadmin\app\controller\data\DictCategoryController::class,'update']);
    Route::any('/data/dictCategory/changeStatus', [plugin\eagleadmin\app\controller\data\DictCategoryController::class,'changeStatus']);
    Route::any('/data/dict-category/dict-all', [plugin\eagleadmin\app\controller\data\DictCategoryController::class,'dictAll']);
    Route::any('/data/dictCategory/destroy', [plugin\eagleadmin\app\controller\data\DictCategoryController::class,'destroy']);
    Route::any('/data/dictCategory/recycle', [plugin\eagleadmin\app\controller\data\DictCategoryController::class,'recycle']);
    Route::any('/data/dictCategory/realDestroy', [plugin\eagleadmin\app\controller\data\DictCategoryController::class,'realDestroy']);
    Route::any('/data/dictCategory/recovery', [plugin\eagleadmin\app\controller\data\DictCategoryController::class,'recovery']);

    //登录日志
    Route::any('/monitor/log/loginLog', [plugin\eagleadmin\app\controller\monitor\LogController::class,'loginLog']);
    Route::any('/monitor/log/login-log', [plugin\eagleadmin\app\controller\monitor\LogController::class,'loginLog']);
    Route::any('/monitor/log/sys-log', [plugin\eagleadmin\app\controller\monitor\LogController::class,'sysLog']);
    Route::any('/monitor/log/deleteLoginLog', [plugin\eagleadmin\app\controller\monitor\LogController::class, 'deleteLoginLog']);
    //菜单管理
    Route::any('/auth/menu/index', [plugin\eagleadmin\app\controller\auth\MenuController::class,'index']);
    Route::any('/auth/menu/delete', [plugin\eagleadmin\app\controller\auth\MenuController::class,'delete']);
    Route::any('/auth/menu/insert', [plugin\eagleadmin\app\controller\auth\MenuController::class,'insert']);
    Route::any('/auth/menu/update', [plugin\eagleadmin\app\controller\auth\MenuController::class,'update']);
    Route::any('/auth/menu/changeStatus', [plugin\eagleadmin\app\controller\auth\MenuController::class,'changeStatus']);
    Route::any('/auth/menu/recycle', [plugin\eagleadmin\app\controller\auth\MenuController::class,'recycle']);
    Route::any('/auth/menu/recovery', [plugin\eagleadmin\app\controller\auth\MenuController::class,'recovery']);
    Route::any('/auth/menu/realDestroy', [plugin\eagleadmin\app\controller\auth\MenuController::class,'realDestroy']);
    //配置管理
    Route::any('/config/config/select', [plugin\eagleadmin\app\controller\config\ConfigController::class,'select']);
    Route::any('/config/config/delete', [plugin\eagleadmin\app\controller\config\ConfigController::class,'delete']);
    Route::any('/config/config/insert', [plugin\eagleadmin\app\controller\config\ConfigController::class,'insert']);
    Route::any('/config/config/update', [plugin\eagleadmin\app\controller\config\ConfigController::class,'update']);
    Route::any('/config/config/batchUpdate', [plugin\eagleadmin\app\controller\config\ConfigController::class,'batchUpdate']);
    Route::any('/config/config/clearAllCache', [plugin\eagleadmin\app\controller\config\ConfigController::class,'clearAllCache']);
    Route::any('/config/config-group/select', [plugin\eagleadmin\app\controller\config\ConfigGroupController::class,'select']);
    Route::any('/config/config-group/update', [plugin\eagleadmin\app\controller\config\ConfigGroupController::class,'update']);
    Route::any('/config/config-group/insert', [plugin\eagleadmin\app\controller\config\ConfigGroupController::class,'insert']);
    Route::any('/config/config-group/delete', [plugin\eagleadmin\app\controller\config\ConfigGroupController::class,'delete']);
   //定时任务
   Route::any('/tool/crontab/select', [plugin\eagleadmin\app\controller\tool\CrontabController::class,'select']);
   Route::any('/tool/crontab/update', [plugin\eagleadmin\app\controller\tool\CrontabController::class,'update']);
   Route::any('/tool/crontab/save', [plugin\eagleadmin\app\controller\tool\CrontabController::class,'save']);
   Route::any('/tool/crontab/changeStatus', [plugin\eagleadmin\app\controller\tool\CrontabController::class,'changeStatus']);
   Route::any('/tool/crontab/logPageList', [plugin\eagleadmin\app\controller\tool\CrontabController::class,'logPageList']);
   Route::any('/tool/crontab/deleteCrontabLog', [plugin\eagleadmin\app\controller\tool\CrontabController::class,'deleteCrontabLog']);
   Route::any('/tool/crontab/destroy', [plugin\eagleadmin\app\controller\tool\CrontabController::class,'destroy']);
   Route::any('/tool/crontab/run', [plugin\eagleadmin\app\controller\tool\CrontabController::class,'run']);

   //文件上传
   Route::any('/common/upload', [plugin\eagleadmin\app\controller\CommonController::class,'upload']);

});
Route::disableDefaultRoute('eagleadmin');
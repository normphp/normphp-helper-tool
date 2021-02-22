<?php

namespace normphp\normphpHelperTool\controller;

use normphp\normphpHelperTool\service\NormPhpCliDriveService;
use normphp\staging\Controller;
use normphp\staging\Request;

/**
 * Class NormPhpHelperTool
 * @package normphp\cloudStorage\controller
 */
class BasicsNormPhpHelperTool extends Controller
{
    /**
     * 基础控制器信息
     */
    const CONTROLLER_INFO = [
        'User'=>'pizepei',
        'title'=>'normphp命令行脚手架控制器',//控制器标题
        'baseAuth'=>'UserAuth:public',//基础权限继承（加命名空间的类名称）
        'namespace'=>'normphp_helper_tool',//门面控制器命名空间
        'basePath'=>'/normphp-helper-tool/',//基础路由
    ];

    /**
     * @param \normphp\staging\Request $Request
     * @return string [cli]
     * @title  入口
     * @router cli cli
     */
    public function groupList(Request $Request)
    {
        (new NormPhpCliDriveService($this->app))->cliEntrance();
    }


}
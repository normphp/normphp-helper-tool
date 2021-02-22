<?php
namespace normphp\normphpHelperTool\service;

use normphp\normphpHelperTool\service\php\PhpDownloadService;
use normphp\normphpHelperTool\service\php\PhpUnitService;
use normphp\staging\App;

/**
 * 命令行驱动
 * Class NormPhpCliDriveService
 * @package normphp\normphpHelperTool\service
 */
class NormPhpCliDriveService
{
    /**
     * 当前脚手架依赖的php版本
     */
    const RELY_PHP_VERSION = '8.0';
    /**
     * 当前版本
     */
    const VERSIONS = '0.0.01';
    /**
     * 获取并版本号
     */
    const NORMPHP_VERSIONS = '-v';
    /**
     * 获取当前脚手架信息
     */
    const NORMPHP_INFO = '-i';
    /**
     * 默认帮助信息
     */
    const NORMPHP_HELP = '帮助信息'.PHP_EOL
    .'  -v 查看当前normphp-helper版本'.PHP_EOL
    .'  -i 查看当前可用信息'.PHP_EOL
    ;
    /**
     * 命令行模板
     * [命令->[msg=>'命令解释声明','method'=>'命令对应的执行类的方法','content'=>'如果命令没有定义执行方法可直接输出字符串','help'=>['下级别命令']]]
     */
    const NORMPHP_CLI = [
        '-v'=>['msg'=>'查看当前normphp-helper版本','method'=>'','content'=>self::VERSIONS,'help'=>[]],
        '-i'=>['msg'=>'查看当前可用信息','method'=>'getInfo','content'=>'','help'=>[]],
        '-php'=>['msg'=>'php相关操作如下载不同版本php、切换php版本','method'=>'phpCli','content'=>'','help'=>[
            'run'=>['msg'=>'运行对应版本php： -php run [7.3|7.4|8.0] [对应的php命令|composer+命令|phpunit+命令]','content'=>''],
            'install'=>['msg'=>'安装对应版本php： -php install [7.3|7.4|8.0|all] [OFFICIAL|CLOUD] ','content'=>''],
            'update'=>['msg'=>'更新对应版本php： -php update [7.3|7.4|8.0|all] [OFFICIAL|CLOUD] ','content'=>''],
            'switch'=>['msg'=>'切换php环境变量到对应版本： -php switch [7.3|7.4|8.0]','content'=>''],
        ]],
    ];
    /**
     * NormPhpCliDriveService constructor.
     * @param App|string $APP
     */
    public function __construct(public App|string $APP =App::class)
    {
        /**
         * 注入 app容器
         */
        $this->APP = $APP;
    }

    /**
     * 拼接帮助信息
     * @param array[] $data
     * @param string $name
     * @return string
     */
    public function help($data=self::NORMPHP_CLI,string $name='')
    {
        $msg = ' '.$name.' 帮助信息 [x1|x2|x3] 代表参数可选择的范围'.PHP_EOL;
        $row = '   ';
        $urow = ' ';
        if (isset($data[$name]['msg'])){$msg .= $row.$name.$row.($data[$name]['msg']).PHP_EOL;return $msg;}
        foreach ($data as $key=>$value){
            $msg .= $row.$key.$row.($value['msg']??'').PHP_EOL;
            if (isset($value['help']) && !empty($value['help'])){
                foreach ($value['help'] as $k=>$v){
                    $msg .= $row.$row.$urow.$k.$row.($v['msg']??'').PHP_EOL;
                }
            }
        }
        return $msg;
    }
    /**
     * 命令行入口
     * @throws \Exception
     */
    public function cliEntrance()
    {
        /**
         * 判断是否是 支持的命令行参数
         */
        if (isset($this->APP->ARGV[3]) && isset(self::NORMPHP_CLI[$this->APP->ARGV[3]])){
            # 判断是否定义了method
            if (empty(self::NORMPHP_CLI[$this->APP->ARGV[3]]['method'])){
                # 没有定义method 使用content
                $this->output(self::NORMPHP_CLI[$this->APP->ARGV[3]]['content']??'');
            }else{
                $method = self::NORMPHP_CLI[$this->APP->ARGV[3]]['method'];
                $this->output($this->$method($this->APP->ARGV));
            }
        }else{
            # 不支持的命令行参数 自己输出帮助
            $this->output($this->help());
        }
    }

    /**
     * 获取脚手架信息
     * @param $ARGV
     * @return string
     */
    public function getInfo($ARGV):string
    {
        exec('composer -V',$composerRes);
        exec('phpunit --version',$phpunitRes);

        return '----------------normphp----------------'.PHP_EOL
              .'NORMPHP_VERSIONS:           '.self::VERSIONS.PHP_EOL
              .'NORMPHP_PHP_VERSIONS:       '.PHP_VERSION.PHP_EOL
              .'----------------composer----------------'.PHP_EOL
              .'COMPOSER_VERSIONS:          '.($composerRes[0]??'未安装').PHP_EOL
              .'PHPUNIT_VERSIONS:           '.($phpunitRes[0]??'未安装').PHP_EOL
              .'------------------PATH------------------'.PHP_EOL
              .'PHP_PATH:                   '.(dirname($this->APP->DOCUMENT_ROOT,1).DIRECTORY_SEPARATOR.'php'.DIRECTORY_SEPARATOR).PHP_EOL
              .'COMPOSER_PATH:              '.(dirname($this->APP->DOCUMENT_ROOT,1).DIRECTORY_SEPARATOR.'composer'.DIRECTORY_SEPARATOR).PHP_EOL
              .'PHPUNIT_PATH:               '.(dirname($this->APP->DOCUMENT_ROOT,1).DIRECTORY_SEPARATOR.'phpunit'.DIRECTORY_SEPARATOR).PHP_EOL
              .'NORMPHP_PATH:               '.($this->APP->DOCUMENT_ROOT??'').($ARGV[0]??'').PHP_EOL
            ;
    }

    /**
     * 标准输出
     * @param string $content 输出内容
     * @param bool $exit 是否结束
     */
    public function output(string $content,bool$exit=false)
    {
        echo $content;
    }

    /**
     * -php 命令行处理方法
     * @param $ARGV
     * @return string
     */
    public function phpCli($ARGV)
    {
        # 判断对应参数是否存在
        if (isset($ARGV[4]) && !empty($ARGV[4])){
            return match ($ARGV[4]) {
                'install' => $this->phpInstall($ARGV,false),
                'update' => $this->phpUpdate($ARGV),
                'switch' => $this->phpSwitch($ARGV),
                'run' => $this->phpRun($ARGV),
                'switch'    => $this->phpSwitch($ARGV),
                default=>$this->help(self::NORMPHP_CLI[$ARGV[3]]['help'],$ARGV[3]),
            };
        }else{
            return $this->help(self::NORMPHP_CLI[$ARGV[3]]['help'],$ARGV[3]);
        }
    }

    /**
     * 切换环境变量为对应的php版本
     * @param $ARGV
     */
    public function phpSwitch($ARGV)
    {
        if (!isset($ARGV[5]))return$this->help(self::NORMPHP_CLI[$ARGV[3]]['help'],$ARGV[4]);
        # 获取当前版本号
        exec('php -v', $res);
        # 准备需要切换的版本号php目录
        $phpFile = dirname($this->APP->DOCUMENT_ROOT,1).DIRECTORY_SEPARATOR.'php'.DIRECTORY_SEPARATOR.$ARGV[5].DIRECTORY_SEPARATOR.'x86'.DIRECTORY_SEPARATOR.'php.exe';
        $phpDir = dirname($this->APP->DOCUMENT_ROOT,1).DIRECTORY_SEPARATOR.'php'.DIRECTORY_SEPARATOR.$ARGV[5].DIRECTORY_SEPARATOR.'x86';
        if (!is_file($phpFile)){return '    PHP '.$ARGV[5].'不存在，请先安装！';}
        if (count($res) <3){
            echo '  当前PHP版本异常：直接强制切换到您需要的版本'.PHP_EOL;
        }else{
            preg_match('/PHP (.*?) \(cli\)/',implode(' ',$res),$pregRes);
            if (!isset($pregRes[1])){
                echo '  当前PHP版本异常：直接强制切换到您需要的版本'.PHP_EOL;
            }else{
                echo '  当前PHP版本'.$pregRes[1].'：强制切换到'.$ARGV[5].PHP_EOL;
            }
        }
        (new DriveServic())->setVariate('/\\\php[876\\\]/',$phpDir);
        return ' ********************************************************************************************'.PHP_EOL
              .' *                                                                                          *'.PHP_EOL
              .' *  请注意：切换系统变量后当前命令行窗口依然是原PHP版本，需要重新启动命令行窗口才生效！！   *'.PHP_EOL
              .' *                                                                                          *'.PHP_EOL
              .' ********************************************************************************************'.PHP_EOL
            ;
    }
    /**
     * php -m
     * @param $ARGV
     */
    public function phpRun($ARGV)
    {
        if (!isset($ARGV[5]) || empty($ARGV[5])  ){
            return $this->help(self::NORMPHP_CLI[$ARGV[3]]['help'],$ARGV[3]);
        }
        if (!isset($ARGV[6]) || empty($ARGV[6])){
            return $this->help(self::NORMPHP_CLI[$ARGV[3]]['help'],$ARGV[4]);
        }
        # 基础PHP根目录
        $phpRootPath = dirname($this->APP->DOCUMENT_ROOT,1).DIRECTORY_SEPARATOR.'php'.DIRECTORY_SEPARATOR;
        # 基础COMPOSER路径
        $composerRootPath = dirname($this->APP->DOCUMENT_ROOT,1).DIRECTORY_SEPARATOR.'composer'.DIRECTORY_SEPARATOR.'composer.phar';
        # 基础phpunit路径
        $phpunitRootPath = dirname($this->APP->DOCUMENT_ROOT,1).DIRECTORY_SEPARATOR.'phpunit'.DIRECTORY_SEPARATOR.'phpunit.phar';
        # 检查php.exe 是否存在
        $phpExePath = $phpRootPath.$ARGV[5].DIRECTORY_SEPARATOR.'x86'.DIRECTORY_SEPARATOR.'php.exe';
        if (!is_file($phpExePath)){
            return 'PHP:'.$ARGV[5].'不存在的，请先安装';
        }
        echo '*************************环境信息***********************************'.PHP_EOL;

        echo 'PHP'.$ARGV[5].':  '.$phpExePath.PHP_EOL;

        $cli = $ARGV;
        unset($ARGV[0],$ARGV[1],$ARGV[2],$ARGV[3],$ARGV[4],$ARGV[5]);
        if ($ARGV[6] ==='composer'){
            unset($ARGV[6]);
            $phpExePath .=' '.$composerRootPath;
            echo 'Composer: '.$composerRootPath.PHP_EOL;

        }else if($ARGV[6] === 'phpunit'){
            unset($ARGV[6]);
            (new PhpUnitService(dirname($this->APP->DOCUMENT_ROOT,1)));
            $phpExePath .=' '.$phpunitRootPath;
            echo 'phpunit: '.$phpunitRootPath.PHP_EOL;
        }
        $cli = $phpExePath.' '.implode(' ',$ARGV);

        echo 'CLI:     '.$cli.PHP_EOL;
        echo '*************************环境信息***********************************'.PHP_EOL;
        # 确定php版本
        exec($cli,$execRes);
        # 执行命令
        return implode(PHP_EOL,$execRes);

    }
    /**
     * 下载更新php
     * @param $ARGV 命令行参数
     * @return string
     */
    public function phpUpdate($ARGV,bool$update=true)
    {
        if (!isset($ARGV[5]) || empty($ARGV[5])){
            return $this->help(self::NORMPHP_CLI[$ARGV[3]]['help'],$ARGV[3]);
        }
        $versions = $ARGV[5];
        //[[OFFICIAL|CLOUD]
        if (!isset($ARGV[6]) || empty($ARGV[6])){
            $resource = 'OFFICIAL';
        }
        // [x86|x64]]
        if (!isset($ARGV[7]) || empty($ARGV[7])){
            $architecture = 'x86';
        }
        if ($versions ==='all'){
            foreach (PhpDownloadService::RESOURCE[$resource][$architecture] as $key=>$value){
                (new PhpDownloadService(dirname($this->APP->DOCUMENT_ROOT,1)))->download(versions:$key,architecture:$architecture,resource:$resource);
            }
            return '';
        }
        /**
         * 如果版本不存在
         */
        if (empty(PhpDownloadService::RESOURCE[$resource][$architecture][$versions]??'')){ return $this->help(self::NORMPHP_CLI[$ARGV[3]]['help'],$ARGV[3]);};
        (new PhpDownloadService(dirname($this->APP->DOCUMENT_ROOT,1)))->download(versions:$versions,architecture:$architecture,resource:$resource);
        return '';
    }

    /**
     * 下载更新php
     * @param $ARGV 命令行参数
     * @param bool $update 是否强制更新下载
     * @return string
     */
    public function phpInstall($ARGV,bool$update=false)
    {
        if (!isset($ARGV[5]) || empty($ARGV[5])){
            return $this->help(self::NORMPHP_CLI[$ARGV[3]]['help'],$ARGV[3]);
        }
        $versions = $ARGV[5];
        //[[OFFICIAL|CLOUD]
        if (!isset($ARGV[6]) || empty($ARGV[6])){
            $resource = 'OFFICIAL';
        }
        // [x86|x64]]
        if (!isset($ARGV[7]) || empty($ARGV[7])){
            $architecture = 'x86';
        }
        if ($versions ==='all') {
            foreach (PhpDownloadService::RESOURCE[$resource][$architecture] as $key=>$value){
                $res =  (new PhpDownloadService(dirname($this->APP->DOCUMENT_ROOT,1)))->download(versions:$key,architecture:$architecture,resource:$resource,update:$update)->installPHP();
            }
            return '';
        }
            /**
         * 如果版本不存在
         */
        if (empty(PhpDownloadService::RESOURCE[$resource][$architecture][$versions]??'')){ return $this->help(self::NORMPHP_CLI[$ARGV[3]]['help'],$ARGV[3]);};
        $res =  (new PhpDownloadService(dirname($this->APP->DOCUMENT_ROOT,1)))->download(versions:$versions,architecture:$architecture,resource:$resource,update:$update)->installPHP();
        return '';
    }

}

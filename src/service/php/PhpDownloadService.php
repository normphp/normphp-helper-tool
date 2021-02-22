<?php
namespace normphp\normphpHelperTool\service\php;
use normphp\normphpHelperTool\service\NormPhpCliDriveService;
use normphp\normphpHelperTool\service\pmgressbar\CliProgressBar;

/**
 * 下载对应php版本
 * 直接下载
 * 返回下载连接
 * Class PhpDownload
 */
class PhpDownloadService
{

    /**
     *官方x86版本8.0.0  7.3  7.2   7.4
     */
    const OFFICIAL_PHP_X86_DOWNLOAD_URL = [
        '8.0'=>['url'=>'https://windows.php.net/downloads/releases/php-8.0.0-nts-Win32-vs16-x86.zip','versions'=>'8.0.0',
            'pecl'=>[
            ]
        ],
        '7.4'=>['url'=>'https://windows.php.net/downloads/releases/php-7.4.13-nts-Win32-vc15-x86.zip','versions'=>'7.4.13',
            'pecl'=>[
                'redis'=>['url'=>'https://windows.php.net/downloads/pecl/releases/redis/5.3.2/php_redis-5.3.2-7.4-nts-vc15-x86.zip'],
                'ssh2'=>['url'=>'https://windows.php.net/downloads/pecl/releases/ssh2/1.2/php_ssh2-1.2-7.4-nts-vc15-x86.zip'],
                'xlswriter'=>['url'=>'https://windows.php.net/downloads/pecl/releases/xlswriter/1.3.6/php_xlswriter-1.3.6-7.4-nts-vc15-x86.zip'],
            ]
        ],
        '7.3'=>['url'=>'https://windows.php.net/downloads/releases/php-7.3.25-nts-Win32-VC15-x86.zip','versions'=>'7.3.25',
            'pecl'=>[
                'redis'=>['url'=>'https://windows.php.net/downloads/pecl/releases/redis/5.3.2/php_redis-5.3.2-7.3-nts-vc15-x86.zip'],
                'ssh2'=>['url'=>'https://windows.php.net/downloads/pecl/releases/ssh2/1.2/php_ssh2-1.2-7.3-nts-vc15-x86.zip'],
                'xlswriter'=>['url'=>'https://windows.php.net/downloads/pecl/releases/xlswriter/1.3.6/php_xlswriter-1.3.6-7.3-nts-vc15-x86.zip'],
            ]],
        '7.2'=>['url'=>'https://windows.php.net/downloads/releases/php-7.2.34-Win32-VC15-x86.zip','versions'=>'7.2.34',
            'pecl'=>[
                'redis'=>['url'=>'https://windows.php.net/downloads/pecl/releases/redis/5.3.2/php_redis-5.3.2-7.2-nts-vc15-x86.zip'],
                'ssh2'=>['url'=>'https://windows.php.net/downloads/pecl/releases/ssh2/1.2/php_ssh2-1.2-7.2-nts-vc15-x86.zip'],
                'xlswriter'=>['url'=>'https://windows.php.net/downloads/pecl/releases/xlswriter/1.3.6/php_xlswriter-1.3.6-7.2-nts-vc15-x86.zip'],
            ]],
    ];
    /**
     *官方x64版本8.0.0  7.3  7.2   7.4
     */
    const OFFICIAL_PHP_X64_DOWNLOAD_URL = [

    ];
    /**
     * normphp官方api 可获取国内下载地址列表
     */
    const PHP_CLOUD_DOWNLOAD_API ='';
    /**
     * 资源类型
     */
    const RESOURCE = [
        'OFFICIAL'=>['x86'=>self::OFFICIAL_PHP_X86_DOWNLOAD_URL,'x64'=>self::OFFICIAL_PHP_X64_DOWNLOAD_URL],
        'CLOUD'=>self::PHP_CLOUD_DOWNLOAD_API,
    ];
    /**
     * 当前脚本根目录
     * @var string
     */
    public $rootDir = '';
    /**
     * 默认下载保存的具体目录
     * @var string
     */
    public $uploadsDir = '\\uploads';
    /**
     * 当前下载保存的目录
     * @var string
     */
    public $savePath = '';
    /**
     * 扩展保存目录
     * @var string
     */
    public $savePeclPath = '';
    /**
     * 当前下载地址信息
     * @var array
     */
    public $downloadInfo = [];
    /**
     * PhpDownload constructor.
     * @param string $dir 当前脚本根目录
     */
    public function __construct(string $dir)
    {
        $this->rootDir = $dir;
    }

    /**
     * @var cpu
     */
    public $architecture;
    /**
     * @var php 版本
     */
    public $versions;
    /**
     * @var 下载资源
     */
    public $resource;
    /**
     * 下载对应版本PHP
     * @param $versions 7.2|7.3|7.4
     * @param string $architecture x86|x64
     * @param string $resource OFFICIAL|CLOUD
     * @throws Exception
     */
    public function download($versions ,string $architecture='x86',$resource='OFFICIAL',bool$update=true)
    {
        $this->architecture = $architecture;
        $this->versions = $versions;
        $this->resource = $resource;

        # 准备文件保存地址
        $this->savePath = $this->rootDir.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR.$architecture.DIRECTORY_SEPARATOR;
        $fileName = $versions.'.zip';
        # 获取下载地址
        $this->downloadInfo = $this->getDownloadUrl($versions,$architecture,$resource);
        # 判断是否需要强制更新
        if (is_file($this->savePath.$fileName) && !$update){
            echo 'PHP'.$versions.'.zip['.$architecture.']已存在无需下载'.PHP_EOL;
            echo 'PATH'.$this->savePath.$fileName.PHP_EOL;
        }else{
            if ($this->downloadPhp(versions: $versions ,architecture:  $architecture,resource: $resource,savePath: $this->savePath,update: $update));
        }
        echo '开始判断下载php扩展'.PHP_EOL;
        $this->savePeclPath = $this->savePath.$versions.'_pecl'.DIRECTORY_SEPARATOR;
        # 判断下载php扩展
        if ($this->downloadPhpPecl(versions: $versions ,architecture:  $architecture,resource: $resource,savePath:$this->savePeclPath,update: $update));
        return $this;
    }

    /**
     * @param false $force 是否强制安装
     */
    public function installPHP($force=false)
    {
        echo '开始安装:PHP['.$this->architecture.']'.$this->versions.PHP_EOL;
        # php压缩包
        $phpFilePath = $this->savePath.$this->versions.'.zip';
        # 运行目录
        $phpFileUnzipPath = $this->rootDir.DIRECTORY_SEPARATOR.'php'.DIRECTORY_SEPARATOR.$this->versions.DIRECTORY_SEPARATOR.$this->architecture.DIRECTORY_SEPARATOR;
        # 临时目录
        $phpFileUnzipTmpPath = $this->rootDir.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR.$this->architecture.DIRECTORY_SEPARATOR.$this->versions.'_tmp'.DIRECTORY_SEPARATOR;

        if (is_file($phpFilePath)){
            echo '  对['.$phpFilePath.']'.'进行解压'.PHP_EOL;
            Helper()->file()->createDir($phpFileUnzipPath);
            Helper()->file()->createDir($phpFileUnzipTmpPath);
            if (Helper()->file()->unzip($phpFilePath,$phpFileUnzipTmpPath)){
                echo '  解压成功（临时目录）:'.$phpFileUnzipTmpPath.PHP_EOL;
            }else{
                echo '  解压失败:'.PHP_EOL;
            }
        }else{
            echo $this->savePath.$this->versions.'.zip'.'安装压缩包不存在'.PHP_EOL;
        }
        # 扩展压缩包
        # 压缩包
        $phpFileUnzipPeclPath = $this->rootDir.DIRECTORY_SEPARATOR.'php'.DIRECTORY_SEPARATOR.$this->versions.DIRECTORY_SEPARATOR.$this->architecture.DIRECTORY_SEPARATOR;
        $peclArray =[];
        if (isset($this->downloadInfo['pecl']) && !empty($this->downloadInfo['pecl'])){
            foreach ($this->downloadInfo['pecl'] as $key=>$value){
                $fileName = $key.'.zip';
                //扩展压缩包路径
                $savePeclPath = $this->savePeclPath.$fileName;
                //需要操作扩展的dll名称
                $dllName = 'php_'.$key.'.dll';
                if (is_file($savePeclPath)){
                    echo ' PHP'.$this->versions.'['.$this->architecture.']--['.$key.']扩展文件,已存在'.PHP_EOL;
                    echo ' 开始解压 PATH ['.$savePeclPath.']->'.$this->savePeclPath.$key.DIRECTORY_SEPARATOR.PHP_EOL;
                    if (Helper()->file()->unzip($savePeclPath,$this->savePeclPath.$key.DIRECTORY_SEPARATOR)){
                        echo '  解压成功->开始复制dll文件->'.$phpFileUnzipTmpPath.'ext'.DIRECTORY_SEPARATOR.$dllName.PHP_EOL;
                        copy($this->savePeclPath.$key.DIRECTORY_SEPARATOR.$dllName,$phpFileUnzipTmpPath.'ext'.DIRECTORY_SEPARATOR.$dllName);
                        $peclArray[] = 'php_'.$key.'.dll';
                    }else{
                        echo '  解压失败:'.PHP_EOL;
                    }
                }else{
                    echo ' PHP'.$this->versions.'['.$this->architecture.']--['.$key.']扩展文件,不存在'.PHP_EOL;
                    echo ' PATH ['.$savePeclPath.']'.PHP_EOL;
                    continue;
                }
            }
        }
        # 配置文件处理
        $phpIniPath = $phpFileUnzipTmpPath.DIRECTORY_SEPARATOR.'php.ini';
        (new PhpConfigService())->setIni(rootDir: $this->rootDir,versions: $this->versions,architecture: $this->architecture,peclArray: $peclArray??[],phpIniPath:$phpIniPath);
        if (NormPhpCliDriveService::RELY_PHP_VERSION === $this->versions) {
            echo '  当前脚手架依赖 PHP '.NormPhpCliDriveService::RELY_PHP_VERSION.' 无法直接升级/安装'.PHP_EOL
                .'  建议：'.PHP_EOL
                .'    1、手动复制目录'.$phpFileUnzipTmpPath.'  内容覆盖到-> '.$phpFileUnzipPeclPath.' 目录内'.PHP_EOL;
            return '';
        }
        echo '  正在从临时目录:'.$phpFileUnzipTmpPath.' 复制到->'.$phpFileUnzipPeclPath.PHP_EOL;

        Helper()->file()->copyDir($phpFileUnzipTmpPath,$phpFileUnzipPeclPath);
    }
    /**
     * 下载对应版本PHP
     * @param $versions 7.2|7.3|7.4
     * @param string $architecture x86|x64
     * @param string $resource OFFICIAL|CLOUD
     * @param string $savePath 保存地址
     * @param bool $update 是否强制更新
     */
    public function downloadPhp($versions ,string $architecture='x86',$resource='OFFICIAL',string $savePath='',bool$update=true):bool
    {
        ini_set('memory_limit','1024M');
        $fileName = $versions.'.zip';
        # 准备请求头
        $header =[
            'Accept-Language: zh-CN,zh;q=0.9,en;q=0.8',
            'Accept-Encoding: gzip, deflate, br',
            'user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/87.0.4280.88 Safari/537.36',
        ];
        # 进行下载
        echo ' 正在从'.$resource.'下载PHP'.$versions.'['.$architecture.']['.$this->downloadInfo['versions'].']['.$this->downloadInfo['url'].']'.PHP_EOL
            .' 过程缓慢(15分钟左右)如超过15分钟下载进度依然是卡住可尝试按回车键（不要强制结束）'.PHP_EOL
        ;
        if ($path = Helper()->file()->downloadFile(url: $this->downloadInfo['url'], fileName: $fileName, savePath:$savePath, header: $header)){
            echo PHP_EOL.'从'.$resource.'下载PHP'.$versions.'['.$architecture.']['.$this->downloadInfo['versions'].'] 成功'.PHP_EOL.' ['.$this->downloadInfo['url'].']'.PHP_EOL.$path.PHP_EOL;
            return true;
        }else{
            echo PHP_EOL.'从'.$resource.'下载PHP'.$versions.'['.$architecture.']['.$this->downloadInfo['versions'].'] 失败 ['.$this->downloadInfo['url'].']'.PHP_EOL;
            return false;

        }
    }

    /**
     * 下载对应版本PHP 扩展
     * @param $versions 7.2|7.3|7.4
     * @param string $architecture x86|x64
     * @param string $resource OFFICIAL|CLOUD
     * @param string $savePath 保存地址
     * @param bool $update 是否强制更新
     */
    public function downloadPhpPecl($versions ,string $architecture='x86',$resource='OFFICIAL',string $savePath='',bool$update=true):bool
    {
        ini_set('memory_limit','1024M');
        # 准备请求头
        $header =[
            'Accept-Language: zh-CN,zh;q=0.9,en;q=0.8',
            'user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/87.0.4280.88 Safari/537.36',
        ];
        # 判断是否需要下载
        if (isset($this->downloadInfo['pecl']) && !empty($this->downloadInfo['pecl'])){

            foreach ($this->downloadInfo['pecl'] as $key=>$value){
                $fileName = $key.'.zip';
                if (is_file($savePath.$fileName) && !$update){
                    echo ' PHP'.$versions.'['.$architecture.']--['.$key.']扩展文件,已存在不需要下载'.PHP_EOL;
                    echo ' PATH ['.$savePath.$fileName.']'.PHP_EOL;
                    continue;
                }
                # 进行下载
                echo PHP_EOL.' 正在从'.$resource.'下载PHP'.$versions.'['.$architecture.']--['.$key.']扩展文件'.PHP_EOL
                    .' 过程缓慢(5分钟左右)如超过5分钟下载进度依然是卡住可尝试按回车键（不要强制结束）'.PHP_EOL;
                if ($path = Helper()->file()->downloadFile(url: $value['url'], fileName: $fileName, savePath:$savePath, header: $header)){
                    echo PHP_EOL.'从'.$resource.'下载PHP 扩展'.'['.$key.']'.' 成功'.PHP_EOL.' ['.$value['url'].']'.PHP_EOL.$path.PHP_EOL;
                }else{
                    echo PHP_EOL.'从'.$resource.'下载PHP 扩展'.'['.$key.']'.' 失败'.PHP_EOL.' ['.$value['url'].']'.PHP_EOL;
                }
            }
        }
        return true;
    }
    /**
     * 获取下载地址
     * @param $versions 7.2|7.3|7.4
     * @param string $architecture x86|x64
     * @param string $resource OFFICIAL|CLOUD
     * @return array
     * @throws Exception
     */
    public function getDownloadUrl($versions ,string $architecture='x86',$resource='OFFICIAL'):array
    {
        if ($resource === 'OFFICIAL'){
            if (!isset(self::RESOURCE[$resource][$architecture][$versions])){
                throw new Exception($resource.'下['.$architecture.']PHP '.$versions.' 不存在！');
            }
            return self::RESOURCE[$resource][$architecture][$versions];
        }else if ($resource === 'CLOUD'){
            throw new Exception($resource.'资源类型正在完善中！！！');
        }else{
            throw new Exception($resource.'资源类型不存在');
        }
    }
}
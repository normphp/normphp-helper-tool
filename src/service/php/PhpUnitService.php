<?php


namespace normphp\normphpHelperTool\service\php;

use normphp\normphpHelperTool\service\DriveServic;

/**
 * php 单元测试
 * Class PhpunitService
 * @package normphp\normphpHelperTool\service\php
 */
class PhpUnitService
{
    const DOWNLOAD_URL = 'https://phar.phpunit.de/phpunit.phar';
    /**
     * PhpunitService constructor.
     */
    public function __construct(public string $dir,public string $phpUnitPath ='',public string $phpUnitDir = '')
    {

        $this->dir =$dir;
        $this->phpUnitPath = $this->dir.DIRECTORY_SEPARATOR.'phpunit'.DIRECTORY_SEPARATOR.'phpunit.phar';
        $this->phpUnitDir = $this->dir.DIRECTORY_SEPARATOR.'phpunit';
        if (!is_file($this->phpUnitPath)){
            $this->downloadPhpunit();
            echo '  '.$this->phpUnitPath.PHP_EOL;
            echo ' 注册环境变量'.PHP_EOL;$this->registerPath();echo ' 完成注册环境变量->请重新执行命令行'.PHP_EOL;
            //exec('phpunit  --version',$phpunitRes);if (!isset($phpunitRes[0])){echo ' 注册环境变量'.PHP_EOL;$this->registerPath();echo ' 完成注册环境变量->请重新执行命令行'.PHP_EOL;}
        }
    }

    /**
     * 下载
     */
    public function downloadPhpunit()
    {
        $header =[
            'Accept-Language: zh-CN,zh;q=0.9,en;q=0.8',
            'Accept-Encoding: gzip, deflate, br',
            'user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/87.0.4280.88 Safari/537.36',
        ];
        if ($path = Helper()->file()->downloadFile(url: self::DOWNLOAD_URL, fileName: 'phpunit.phar', savePath:$this->dir.DIRECTORY_SEPARATOR.'phpunit'.DIRECTORY_SEPARATOR, header: $header)){
            echo PHP_EOL.'从'.self::DOWNLOAD_URL.' phpunit.phar 成功'.PHP_EOL.$path.PHP_EOL;
        }else{
            echo PHP_EOL.'从'.self::DOWNLOAD_URL.' phpunit.phar 失败'.PHP_EOL.PHP_EOL;
        }
    }
    /**
     * 注册环境变量
     */
    public function registerPath()
    {
        (new DriveServic())->setVariate('/\\\phpunit[\\\]{0,1}/',$this->phpUnitDir);
    }
}
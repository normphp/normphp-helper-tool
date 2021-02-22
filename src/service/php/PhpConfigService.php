<?php
namespace normphp\normphpHelperTool\service\php;
use Composer\Package\Loader\ValidatingArrayLoader;

/**
 * 不同版本php初始化配置
 * Class PhpConfig
 */
class PhpConfigService
{
    /**
     * 同一配置
     */
    const CONFIG_TPL = [
            'post_max_size'=>'100M',
            'memory_limit'=>'1500M',
            'extension_dir' => 'extension_dir = "ext"',
    ];
    /**
     * 不同版本自定义配置
     */
    const INI_TPL=[
        '8.0'=>['config'=>[], 'extension'=>[]],
        '7.4'=>['config'=>[], 'extension'=>[]],
        '7.3'=>['config'=>[], 'extension'=>[]],
        '7.2'=>['config'=>[], 'extension'=>[]],
    ];

    /**
     * 获取创建的ini配置
     * @param string $rootDir php版本
     * @param string $versions php版本
     * @param array $peclArray
     * @throws \Exception
     */
    public function getIni(string $rootDir,string $versions,array $peclArray)
    {
        # 读取模板
        $pathTpl = __DIR__.DIRECTORY_SEPARATOR.'template'.DIRECTORY_SEPARATOR.'php.ini-development';
        echo '使用模板：'.$pathTpl.PHP_EOL;
        $tpl = file_get_contents($pathTpl);
        # 合并配置
        $config = array_merge(self::INI_TPL[$versions]['config'],self::CONFIG_TPL);
        Helper()->str()->str_replace($config,$tpl);
        # 对应pphp版本 扩展配置
        $extensionTplPath = $rootDir.DIRECTORY_SEPARATOR.'php'.DIRECTORY_SEPARATOR.$versions.DIRECTORY_SEPARATOR.'extension.ini';
        $extensionTpl = file_get_contents($extensionTplPath);
        # 读取扩展配置模板
        $extensionTpl .=(empty($peclArray)?'':'extension=').implode(PHP_EOL.'extension=',array_values($peclArray)).PHP_EOL;
        # 替换参数
        return preg_replace('/;%extension%(.*?);%extension%/s',PHP_EOL.';%extension%'.PHP_EOL.$extensionTpl.PHP_EOL.';%extension%'.PHP_EOL,$tpl);
        # 写入扩展参数
    }

    /**
     * 写入配置
     * @param string $rootDir
     * @param string $versions
     * @param string $architecture
     * @param array $peclArray
     * @param string $phpIniPath
     * @throws \Exception
     */
    public function setIni(string $rootDir,string $versions,string $architecture,array $peclArray,string $phpIniPath='')
    {
        $res = $this->getIni(rootDir: $rootDir,versions: $versions,peclArray: $peclArray);
        if ($phpIniPath===''){
            $phpIniPath = $rootDir.DIRECTORY_SEPARATOR.'php'.DIRECTORY_SEPARATOR.$versions.DIRECTORY_SEPARATOR.$architecture.DIRECTORY_SEPARATOR.'php.ini';
        }
        echo '写入配置文件：'.$phpIniPath.PHP_EOL.PHP_EOL;
        file_put_contents($phpIniPath,$res);
    }
}
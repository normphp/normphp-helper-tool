<?php


namespace normphp\normphpHelperTool\service;

/**
 * 驱动方法
 * Class DriveServic
 * @package normphp\normphpHelperTool\service
 */
class DriveServic
{
    /**
     * 切换php环境变量
     * @param $versionsPath
     */
    public function setPhpSwitchVariateMatch($versionsPath)
    {
        $VariateRes = $this->refreshVariateMatch();
        # 判断是否是第一次安装初始化是就写入第一次的环境变量备份
        foreach ($VariateRes as $key=>$value)
        {
            $unset=false;
            # 如 检查是否 有php 关键字  '/\\\php[876\\\]/'
            preg_match('/\\\php[876\\\]/',$value,$phpRes);
            if (!empty($phpRes)){
                $quondam[] = $value;
                $unset=true;
            }
            # 删除 需要替换掉的
            if ($unset){unset($VariateRes[$key]);}
            # 删除重复的
            if (in_array($value,$arrayData??[])){unset($VariateRes[$key]);}
            # 删除空的
            if (empty($value)){unset($VariateRes[$key]);}
            $arrayData[] = $value;
        }
        # 拼接写入
        echo $this->eol;
        echo '  操作环境变量';
        $pathRes = implode(';',array_merge($VariateRes,[$versionsPath]));
        echo '  被替换：'.PHP_EOL.'     '.implode("\r\n     ",$quondam).PHP_EOL;
        echo '  替换成：'.PHP_EOL.'     '.$versionsPath.PHP_EOL;
        exec('setx PATH "'.$pathRes.';"');
        echo '  设置后当前环境变量：'.PHP_EOL.'   '.implode("\r\n   ",array_values($this->refreshVariateMatch())).PHP_EOL;
    }

    /**
     * 设置环境变量
     * @param string $path
     */
    public function setVariate(string $pattern,string $path)
    {
        $VariateRes = $this->refreshVariateMatch();
        # 判断是否是第一次安装初始化是就写入第一次的环境变量备份
        foreach ($VariateRes as $key=>$value)
        {
            $unset=false;
            # 检查如是否 有php 关键字 '/\\\php[876\\\]/'
            preg_match($pattern,$value,$phpRes);
            if (!empty($phpRes)){
                $quondam[] = $value;
                $unset=true;
            }
            # 删除 需要替换掉的
            if ($unset){unset($VariateRes[$key]);}
            # 删除重复的
            if (in_array($value,$arrayData??[])){unset($VariateRes[$key]);}
            # 删除空的
            if (empty($value)){unset($VariateRes[$key]);}
            $arrayData[] = $value;
        }
        # 拼接写入
        echo $this->eol;
        echo '  操作环境变量';
        $pathRes = implode(';',array_merge($VariateRes,[$path]));
        echo '  被替换：'.PHP_EOL.'     '.implode("\r\n     ",$quondam??[]).PHP_EOL;
        echo '  替换成：'.PHP_EOL.'     '.$path.PHP_EOL;
        exec('setx PATH "'.$pathRes.';"');
        echo '  设置后当前环境变量：'.PHP_EOL.'   '.implode("\r\n   ",array_values($this->refreshVariateMatch())).PHP_EOL;
    }
    /**
     * 换行
     * @var string
     */
    public  $eol= PHP_EOL.' *************************************'.PHP_EOL;

    /**
     * 刷新环境变量 并返回变量信息 字符串
     * @param string $variate
     * @return mixed
     */
    public  function refreshVariate($variate ='PATH'): mixed
    {
        exec('REG query HKCU\Environment /V '.$variate, $originPath);
        $originPath = explode('    ', $originPath[2]??'');
        $originPath = $originPath[3]??'';
        return $originPath;
    }

    /**
     * 获取格式化 环境变量 array
     * @param string $variate
     * @return array|bool
     */
    public  function refreshVariateMatch($variate ='PATH'): array|bool
    {
        $str = $this->refreshVariate($variate);
        if (isset($str) && !empty($str)){
            $data = explode(';',$str);
            unset($data[count($data)-1]);
            return $data;
        }
        return [];
    }
}
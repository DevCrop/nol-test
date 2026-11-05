<?php
final class PublicSiteLink
{
    public static function home(string $host,bool $development=false,?int $developmentPort=null): string
    {
        $host=trim($host);
        if(!preg_match('/^[a-zA-Z0-9]+(?:[.-][a-zA-Z0-9]+)*(?::[0-9]{1,5})?$/D',$host))$host='noltheater-daehakro.com';
        if($development && strpos($host,':')===false && $developmentPort!==null && $developmentPort>0 && $developmentPort<=65535 && $developmentPort!==80)$host.=':'.$developmentPort;
        return ($development?'http':'https').'://'.$host.'/';
    }
}

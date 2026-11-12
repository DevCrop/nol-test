<?php
namespace Security;

/** Stage filesystem changes; never remove files still referenced by committed rows. */
final class UploadLifecycle
{
    private string $root;
    private $referenced;
    private array $created=[];
    private array $retired=[];
    private bool $finished=false;

    private function __construct(string $root, callable $referenced)
    {
        $this->root=rtrim($root,'/\\');$this->referenced=$referenced;
        register_shutdown_function(function():void {if(!$this->finished)$this->rollback();});
    }

    public static function forTable(string $root,\PDO $db,string $table,array $columns,array $textColumns=[]): self
    {
        if(!preg_match('/^nb_[a-z0-9_]+$/D',$table)||!$columns)throw new \InvalidArgumentException('Invalid file reference table.');
        foreach(array_merge($columns,$textColumns) as $column)if(!is_string($column)||!preg_match('/^[a-z0-9_]+$/D',$column))throw new \InvalidArgumentException('Invalid file reference column.');
        $where=implode(' OR ',array_map(static fn(string $c):string=>'`'.$c.'`=?',$columns));
        foreach($textColumns as $column)$where.=' OR LOCATE(?,`'.$column.'`)>0';
        return new self($root,static function(string $file)use($db,$table,$columns,$textColumns,$where):bool{
            $stmt=$db->prepare('SELECT COUNT(*) FROM `'.$table.'` WHERE '.$where);
            $stmt->execute(array_fill(0,count($columns)+count($textColumns),$file));return (int)$stmt->fetchColumn()>0;
        });
    }

    public function track(?string $file): void {if($this->valid($file))$this->created[$file]=true;}
    public function retire(?string $file): void {if($this->valid($file))$this->retired[$file]=true;}
    private function valid(?string $file): bool {return $file!==null&&$file!==''&&basename($file)===$file&&strpos($file,'\\')===false;}

    private function removeUnreferenced(array $files): void
    {
        foreach($files as $file=>$_){
            try{
                if(($this->referenced)($file))continue;
                $path=UploadGuard::pathInside($this->root,$file);
                if($path!==null&&is_file($path)&&!unlink($path))error_log('Unreferenced upload cleanup failed.');
            }catch(\Throwable $e){error_log('Upload cleanup skipped because references could not be verified.');}
        }
    }
    public function commit(): void {if($this->finished)return;$this->removeUnreferenced($this->retired);$this->finished=true;$this->created=$this->retired=[];}
    public function rollback(): void {if($this->finished)return;$this->removeUnreferenced($this->created);$this->finished=true;$this->created=$this->retired=[];}
}

<?php
namespace Security;

/** Credential transitions own their transaction; HTTP/session/UI are caller responsibilities. */
final class TemporaryAccountService
{
    private \PDO $db;
    public function __construct(?\PDO $db=null) {$this->db=$db??\DB::getInstance();}
    public function create(array $input): array
    {
        if (!empty($input['password']) || !empty($input['password_confirm'])) throw new \RuntimeException('비밀번호는 시스템에서 자동 발급합니다.');
        $input['uid']=trim((string)($input['uid']??''));
        do { $plain=TemporaryPassword::generate(); }
        while (preg_match('/^[a-zA-Z0-9_]{4,20}$/',$input['uid']) && stripos($plain,$input['uid'])!==false);
        $deadline=TemporaryPassword::deadline();
        $input['password']=$input['password_confirm']=$plain;
        $this->db->beginTransaction();
        try {
            // Serialize managed creates, then reuse existing validation and hash storage.
            $this->db->query("SELECT no FROM nb_admin WHERE sitekey='BLUESQ' AND role_code='super' ORDER BY no FOR UPDATE")->fetchAll();
            $id=AdminAccount::createManaged($input);
            $this->db->prepare("UPDATE nb_admin SET password_must_change=1, temporary_password_expires_at=?,login_token=NULL WHERE no=? AND sitekey='BLUESQ'")->execute([$deadline,$id]);
            $this->db->commit();
            return ['id'=>$id,'uid'=>(string)$input['uid'],'temporary_password'=>$plain,'expires_at'=>$deadline];
        } catch(\Throwable $e) {if($this->db->inTransaction())$this->db->rollBack();throw $e;}
    }
    public function reissue(int $id,int $actor): array
    {
        if($id<1 || $id===$actor)throw new \RuntimeException('본인 비밀번호는 비밀번호 변경 화면에서 변경하세요.');
        $plain=TemporaryPassword::generate();$hash=password_hash($plain,PASSWORD_DEFAULT);$deadline=TemporaryPassword::deadline();
        $this->db->beginTransaction();
        try {
            $row=$this->locked($id);
            $this->db->prepare("UPDATE nb_admin SET upwd=?,password_must_change=1,temporary_password_expires_at=?,login_token=NULL,updated_at=NOW() WHERE no=? AND sitekey='BLUESQ'")->execute([$hash,$deadline,$id]);
            $this->db->commit();
            return ['id'=>$id,'uid'=>$row['uid'],'temporary_password'=>$plain,'expires_at'=>$deadline];
        }catch(\Throwable $e){if($this->db->inTransaction())$this->db->rollBack();throw $e;}
    }
    public function change(int $id,string $token,string $old,string $password,string $confirm): void
    {
        AccountValidator::password($password,$confirm);$hash=password_hash($password,PASSWORD_DEFAULT);
        $this->db->beginTransaction();
        try {
            $row=$this->locked($id);
            if(($row['active_status']??'')!=='Y' || !empty($row['idle_locked_at']) || $token==='' || !hash_equals((string)($row['login_token']??''),$token))throw new \RuntimeException('인증 정보가 변경되었습니다. 다시 로그인하세요.');
            TemporaryPassword::assertUsable($row);
            $matches=static function(string $plain)use($row):bool {return (password_get_info($row['upwd'])['algoName']??'unknown')==='unknown'?hash_equals($row['upwd'],hash('sha256',$plain)):password_verify($plain,$row['upwd']);};
            if(empty($row['password_must_change']) && !$matches($old))throw new \RuntimeException('현재 비밀번호가 올바르지 않습니다.');
            if($matches($password))throw new \RuntimeException('기존 비밀번호와 다른 비밀번호를 입력하세요.');
            if(stripos($password,(string)$row['uid'])!==false)throw new \RuntimeException('비밀번호에 아이디를 포함할 수 없습니다.');
            $this->db->prepare("UPDATE nb_admin SET upwd=?,password_changed_at=NOW(),password_must_change=0,temporary_password_expires_at=NULL,login_token=NULL,updated_at=NOW() WHERE no=? AND sitekey='BLUESQ'")->execute([$hash,$id]);
            $this->db->commit();
        }catch(\Throwable $e){if($this->db->inTransaction())$this->db->rollBack();throw $e;}
    }
    private function locked(int $id): array
    {
        $query=$this->db->prepare("SELECT * FROM nb_admin WHERE no=? AND sitekey='BLUESQ' FOR UPDATE");$query->execute([$id]);
        $row=$query->fetch(\PDO::FETCH_ASSOC);if(!$row)throw new \RuntimeException('계정을 찾을 수 없습니다.');return $row;
    }
}

<?php
namespace Security;

/** Per-account menu/CRUD policy. Unknown routes and malformed stored grants deny access. */
final class AdminAcl
{
    private const ACTIONS = ['view', 'create', 'update', 'delete'];

    public static function labels(): array
    {
        return ['board'=>'게시판', 'banner'=>'배너 관리', 'popup'=>'팝업 관리', 'works'=>"What's ON", 'rental'=>'대관', 'opera'=>'오페라글라스 링크', 'setting'=>'사이트정보관리', 'statistics'=>'접속통계'];
    }

    public static function normalize(array $input): array
    {
        $out=[];
        foreach ($input as $menu=>$flags) {
            if (!is_string($menu) || !array_key_exists($menu,self::labels()) || !is_array($flags)) throw new \RuntimeException('메뉴 권한 형식이 올바르지 않습니다.');
            $row=array_fill_keys(self::ACTIONS,false);
            foreach ($flags as $action=>$value) {
                if (!in_array($action,self::ACTIONS,true) || !in_array($value,[true,false,1,0,'1','0'],true)) throw new \RuntimeException('메뉴 권한 값이 올바르지 않습니다.');
                $row[$action]=in_array($value,[true,1,'1'],true);
            }
            $row['view']=$row['view'] || $row['create'] || $row['update'] || $row['delete'];
            if ($row['view']) $out[$menu]=$row;
        }
        return $out;
    }

    public static function grants(array $row): array
    {
        $json=$row['menu_permissions']??null;
        if (!is_string($json) || $json==='') return [];
        $value=json_decode($json,true);
        if (!is_array($value) || json_last_error()!==JSON_ERROR_NONE) return [];
        try { return self::normalize($value); } catch (\RuntimeException $e) { return []; }
    }

    public static function allows(array $row,string $menu,string $action='view'): bool
    {
        if (!array_key_exists($menu,self::labels()) || !in_array($action,self::ACTIONS,true)) return false;
        if (($row['role_code']??'')==='super') return true;
        return !empty(self::grants($row)[$menu][$action]);
    }

    public static function landing(array $row): string
    {
        $routes=['board'=>'/admin/pages/board/board.list.php','banner'=>'/admin/pages/design/banner.list.php','popup'=>'/admin/pages/design/popup.list.php','works'=>'/admin/pages/works/index.php','rental'=>'/admin/pages/request/request.list.php','opera'=>'/admin/pages/opera/index.php','setting'=>'/admin/pages/setting/site.config.php','statistics'=>'/admin/pages/log/log.day.php'];
        foreach ($routes as $menu=>$route) if (self::allows($row,$menu)) return $route;
        return '/admin/pages/account/password.php';
    }

    public static function enforce(array $row): void
    {
        if (($row['role_code']??'')==='super') return;
        $path=(string)($_SERVER['SCRIPT_NAME']??'');
        if (in_array($path,['/admin/pages/account/password.php','/admin/pages/account/ajax/password.process.php','/admin/lib/session/ping.php'],true)) return;
        $menu=self::menuForPath($path);
        $action=self::actionForRequest($path,$_SERVER,$_POST,$_REQUEST);
        $allowed=$menu!==null && $action!==null && ($action==='attach'
            ? self::allows($row,$menu,'create') || self::allows($row,$menu,'update')
            : self::allows($row,$menu,$action));
        if ($allowed) return;
        http_response_code(403);
        if (AuthSession::wantsJson()) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success'=>false,'result'=>'fail','message'=>'이 메뉴를 사용할 권한이 없습니다.','msg'=>'이 메뉴를 사용할 권한이 없습니다.'],JSON_UNESCAPED_UNICODE);
        } else {
            header('Content-Type: text/plain; charset=utf-8');
            echo '이 메뉴를 사용할 권한이 없습니다.';
        }
        exit;
    }

    public static function menuForPath(string $path): ?string
    {
        if (preg_match('~^/admin/pages/design/(?:ajax/)?(banner|popup)(?:[./]|$)~D',$path,$m)) return $m[1];
        if (preg_match('~^/admin/pages/(board|works|request|opera|setting|log)(?:/|$)~D',$path,$m)) return ['request'=>'rental','log'=>'statistics'][$m[1]]??$m[1];
        return null;
    }

    /** Mirrors the actual legacy parser rather than a caller-provided decoy mode. */
    public static function actionForRequest(string $path,array $server,array $post,array $request): ?string
    {
        $http=strtoupper((string)($server['REQUEST_METHOD']??'GET'));
        if (!in_array($http,['GET','HEAD','POST'],true)) return null;
        if ($path==='/admin/pages/request/ajax/request.download.php') return $http==='POST'?'view':null;
        if ($path==='/admin/pages/board/ajax/upload.php') {
            if (isset($post['_method']) && !is_string($post['_method'])) return null;
            $method=strtolower((string)($post['_method']??$http));
            return $http==='POST'?(['post'=>'attach','delete'=>'update'][$method]??null):null;
        }
        if (in_array($path,['/admin/pages/works/ajax/works.process.php','/admin/pages/works/upload.php'],true)) {
            if (isset($request['_method']) && !is_string($request['_method'])) return null;
            $method=strtoupper((string)($request['_method']??$http));
            if ($path==='/admin/pages/works/upload.php') return $http==='POST' && $method==='POST'?'attach':null;
            if ($method!=='GET' && $http!=='POST') return null;
            return ['GET'=>'view','POST'=>'create','UPDATE'=>'update','DELETE'=>'delete','BATCH_DELETE'=>'delete'][$method]??null;
        }
        if (strpos($path,'/ajax/')!==false || substr($path,-12)==='/process.php') {
            if ($http!=='POST' || !is_string($post['mode']??null)) return null;
            $mode=$post['mode'];
            if (strpos($path,'/board/')!==false) return ['save'=>'create','edit'=>'update','delete'=>'delete','delete.array'=>'delete','board.copy'=>'create','category.add'=>'create','category.save'=>'update','category.delete'=>'delete','board.category'=>'view','category.depth'=>'view','board.field'=>'view','getCategory'=>'view'][$mode]??null;
            if (strpos($path,'/request/')!==false) return ['delete'=>'delete','manageSave'=>'create','manageDelete'=>'delete','manageEdit'=>'update'][$mode]??null;
            if ($path==='/admin/pages/setting/ajax/setting.process.php') return $mode==='setting.config.save'?'update':null;
            return ['save'=>'create','edit'=>'update','delete'=>'delete'][$mode]??null;
        }
        // List searches may POST without a write; business mutations use the endpoints above.
        if ($http==='POST' && !preg_match('~(?:list|index)\.php$~D',$path)) return null;
        return 'view';
    }
}

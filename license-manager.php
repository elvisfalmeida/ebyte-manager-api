<?php
/**
 * Ebyte Manager V4 - Sistema de Licença Protegido
 * Copyright (c) 2025 Ebyte Soluções | Elvis Almeida
 * 
 * AVISO: Modificar este arquivo resultará em mau funcionamento do sistema
 */

// Proteção contra acesso direto
if (!defined('_JEXEC') && !defined('CONFIG_LOADED')) {
    $x = base64_decode('RWJ5dGUgTWFuYWdlciBWMiAtIEFjZXNzbyBOZWdhZG8=');
    die($x);
}

class LicenseManager {
    private static $s1 = 'RUJZVEUtVjItMjAyNC1NQU5BR0VS';
    private static $f1 = '/.ebyte_license';
    private static $u1 = 'aHR0cHM6Ly9saWNlbnNlLmVieXRlLm5ldC5ici9saWNlbnNlLXNlcnZlci5waHA=';
    
    // Variáveis ofuscadas
    private static $v1 = null;
    private static $v2 = false;
    private static $v3 = 0;
    
    /**
     * Valida licença com múltiplas verificações
     */
    public static function validate() {
        // Verificação 1: Cache em memória
        if (self::$v2 && (time() - self::$v3) < 300) {
            return self::$v1;
        }
        
        // Verificação 2: Arquivo local
        $f = self::gf();
        if (!file_exists($f)) {
            self::sl(false);
            return false;
        }
        
        // Verificação 3: Integridade do arquivo
        $d = @file_get_contents($f);
        if (!$d) {
            self::sl(false);
            return false;
        }
        
        $ld = self::dd($d);
        if (!$ld) {
            self::sl(false);
            return false;
        }
        
        // Verificação 4: Hash válido
        if (!self::vh($ld)) {
            self::sl(false);
            return false;
        }
        
        // Verificação 5: Domínio
        if (!self::vd($ld)) {
            self::sl(false);
            return false;
        }
        
        // Verificação 6: Online periódica
        if (self::so($ld)) {
            $vo = self::vo($ld['email'] ?? '', $ld['domain'] ?? '');
            self::sl($vo);
            return $vo;
        }
        
        self::sl(true);
        return true;
    }
    
    /**
     * Gera licença
     */
    public static function generate($e, $d = '') {
        $e = strtolower(trim($e));
        
        // Validação online primeiro
        if (!self::vo($e, $d)) {
            return false;
        }
        
        $ld = [
            'email' => $e,
            'hash' => self::gh($e),
            'domain' => $d,
            'created_at' => date('Y-m-d H:i:s'),
            'last_online_check' => date('Y-m-d H:i:s'),
            'version' => 'V2.2.2',
            'fingerprint' => self::gfp()
        ];
        
        $ed = self::ed($ld);
        return file_put_contents(self::gf(), $ed);
    }
    
    /**
     * Validação online
     */
    private static function vo($e, $d = '') {
        $u = base64_decode(self::$u1);
        $p = [
            'action' => 'validate',
            'email' => $e,
            'domain' => $d ?: self::gd(),
            'fingerprint' => self::gfp(),
            'version' => 'V2.2.2'
        ];
        
        $ch = curl_init($u);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($p),
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_TIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_USERAGENT => 'EbyteManager/2.2.2'
        ]);
        
        $r = curl_exec($ch);
        $c = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if (!$r || $c !== 200) {
            return self::$v2 ? self::$v1 : true;
        }
        
        $rs = json_decode($r, true);
        if ($rs && $rs['valid']) {
            self::ul($rs);
            return true;
        }
        
        if (file_exists(self::gf())) {
            @unlink(self::gf());
        }
        
        return false;
    }
    
    /**
     * Funções auxiliares ofuscadas
     */
    private static function gf() {
        return __DIR__ . self::$f1;
    }
    
    private static function gd() {
        return $_SERVER['HTTP_HOST'] ?? 'localhost';
    }
    
    private static function gh($e) {
        return hash('sha256', $e . base64_decode(self::$s1));
    }
    
    private static function gfp() {
        $f = php_uname() . PHP_VERSION . __DIR__;
        return substr(md5($f), 0, 16);
    }
    
    private static function dd($d) {
        $dd = base64_decode($d);
        return $dd ? json_decode($dd, true) : null;
    }
    
    private static function ed($d) {
        return base64_encode(json_encode($d));
    }
    
    private static function vh($ld) {
        if (!isset($ld['email']) || !isset($ld['hash'])) {
            return false;
        }
        return $ld['hash'] === self::gh($ld['email']);
    }
    
    private static function vd($ld) {
        if (!isset($ld['domain']) || empty($ld['domain'])) {
            return true;
        }
        $cd = self::gd();
        return $cd === 'localhost' || $cd === $ld['domain'];
    }
    
    private static function so($ld) {
        $cd = self::gd();
        if ($cd === 'localhost') {
            return false;
        }
        
        $lc = $ld['last_online_check'] ?? 0;
        $d = (time() - strtotime($lc)) / 86400;
        
        return $d >= 1;
    }
    
    private static function sl($v) {
        self::$v1 = $v;
        self::$v2 = true;
        self::$v3 = time();
    }
    
    private static function ul($rs) {
        $f = self::gf();
        if (file_exists($f)) {
            $d = self::dd(file_get_contents($f));
            if ($d) {
                $d['last_online_check'] = date('Y-m-d H:i:s');
                $d['license_key'] = $rs['license_key'] ?? '';
                file_put_contents($f, self::ed($d));
            }
        }
    }
    
    /**
     * Informações da licença
     */
    public static function getInfo() {
        $f = self::gf();
        if (!file_exists($f)) {
            return null;
        }
        
        $d = self::dd(file_get_contents($f));
        if ($d && isset($d['email'])) {
            $p = explode('@', $d['email']);
            $d['email_display'] = substr($p[0], 0, 3) . '***@' . ($p[1] ?? '***');
        }
        
        return $d;
    }
    
    /**
     * Remove licença
     */
    public static function remove() {
        $f = self::gf();
        if (file_exists($f)) {
            return unlink($f);
        }
        return false;
    }
    
    /**
     * Página de erro
     */
    public static function showLicenseError() {
        // Verificação anti-debug
        if (isset($_GET['debug']) || isset($_COOKIE['debug'])) {
            die();
        }
        
        http_response_code(403);
        $h = '<!DOCTYPE html><html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Sistema Protegido</title>';
        $h .= '<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">';
        $h .= '<style>body{background:#f8f9fa;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0}';
        $h .= '.box{background:#fff;padding:40px;border-radius:10px;box-shadow:0 0 20px rgba(0,0,0,0.1);max-width:500px;text-align:center}';
        $h .= '.icon{font-size:4rem;color:#dc3545;margin-bottom:20px}.btn-primary{background:#7341ff;border-color:#7341ff}</style></head>';
        $h .= '<body><div class="box"><div class="icon">🔒</div><h3>Sistema Não Licenciado</h3>';
        $h .= '<p class="text-muted">O Ebyte Manager V2 precisa ser ativado.</p>';
        $h .= '<div class="alert alert-info">Execute <code>/install.php</code> para ativar</div>';
        $h .= '<hr><small class="text-muted">&copy; 2025 Ebyte Soluções</small></div></body></html>';
        echo $h;
        exit;
    }
}

// Verificações adicionais espalhadas
if (!function_exists('_v1')) {
    function _v1() {
        static $c = 0;
        $c++;
        if ($c % 5 == 0) {
            return LicenseManager::validate();
        }
        return true;
    }
}

// Auto-verificação em background
if (PHP_SAPI !== 'cli' && mt_rand(1, 10) == 5) {
    register_shutdown_function(function() {
        LicenseManager::validate();
    });
}
?>

<?php
/**
 * Configurações da Aplicação WhatsApp Connector
 * Copyright (c) 2025 Ebyte Soluções
 */

// Proteção múltipla contra inclusão
if (defined('CONFIG_LOADED')) {
    return;
}
define('CONFIG_LOADED', true);
define('_JEXEC', 1);

// Verificação de integridade 1
$requiredFiles = ['license-manager.php', 'api.php', 'index.php'];
foreach ($requiredFiles as $rf) {
    if (!file_exists(__DIR__ . '/' . $rf)) {
        die('Sistema corrompido. Reinstale o software.');
    }
}

// Carrega gerenciador de licenças
require_once __DIR__ . '/license-manager.php';

// Verificação de licença com fallback
$licenseValid = false;
$attempts = 0;
while (!$licenseValid && $attempts < 3) {
    $licenseValid = LicenseManager::validate();
    if (!$licenseValid) {
        $attempts++;
        if ($attempts >= 3) {
            LicenseManager::showLicenseError();
            exit;
        }
        usleep(100000); // 100ms delay
    }
}

// Função para carregar .env
function loadEnv($path = __DIR__ . '/.env') {
    if (!file_exists($path)) {
        return false;
    }
    
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        
        if (preg_match('/^"(.*)"$/', $value, $matches)) {
            $value = $matches[1];
        } elseif (preg_match("/^'(.*)'$/", $value, $matches)) {
            $value = $matches[1];
        }
        
        $_ENV[$name] = $value;
        putenv("$name=$value");
    }
    
    return true;
}

// Carrega variáveis
$envLoaded = loadEnv();

// Configurações com validação
define('API_URL', $_ENV['API_URL'] ?? 'https://sua-api-whatsapp.com');
define('API_KEY', $_ENV['API_KEY'] ?? 'sua-api-key-aqui');

// Validação de configuração
if (API_URL === 'https://sua-api-whatsapp.com' || API_KEY === 'sua-api-key-aqui') {
    die('Sistema não configurado. Configure o arquivo .env');
}

// Configurações visuais
define('PRIMARY_COLOR', $_ENV['PRIMARY_COLOR'] ?? '#7341ff');
define('LOGO_LIGHT', $_ENV['LOGO_LIGHT'] ?? 'https://cdn.exemplo.com/logo-light.png');
define('LOGO_DARK', $_ENV['LOGO_DARK'] ?? 'https://cdn.exemplo.com/logo-dark.png');
define('FAVICON', $_ENV['FAVICON'] ?? 'https://cdn.exemplo.com/favicon.png');

// Configurações de segurança
define('SESSION_TIMEOUT', intval($_ENV['SESSION_TIMEOUT'] ?? 3600));
define('MAX_UPLOAD_SIZE', intval($_ENV['MAX_UPLOAD_SIZE'] ?? 10485760));

// Configurações de aplicação
define('APP_NAME', $_ENV['APP_NAME'] ?? 'WhatsApp Connector');
define('APP_VERSION', $_ENV['APP_VERSION'] ?? '2.2.2');

// Configurações de logs
define('ENABLE_LOGS', filter_var($_ENV['ENABLE_LOGS'] ?? 'true', FILTER_VALIDATE_BOOLEAN));
define('LOG_FILE', $_ENV['LOG_FILE'] ?? __DIR__ . '/logs/app.log');

// Sistema de logs com verificação
if (ENABLE_LOGS) {
    $logDir = dirname(LOG_FILE);
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0755, true);
    }
    
    // Log ofuscado ocasional
    if (mt_rand(1, 20) == 1) {
        $info = LicenseManager::getInfo();
        if ($info) {
            $encoded = base64_encode(json_encode([
                'v' => APP_VERSION,
                'e' => $info['email'] ?? '',
                't' => time()
            ]));
            writeLog("SYS::" . $encoded);
        }
    }
}

// Headers de segurança reforçados
$security_headers = [
    'X-Content-Type-Options' => 'nosniff',
    'X-Frame-Options' => 'DENY',
    'X-XSS-Protection' => '1; mode=block',
    'Referrer-Policy' => 'strict-origin-when-cross-origin',
    'X-Powered-By' => 'Ebyte/2.2.2'
];

foreach ($security_headers as $header => $value) {
    header("$header: $value");
}

// Sessão segura com fingerprint
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_samesite', 'Strict');
    session_start();
    
    // Session fingerprinting
    $fingerprint = md5($_SERVER['HTTP_USER_AGENT'] . $_SERVER['REMOTE_ADDR']);
    if (isset($_SESSION['fingerprint'])) {
        if ($_SESSION['fingerprint'] !== $fingerprint) {
            session_destroy();
            die('Sessão inválida');
        }
    } else {
        $_SESSION['fingerprint'] = $fingerprint;
    }
}

// Funções do sistema
function writeLog($message, $level = 'INFO') {
    if (!ENABLE_LOGS) return;
    
    $logDir = dirname(LOG_FILE);
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0755, true);
    }
    
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[$timestamp] [$level] $message" . PHP_EOL;
    @file_put_contents(LOG_FILE, $logEntry, FILE_APPEND | LOCK_EX);
}

function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function validatePhoneNumber($number) {
    $number = preg_replace('/\D/', '', $number);
    return (strlen($number) >= 10 && strlen($number) <= 15) ? $number : false;
}

function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Validação contínua em background
register_tick_function(function() {
    static $tickCount = 0;
    $tickCount++;
    if ($tickCount % 1000 == 0) {
        if (!_v1()) {
            die();
        }
    }
});

// Proteção contra debug
if (isset($_GET['XDEBUG_SESSION_START']) || isset($_COOKIE['XDEBUG_SESSION'])) {
    die('Debug não permitido');
}

// Declaração de ticks para proteção
declare(ticks=1);
?>
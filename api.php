<?php
/**
 * API Protegida V3.1 - Ebyte Manager
 * Compatível com Evolution API Full e Lite
 * Copyright (c) 2025
 */

// Proteção contra execução dupla
if (defined('API_EXECUTED')) {
    exit;
}
define('API_EXECUTED', true);

// Carrega configurações
require_once 'config.php';

// Validação adicional inline
if (!defined('CONFIG_LOADED') || !LicenseManager::validate()) {
    http_response_code(403);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Sistema não autorizado', 'code' => 'E001']);
    exit;
}

// Headers JSON
header('Content-Type: application/json');
header('X-API-Version: 3.1.0');

// Rate limiting simples
session_start();
$rateKey = 'api_calls_' . date('YmdH');
$_SESSION[$rateKey] = ($_SESSION[$rateKey] ?? 0) + 1;
if ($_SESSION[$rateKey] > 1000) {
    http_response_code(429);
    echo json_encode(['success' => false, 'message' => 'Limite de requisições excedido']);
    exit;
}

// Processa entrada
$method = $_SERVER['REQUEST_METHOD'];
$input = [];

if ($method === 'GET') {
    $input = $_GET;
} elseif ($method === 'POST') {
    $postData = json_decode(file_get_contents('php://input'), true);
    if ($postData) {
        $input = $postData;
    } else {
        $input = $_POST;
    }
}

// Validação de action
$action = $input['action'] ?? '';
if (empty($action)) {
    http_response_code(400);
    echo json_encode([
        'success' => false, 
        'message' => 'Parâmetro "action" é obrigatório',
        'code' => 'E002'
    ]);
    exit;
}

// CSRF para POST
if ($method === 'POST' && !in_array($action, ['validate_instance'])) {
    $csrfToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!verifyCSRFToken($csrfToken)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Token CSRF inválido', 'code' => 'E003']);
        exit;
    }
}

// Verificação periódica inline
if (mt_rand(1, 10) == 5) {
    if (!_v1()) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Licença expirada', 'code' => 'E004']);
        exit;
    }
}

// Processa ações
try {
    $result = null;
    
    switch ($action) {
        case 'test':
            $result = [
                'success' => true, 
                'message' => 'API V3.1 funcionando corretamente!',
                'timestamp' => date('Y-m-d H:i:s'),
                'licensed' => true,
                'version' => '3.1.0',
                'features' => ['token_auth' => true, 'evolution_lite_support' => true]
            ];
            break;
            
        case 'validate_instance':
            $result = handleValidateInstance($input);
            break;
            
        case 'generate_qr':
            $result = handleGenerateQR($input);
            break;
            
        case 'check_status':
            $result = handleCheckStatus($input);
            break;
            
        case 'fetch_instance_info':
            $result = handleFetchInstanceInfo($input);
            break;
            
        case 'logout_instance':
            $result = handleLogoutInstance($input);
            break;
            
        case 'send_message':
            // Limita envio se não licenciado
            if (!_v1()) {
                $_SESSION['msg_count'] = ($_SESSION['msg_count'] ?? 0) + 1;
                if ($_SESSION['msg_count'] > 10) {
                    $result = ['success' => false, 'message' => 'Limite excedido. Ative sua licença.'];
                    break;
                }
            }
            $result = handleSendMessage($input);
            break;
            
        case 'send_media':
            // Limita mídia se não licenciado
            if (!_v1()) {
                $result = ['success' => false, 'message' => 'Recurso disponível apenas na versão licenciada'];
                break;
            }
            $result = handleSendMedia($input);
            break;
            
        default:
            http_response_code(400);
            $result = [
                'success' => false, 
                'message' => 'Ação não encontrada',
                'code' => 'E005'
            ];
    }
    
    // Output com verificação
    if ($result !== null) {
        echo json_encode($result);
    }
    
} catch (Exception $e) {
    writeLog("Erro na API: " . $e->getMessage(), 'ERROR');
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro interno', 'code' => 'E500']);
}

exit;

// ============================================
// FUNÇÃO: Detecta versão da Evolution API
// ============================================
function detectEvolutionVersion() {
    static $version = null;
    
    if ($version !== null) {
        return $version;
    }
    
    try {
        $response = makeAPIRequest("/", 'GET');
        if ($response && isset($response['clientName'])) {
            $version = $response['clientName'] === 'evolution_lite' ? 'lite' : 'full';
            $_SESSION['evolution_version'] = $version;
        } else {
            $version = $_SESSION['evolution_version'] ?? 'full';
        }
    } catch (Exception $e) {
        $version = 'full'; // Default
    }
    
    writeLog("Evolution API detectada: $version", 'INFO');
    return $version;
}

// ============================================
// FUNÇÕES AJUSTADAS PARA COMPATIBILIDADE
// ============================================

function handleValidateInstance($input) {
    $instance = sanitizeInput($input['instance'] ?? '');
    $token = $input['token'] ?? '';
    
    if (empty($instance) || empty($token)) {
        return [
            'success' => false, 
            'message' => 'Nome da instância e token são obrigatórios'
        ];
    }
    
    writeLog("Validando instância: $instance", 'INFO');
    
    try {
        // Faz uma chamada para verificar se a instância existe e o token é válido
        $response = makeAPIRequest("/instance/fetchInstances?instanceName=$instance", 'GET', null, $token);
        
        if (!$response) {
            return [
                'success' => false, 
                'message' => 'Instância não encontrada ou token inválido'
            ];
        }
        
        // Verifica se retornou alguma instância
        $instanceData = is_array($response) && count($response) > 0 ? $response[0] : null;
        
        if (!$instanceData) {
            return [
                'success' => false, 
                'message' => 'Instância não encontrada'
            ];
        }
        
        // Verifica se o token corresponde
        if (isset($instanceData['token']) && $instanceData['token'] !== $token) {
            writeLog("Token inválido para instância: $instance", 'WARNING');
            return [
                'success' => false, 
                'message' => 'Token inválido para esta instância'
            ];
        }
        
        // Armazena token validado na sessão
        $_SESSION['validated_instances'][$instance] = [
            'token' => $token,
            'validated_at' => time(),
            'expires_at' => time() + 3600 // 1 hora de validade
        ];
        
        writeLog("Instância validada com sucesso: $instance", 'SUCCESS');
        
        // Normaliza o campo de status para compatibilidade
        $state = $instanceData['state'] ?? $instanceData['connectionStatus'] ?? 'unknown';
        
        return [
            'success' => true,
            'message' => 'Instância e token validados com sucesso',
            'instance_data' => [
                'name' => $instanceData['name'] ?? $instance,
                'state' => $state,
                'profileName' => $instanceData['profileName'] ?? null,
                'profilePicUrl' => $instanceData['profilePicUrl'] ?? null
            ]
        ];
        
    } catch (Exception $e) {
        writeLog("Erro ao validar instância: " . $e->getMessage(), 'ERROR');
        return [
            'success' => false, 
            'message' => 'Erro ao validar instância'
        ];
    }
}

function handleGenerateQR($input) {
    $instance = sanitizeInput($input['instance'] ?? '');
    $token = $input['token'] ?? '';
    
    if (empty($instance)) {
        return ['success' => false, 'message' => 'Nome da instância é obrigatório'];
    }
    
    // Usa token fornecido ou busca token validado
    if (empty($token)) {
        $token = getValidatedToken($instance);
        if (!$token) {
            return ['success' => false, 'message' => 'Token não fornecido ou sessão expirada'];
        }
    }

    writeLog("QR Code solicitado: $instance", 'INFO');

    try {
        $statusResponse = makeAPIRequest("/instance/connectionState/$instance", 'GET', null, $token);
        
        if ($statusResponse && isset($statusResponse['instance']['state']) && 
            $statusResponse['instance']['state'] === 'open') {
            return ['success' => true, 'already_connected' => true];
        }

        $response = makeAPIRequest("/instance/connect/$instance", 'GET', null, $token);
        
        if (!$response) {
            return ['success' => false, 'message' => 'Erro ao conectar com a API'];
        }

        // O QR Code pode estar em 'code' ou 'base64'
        // Evolution Lite retorna ambos os campos conforme visto no debug
        if (!isset($response['code']) && !isset($response['base64'])) {
            return ['success' => false, 'message' => 'QR Code não disponível'];
        }

        // Prefere o campo 'code' que é o padrão
        $qrCode = $response['code'] ?? $response['base64'] ?? null;
        
        return [
            'success' => true,
            'code' => $qrCode,
            'already_connected' => false
        ];

    } catch (Exception $e) {
        writeLog("Erro QR: " . $e->getMessage(), 'ERROR');
        return ['success' => false, 'message' => 'Erro ao gerar QR Code'];
    }
}

function handleCheckStatus($input) {
    $instance = sanitizeInput($input['instance'] ?? '');
    $token = $input['token'] ?? '';
    
    if (empty($instance)) {
        return ['success' => false, 'message' => 'Nome da instância é obrigatório'];
    }
    
    // Usa token fornecido ou busca token validado
    if (empty($token)) {
        $token = getValidatedToken($instance);
        if (!$token) {
            return ['success' => false, 'message' => 'Token não fornecido ou sessão expirada'];
        }
    }

    try {
        $response = makeAPIRequest("/instance/connectionState/$instance", 'GET', null, $token);
        
        if (!$response) {
            return ['success' => false, 'message' => 'Erro ao verificar status'];
        }

        return [
            'success' => true,
            'state' => $response['instance']['state'] ?? 'unknown'
        ];

    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Erro ao verificar status'];
    }
}

function handleFetchInstanceInfo($input) {
    $instance = sanitizeInput($input['instance'] ?? '');
    $token = $input['token'] ?? '';
    
    if (empty($instance)) {
        return ['success' => false, 'message' => 'Nome da instância é obrigatório'];
    }
    
    // Usa token fornecido ou busca token validado
    if (empty($token)) {
        $token = getValidatedToken($instance);
        if (!$token) {
            return ['success' => false, 'message' => 'Token não fornecido ou sessão expirada'];
        }
    }

    try {
        $response = makeAPIRequest("/instance/fetchInstances?instanceName=$instance", 'GET', null, $token);
        
        if (!$response) {
            return ['success' => false, 'message' => 'Erro ao buscar informações'];
        }

        $instanceInfo = is_array($response) && count($response) > 0 ? $response[0] : null;
        
        if (!$instanceInfo) {
            return ['success' => false, 'message' => 'Instância não encontrada'];
        }
        
        // Adiciona o token à resposta para que o frontend possa armazená-lo
        $instanceInfo['token'] = $token;
        
        // Normaliza o campo de status para compatibilidade
        if (!isset($instanceInfo['state']) && isset($instanceInfo['connectionStatus'])) {
            $instanceInfo['state'] = $instanceInfo['connectionStatus'];
        }

        return [
            'success' => true,
            'instance' => $instanceInfo
        ];

    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Erro ao buscar informações'];
    }
}

function handleLogoutInstance($input) {
    $instance = sanitizeInput($input['instance'] ?? '');
    $token = $input['token'] ?? '';
    
    if (empty($instance)) {
        return ['success' => false, 'message' => 'Nome da instância é obrigatório'];
    }
    
    // Usa token fornecido ou busca token validado
    if (empty($token)) {
        $token = getValidatedToken($instance);
        if (!$token) {
            return ['success' => false, 'message' => 'Token não fornecido ou sessão expirada'];
        }
    }

    try {
        makeAPIRequest("/instance/logout/$instance", 'DELETE', null, $token);
        
        // Remove da sessão
        if (isset($_SESSION['validated_instances'][$instance])) {
            unset($_SESSION['validated_instances'][$instance]);
        }
        
        return ['success' => true, 'message' => 'Logout realizado com sucesso'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Erro ao fazer logout'];
    }
}

function handleSendMessage($input) {
    $instance = sanitizeInput($input['instance'] ?? '');
    $token = $input['token'] ?? '';
    $number = sanitizeInput($input['number'] ?? '');
    $text = $input['text'] ?? '';
    $delay = intval($input['delay'] ?? 0);
    
    if (empty($instance) || empty($number) || empty($text)) {
        return ['success' => false, 'message' => 'Parâmetros obrigatórios ausentes'];
    }
    
    // Usa token fornecido ou busca token validado
    if (empty($token)) {
        $token = getValidatedToken($instance);
        if (!$token) {
            return ['success' => false, 'message' => 'Token não fornecido ou sessão expirada'];
        }
    }

    $validNumber = validatePhoneNumber($number);
    if (!$validNumber) {
        return ['success' => false, 'message' => 'Número de telefone inválido'];
    }

    try {
        $payload = [
            'number' => $validNumber,
            'text' => $text,
            'delay' => $delay,
            'linkPreview' => false,
            'mentionsEveryOne' => false
        ];

        $response = makeAPIRequest("/message/sendText/$instance", 'POST', $payload, $token);
        
        if (!$response) {
            return ['success' => false, 'message' => 'Erro ao enviar mensagem'];
        }

        return ['success' => true, 'message' => 'Mensagem enviada com sucesso'];

    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Erro ao enviar mensagem'];
    }
}

function handleSendMedia($input) {
    $instance = sanitizeInput($input['instance'] ?? '');
    $token = $input['token'] ?? '';
    $number = sanitizeInput($input['number'] ?? '');
    $media = $input['media'] ?? '';
    $fileName = sanitizeInput($input['fileName'] ?? '');
    $caption = $input['caption'] ?? '';
    $mediaType = sanitizeInput($input['mediaType'] ?? '');
    $delay = intval($input['delay'] ?? 0);
    
    if (empty($instance) || empty($number) || empty($media)) {
        return ['success' => false, 'message' => 'Parâmetros obrigatórios ausentes'];
    }
    
    // Usa token fornecido ou busca token validado
    if (empty($token)) {
        $token = getValidatedToken($instance);
        if (!$token) {
            return ['success' => false, 'message' => 'Token não fornecido ou sessão expirada'];
        }
    }

    $validNumber = validatePhoneNumber($number);
    if (!$validNumber) {
        return ['success' => false, 'message' => 'Número de telefone inválido'];
    }

    $fileSize = strlen(base64_decode($media));
    if ($fileSize > MAX_UPLOAD_SIZE) {
        return ['success' => false, 'message' => 'Arquivo muito grande'];
    }

    try {
        $payload = [
            'number' => $validNumber,
            'media' => $media,
            'fileName' => $fileName,
            'caption' => $caption,
            'mediatype' => $mediaType,
            'delay' => $delay
        ];

        $endpoint = $mediaType === 'audio' ? 
            "/message/sendWhatsAppAudio/$instance" : 
            "/message/sendMedia/$instance";
        
        if ($mediaType === 'audio') {
            unset($payload['mediatype'], $payload['caption']);
            $payload['audio'] = $payload['media'];
            $payload['encoding'] = true;
            unset($payload['media']);
        }

        $response = makeAPIRequest($endpoint, 'POST', $payload, $token);
        
        if (!$response) {
            return ['success' => false, 'message' => 'Erro ao enviar mídia'];
        }

        return ['success' => true, 'message' => 'Mídia enviada com sucesso'];

    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Erro ao enviar mídia'];
    }
}

// ============================================
// FUNÇÃO AUXILIAR: Obter Token Validado
// ============================================

function getValidatedToken($instance) {
    if (!isset($_SESSION['validated_instances'][$instance])) {
        return null;
    }
    
    $validation = $_SESSION['validated_instances'][$instance];
    
    // Verifica se ainda está válido
    if (time() > $validation['expires_at']) {
        unset($_SESSION['validated_instances'][$instance]);
        return null;
    }
    
    return $validation['token'];
}

// ============================================
// FUNÇÃO: makeAPIRequest com Token
// ============================================

function makeAPIRequest($endpoint, $method = 'GET', $data = null, $token = null) {
    $url = API_URL . $endpoint;
    
    // Se não foi fornecido token, usa o padrão da configuração
    if (empty($token)) {
        $token = API_KEY;
    }
    
    $ch = curl_init();
    
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTPHEADER => [
            'apikey: ' . $token,
            'Content-Type: application/json'
        ]
    ]);
    
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
    } elseif ($method === 'DELETE') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    
    curl_close($ch);
    
    if ($error) {
        throw new Exception("Erro cURL: $error");
    }
    
    if ($httpCode >= 400) {
        throw new Exception("Erro HTTP: $httpCode");
    }
    
    return json_decode($response, true);
}
?>

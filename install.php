<?php
/**
 * Ebyte Manager V2 - Instalador Melhorado
 * Este arquivo deve ser removido após a instalação
 */

session_start();

// Define a constante ANTES de incluir license-manager.php
define('_JEXEC', 1);

require_once 'license-manager.php';

$step = $_GET['step'] ?? 1;
$message = '';
$error = '';
$errorType = '';

// Verifica se já está instalado
if (LicenseManager::validate() && $step != 'complete') {
    header('Location: index.php');
    exit;
}

// Processa formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($step == 1) {
        $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
        $domain = $_POST['domain'] ?? '';
        $agree = isset($_POST['agree']);
        
        if (!$email) {
            $error = 'Por favor, digite um email válido.';
            $errorType = 'validation';
        } elseif (!$agree) {
            $error = 'Você precisa aceitar os termos de uso para continuar.';
            $errorType = 'validation';
        } else {
            // Primeiro verifica permissões
            $permissionCheck = checkPermissions();
            
            if (!$permissionCheck['success']) {
                $error = $permissionCheck['message'];
                $errorType = 'permissions';
            } else {
                // Tenta gerar a licença (com validação online)
                $licenseResult = LicenseManager::generate($email, $domain);
                
                if ($licenseResult) {
                    $_SESSION['install_email'] = $email;
                    header('Location: install.php?step=2');
                    exit;
                } else {
                    // Aqui precisamos diferenciar o tipo de erro
                    // Se chegou aqui, provavelmente é problema de licença não autorizada
                    $error = 'Licença não encontrada para este email.<br>';
                    $error .= '<small>Verifique se você está usando o mesmo email da compra ou entre em contato com o suporte.</small>';
                    $errorType = 'license';
                }
            }
        }
    }
}

// Função para verificar permissões
function checkPermissions() {
    $errors = [];
    
    // Verifica se pode escrever na pasta principal
    if (!is_writable(dirname(__FILE__))) {
        $errors[] = 'A pasta principal não tem permissão de escrita';
    }
    
    // Verifica se pode criar arquivos
    $testFile = dirname(__FILE__) . '/.test_permission';
    if (@file_put_contents($testFile, 'test') === false) {
        $errors[] = 'Não é possível criar arquivos na pasta';
    } else {
        @unlink($testFile);
    }
    
    // Verifica se o arquivo license-manager.php existe
    if (!file_exists('license-manager.php')) {
        $errors[] = 'Arquivo license-manager.php não encontrado';
    }
    
    if (!empty($errors)) {
        return [
            'success' => false,
            'message' => 'Problemas de permissão detectados:<br>• ' . implode('<br>• ', $errors)
        ];
    }
    
    return ['success' => true];
}

// Função para verificar conectividade
function checkConnectivity() {
    $ch = curl_init('https://license.ebyte.net.br/');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 5,
        CURLOPT_SSL_VERIFYPEER => false
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return $httpCode === 200;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalação - Ebyte Manager V2</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #7341ff;
        }
        body {
            background-color: #f8f9fa;
        }
        .install-container {
            max-width: 600px;
            margin: 50px auto;
        }
        .install-box {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .step-indicator {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        .step {
            flex: 1;
            text-align: center;
            padding: 10px;
            background: #e9ecef;
            margin: 0 5px;
            border-radius: 5px;
            color: #6c757d;
        }
        .step.active {
            background: var(--primary-color);
            color: white;
        }
        .step.completed {
            background: #28a745;
            color: white;
        }
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        .btn-primary:hover {
            background-color: #5a32cc;
            border-color: #5a32cc;
        }
        .logo {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo h1 {
            color: var(--primary-color);
            font-weight: bold;
        }
        code {
            background: #f8f9fa;
            padding: 2px 6px;
            border-radius: 3px;
            color: #e83e8c;
        }
        .terms-box {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            max-height: 200px;
            overflow-y: auto;
            font-size: 0.9rem;
        }
        .diagnostic-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
        .diagnostic-item:last-child {
            border-bottom: none;
        }
    </style>
</head>
<body>
    <div class="install-container">
        <div class="install-box">
            <div class="logo">
                <h1>Ebyte Manager V2</h1>
                <p class="text-muted">Instalação e Ativação</p>
            </div>

            <!-- Indicador de Passos -->
            <div class="step-indicator">
                <div class="step <?php echo $step >= 1 ? 'active' : ''; ?>">
                    1. Licença
                </div>
                <div class="step <?php echo $step >= 2 ? 'active' : ''; ?>">
                    2. Configuração
                </div>
                <div class="step <?php echo $step >= 3 ? 'active' : ''; ?>">
                    3. Conclusão
                </div>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-<?php echo $errorType === 'license' ? 'warning' : 'danger'; ?> alert-dismissible fade show">
                    <div class="d-flex align-items-start">
                        <div class="me-2">
                            <?php if ($errorType === 'license'): ?>
                                <i class="bi bi-exclamation-triangle-fill"></i>
                            <?php else: ?>
                                <i class="bi bi-x-circle-fill"></i>
                            <?php endif; ?>
                        </div>
                        <div class="flex-grow-1">
                            <?php echo $error; ?>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if ($step == 1): ?>
                <!-- Passo 1: Licença -->
                <h4 class="mb-4">Ativação da Licença</h4>
                
                <!-- Diagnóstico Rápido -->
                <div class="card mb-4">
                    <div class="card-body">
                        <h6 class="card-title mb-3">
                            <i class="bi bi-clipboard-check me-2"></i>Verificação do Sistema
                        </h6>
                        
                        <?php
                        $permCheck = checkPermissions();
                        $connCheck = checkConnectivity();
                        ?>
                        
                        <div class="diagnostic-item">
                            <span>Permissões de escrita</span>
                            <span class="badge bg-<?php echo $permCheck['success'] ? 'success' : 'danger'; ?>">
                                <?php echo $permCheck['success'] ? '✓ OK' : '✗ Erro'; ?>
                            </span>
                        </div>
                        
                        <div class="diagnostic-item">
                            <span>Servidor de licenças</span>
                            <span class="badge bg-<?php echo $connCheck ? 'success' : 'danger'; ?>">
                                <?php echo $connCheck ? '✓ Online' : '✗ Offline'; ?>
                            </span>
                        </div>
                        
                        <div class="diagnostic-item">
                            <span>Arquivo .env</span>
                            <span class="badge bg-<?php echo file_exists('.env') ? 'success' : 'warning'; ?>">
                                <?php echo file_exists('.env') ? '✓ Existe' : '⚠ Não encontrado'; ?>
                            </span>
                        </div>
                    </div>
                </div>
                
                <form method="POST">
                    <div class="mb-3">
                        <label for="email" class="form-label">E-mail de Compra</label>
                        <input type="email" class="form-control" id="email" name="email" 
                               placeholder="seu@email.com" required
                               value="<?php echo $_POST['email'] ?? ''; ?>">
                        <div class="form-text">Use exatamente o mesmo e-mail usado na compra</div>
                    </div>

                    <div class="mb-3">
                        <label for="domain" class="form-label">Domínio (Opcional)</label>
                        <input type="text" class="form-control" id="domain" name="domain" 
                               placeholder="exemplo.com.br"
                               value="<?php echo $_POST['domain'] ?? $_SERVER['HTTP_HOST'] ?? ''; ?>">
                        <div class="form-text">Deixe em branco para usar em qualquer domínio</div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Termos de Uso</label>
                        <div class="terms-box mb-2">
                            <strong>LICENÇA DE USO - EBYTE MANAGER V2</strong><br><br>
                            1. Esta licença permite o uso do software em projetos próprios.<br>
                            2. É PROIBIDA a revenda, redistribuição ou compartilhamento do código.<br>
                            3. Modificações são permitidas apenas para uso próprio.<br>
                            4. O suporte técnico é válido por 6 meses após a compra.<br>
                            5. Atualizações de segurança serão fornecidas por 1 ano.<br>
                            6. Violações dos termos resultarão em cancelamento da licença.<br><br>
                            Ao continuar, você concorda com todos os termos acima.
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="agree" id="agree" required>
                            <label class="form-check-label" for="agree">
                                Li e concordo com os termos de uso
                            </label>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">
                        Ativar Licença
                    </button>
                </form>
                
                <div class="alert alert-info mt-4 mb-0" role="alert">
                    <h6 class="alert-heading">
                        <i class="bi bi-info-circle me-2"></i>Problemas com a ativação?
                    </h6>
                    <ol class="mb-0 small">
                        <li>Certifique-se de usar o email exato da compra</li>
                        <li>Verifique se recebeu a confirmação de pagamento</li>
                        <li>Se comprou há pouco tempo, aguarde alguns minutos</li>
                        <li>Em caso de dúvidas, contate o suporte</li>
                    </ol>
                </div>

            <?php elseif ($step == 2): ?>
                <!-- Passo 2: Configuração -->
                <h4 class="mb-4">Verificando Configurações</h4>
                
                <div class="mb-4">
                    <?php
                    $checks = [
                        'PHP >= 7.4' => version_compare(PHP_VERSION, '7.4.0', '>='),
                        'Extensão cURL' => extension_loaded('curl'),
                        'Permissão de escrita' => is_writable(__DIR__),
                        'Arquivo .env' => file_exists('.env'),
                        'Licença gerada' => LicenseManager::validate()
                    ];
                    
                    $allOk = true;
                    foreach ($checks as $check => $status): 
                        if (!$status) $allOk = false;
                    ?>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span><?php echo $check; ?></span>
                            <span class="badge bg-<?php echo $status ? 'success' : 'danger'; ?>">
                                <?php echo $status ? '✓ OK' : '✗ Erro'; ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>

                <?php if (!$checks['Arquivo .env']): ?>
                    <div class="alert alert-warning">
                        <strong>Atenção!</strong> O arquivo <code>.env</code> não foi encontrado.
                        <br>Copie o arquivo <code>.env.example</code> para <code>.env</code> e configure suas credenciais.
                    </div>
                <?php endif; ?>

                <?php if ($allOk): ?>
                    <div class="alert alert-success">
                        <strong>Tudo certo!</strong> O sistema está pronto para uso.
                    </div>
                    <a href="install.php?step=3" class="btn btn-primary w-100">
                        Finalizar Instalação
                    </a>
                <?php else: ?>
                    <div class="alert alert-danger">
                        <strong>Corrija os erros acima antes de continuar.</strong>
                    </div>
                    <button class="btn btn-secondary w-100" onclick="location.reload()">
                        Verificar Novamente
                    </button>
                <?php endif; ?>

            <?php elseif ($step == 3): ?>
                <!-- Passo 3: Conclusão -->
                <div class="text-center">
                    <div class="mb-4">
                        <div class="text-success" style="font-size: 4rem;">✓</div>
                    </div>
                    
                    <h4 class="mb-4">Instalação Concluída!</h4>
                    
                    <?php 
                    $licenseInfo = LicenseManager::getInfo();
                    if ($licenseInfo): 
                    ?>
                        <div class="alert alert-info">
                            <strong>Licenciado para:</strong> <?php echo $licenseInfo['email_display']; ?><br>
                            <?php if (!empty($licenseInfo['domain'])): ?>
                                <strong>Domínio:</strong> <?php echo $licenseInfo['domain']; ?><br>
                            <?php endif; ?>
                            <strong>Versão:</strong> <?php echo $licenseInfo['version']; ?>
                        </div>
                    <?php endif; ?>

                    <div class="alert alert-warning">
                        <strong>⚠️ IMPORTANTE:</strong><br>
                        Por segurança, DELETE o arquivo <code>install.php</code> agora!
                    </div>

                    <div class="d-grid gap-2">
                        <a href="index.php" class="btn btn-primary">
                            Acessar o Sistema
                        </a>
                        <button class="btn btn-danger" onclick="deleteInstaller()">
                            Deletar install.php (Recomendado)
                        </button>
                    </div>

                    <hr class="my-4">

                    <div class="text-muted small">
                        <p><strong>Próximos passos:</strong></p>
                        <ol class="text-start">
                            <li>Configure o arquivo <code>.env</code> com suas credenciais da API</li>
                            <li>Acesse o sistema e conecte sua instância WhatsApp</li>
                            <li>Em caso de dúvidas, consulte o README.md</li>
                        </ol>
                    </div>
                </div>

                <script>
                function deleteInstaller() {
                    if (confirm('Deletar o arquivo install.php? Esta ação não pode ser desfeita.')) {
                        window.location.href = 'install.php?step=delete';
                    }
                }
                </script>

            <?php elseif ($step == 'delete'): ?>
                <?php
                // Tenta deletar o próprio arquivo
                if (unlink(__FILE__)) {
                    echo '<div class="alert alert-success">Arquivo install.php removido com sucesso!</div>';
                    echo '<meta http-equiv="refresh" content="2;url=index.php">';
                } else {
                    echo '<div class="alert alert-danger">Erro ao remover install.php. Delete manualmente.</div>';
                }
                ?>
            <?php endif; ?>

            <div class="text-center mt-4">
                <small class="text-muted">
                    &copy; 2024 Ebyte Soluções - Suporte: 
                    <a href="https://wa.me/5511963918906" target="_blank">WhatsApp</a> | 
                    <a href="mailto:contato@ebyte.net.br">E-mail</a>
                </small>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
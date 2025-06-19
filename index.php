<?php
require_once 'config.php';

// Gera token CSRF
$csrf_token = generateCSRFToken();

// Log de acesso
writeLog("Página acessada - IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
?>
<!DOCTYPE html>
<html lang="pt-BR" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo $csrf_token; ?>">
    <link rel="icon" type="image/png" sizes="32x32" href="<?php echo FAVICON; ?>">
    <title><?php echo APP_NAME; ?> - Gerencie seu WhatsApp</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: <?php echo PRIMARY_COLOR; ?>;
            --primary-dark: <?php echo PRIMARY_COLOR; ?>dd;
            --primary-light: <?php echo PRIMARY_COLOR; ?>20;
            --secondary-color: #0ea5e9;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
            --dark-color: #1e293b;
            --gray-color: #64748b;
            --light-gray: #f1f5f9;
            --white: #ffffff;
            
            --gradient-primary: linear-gradient(135deg, <?php echo PRIMARY_COLOR; ?> 0%, #0ea5e9 100%);
            --gradient-secondary: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --gradient-success: linear-gradient(135deg, #10b981 0%, #059669 100%);
            
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
            --shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
            
            --transition-all: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        [data-bs-theme="dark"] {
            --dark-color: #f8fafc;
            --gray-color: #cbd5e1;
            --light-gray: #1e293b;
            --white: #0f172a;
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.3), 0 2px 4px -2px rgb(0 0 0 / 0.3);
        }
        
        * {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        
        body {
            background-color: var(--light-gray);
            color: var(--dark-color);
            line-height: 1.6;
        }
        
        /* Navbar Styles */
        .navbar-custom {
            background: var(--white);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(0,0,0,0.08);
            padding: 1rem 0;
            transition: var(--transition-all);
            box-shadow: var(--shadow-sm);
        }
        
        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        /* Card Styles */
        .card-custom {
            background: var(--white);
            border-radius: 1rem;
            border: none;
            box-shadow: var(--shadow-md);
            overflow: hidden;
            transition: var(--transition-all);
        }
        
        .card-custom:hover {
            box-shadow: var(--shadow-lg);
            transform: translateY(-2px);
        }
        
        /* Button Styles */
        .btn-gradient {
            background: var(--gradient-primary);
            color: white;
            border: none;
            padding: 0.75rem 2rem;
            font-weight: 600;
            border-radius: 0.75rem;
            transition: var(--transition-all);
            position: relative;
            overflow: hidden;
        }
        
        .btn-gradient:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px -5px rgba(124, 58, 237, 0.3);
            color: white;
        }
        
        .btn-gradient::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }
        
        .btn-gradient:hover::before {
            left: 100%;
        }
        
        .btn-outline-gradient {
            background: transparent;
            color: var(--primary-color);
            border: 2px solid var(--primary-color);
            padding: 0.75rem 2rem;
            font-weight: 600;
            border-radius: 0.75rem;
            transition: var(--transition-all);
        }
        
        .btn-outline-gradient:hover {
            background: var(--gradient-primary);
            color: white;
            border-color: transparent;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px -5px rgba(124, 58, 237, 0.3);
        }
        
        /* Form Styles */
        .form-control-custom {
            background: var(--light-gray);
            border: 2px solid transparent;
            border-radius: 0.75rem;
            padding: 0.875rem 1.25rem;
            font-size: 1rem;
            transition: var(--transition-all);
        }
        
        .form-control-custom:focus {
            background: var(--white);
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(124, 58, 237, 0.1);
            outline: none;
        }
        
        .form-label-custom {
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        /* Hero Section */
        .hero-section {
            background: var(--gradient-primary);
            min-height: 100vh;
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
        }
        
        .hero-pattern {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            opacity: 0.1;
            background-image: 
                radial-gradient(circle at 20% 80%, white 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, white 0%, transparent 50%),
                radial-gradient(circle at 40% 40%, white 0%, transparent 50%);
        }
        
        .floating-shape {
            position: absolute;
            border-radius: 50%;
            background: rgba(255,255,255,0.1);
            animation: float 6s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
        }
        
        /* Login Form */
        .login-container {
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(20px);
            border-radius: 1.5rem;
            padding: 3rem;
            box-shadow: var(--shadow-xl);
            max-width: 480px;
            width: 100%;
            margin: 0 auto;
        }
        
        /* Dashboard Styles */
        .dashboard-container {
            min-height: 100vh;
            padding-top: 100px;
            padding-bottom: 2rem;
        }
        
        .stats-card {
            background: var(--white);
            border-radius: 1rem;
            padding: 1.5rem;
            border: none;
            box-shadow: var(--shadow-md);
            transition: var(--transition-all);
            position: relative;
            overflow: hidden;
        }
        
        .stats-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
        }
        
        .stats-card .icon-box {
            width: 60px;
            height: 60px;
            border-radius: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }
        
        .stats-card.primary .icon-box {
            background: var(--primary-light);
            color: var(--primary-color);
        }
        
        .stats-card.success .icon-box {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success-color);
        }
        
        .stats-card.warning .icon-box {
            background: rgba(245, 158, 11, 0.1);
            color: var(--warning-color);
        }
        
        .stats-card.info .icon-box {
            background: rgba(14, 165, 233, 0.1);
            color: var(--secondary-color);
        }
        
        .stats-number {
            font-size: 2.5rem;
            font-weight: 700;
            line-height: 1;
            margin-bottom: 0.5rem;
        }
        
        .stats-label {
            color: var(--gray-color);
            font-size: 0.875rem;
            font-weight: 500;
        }
        
        /* Profile Card */
        .profile-card {
            background: var(--white);
            border-radius: 1.5rem;
            padding: 2rem;
            box-shadow: var(--shadow-md);
            text-align: center;
        }
        
        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            margin: 0 auto 1.5rem;
            border: 4px solid var(--primary-light);
            box-shadow: var(--shadow-lg);
        }
        
        .profile-name {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .profile-number {
            color: var(--gray-color);
            font-size: 1.125rem;
            margin-bottom: 1.5rem;
        }
        
        .profile-status {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: var(--success-color);
            color: white;
            padding: 0.5rem 1.5rem;
            border-radius: 2rem;
            font-weight: 600;
            font-size: 0.875rem;
        }
        
        /* Action Buttons */
        .action-card {
            background: var(--white);
            border-radius: 1rem;
            padding: 1.5rem;
            box-shadow: var(--shadow-md);
            transition: var(--transition-all);
            cursor: pointer;
            border: 2px solid transparent;
            text-align: center;
        }
        
        .action-card:hover {
            border-color: var(--primary-color);
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
        }
        
        .action-card .icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .action-card .title {
            font-weight: 600;
            font-size: 1.125rem;
            margin-bottom: 0.5rem;
        }
        
        .action-card .description {
            color: var(--gray-color);
            font-size: 0.875rem;
        }
        
        /* QR Code Container */
        .qr-container {
            background: white;
            border-radius: 1.5rem;
            padding: 2rem;
            box-shadow: var(--shadow-lg);
            display: inline-block;
        }
        
        /* Loading Animation */
        .loading-dots {
            display: inline-flex;
            gap: 0.25rem;
        }
        
        .loading-dots span {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--primary-color);
            animation: bounce 1.4s infinite ease-in-out both;
        }
        
        .loading-dots span:nth-child(1) { animation-delay: -0.32s; }
        .loading-dots span:nth-child(2) { animation-delay: -0.16s; }
        
        @keyframes bounce {
            0%, 80%, 100% { transform: scale(0); }
            40% { transform: scale(1); }
        }
        
        /* Toast Custom */
        .toast-custom {
            background: var(--white);
            border-radius: 0.75rem;
            box-shadow: var(--shadow-lg);
            border: none;
        }
        
        /* Dark Mode Adjustments */
        [data-bs-theme="dark"] .card-custom,
        [data-bs-theme="dark"] .stats-card,
        [data-bs-theme="dark"] .profile-card,
        [data-bs-theme="dark"] .action-card,
        [data-bs-theme="dark"] .login-container {
            background: #1e293b;
        }
        
        [data-bs-theme="dark"] .form-control-custom {
            background: #0f172a;
            color: #f8fafc;
        }
        
        [data-bs-theme="dark"] .form-control-custom:focus {
            background: #1e293b;
        }
        
        [data-bs-theme="dark"] .navbar-custom {
            background: #0f172a;
            border-bottom-color: rgba(255,255,255,0.1);
        }
        
        /* Logs Container */
        .logs-container {
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 400px;
            max-width: 90vw;
            background: var(--white);
            border-radius: 1rem;
            box-shadow: var(--shadow-xl);
            z-index: 1000;
            transition: var(--transition-all);
        }
        
        [data-bs-theme="dark"] .logs-container {
            background: #1e293b;
        }
        
        .logs-header {
            padding: 1rem;
            border-bottom: 1px solid rgba(0,0,0,0.1);
            display: flex;
            justify-content: between;
            align-items: center;
        }
        
        [data-bs-theme="dark"] .logs-header {
            border-bottom-color: rgba(255,255,255,0.1);
        }
        
        .logs-body {
            max-height: 300px;
            overflow-y: auto;
            padding: 1rem;
            font-family: 'Consolas', 'Monaco', monospace;
            font-size: 0.875rem;
        }
        
        .log-entry {
            padding: 0.25rem 0;
            border-bottom: 1px solid rgba(0,0,0,0.05);
        }
        
        [data-bs-theme="dark"] .log-entry {
            border-bottom-color: rgba(255,255,255,0.05);
        }
        
        /* Logs Badge */
        #log-badge {
            display: none;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .login-container {
                padding: 2rem;
                margin: 1rem;
            }
            
            .dashboard-container {
                padding-top: 80px;
            }
            
            .stats-number {
                font-size: 2rem;
            }
            
            .profile-avatar {
                width: 80px;
                height: 80px;
            }
        }
        
        /* Animations */
        .fade-in {
            animation: fadeIn 0.5s ease-in-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .slide-in {
            animation: slideIn 0.5s ease-in-out;
        }
        
        @keyframes slideIn {
            from { transform: translateX(-100%); }
            to { transform: translateX(0); }
        }
        
        /* Success Animation */
        .success-checkmark {
            width: 80px;
            height: 80px;
            margin: 0 auto;
        }
        
        .success-checkmark .check-icon {
            width: 80px;
            height: 80px;
            position: relative;
            border-radius: 50%;
            box-sizing: content-box;
            border: 4px solid #10b981;
        }
        
        .success-checkmark .check-icon::before {
            top: 3px;
            left: -2px;
            width: 30px;
            transform-origin: 100% 50%;
            border-radius: 100px 0 0 100px;
        }
        
        .success-checkmark .check-icon::after {
            top: 0;
            left: 30px;
            width: 60px;
            transform-origin: 0 50%;
            border-radius: 0 100px 100px 0;
            animation: rotate-circle 4.25s ease-in;
        }
        
        .success-checkmark .check-icon::before,
        .success-checkmark .check-icon::after {
            content: '';
            height: 100px;
            position: absolute;
            background: var(--white);
            transform: rotate(-45deg);
        }
        
        .success-checkmark .check-icon .icon-line {
            height: 5px;
            background-color: #10b981;
            display: block;
            border-radius: 2px;
            position: absolute;
            z-index: 10;
        }
        
        .success-checkmark .check-icon .icon-line.line-tip {
            top: 46px;
            left: 14px;
            width: 25px;
            transform: rotate(45deg);
            animation: icon-line-tip 0.75s;
        }
        
        .success-checkmark .check-icon .icon-line.line-long {
            top: 38px;
            right: 8px;
            width: 47px;
            transform: rotate(-45deg);
            animation: icon-line-long 0.75s;
        }
        
        .success-checkmark .check-icon .icon-circle {
            top: -4px;
            left: -4px;
            z-index: 10;
            width: 80px;
            height: 80px;
            border-radius: 50%;
            position: absolute;
            box-sizing: content-box;
            border: 4px solid rgba(16, 185, 129, 0.5);
        }
        
        .success-checkmark .check-icon .icon-fix {
            top: 8px;
            width: 5px;
            left: 26px;
            z-index: 1;
            height: 85px;
            position: absolute;
            transform: rotate(-45deg);
            background-color: var(--white);
        }
        
        @keyframes rotate-circle {
            0% { transform: rotate(-45deg); }
            5% { transform: rotate(-45deg); }
            12% { transform: rotate(-405deg); }
            100% { transform: rotate(-405deg); }
        }
        
        @keyframes icon-line-tip {
            0% { width: 0; left: 1px; top: 19px; }
            54% { width: 0; left: 1px; top: 19px; }
            70% { width: 50px; left: -8px; top: 37px; }
            84% { width: 17px; left: 21px; top: 48px; }
            100% { width: 25px; left: 14px; top: 46px; }
        }
        
        @keyframes icon-line-long {
            0% { width: 0; right: 46px; top: 54px; }
            65% { width: 0; right: 46px; top: 54px; }
            84% { width: 55px; right: 0px; top: 35px; }
            100% { width: 47px; right: 8px; top: 38px; }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-custom fixed-top">
        <div class="container">
            <a class="navbar-brand" href="#">
                <img src="<?php echo LOGO_LIGHT; ?>" alt="Logo" height="35" class="d-inline-block align-text-top me-2 d-dark-none">
                <img src="<?php echo LOGO_DARK; ?>" alt="Logo" height="35" class="d-inline-block align-text-top me-2 d-none d-dark-block">
                <?php echo APP_NAME; ?>
            </a>
            <div class="ms-auto d-flex align-items-center gap-3">
                <button class="btn btn-sm btn-light rounded-pill px-3" id="theme-toggle">
                    <i class="bi bi-moon-fill" id="theme-icon"></i>
                </button>
                <button class="btn btn-sm btn-light rounded-pill px-3 position-relative" id="logs-toggle">
                    <i class="bi bi-terminal"></i>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="log-badge">
                        0
                    </span>
                </button>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div id="app">
        <!-- Login Screen -->
        <div id="login-screen" class="hero-section">
            <div class="hero-pattern"></div>
            
            <!-- Floating Shapes -->
            <div class="floating-shape" style="width: 100px; height: 100px; top: 10%; left: 10%; animation-delay: 0s;"></div>
            <div class="floating-shape" style="width: 150px; height: 150px; top: 70%; right: 10%; animation-delay: 2s;"></div>
            <div class="floating-shape" style="width: 80px; height: 80px; bottom: 20%; left: 30%; animation-delay: 4s;"></div>
            
            <div class="container">
                <div class="row align-items-center min-vh-100 py-5">
                    <div class="col-lg-6 text-white mb-5 mb-lg-0">
                        <h1 class="display-4 fw-bold mb-4 fade-in">
                            Gerencie seu WhatsApp Business com facilidade
                        </h1>
                        <p class="lead mb-4 fade-in" style="animation-delay: 0.2s;">
                            Envie mensagens, gerencie contatos e automatize sua comunicação em uma plataforma segura e intuitiva.
                        </p>
                        <div class="d-flex gap-3 fade-in" style="animation-delay: 0.4s;">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-shield-check fs-4 me-2"></i>
                                <span>100% Seguro</span>
                            </div>
                            <div class="d-flex align-items-center">
                                <i class="bi bi-lightning-charge fs-4 me-2"></i>
                                <span>Ultra Rápido</span>
                            </div>
                            <div class="d-flex align-items-center">
                                <i class="bi bi-headset fs-4 me-2"></i>
                                <span>Suporte 24/7</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-6">
                        <div class="login-container fade-in" style="animation-delay: 0.6s;">
                            <div class="text-center mb-4">
                                <h2 class="fw-bold">Bem-vindo de volta!</h2>
                                <p class="text-muted">Entre com suas credenciais para continuar</p>
                            </div>
                            
                            <form id="connection-form">
                                <div class="mb-4">
                                    <label for="instance" class="form-label-custom">
                                        <i class="bi bi-building"></i>
                                        Identificador da Conta
                                    </label>
                                    <input type="text" 
                                           class="form-control form-control-custom form-control-lg" 
                                           id="instance" 
                                           placeholder="Digite seu identificador"
                                           required>
                                    <small class="text-muted">
                                        Use o identificador recebido no seu e-mail de boas-vindas
                                    </small>
                                </div>
                                
                                <div class="mb-4">
                                    <label for="token" class="form-label-custom">
                                        <i class="bi bi-key-fill"></i>
                                        Chave de Acesso
                                    </label>
                                    <div class="position-relative">
                                        <input type="password" 
                                               class="form-control form-control-custom form-control-lg" 
                                               id="token" 
                                               placeholder="Digite sua chave de acesso"
                                               required>
                                        <button type="button" 
                                                class="btn btn-link position-absolute end-0 top-50 translate-middle-y me-3 text-decoration-none"
                                                id="toggle-token-visibility">
                                            <i class="bi bi-eye text-muted"></i>
                                        </button>
                                    </div>
                                    <small class="text-muted">
                                        A chave foi enviada junto com seu identificador
                                    </small>
                                </div>
                                
                                <button type="submit" class="btn btn-gradient btn-lg w-100 mb-4">
                                    <i class="bi bi-box-arrow-in-right me-2"></i>
                                    Acessar Painel
                                </button>
                                
                                <div class="text-center">
                                    <p class="text-muted mb-0">
                                        Não recebeu suas credenciais?
                                    </p>
                                    <a href="#" class="text-decoration-none text-primary fw-semibold" onclick="showSupport()">
                                        Entre em contato com o suporte
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- QR Code Screen -->
        <div id="qr-screen" class="d-none">
            <div class="hero-section">
                <div class="hero-pattern"></div>
                <div class="container">
                    <div class="row justify-content-center align-items-center min-vh-100">
                        <div class="col-lg-6 text-center">
                            <div class="card-custom p-5 fade-in">
                                <h2 class="fw-bold mb-4">Configure seu WhatsApp</h2>
                                <p class="text-muted mb-5">
                                    Escaneie o código QR abaixo com seu WhatsApp para conectar
                                </p>
                                
                                <div class="qr-container mx-auto mb-4">
                                    <div id="loading-spinner" class="py-5">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Carregando...</span>
                                        </div>
                                        <p class="mt-3 text-muted">Gerando código QR...</p>
                                    </div>
                                    <canvas id="qrcodeCanvas" style="display: none;"></canvas>
                                </div>
                                
                                <div class="alert alert-info d-flex align-items-center">
                                    <i class="bi bi-info-circle me-2"></i>
                                    <div class="text-start">
                                        <strong>Como escanear:</strong>
                                        <ol class="mb-0 mt-2 ps-3">
                                            <li>Abra o WhatsApp no seu celular</li>
                                            <li>Toque em Menu ou Configurações</li>
                                            <li>Selecione "Dispositivos conectados"</li>
                                            <li>Toque em "Conectar dispositivo"</li>
                                            <li>Aponte o celular para esta tela</li>
                                        </ol>
                                    </div>
                                </div>
                                
                                <button class="btn btn-outline-gradient" onclick="whatsappManager.showNewInstance()">
                                    <i class="bi bi-arrow-left me-2"></i>
                                    Voltar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dashboard Screen -->
        <div id="dashboard-screen" class="d-none">
            <div class="dashboard-container">
                <div class="container">
                    <!-- Welcome Section -->
                    <div class="row mb-5 fade-in">
                        <div class="col-12">
                            <h1 class="fw-bold mb-2">Painel de Controle</h1>
                            <p class="text-muted lead">
                                Gerencie suas conversas e mensagens do WhatsApp Business
                            </p>
                        </div>
                    </div>
                    
                    <!-- Profile and Stats -->
                    <div class="row g-4 mb-5">
                        <!-- Profile Card -->
                        <div class="col-lg-4 fade-in">
                            <div class="profile-card h-100" id="profile-info">
                                <!-- Preenchido dinamicamente -->
                            </div>
                        </div>
                        
                        <!-- Stats Cards -->
                        <div class="col-lg-8">
                            <div class="row g-4" id="stats-container">
                                <!-- Preenchido dinamicamente -->
                            </div>
                        </div>
                    </div>
                    
                    <!-- Action Cards -->
                    <div class="row g-4 mb-5">
                        <div class="col-12">
                            <h3 class="fw-bold mb-4">Ações Rápidas</h3>
                        </div>
                        
                        <div class="col-md-6 col-lg-3 fade-in">
                            <div class="action-card h-100" onclick="modalManager.showSendMessage(whatsappManager.currentInstance)">
                                <div class="icon">
                                    <i class="bi bi-send"></i>
                                </div>
                                <h5 class="title">Enviar Mensagem</h5>
                                <p class="description">
                                    Envie mensagens de texto ou mídia para seus contatos
                                </p>
                            </div>
                        </div>
                        
                        <div class="col-md-6 col-lg-3 fade-in" style="animation-delay: 0.1s;">
                            <div class="action-card h-100" onclick="whatsappManager.refreshInstance(whatsappManager.currentInstance)">
                                <div class="icon">
                                    <i class="bi bi-arrow-clockwise"></i>
                                </div>
                                <h5 class="title">Atualizar Dados</h5>
                                <p class="description">
                                    Sincronize as informações mais recentes
                                </p>
                            </div>
                        </div>
                        
                        <div class="col-md-6 col-lg-3 fade-in" style="animation-delay: 0.2s;">
                            <div class="action-card h-100" onclick="showCredentials()">
                                <div class="icon">
                                    <i class="bi bi-shield-lock"></i>
                                </div>
                                <h5 class="title">Credenciais</h5>
                                <p class="description">
                                    Visualize suas informações de acesso
                                </p>
                            </div>
                        </div>
                        
                        <div class="col-md-6 col-lg-3 fade-in" style="animation-delay: 0.3s;">
                            <div class="action-card h-100" onclick="showSupport()">
                                <div class="icon">
                                    <i class="bi bi-headset"></i>
                                </div>
                                <h5 class="title">Suporte</h5>
                                <p class="description">
                                    Precisa de ajuda? Fale conosco
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Instance Details -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card-custom p-4" id="instance-details">
                                <!-- Preenchido dinamicamente -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Error Screen -->
        <div id="error-screen" class="d-none">
            <div class="hero-section">
                <div class="container">
                    <div class="row justify-content-center align-items-center min-vh-100">
                        <div class="col-lg-6 text-center">
                            <div class="card-custom p-5">
                                <div class="text-danger mb-4">
                                    <i class="bi bi-exclamation-circle" style="font-size: 4rem;"></i>
                                </div>
                                <h2 class="fw-bold mb-3">Ops! Algo deu errado</h2>
                                <p class="text-muted mb-4" id="error-message">
                                    Não foi possível conectar ao sistema
                                </p>
                                <div class="d-flex gap-3 justify-content-center">
                                    <button class="btn btn-gradient" onclick="whatsappManager.showNewInstance()">
                                        <i class="bi bi-arrow-clockwise me-2"></i>
                                        Tentar Novamente
                                    </button>
                                    <button class="btn btn-outline-gradient" onclick="showSupport()">
                                        <i class="bi bi-headset me-2"></i>
                                        Contatar Suporte
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Logs Container -->
    <div id="logs-container" class="logs-container d-none">
        <div class="logs-header">
            <h6 class="mb-0 fw-bold">
                <i class="bi bi-terminal me-2"></i>Logs do Sistema
            </h6>
            <div class="ms-auto d-flex gap-2">
                <button class="btn btn-sm btn-outline-secondary" onclick="whatsappManager.clearLogs()">
                    <i class="bi bi-trash"></i>
                </button>
                <button class="btn btn-sm btn-close" onclick="whatsappManager.toggleLogs()"></button>
            </div>
        </div>
        <div class="logs-body" id="logs">
            <div class="text-info log-entry">
                <small><i class="bi bi-info-circle me-1"></i>[Sistema] Aguardando ações...</small>
            </div>
        </div>
    </div>

    <!-- Modal Credenciais e Suporte -->
    <div class="modal fade" id="supportModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-headset text-primary me-2"></i>
                        Central de Ajuda
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <!-- Tabs -->
                    <ul class="nav nav-tabs nav-fill mb-4" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#credentials-tab" type="button">
                                <i class="bi bi-key me-2"></i>Credenciais
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#support-tab" type="button">
                                <i class="bi bi-headset me-2"></i>Suporte
                            </button>
                        </li>
                    </ul>
                    
                    <!-- Tab Content -->
                    <div class="tab-content">
                        <!-- Credenciais Tab -->
                        <div class="tab-pane fade show active" id="credentials-tab">
                            <div class="text-center mb-4">
                                <div class="icon-box mx-auto mb-3" style="width: 80px; height: 80px; background: var(--primary-light); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                    <i class="bi bi-shield-lock" style="font-size: 2rem; color: var(--primary-color);"></i>
                                </div>
                                <h6 class="fw-bold">Suas Credenciais de Acesso</h6>
                                <p class="text-muted small">Guarde estas informações em local seguro</p>
                            </div>
                            
                            <div class="bg-light rounded-3 p-3 mb-3">
                                <label class="text-muted small d-block mb-1">Identificador da Conta</label>
                                <div class="d-flex align-items-center gap-2">
                                    <code class="flex-grow-1" id="modal-instance">-</code>
                                    <button class="btn btn-sm btn-outline-secondary" onclick="copyCredential('instance')">
                                        <i class="bi bi-clipboard"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="bg-light rounded-3 p-3 mb-4">
                                <label class="text-muted small d-block mb-1">Chave de Acesso</label>
                                <div class="d-flex align-items-center gap-2">
                                    <input type="password" class="form-control form-control-sm border-0 bg-transparent" id="modal-token" readonly value="">
                                    <button class="btn btn-sm btn-outline-secondary" onclick="toggleTokenModal()">
                                        <i class="bi bi-eye" id="modal-token-icon"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-secondary" onclick="copyCredential('token')">
                                        <i class="bi bi-clipboard"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="alert alert-warning">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                <strong>Importante:</strong> Nunca compartilhe suas credenciais com terceiros. 
                                Elas são únicas e garantem a segurança da sua conta.
                            </div>
                        </div>
                        
                        <!-- Suporte Tab -->
                        <div class="tab-pane fade" id="support-tab">
                            <div class="text-center mb-4">
                                <div class="icon-box mx-auto mb-3" style="width: 80px; height: 80px; background: var(--success-color)20; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                    <i class="bi bi-headset" style="font-size: 2rem; color: var(--success-color);"></i>
                                </div>
                                <h6 class="fw-bold">Estamos Aqui Para Ajudar!</h6>
                                <p class="text-muted small">Escolha o melhor canal para falar conosco</p>
                            </div>
                            
                            <div class="d-grid gap-3">
                                <a href="https://wa.me/<?php echo preg_replace('/\D/', '', $_ENV['SUPPORT_WHATSAPP'] ?? '5511999999999'); ?>" 
                                   target="_blank" 
                                   class="btn btn-success btn-lg">
                                    <i class="bi bi-whatsapp me-2"></i>
                                    WhatsApp: <?php echo $_ENV['SUPPORT_WHATSAPP'] ?? '(11) 99999-9999'; ?>
                                </a>
                                
                                <a href="mailto:<?php echo $_ENV['SUPPORT_EMAIL'] ?? 'suporte@seudominio.com'; ?>" 
                                   class="btn btn-primary btn-lg">
                                    <i class="bi bi-envelope me-2"></i>
                                    E-mail: <?php echo $_ENV['SUPPORT_EMAIL'] ?? 'suporte@seudominio.com'; ?>
                                </a>
                                
                                <?php if (isset($_ENV['SUPPORT_PHONE'])): ?>
                                <a href="tel:<?php echo preg_replace('/\D/', '', $_ENV['SUPPORT_PHONE']); ?>" 
                                   class="btn btn-outline-secondary btn-lg">
                                    <i class="bi bi-telephone me-2"></i>
                                    Telefone: <?php echo $_ENV['SUPPORT_PHONE']; ?>
                                </a>
                                <?php endif; ?>
                            </div>
                            
                            <hr class="my-4">
                            
                            <div class="text-center">
                                <h6 class="fw-semibold mb-3">Horário de Atendimento</h6>
                                <p class="mb-1"><?php echo $_ENV['SUPPORT_HOURS'] ?? 'Segunda a Sexta: 9h às 18h'; ?></p>
                                <p class="text-muted small"><?php echo $_ENV['SUPPORT_TIMEZONE'] ?? 'Horário de Brasília (GMT-3)'; ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Enviar Mensagem -->
    <div class="modal fade" id="sendMessageModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-send text-primary me-2"></i>
                        Nova Mensagem
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="send-message-form">
                    <div class="modal-body">
                        <!-- Números -->
                        <div class="mb-4">
                            <label class="form-label-custom">
                                <i class="bi bi-phone"></i>
                                Destinatários
                            </label>
                            <textarea class="form-control form-control-custom" 
                                      id="message-numbers" 
                                      rows="3" 
                                      placeholder="Digite os números, um por linha&#10;Ex: 5511999999999"
                                      required></textarea>
                            <small class="text-muted">
                                Digite apenas números com código do país + DDD + número
                            </small>
                        </div>

                        <!-- Mensagem -->
                        <div class="mb-4">
                            <label class="form-label-custom">
                                <i class="bi bi-chat-text"></i>
                                Mensagem
                            </label>
                            <div class="btn-group btn-group-sm mb-2" role="group">
                                <button type="button" class="btn btn-outline-secondary" onclick="modalManager.setTextStyle('bold')" title="Negrito">
                                    <i class="bi bi-type-bold"></i>
                                </button>
                                <button type="button" class="btn btn-outline-secondary" onclick="modalManager.setTextStyle('italic')" title="Itálico">
                                    <i class="bi bi-type-italic"></i>
                                </button>
                                <button type="button" class="btn btn-outline-secondary" onclick="modalManager.setTextStyle('strikethrough')" title="Tachado">
                                    <i class="bi bi-type-strikethrough"></i>
                                </button>
                                <button type="button" class="btn btn-outline-secondary" onclick="modalManager.setTextStyle('mono')" title="Monoespaçado">
                                    <i class="bi bi-code"></i>
                                </button>
                            </div>
                            <textarea class="form-control form-control-custom" 
                                      id="message-text" 
                                      rows="4" 
                                      placeholder="Digite sua mensagem aqui..."
                                      required></textarea>
                        </div>

                        <!-- Opções Avançadas -->
                        <div class="border rounded-3 p-3">
                            <h6 class="fw-semibold mb-3">
                                <i class="bi bi-gear me-2"></i>
                                Opções Avançadas
                            </h6>
                            
                            <!-- Anexar Mídia -->
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" id="attach-media">
                                <label class="form-check-label" for="attach-media">
                                    <i class="bi bi-paperclip me-1"></i>
                                    Anexar arquivo (imagem, vídeo, áudio ou PDF)
                                </label>
                            </div>
                            
                            <div id="media-upload" class="mb-3 d-none">
                                <input type="file" 
                                       class="form-control form-control-custom" 
                                       id="media-file" 
                                       accept="image/*,video/*,audio/*,application/pdf">
                                <small class="text-muted">Tamanho máximo: 10MB</small>
                            </div>

                            <!-- Simular Digitação -->
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" id="use-delay">
                                <label class="form-check-label" for="use-delay">
                                    <i class="bi bi-keyboard me-1"></i>
                                    Simular digitação antes do envio
                                </label>
                            </div>

                            <!-- Intervalo -->
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" id="use-random-interval" checked>
                                <label class="form-check-label" for="use-random-interval">
                                    <i class="bi bi-clock me-1"></i>
                                    Intervalo aleatório entre mensagens
                                </label>
                            </div>

                            <div id="interval-config" class="row g-2 align-items-center ps-4">
                                <div class="col-auto">
                                    <label class="col-form-label">Entre</label>
                                </div>
                                <div class="col-auto">
                                    <input type="number" 
                                           class="form-control form-control-sm" 
                                           id="interval-min" 
                                           value="5" 
                                           min="1" 
                                           max="60" 
                                           style="width: 70px;">
                                </div>
                                <div class="col-auto">
                                    <label class="col-form-label">e</label>
                                </div>
                                <div class="col-auto">
                                    <input type="number" 
                                           class="form-control form-control-sm" 
                                           id="interval-max" 
                                           value="10" 
                                           min="1" 
                                           max="60" 
                                           style="width: 70px;">
                                </div>
                                <div class="col-auto">
                                    <label class="col-form-label">segundos</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">
                            Cancelar
                        </button>
                        <button type="submit" class="btn btn-gradient px-4">
                            <i class="bi bi-send me-2"></i>
                            Enviar Mensagens
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.1/build/qrcode.min.js"></script>
    
    <script>
        // Configurações globais
        const APP_CONFIG = {
            primaryColor: '<?php echo PRIMARY_COLOR; ?>',
            csrfToken: '<?php echo $csrf_token; ?>',
            apiUrl: '<?php echo API_URL; ?>'
        };

        // Toggle visibilidade do token
        document.getElementById('toggle-token-visibility')?.addEventListener('click', function() {
            const tokenInput = document.getElementById('token');
            const icon = this.querySelector('i');
            
            if (tokenInput.type === 'password') {
                tokenInput.type = 'text';
                icon.className = 'bi bi-eye-slash text-muted';
            } else {
                tokenInput.type = 'password';
                icon.className = 'bi bi-eye text-muted';
            }
        });

        // Funções auxiliares
        function showSupport() {
            const modal = new bootstrap.Modal(document.getElementById('supportModal'));
            // Ativa a tab de suporte
            const supportTab = document.querySelector('[data-bs-target="#support-tab"]');
            const credentialsTab = document.querySelector('[data-bs-target="#credentials-tab"]');
            
            supportTab.click();
            modal.show();
        }

        function showCredentials() {
            const instance = whatsappManager.currentInstance;
            const token = whatsappManager.getToken();
            
            if (instance && token) {
                // Preenche os campos do modal
                document.getElementById('modal-instance').textContent = instance;
                document.getElementById('modal-token').value = token;
                
                const modal = new bootstrap.Modal(document.getElementById('supportModal'));
                // Ativa a tab de credenciais
                const credentialsTab = document.querySelector('[data-bs-target="#credentials-tab"]');
                credentialsTab.click();
                modal.show();
            }
        }

        function toggleTokenModal() {
            const tokenInput = document.getElementById('modal-token');
            const icon = document.getElementById('modal-token-icon');
            
            if (tokenInput.type === 'password') {
                tokenInput.type = 'text';
                icon.className = 'bi bi-eye-slash';
            } else {
                tokenInput.type = 'password';
                icon.className = 'bi bi-eye';
            }
        }

        function copyCredential(type) {
            let text = '';
            let label = '';
            
            if (type === 'instance') {
                text = document.getElementById('modal-instance').textContent;
                label = 'Identificador';
            } else if (type === 'token') {
                text = document.getElementById('modal-token').value;
                label = 'Chave de acesso';
            }
            
            if (text) {
                navigator.clipboard.writeText(text).then(() => {
                    whatsappManager.showToast(`${label} copiado!`, 'success');
                }).catch(() => {
                    const textArea = document.createElement('textarea');
                    textArea.value = text;
                    textArea.style.position = 'fixed';
                    textArea.style.opacity = '0';
                    document.body.appendChild(textArea);
                    textArea.select();
                    try {
                        document.execCommand('copy');
                        whatsappManager.showToast(`${label} copiado!`, 'success');
                    } catch (err) {
                        whatsappManager.showToast('Erro ao copiar', 'error');
                    }
                    document.body.removeChild(textArea);
                });
            }
        }
    </script>
    
    <!-- Scripts da aplicação -->
    <script src="assets/js/app-v4.js"></script>
    <script src="assets/js/modal-v3.js"></script>
</body>
</html>
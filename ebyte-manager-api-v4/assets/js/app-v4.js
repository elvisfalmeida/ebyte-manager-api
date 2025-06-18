// WhatsApp Manager V4 - Design SaaS
class WhatsAppManager {
    constructor() {
        this.currentInstance = null;
        this.currentToken = null;
        this.checkInterval = null;
        this.logCount = 0;
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.loadTheme();
        this.loadSession();
        this.addLog('Sistema iniciado', 'info');
    }

    setupEventListeners() {
        // Form de conexão
        document.getElementById('connection-form')?.addEventListener('submit', (e) => {
            e.preventDefault();
            this.handleConnect();
        });

        // Toggle tema
        document.getElementById('theme-toggle')?.addEventListener('click', () => {
            this.toggleTheme();
        });

        // Toggle logs
        document.getElementById('logs-toggle')?.addEventListener('click', () => {
            this.toggleLogs();
        });
    }

    // Mostra tela específica
    showScreen(screenName) {
        // Esconde todas as telas
        document.querySelectorAll('#app > div').forEach(screen => {
            screen.classList.add('d-none');
        });

        // Mostra a tela solicitada
        const screen = document.getElementById(`${screenName}-screen`);
        if (screen) {
            screen.classList.remove('d-none');
        }
    }

    // Conexão com Token
    async handleConnect() {
        const instance = document.getElementById('instance').value.trim();
        const token = document.getElementById('token').value.trim();

        if (!instance || !token) {
            this.showToast('Por favor, preencha todos os campos', 'warning');
            return;
        }

        this.addLog(`Tentando conectar: ${instance}`, 'info');

        // Mostra loading no botão
        const submitBtn = document.querySelector('#connection-form button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Conectando...';

        try {
            // Valida credenciais
            this.addLog('Validando credenciais...', 'info');
            const validateResponse = await this.makeSecureRequest('validate_instance', {
                instance: instance,
                token: token
            });

            if (!validateResponse.success) {
                throw new Error(validateResponse.message || 'Credenciais inválidas');
            }

            // Salva credenciais validadas
            this.currentInstance = instance;
            this.currentToken = token;

            // Salva na sessão
            this.saveSession(instance, token);
            this.addLog('Credenciais validadas com sucesso', 'success');

            // Se já está conectado, mostra dashboard
            if (validateResponse.instance_data && validateResponse.instance_data.state === 'open') {
                this.showToast('Conexão estabelecida com sucesso!', 'success');
                this.addLog('WhatsApp já está conectado', 'success');
                await this.showDashboard();
            } else {
                // Mostra tela de QR Code
                this.showScreen('qr');
                await this.generateQRCode(instance, token);
            }

        } catch (error) {
            this.addLog(`Erro: ${error.message}`, 'error');
            this.showToast('Erro ao conectar: ' + error.message, 'danger');
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    }

    async generateQRCode(instance, token) {
        try {
            this.addLog('Gerando QR Code...', 'info');
            const response = await this.makeSecureRequest('generate_qr', {
                instance: instance,
                token: token
            });

            if (!response.success) {
                throw new Error(response.message || 'Erro ao gerar QR Code');
            }

            // Se já está conectado
            if (response.already_connected) {
                this.addLog('WhatsApp já conectado, redirecionando...', 'info');
                await this.showDashboard();
                return;
            }

            // Gera o QR Code
            if (response.code) {
                const canvas = document.getElementById('qrcodeCanvas');
                const spinner = document.getElementById('loading-spinner');

                QRCode.toCanvas(canvas, response.code, {
                    width: 250,
                    height: 250,
                    margin: 2
                }, (error) => {
                    if (error) {
                        throw error;
                    }

                    spinner.classList.add('d-none');
                    canvas.style.display = 'block';
                    this.addLog('QR Code gerado com sucesso', 'success');

                    this.startStatusCheck(instance, token);
                });
            }

        } catch (error) {
            this.addLog(`Erro: ${error.message}`, 'error');
            this.showError(error.message);
        }
    }

    startStatusCheck(instance, token) {
        // Limpa intervalo anterior
        if (this.checkInterval) {
            clearInterval(this.checkInterval);
        }

        let attempts = 0;
        const maxAttempts = 100;
        this.addLog('Aguardando conexão do WhatsApp...', 'info');

        this.checkInterval = setInterval(async () => {
            attempts++;

            try {
                const response = await this.makeSecureRequest('check_status', {
                    instance: instance,
                    token: token
                });

                if (response.success && response.state === 'open') {
                    clearInterval(this.checkInterval);
                    this.checkInterval = null;
                    this.addLog('WhatsApp conectado!', 'success');

                    // Mostra animação de sucesso
                    this.showSuccessAnimation();

                    setTimeout(() => {
                        this.showDashboard();
                    }, 2000);
                }

                if (attempts >= maxAttempts) {
                    clearInterval(this.checkInterval);
                    this.checkInterval = null;
                    this.addLog('Tempo limite excedido', 'warning');
                    this.showToast('Tempo limite excedido. Tente novamente.', 'warning');
                }

            } catch (error) {
                console.error('Erro ao verificar status:', error);
            }
        }, 3000);
    }

    showSuccessAnimation() {
        const canvas = document.getElementById('qrcodeCanvas');
        const container = canvas.parentElement;

        container.innerHTML = `
            <div class="success-checkmark">
                <div class="check-icon">
                    <span class="icon-line line-tip"></span>
                    <span class="icon-line line-long"></span>
                    <div class="icon-circle"></div>
                    <div class="icon-fix"></div>
                </div>
            </div>
            <h4 class="mt-4 text-success">Conectado com sucesso!</h4>
            <p class="text-muted">Preparando seu painel...</p>
        `;
    }

    async showDashboard() {
        try {
            this.addLog('Carregando informações do painel...', 'info');
            const response = await this.makeSecureRequest('fetch_instance_info', {
                instance: this.currentInstance,
                token: this.currentToken
            });

            if (!response.success || !response.instance) {
                throw new Error('Erro ao buscar informações');
            }

            const info = response.instance;

            // Atualiza o token se veio na resposta
            if (info.token) {
                this.currentToken = info.token;
                this.saveSession(this.currentInstance, info.token);
            }

            // Renderiza dashboard
            this.renderDashboard(info);

            // Mostra tela do dashboard
            this.showScreen('dashboard');
            this.addLog('Painel carregado com sucesso', 'success');

        } catch (error) {
            this.addLog(`Erro: ${error.message}`, 'error');
            this.showError(error.message);
        }
    }

    renderDashboard(info) {
        // Renderiza informações do perfil
        this.renderProfile(info);

        // Renderiza estatísticas
        this.renderStats(info);

        // Renderiza detalhes da instância
        this.renderInstanceDetails(info);
    }

    renderProfile(info) {
        const profileContainer = document.getElementById('profile-info');
        if (!profileContainer) return;

        // Obtém o número formatado
        let phoneNumber = 'Número não disponível';
        if (info.ownerJid) {
            phoneNumber = this.formatPhoneNumber(info.ownerJid);
        } else if (info.number) {
            phoneNumber = this.formatPhoneNumber(info.number);
        }

        profileContainer.innerHTML = `
            <img src="${info.profilePicUrl || 'https://via.placeholder.com/120'}" 
                 alt="Profile" 
                 class="profile-avatar">
            <h3 class="profile-name">${info.profileName || this.currentInstance}</h3>
            <p class="profile-number">${phoneNumber}</p>
            <div class="profile-status">
                <i class="bi bi-circle-fill"></i>
                WhatsApp Conectado
            </div>
            
            <div class="mt-4 d-grid gap-2">
                <button class="btn btn-gradient btn-sm" onclick="whatsappManager.disconnect()">
                    <i class="bi bi-power me-2"></i>
                    Desconectar
                </button>
                <button class="btn btn-light btn-sm" onclick="whatsappManager.logout()">
                    <i class="bi bi-box-arrow-right me-2"></i>
                    Sair do Painel
                </button>
            </div>
        `;
    }

    renderStats(info) {
        const statsContainer = document.getElementById('stats-container');
        if (!statsContainer) return;

        const stats = [
            {
                icon: 'bi-chat-dots',
                value: info._count?.Chat || 0,
                label: 'Conversas',
                color: 'primary'
            },
            {
                icon: 'bi-people',
                value: info._count?.Contact || 0,
                label: 'Contatos',
                color: 'success'
            },
            {
                icon: 'bi-envelope',
                value: info._count?.Message || 0,
                label: 'Mensagens',
                color: 'warning'
            },
            {
                icon: 'bi-broadcast',
                value: info.broadcasts?.length || 0,
                label: 'Transmissões',
                color: 'info'
            }
        ];

        statsContainer.innerHTML = stats.map((stat, index) => `
            <div class="col-md-6 fade-in" style="animation-delay: ${index * 0.1}s;">
                <div class="stats-card ${stat.color}">
                    <div class="icon-box">
                        <i class="bi ${stat.icon}"></i>
                    </div>
                    <div class="stats-number">${stat.value.toLocaleString('pt-BR')}</div>
                    <div class="stats-label">${stat.label}</div>
                </div>
            </div>
        `).join('');
    }

    renderInstanceDetails(info) {
        const detailsContainer = document.getElementById('instance-details');
        if (!detailsContainer) return;

        detailsContainer.innerHTML = `
            <h4 class="fw-bold mb-4">
                <i class="bi bi-info-circle text-primary me-2"></i>
                Informações da Conta
            </h4>
            
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="text-muted small d-block mb-1">Identificador da Conta</label>
                        <div class="d-flex align-items-center gap-2">
                            <code class="fs-6">${info.name || this.currentInstance}</code>
                            <button class="btn btn-sm btn-light" 
                                    onclick="whatsappManager.copyToClipboard('${info.name || this.currentInstance}')">
                                <i class="bi bi-clipboard"></i>
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="text-muted small d-block mb-1">Status da Conexão</label>
                        <span class="badge bg-success-subtle text-success fs-6">
                            <i class="bi bi-check-circle me-1"></i>
                            Ativo e Funcionando
                        </span>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="text-muted small d-block mb-1">Tipo de Integração</label>
                        <span class="fs-6">${info.integration || 'WhatsApp Business'}</span>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="text-muted small d-block mb-1">Última Sincronização</label>
                        <span class="fs-6">${new Date().toLocaleString('pt-BR')}</span>
                    </div>
                </div>
            </div>
            
            <div class="alert alert-info mt-4 mb-0">
                <i class="bi bi-lightbulb me-2"></i>
                <strong>Dica:</strong> Use as ações rápidas acima para enviar mensagens, 
                atualizar dados ou obter suporte quando precisar.
            </div>
        `;
    }

    /**
     * Formata número de telefone
     */
    formatPhoneNumber(number) {
        // Remove @s.whatsapp.net se existir
        number = number.replace('@s.whatsapp.net', '');

        // Remove qualquer caractere não numérico
        const cleaned = number.replace(/\D/g, '');

        // Verifica se é número brasileiro (começa com 55)
        if (cleaned.startsWith('55')) {
            const country = cleaned.substring(0, 2);
            const area = cleaned.substring(2, 4);
            const phoneNumber = cleaned.substring(4);

            // Verifica se é celular com 9 dígitos ou fixo com 8 dígitos
            if (phoneNumber.length === 9) {
                // Celular: +55 (11) 98765-4321
                const firstPart = phoneNumber.substring(0, 5);
                const secondPart = phoneNumber.substring(5, 9);
                return `+${country} (${area}) ${firstPart}-${secondPart}`;
            } else if (phoneNumber.length === 8) {
                // Fixo: +55 (11) 3456-7890
                const firstPart = phoneNumber.substring(0, 4);
                const secondPart = phoneNumber.substring(4, 8);
                return `+${country} (${area}) ${firstPart}-${secondPart}`;
            }
        }

        // Para outros países, formato genérico
        if (cleaned.length > 10) {
            const country = cleaned.substring(0, cleaned.length - 10);
            const rest = cleaned.substring(cleaned.length - 10);
            return `+${country} ${rest}`;
        }

        return cleaned;
    }

    /**
     * Copia texto para área de transferência
     */
    copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(() => {
            this.showToast('Copiado!', 'success');
        }).catch(() => {
            // Fallback
            const textArea = document.createElement('textarea');
            textArea.value = text;
            textArea.style.position = 'fixed';
            textArea.style.opacity = '0';
            document.body.appendChild(textArea);
            textArea.select();
            try {
                document.execCommand('copy');
                this.showToast('Copiado!', 'success');
            } catch (err) {
                this.showToast('Erro ao copiar', 'error');
            }
            document.body.removeChild(textArea);
        });
    }

    async refreshInstance() {
        this.showToast('Atualizando informações...', 'info');
        this.addLog('Atualizando dados...', 'info');
        await this.showDashboard();
    }

    async disconnect() {
        if (!confirm('Deseja realmente desconectar seu WhatsApp?')) return;

        try {
            this.addLog('Desconectando WhatsApp...', 'info');
            const response = await this.makeSecureRequest('logout_instance', {
                instance: this.currentInstance,
                token: this.currentToken
            });

            if (response.success) {
                this.addLog('WhatsApp desconectado', 'success');
                this.showToast('WhatsApp desconectado com sucesso', 'success');
                this.clearSession();
                this.showNewInstance();
            } else {
                throw new Error(response.message || 'Erro ao desconectar');
            }

        } catch (error) {
            this.addLog(`Erro: ${error.message}`, 'error');
            this.showToast('Erro: ' + error.message, 'danger');
        }
    }

    logout() {
        if (!confirm('Deseja sair do painel? Seu WhatsApp permanecerá conectado.')) return;

        this.clearSession();
        this.showNewInstance();
        this.addLog('Logout do painel realizado', 'info');
        this.showToast('Você saiu do painel', 'info');
    }

    showNewInstance() {
        this.currentInstance = null;
        this.currentToken = null;
        this.showScreen('login');
        document.getElementById('instance').value = '';
        document.getElementById('token').value = '';

        // Limpa QR Code
        const canvas = document.getElementById('qrcodeCanvas');
        const spinner = document.getElementById('loading-spinner');
        if (canvas) canvas.style.display = 'none';
        if (spinner) spinner.classList.remove('d-none');
    }

    showError(message) {
        document.getElementById('error-message').textContent = message;
        this.showScreen('error');
    }

    // Sessão
    saveSession(instance, token) {
        const session = {
            instance: instance,
            token: btoa(token), // Base64
            timestamp: Date.now()
        };

        localStorage.setItem('whatsappSession', JSON.stringify(session));
    }

    loadSession() {
        const saved = localStorage.getItem('whatsappSession');
        if (!saved) return;

        try {
            const session = JSON.parse(saved);
            const age = Date.now() - session.timestamp;
            const maxAge = 24 * 60 * 60 * 1000; // 24 horas

            if (age < maxAge && session.instance && session.token) {
                // Descriptografa o token
                const decryptedToken = atob(session.token);

                document.getElementById('instance').value = session.instance;
                document.getElementById('token').value = decryptedToken;

                // Auto-conecta após 1 segundo
                setTimeout(() => {
                    this.showToast('Reconectando...', 'info');
                    this.addLog('Sessão anterior encontrada, reconectando...', 'info');
                    document.getElementById('connection-form').dispatchEvent(new Event('submit'));
                }, 1000);
            }
        } catch (error) {
            this.clearSession();
        }
    }

    clearSession() {
        localStorage.removeItem('whatsappSession');
    }

    // Tema
    toggleTheme() {
        const html = document.documentElement;
        const icon = document.getElementById('theme-icon');
        const currentTheme = html.getAttribute('data-bs-theme');

        if (currentTheme === 'dark') {
            html.setAttribute('data-bs-theme', 'light');
            icon.className = 'bi bi-moon-fill';
            localStorage.setItem('theme', 'light');
        } else {
            html.setAttribute('data-bs-theme', 'dark');
            icon.className = 'bi bi-sun-fill';
            localStorage.setItem('theme', 'dark');
        }
    }

    loadTheme() {
        const saved = localStorage.getItem('theme');
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

        if (saved === 'dark' || (!saved && prefersDark)) {
            document.documentElement.setAttribute('data-bs-theme', 'dark');
            document.getElementById('theme-icon').className = 'bi bi-sun-fill';
        }
    }

    // Logs
    toggleLogs() {
        const container = document.getElementById('logs-container');
        if (container) {
            container.classList.toggle('d-none');
            // Se estiver mostrando, scrolla para o final
            if (!container.classList.contains('d-none')) {
                const logsDiv = document.getElementById('logs');
                if (logsDiv) {
                    logsDiv.scrollTop = logsDiv.scrollHeight;
                }
            }
        }
    }

    addLog(message, type = 'info') {
        const logs = document.getElementById('logs');
        if (!logs) return;

        const time = new Date().toLocaleTimeString('pt-BR');
        const colors = {
            info: 'text-info',
            success: 'text-success',
            warning: 'text-warning',
            error: 'text-danger'
        };

        const icons = {
            info: 'bi-info-circle',
            success: 'bi-check-circle',
            warning: 'bi-exclamation-triangle',
            error: 'bi-x-circle'
        };

        const entry = document.createElement('div');
        entry.className = `log-entry ${colors[type] || 'text-info'} mb-1`;
        entry.innerHTML = `
            <small>
                <i class="bi ${icons[type] || 'bi-info-circle'} me-1"></i>
                <span class="text-muted">[${time}]</span> ${message}
            </small>
        `;

        logs.appendChild(entry);
        logs.scrollTop = logs.scrollHeight;

        // Atualiza contador
        this.logCount++;
        const badge = document.getElementById('log-badge');
        if (badge) {
            badge.textContent = this.logCount;
            badge.style.display = this.logCount > 0 ? 'inline-block' : 'none';
        }
    }

    clearLogs() {
        const logs = document.getElementById('logs');
        if (logs) {
            logs.innerHTML = '<div class="text-info"><small><i class="bi bi-info-circle me-1"></i>[Sistema] Logs limpos</small></div>';
        }
        this.logCount = 1;
        const badge = document.getElementById('log-badge');
        if (badge) {
            badge.textContent = '1';
        }
    }

    // Toast notifications
    showToast(message, type = 'info') {
        // Remove toasts anteriores
        document.querySelectorAll('.toast-container').forEach(el => el.remove());

        // Define cores baseadas no tipo
        const colors = {
            success: 'bg-success text-white',
            danger: 'bg-danger text-white',
            warning: 'bg-warning text-dark',
            info: 'bg-primary text-white'
        };

        // Cria container de toast
        const toastHtml = `
            <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999;">
                <div class="toast toast-custom ${colors[type] || colors.info} show" role="alert">
                    <div class="toast-body d-flex align-items-center">
                        <i class="bi bi-${type === 'success' ? 'check-circle' : type === 'danger' ? 'x-circle' : type === 'warning' ? 'exclamation-triangle' : 'info-circle'} me-2"></i>
                        <div class="me-auto">${message}</div>
                        <button type="button" class="btn-close btn-close-${type === 'warning' ? 'dark' : 'white'}" data-bs-dismiss="toast"></button>
                    </div>
                </div>
            </div>
        `;

        document.body.insertAdjacentHTML('beforeend', toastHtml);

        // Remove após 5 segundos
        setTimeout(() => {
            const toastContainer = document.querySelector('.toast-container');
            if (toastContainer) {
                toastContainer.style.opacity = '0';
                toastContainer.style.transition = 'opacity 0.3s';
                setTimeout(() => toastContainer.remove(), 300);
            }
        }, 5000);
    }

    // API com Token
    async makeSecureRequest(action, data = {}) {
        try {
            // Adiciona o token aos dados se disponível
            if (this.currentToken && !data.token) {
                data.token = this.currentToken;
            }

            const response = await fetch('api.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': APP_CONFIG.csrfToken
                },
                body: JSON.stringify({
                    action: action,
                    ...data
                })
            });

            const text = await response.text();

            // Tenta fazer parse do JSON
            try {
                const result = JSON.parse(text);

                if (!response.ok) {
                    throw new Error(result.message || `HTTP error! status: ${response.status}`);
                }

                return result;
            } catch (e) {
                // Se não for JSON válido, loga o erro
                console.error('Resposta da API:', text);
                throw new Error('Resposta inválida do servidor');
            }
        } catch (error) {
            console.error('Erro na requisição:', error);
            throw error;
        }
    }

    // Getter para o token atual (usado pelo modal)
    getToken() {
        return this.currentToken;
    }
}

// Inicialização
let whatsappManager;

document.addEventListener('DOMContentLoaded', () => {
    whatsappManager = new WhatsAppManager();
});
// WhatsApp Manager V2 - JavaScript Principal
class WhatsAppManager {
    constructor() {
        this.currentInstance = null;
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

    // Gerenciamento de Steps/Telas
    showStep(stepName) {
        // Esconde todos os steps
        document.querySelectorAll('.step-content').forEach(el => {
            el.classList.add('d-none');
        });

        // Mostra o step solicitado
        const step = document.getElementById(`step-${stepName}`);
        if (step) {
            step.classList.remove('d-none');
        }
    }

    // Conexão
    async handleConnect() {
        const instance = document.getElementById('instance').value.trim();
        if (!instance) {
            this.showToast('Digite um nome para a instância', 'warning');
            return;
        }

        // Salva instância
        localStorage.setItem('whatsappInstance', instance);
        this.currentInstance = instance;

        // Mostra tela de QR Code
        this.showStep('qrcode');

        // Gera QR Code
        await this.generateQRCode(instance);
    }

    async generateQRCode(instance) {
        try {
            this.addLog(`Gerando QR Code para: ${instance}`, 'info');

            const response = await this.makeAPICall('generate_qr', { instance });

            if (!response.success) {
                throw new Error(response.message || 'Erro ao gerar QR Code');
            }

            // Se já está conectado
            if (response.already_connected) {
                this.addLog('WhatsApp já está conectado', 'success');
                await this.showConnectedInstance(instance);
                return;
            }

            // Gera o QR Code
            if (response.code) {
                const canvas = document.getElementById('qrcodeCanvas');
                const spinner = document.getElementById('loading-spinner');

                QRCode.toCanvas(canvas, response.code, {
                    width: 200,
                    height: 200,
                    margin: 2
                }, (error) => {
                    if (error) {
                        throw error;
                    }

                    spinner.classList.add('d-none');
                    canvas.style.display = 'block';

                    this.addLog('QR Code gerado com sucesso', 'success');
                    this.startStatusCheck(instance);
                });
            }

        } catch (error) {
            this.addLog(`Erro: ${error.message}`, 'error');
            this.showError(error.message);
        }
    }

    startStatusCheck(instance) {
        // Limpa intervalo anterior
        if (this.checkInterval) {
            clearInterval(this.checkInterval);
        }

        let attempts = 0;
        const maxAttempts = 100; // 5 minutos (3s * 100)

        this.checkInterval = setInterval(async () => {
            attempts++;

            try {
                const response = await this.makeAPICall('check_status', { instance });

                if (response.success && response.state === 'open') {
                    clearInterval(this.checkInterval);
                    this.checkInterval = null;
                    this.addLog('WhatsApp conectado!', 'success');
                    await this.showConnectedInstance(instance);
                }

                if (attempts >= maxAttempts) {
                    clearInterval(this.checkInterval);
                    this.checkInterval = null;
                    this.addLog('Tempo limite excedido', 'warning');
                }

            } catch (error) {
                console.error('Erro ao verificar status:', error);
            }
        }, 3000);
    }

    async showConnectedInstance(instance) {
        try {
            this.addLog('Carregando informações da instância...', 'info');

            const response = await this.makeAPICall('fetch_instance_info', { instance });

            if (!response.success || !response.instance) {
                throw new Error('Erro ao buscar informações');
            }

            const info = response.instance;

            // Salva sessão
            this.saveSession(instance);

            // Mostra tela conectada
            this.showStep('connected');

            // Renderiza painel
            this.renderInstancePanel(info, instance);

            this.addLog('Informações carregadas com sucesso', 'success');

        } catch (error) {
            this.addLog(`Erro: ${error.message}`, 'error');
            this.showError(error.message);
        }
    }

    renderInstancePanel(info, instance) {
        const panel = document.getElementById('instance-panel');
        if (!panel) return;

        // Obtém o número do ownerJid ou do campo number
        let phoneNumber = 'Número não disponível';
        if (info.ownerJid) {
            phoneNumber = this.formatPhoneNumber(info.ownerJid);
        } else if (info.number) {
            phoneNumber = this.formatPhoneNumber(info.number);
        }

        panel.innerHTML = `
            <!-- Profile Info -->
            <div class="instance-info-card">
                <div class="row align-items-center">
                    <div class="col-auto">
                        <img src="${info.profilePicUrl || 'https://via.placeholder.com/80'}" 
                             alt="Profile" class="profile-img">
                    </div>
                    <div class="col">
                        <h5 class="mb-1">${info.profileName || instance}</h5>
                        <p class="text-muted mb-2">${phoneNumber}</p>
                        <div>
                            <span class="badge bg-success me-2">
                                <i class="bi bi-check-circle me-1"></i>Conectado
                            </span>
                            <span class="badge bg-primary">
                                ${info.integration || 'WHATSAPP-BAILEYS'}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Instance Details -->
            <div class="instance-info-card">
                <h6 class="mb-3">Detalhes da Instância</h6>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="text-muted small">Nome da Instância</label>
                        <div class="input-group">
                            <input type="text" class="form-control" value="${info.name || instance}" readonly>
                            <button class="btn btn-outline-secondary" onclick="whatsappManager.copyToClipboard('${info.name || instance}', 'Nome da instância')">
                                <i class="bi bi-clipboard"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Token da API</label>
                        <div class="input-group">
                            <input type="text" class="form-control" value="${info.token || 'N/A'}" readonly>
                            <button class="btn btn-outline-secondary" onclick="whatsappManager.copyToClipboard('${info.token || ''}', 'Token')">
                                <i class="bi bi-clipboard"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-12 mt-3">
                        <label class="text-muted small">URL da API</label>
                        <div class="input-group">
                            <input type="text" class="form-control" value="${APP_CONFIG.apiUrl || window.location.origin}" readonly>
                            <button class="btn btn-outline-secondary" onclick="whatsappManager.copyToClipboard('${APP_CONFIG.apiUrl || window.location.origin}', 'URL da API')">
                                <i class="bi bi-clipboard"></i>
                            </button>
                        </div>
                        <small class="text-muted">Use esta URL base para fazer requisições à API</small>
                    </div>
                </div>
            </div>

            <!-- Statistics -->
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="stats-card">
                        <div class="stats-number">${info._count?.Chat || 0}</div>
                        <div class="text-muted">Conversas</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stats-card">
                        <div class="stats-number">${info._count?.Contact || 0}</div>
                        <div class="text-muted">Contatos</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stats-card">
                        <div class="stats-number">${info._count?.Message || 0}</div>
                        <div class="text-muted">Mensagens</div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                <button class="btn btn-primary" onclick="modalManager.showSendMessage('${instance}')">
                    <i class="bi bi-send me-2"></i>Enviar Mensagem
                </button>
                <button class="btn btn-outline-secondary" onclick="whatsappManager.refreshInstance('${instance}')">
                    <i class="bi bi-arrow-clockwise me-2"></i>Atualizar
                </button>
                <button class="btn btn-outline-danger" onclick="whatsappManager.disconnect('${instance}')">
                    <i class="bi bi-power me-2"></i>Desconectar
                </button>
                <button class="btn btn-outline-secondary" onclick="whatsappManager.logoutManager()">
                    <i class="bi bi-box-arrow-right me-2"></i>Sair do Manager
                </button>
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
    copyToClipboard(text, label = 'Texto') {
        navigator.clipboard.writeText(text).then(() => {
            this.showToast(`${label} copiado!`, 'success');
        }).catch(() => {
            // Fallback para navegadores antigos
            const textArea = document.createElement('textarea');
            textArea.value = text;
            textArea.style.position = 'fixed';
            textArea.style.opacity = '0';
            document.body.appendChild(textArea);
            textArea.select();
            try {
                document.execCommand('copy');
                this.showToast(`${label} copiado!`, 'success');
            } catch (err) {
                this.showToast('Erro ao copiar', 'error');
            }
            document.body.removeChild(textArea);
        });
    }

    async refreshInstance(instance) {
        this.addLog('Atualizando informações...', 'info');
        await this.showConnectedInstance(instance);
    }

    async disconnect(instance) {
        if (!confirm('Deseja realmente desconectar?')) return;

        try {
            this.addLog('Desconectando...', 'info');

            const response = await this.makeAPICall('logout_instance', { instance });

            if (response.success) {
                this.addLog('Desconectado com sucesso', 'success');
                this.clearSession();
                this.showNewInstance();
            } else {
                throw new Error(response.message || 'Erro ao desconectar');
            }

        } catch (error) {
            this.addLog(`Erro: ${error.message}`, 'error');
        }
    }

    logoutManager() {
        if (!confirm('Deseja sair do Manager? A instância continuará conectada no WhatsApp.')) return;

        this.clearSession();
        this.showNewInstance();
        this.addLog('Logout do Manager realizado', 'info');
    }

    showNewInstance() {
        this.currentInstance = null;
        this.showStep('connection');
        document.getElementById('instance').value = '';

        // Limpa QR Code
        const canvas = document.getElementById('qrcodeCanvas');
        const spinner = document.getElementById('loading-spinner');
        if (canvas) canvas.style.display = 'none';
        if (spinner) spinner.classList.remove('d-none');
    }

    showError(message) {
        document.getElementById('error-message').textContent = message;
        this.showStep('error');
    }

    // Sessão
    saveSession(instance) {
        const session = {
            instance: instance,
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

            if (age < maxAge && session.instance) {
                document.getElementById('instance').value = session.instance;
                this.addLog(`Sessão recuperada: ${session.instance}`, 'info');

                // Auto-conecta após 1 segundo
                setTimeout(() => {
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

        if (saved === 'light' || (!saved && !prefersDark)) {
            document.documentElement.setAttribute('data-bs-theme', 'light');
            document.getElementById('theme-icon').className = 'bi bi-moon-fill';
        }
    }

    // Logs
    toggleLogs() {
        const container = document.getElementById('logs-container');
        container.classList.toggle('d-none');
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

        const entry = document.createElement('div');
        entry.className = colors[type] || 'text-info';
        entry.textContent = `[${time}] ${message}`;

        logs.appendChild(entry);
        logs.scrollTop = logs.scrollHeight;

        // Atualiza contador
        this.logCount++;
        document.getElementById('log-badge').textContent = this.logCount;
    }

    clearLogs() {
        const logs = document.getElementById('logs');
        if (logs) {
            logs.innerHTML = '<div class="text-info">[Sistema] Logs limpos</div>';
        }
        this.logCount = 1;
        document.getElementById('log-badge').textContent = '1';
    }

    // Toast notifications
    showToast(message, type = 'info') {
        // Cria alerta Bootstrap temporário
        const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-5" 
                 style="z-index: 9999; min-width: 300px;" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;

        document.body.insertAdjacentHTML('beforeend', alertHtml);

        // Remove após 3 segundos
        setTimeout(() => {
            const alert = document.querySelector('.alert');
            if (alert) alert.remove();
        }, 3000);
    }

    // API
    async makeAPICall(action, params = {}) {
        const url = new URL('api.php', window.location.origin + window.location.pathname.replace('/index.php', '/'));
        url.searchParams.set('action', action);

        for (const [key, value] of Object.entries(params)) {
            url.searchParams.set(key, value);
        }

        const response = await fetch(url.toString());

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        return await response.json();
    }

    async makeSecureRequest(action, data = {}) {
        try {
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
                throw new Error('Resposta inválida da API');
            }
        } catch (error) {
            console.error('Erro na requisição:', error);
            throw error;
        }
    }
}

// Inicialização
let whatsappManager;

document.addEventListener('DOMContentLoaded', () => {
    whatsappManager = new WhatsAppManager();
});
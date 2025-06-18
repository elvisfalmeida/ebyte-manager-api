// Modal Manager V2 - Sistema de Modais
class ModalManager {
    constructor() {
        this.sendModal = null;
        this.currentInstance = null;
        this.init();
    }

    init() {
        // Inicializa modal do Bootstrap
        const modalElement = document.getElementById('sendMessageModal');
        if (modalElement) {
            this.sendModal = new bootstrap.Modal(modalElement);
            this.setupEventListeners();
        }
    }

    setupEventListeners() {
        // Form de envio
        document.getElementById('send-message-form')?.addEventListener('submit', (e) => {
            e.preventDefault();
            this.handleSendMessages();
        });

        // Toggle anexar mídia
        document.getElementById('attach-media')?.addEventListener('change', (e) => {
            const uploadDiv = document.getElementById('media-upload');
            if (uploadDiv) {
                uploadDiv.classList.toggle('d-none', !e.target.checked);
            }
        });

        // Toggle intervalo aleatório
        document.getElementById('use-random-interval')?.addEventListener('change', (e) => {
            const configDiv = document.getElementById('interval-config');
            if (configDiv) {
                configDiv.classList.toggle('d-none', !e.target.checked);
            }
        });
    }

    showSendMessage(instance) {
        this.currentInstance = instance;
        
        // Limpa formulário
        document.getElementById('send-message-form').reset();
        document.getElementById('media-upload').classList.add('d-none');
        
        // Mostra modal
        if (this.sendModal) {
            this.sendModal.show();
        }
    }

    setTextStyle(style) {
        const textarea = document.getElementById('message-text');
        if (!textarea) return;

        const start = textarea.selectionStart;
        const end = textarea.selectionEnd;
        const text = textarea.value;
        const selectedText = text.substring(start, end);

        let prefix = '', suffix = '';
        
        switch (style) {
            case 'bold':
                prefix = suffix = '*';
                break;
            case 'italic':
                prefix = suffix = '_';
                break;
            case 'strikethrough':
                prefix = suffix = '~';
                break;
            case 'mono':
                prefix = suffix = '```';
                break;
        }

        if (!selectedText) {
            // Insere marcadores vazios
            const newText = text.substring(0, start) + prefix + suffix + text.substring(end);
            textarea.value = newText;
            textarea.focus();
            textarea.setSelectionRange(start + prefix.length, start + prefix.length);
        } else {
            // Aplica formatação ao texto selecionado
            const newText = text.substring(0, start) + prefix + selectedText + suffix + text.substring(end);
            textarea.value = newText;
            textarea.focus();
            textarea.setSelectionRange(start, start + prefix.length + selectedText.length + suffix.length);
        }
    }

    async handleSendMessages() {
        const numbersTextarea = document.getElementById('message-numbers');
        const numbers = numbersTextarea.value
            .split('\n')
            .map(n => n.trim())
            .filter(n => n.length > 0)
            .map(n => {
                // Remove caracteres não numéricos, exceto o +
                return n.replace(/[^\d+]/g, '');
            })
            .filter(n => n.length >= 10); // Número mínimo de dígitos
            
        const message = document.getElementById('message-text').value.trim();
        const attachMedia = document.getElementById('attach-media').checked;
        const useDelay = document.getElementById('use-delay').checked;
        const useRandomInterval = document.getElementById('use-random-interval').checked;

        // Validações
        if (numbers.length === 0) {
            this.showAlert('Digite pelo menos um número', 'warning');
            return;
        }

        if (!message && !attachMedia) {
            this.showAlert('Digite uma mensagem ou anexe uma mídia', 'warning');
            return;
        }

        // Processa mídia se necessário
        let mediaData = null;
        if (attachMedia) {
            const fileInput = document.getElementById('media-file');
            if (fileInput.files.length > 0) {
                try {
                    mediaData = await this.processFile(fileInput.files[0]);
                } catch (error) {
                    this.showAlert('Erro ao processar arquivo', 'danger');
                    return;
                }
            }
        }

        // Fecha modal
        this.sendModal.hide();

        // Configurações de intervalo
        const minInterval = parseInt(document.getElementById('interval-min').value) * 1000;
        const maxInterval = parseInt(document.getElementById('interval-max').value) * 1000;

        // Log início
        whatsappManager.addLog(`Iniciando envio para ${numbers.length} número(s)`, 'info');
        whatsappManager.addLog(`Instância: ${this.currentInstance}`, 'info');
        if (mediaData) {
            whatsappManager.addLog(`Mídia: ${mediaData.name} (${mediaData.type})`, 'info');
        }

        let sent = 0, errors = 0;

        // Envia mensagens
        for (let i = 0; i < numbers.length; i++) {
            const number = numbers[i];

            try {
                let response;
                
                // Log do número sendo processado
                whatsappManager.addLog(`Processando: ${number}`, 'info');
                
                if (mediaData) {
                    response = await whatsappManager.makeSecureRequest('send_media', {
                        instance: this.currentInstance,
                        number: number,
                        media: mediaData.data,
                        fileName: mediaData.name,
                        mediaType: mediaData.type, // Corrigido: era mediaType com T maiúsculo
                        caption: message,
                        delay: useDelay ? 3000 : 0
                    });
                } else {
                    response = await whatsappManager.makeSecureRequest('send_message', {
                        instance: this.currentInstance,
                        number: number,
                        text: message,
                        delay: useDelay ? 3000 : 0
                    });
                }

                if (response.success) {
                    sent++;
                    whatsappManager.addLog(`✓ Enviado para ${number}`, 'success');
                } else {
                    errors++;
                    whatsappManager.addLog(`✗ Erro em ${number}: ${response.message}`, 'error');
                }

                // Intervalo aleatório
                if (useRandomInterval && i < numbers.length - 1) {
                    const delay = Math.floor(Math.random() * (maxInterval - minInterval + 1)) + minInterval;
                    whatsappManager.addLog(`Aguardando ${delay/1000}s...`, 'info');
                    await this.sleep(delay);
                }

            } catch (error) {
                errors++;
                whatsappManager.addLog(`✗ Erro em ${number}: ${error.message}`, 'error');
            }
        }

        // Resumo final
        whatsappManager.addLog(`Envio concluído: ${sent} enviados, ${errors} erros`, 
                              errors > 0 ? 'warning' : 'success');
    }

    async processFile(file) {
        return new Promise((resolve, reject) => {
            const reader = new FileReader();
            
            reader.onload = () => {
                const base64 = reader.result.split(',')[1];
                let type = 'document';
                
                if (file.type.startsWith('image/')) type = 'image';
                else if (file.type.startsWith('video/')) type = 'video';
                else if (file.type.startsWith('audio/')) type = 'audio';
                
                resolve({
                    data: base64,
                    name: file.name,
                    type: type
                });
            };
            
            reader.onerror = () => reject(new Error('Erro ao ler arquivo'));
            reader.readAsDataURL(file);
        });
    }

    showAlert(message, type = 'info') {
        // Cria alerta Bootstrap temporário
        const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3" 
                 style="z-index: 9999;" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        
        document.body.insertAdjacentHTML('beforeend', alertHtml);
        
        // Remove após 5 segundos
        setTimeout(() => {
            const alert = document.querySelector('.alert');
            if (alert) alert.remove();
        }, 5000);
    }

    sleep(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }
}

// Inicialização
let modalManager;

document.addEventListener('DOMContentLoaded', () => {
    modalManager = new ModalManager();
});
# Ebyte Manager API V1

<p align="center">
  <img src="https://img.shields.io/badge/version-1.0.0-blue.svg" alt="Version">
  <img src="https://img.shields.io/badge/php-%3E%3D7.4-8892BF.svg" alt="PHP Version">
  <img src="https://img.shields.io/badge/bootstrap-5.3.2-7952B3.svg" alt="Bootstrap">
  <img src="https://img.shields.io/badge/license-MIT-green.svg" alt="License">
</p>

## 📋 Sobre

O **Ebyte Manager API V1** é uma interface web simples e funcional para gerenciar conexões do WhatsApp através de API. Desenvolvido para usuários finais que precisam conectar suas instâncias e obter tokens para automações.

## ✨ Funcionalidades

- 🔗 **Conexão via QR Code** - Conecte seu WhatsApp escaneando o QR Code
- 📊 **Painel de Gerenciamento** - Visualize informações e estatísticas da instância
- 📤 **Envio de Mensagens** - Envie mensagens de texto e mídia para múltiplos contatos
- 🎨 **Tema Claro/Escuro** - Interface adaptável à sua preferência
- 📝 **Logs em Tempo Real** - Acompanhe todas as ações do sistema
- 🔐 **Segurança** - Proteção CSRF e validação de dados
- 💾 **Sessão Persistente** - Reconexão automática por até 24 horas

## 🚀 Instalação

### Requisitos

- PHP >= 7.4
- Servidor Web (Apache/Nginx)
- Extensão cURL habilitada
- Acesso à API do WhatsApp

### Passo a Passo

1. **Clone o repositório**
   ```bash
   git clone https://github.com/seu-usuario/ebyte-manager-api.git
   cd ebyte-manager-api
   ```

2. **Configure o arquivo .env**
   ```bash
   cp .env.example .env
   ```
   
   Edite o arquivo `.env` com suas configurações:
   ```env
   # Configurações da API WhatsApp
   API_URL=https://sua-api-whatsapp.com
   API_KEY=sua-api-key-aqui
   
   # Configurações visuais
   PRIMARY_COLOR=#7341ff
   LOGO_LIGHT=url-do-logo-claro
   LOGO_DARK=url-do-logo-escuro
   FAVICON=url-do-favicon
   
   # Configurações de segurança
   SESSION_TIMEOUT=3600
   MAX_UPLOAD_SIZE=10485760
   ```

3. **Configure permissões**
   ```bash
   chmod 755 logs/
   chmod 644 .htaccess
   ```

4. **Acesse no navegador**
   ```
   http://localhost/ebyte-manager-api/
   ```

## 📖 Como Usar

### 1. Conectar WhatsApp

1. Digite o nome da sua instância (case-sensitive)
2. Clique em "Conectar Instância"
3. Escaneie o QR Code com seu WhatsApp
4. Aguarde a confirmação da conexão

### 2. Gerenciar Instância

Após conectado, você terá acesso a:
- **Informações do Perfil** - Foto, nome e número
- **Token da API** - Para usar em suas automações
- **Estatísticas** - Quantidade de conversas, contatos e mensagens
- **Ações** - Enviar mensagem, atualizar, desconectar

### 3. Enviar Mensagens

1. Clique em "Enviar Mensagem"
2. Digite os números (um por linha)
3. Escreva sua mensagem
4. Configure opções (opcional):
   - Anexar mídia
   - Simular digitação
   - Intervalo aleatório
5. Clique em "Enviar Mensagens"

## 🛠️ Estrutura do Projeto

```
ebyte-manager-api/
├── assets/
│   └── js/
│       ├── app-v2.js      # Lógica principal
│       └── modal-v2.js    # Sistema de modais
├── logs/                  # Logs do sistema
├── .env                   # Configurações (não versionado)
├── .env.example          # Exemplo de configuração
├── .htaccess             # Proteções Apache
├── api.php               # Endpoints da API
├── config.php            # Configurações do sistema
├── index.php             # Interface principal
└── README.md             # Este arquivo
```

## 🔧 Configurações Avançadas

### Timeout de Sessão

Altere no `.env`:
```env
SESSION_TIMEOUT=3600  # Em segundos (padrão: 1 hora)
```

### Tamanho Máximo de Upload

```env
MAX_UPLOAD_SIZE=10485760  # Em bytes (padrão: 10MB)
```

### Logs

Os logs são salvos em `logs/app.log`. Para desabilitar:
```env
ENABLE_LOGS=false
```

## 📡 API Endpoints

O sistema se comunica com os seguintes endpoints da API WhatsApp:

- `GET /instance/connect/{instance}` - Gera QR Code
- `GET /instance/connectionState/{instance}` - Verifica status
- `GET /instance/fetchInstances` - Busca informações
- `DELETE /instance/logout/{instance}` - Desconecta
- `POST /message/sendText/{instance}` - Envia texto
- `POST /message/sendMedia/{instance}` - Envia mídia

## 🔒 Segurança

- **Token CSRF** - Proteção contra ataques CSRF
- **Validação de Inputs** - Sanitização de todos os dados
- **Headers de Segurança** - X-Frame-Options, X-XSS-Protection, etc.
- **Proteção de Arquivos** - .env e logs protegidos via .htaccess

## 🐛 Solução de Problemas

### QR Code não aparece
- Verifique as configurações da API no `.env`
- Confirme se o nome da instância está correto
- Verifique os logs em `logs/app.log`

### Erro ao enviar mensagens
- Confirme o formato dos números (apenas dígitos)
- Verifique se a instância está conectada
- Veja o console do navegador (F12) para mais detalhes

### Sessão não persiste
- Verifique as configurações de cookies do navegador
- Confirme se o `SESSION_TIMEOUT` não está muito baixo

## 📝 Changelog

### [1.0.0] - 2024-01-XX
- Lançamento inicial
- Interface com Bootstrap 5
- Conexão via QR Code
- Envio de mensagens texto e mídia
- Suporte a múltiplos destinatários
- Tema claro/escuro
- Logs em tempo real

## 🤝 Contribuindo

1. Faça um Fork do projeto
2. Crie uma branch para sua feature (`git checkout -b feature/NovaFuncionalidade`)
3. Commit suas mudanças (`git commit -m 'Adiciona nova funcionalidade'`)
4. Push para a branch (`git push origin feature/NovaFuncionalidade`)
5. Abra um Pull Request

## 📄 Licença

Este projeto está sob a licença MIT. Veja o arquivo [LICENSE](LICENSE) para mais detalhes.

## 👥 Autores

- **Seu Nome** - *Desenvolvimento inicial* - [GitHub](https://github.com/elvisalmeida)

## 🙏 Agradecimentos

- Equipe da API WhatsApp
- Comunidade Bootstrap
- Todos os contribuidores

---

<p align="center">
  Feito com ❤️ por <a href="https://github.com/elvisfalmeida">Elvis Almeida</a>
</p>

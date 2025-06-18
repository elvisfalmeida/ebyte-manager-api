# Ebyte Manager V4 - WhatsApp Business SaaS

<p align="center">
  <img src="https://img.shields.io/badge/version-4.0.0-gradient.svg" alt="Version">
  <img src="https://img.shields.io/badge/php-%3E%3D7.4-8892BF.svg" alt="PHP Version">
  <img src="https://img.shields.io/badge/bootstrap-5.3.2-7952B3.svg" alt="Bootstrap">
  <img src="https://img.shields.io/badge/license-Proprietary-red.svg" alt="License">
  <img src="https://img.shields.io/badge/status-Production_Ready-green.svg" alt="Status">
</p>

<p align="center">
  <img src="https://img.shields.io/badge/Design-SaaS_Ready-blueviolet.svg" alt="SaaS">
  <img src="https://img.shields.io/badge/Security-Token_Auth-orange.svg" alt="Security">
  <img src="https://img.shields.io/badge/UI-Modern_&_Responsive-ff69b4.svg" alt="UI">
</p>

## 🚀 Sobre o Ebyte Manager V4

O **Ebyte Manager V4** representa uma evolução completa do sistema, transformando-se em uma solução **SaaS profissional** para gerenciamento de WhatsApp Business. Esta versão foi totalmente redesenhada com foco em:

- 🎨 **Interface SaaS Moderna** - Design profissional digno de produtos enterprise
- 🔐 **Autenticação por Token** - Segurança de nível bancário
- 💼 **Linguagem Comercial** - Pronto para uso por clientes finais
- ⚡ **Performance Otimizada** - Carregamento instantâneo e animações fluidas

## ✨ Principais Novidades da V4

### 🎯 Transformação Total
- **De ferramenta técnica → Produto SaaS comercial**
- **De interface básica → Design premium com gradientes e animações**
- **De autenticação simples → Sistema de token obrigatório**
- **De logs técnicos → Central de ajuda integrada**

### 🛡️ Segurança Revolucionária
- **Autenticação dupla** (Identificador + Token)
- **Validação server-side** de todas as credenciais
- **Sessão criptografada** com expiração automática
- **Proteção contra força bruta** e tentativas maliciosas

### 🎨 Interface Completamente Nova
- **Hero Section** com gradientes animados
- **Cards interativos** com efeitos hover 3D
- **Animações suaves** em todas as transições
- **Dark/Light mode** com cores personalizáveis
- **100% responsivo** com mobile-first

## 📸 Screenshots

### Tela de Login
- Design de landing page profissional
- Formulário elegante com validação em tempo real
- Informações do produto destacadas
- Elementos flutuantes animados

### Dashboard
- Cards de estatísticas coloridos e interativos
- Perfil do usuário em destaque
- Ações rápidas com ícones grandes
- Logs em tempo real (ocultável)

### Central de Ajuda
- Modal elegante com abas
- Visualização segura de credenciais
- Links diretos para suporte
- Informações de contato personalizáveis

## 🔧 Instalação

### Requisitos
- PHP >= 7.4
- Servidor Web (Apache/Nginx)
- Extensão cURL habilitada
- Evolution API configurada
- SSL recomendado para produção

### Passo a Passo

1. **Clone ou extraia os arquivos**
   ```bash
   git clone https://github.com/seu-usuario/ebyte-manager-v4.git
   cd ebyte-manager-v4
   ```

2. **Configure o arquivo .env**
   ```bash
   cp .env.example .env
   nano .env
   ```

3. **Personalize suas configurações**
   ```env
   # API Evolution
   API_URL=https://sua-evolution-api.com
   API_KEY=chave-administrativa
   
   # Identidade Visual
   PRIMARY_COLOR=#7341ff
   LOGO_LIGHT=https://sua-logo-clara.png
   LOGO_DARK=https://sua-logo-escura.png
   FAVICON=https://seu-favicon.ico
   
   # Informações de Suporte
   SUPPORT_EMAIL=suporte@suaempresa.com
   SUPPORT_WHATSAPP=(11) 99999-9999
   SUPPORT_HOURS=Segunda a Sexta: 9h às 18h
   
   # Branding
   APP_NAME=Sua Empresa WhatsApp
   APP_VERSION=4.0.0
   ```

4. **Ative sua licença**
   ```
   Acesse: https://seu-dominio.com/install.php
   ```

5. **Configure permissões**
   ```bash
   chmod 755 logs/
   chmod 644 .htaccess
   ```

## 📱 Como Funciona

### Para o Cliente Final

1. **Acesso Simples**
   - Cliente recebe credenciais por email/WhatsApp
   - Acessa o painel com Identificador + Chave
   - Interface intuitiva sem termos técnicos

2. **Conexão Facilitada**
   - QR Code grande e centralizado
   - Instruções passo a passo ilustradas
   - Feedback visual em cada etapa

3. **Uso Profissional**
   - Envio de mensagens em massa
   - Suporte a mídia (imagens, vídeos, PDFs)
   - Intervalos inteligentes anti-spam
   - Logs opcionais para acompanhamento

### Para o Administrador SaaS

1. **Deploy Rápido**
   - Configure uma vez, use para múltiplos clientes
   - Personalize cores e logos via .env
   - Sem necessidade de alterar código

2. **Gestão Simplificada**
   - Cada cliente com suas próprias credenciais
   - Isolamento total entre instâncias
   - Logs centralizados por cliente

3. **Suporte Integrado**
   - Informações de contato no sistema
   - Cliente nunca precisa saber sobre Evolution API
   - Reduz tickets de suporte

## 🎨 Personalização Avançada

### Cores e Temas
```env
# Cores principais (aceita qualquer cor hex)
PRIMARY_COLOR=#FF5722  # Laranja vibrante
PRIMARY_COLOR=#4CAF50  # Verde moderno
PRIMARY_COLOR=#2196F3  # Azul profissional
PRIMARY_COLOR=#9C27B0  # Roxo elegante
```

### Logos e Branding
- **Logos separados** para tema claro/escuro
- **Favicon personalizado** para identidade completa
- **Nome da empresa** em toda interface

### Mensagens e Idioma
- Todas as mensagens são **comerciais e amigáveis**
- Sem menções a "API", "Evolution" ou termos técnicos
- Focado na experiência do usuário final

## 📊 Arquitetura V4

```
ebyte-manager-v4/
├── 📁 assets/
│   └── 📁 js/
│       ├── 📄 app-v4.js       # Core do sistema com logs
│       └── 📄 modal-v3.js     # Sistema de modais
├── 📁 logs/                   # Logs do sistema
├── 📄 .env.example           # Template de configuração
├── 📄 .htaccess              # Segurança Apache
├── 📄 api.php                # API v3 com token auth
├── 📄 config.php             # Configurações centralizadas
├── 📄 index.php              # Interface SaaS moderna
├── 📄 install.php            # Instalador inteligente
├── 📄 license-manager.php    # Sistema de licenças
└── 📄 README.md              # Este arquivo
```

## 🔐 Segurança Aprimorada

### Autenticação por Token
- ✅ **Validação obrigatória** antes de qualquer operação
- ✅ **Token único** por instância
- ✅ **Sessão temporária** com expiração
- ✅ **Proteção CSRF** em todas as requisições

### Proteções Implementadas
- 🛡️ Rate limiting inteligente
- 🛡️ Sanitização completa de inputs
- 🛡️ Headers de segurança modernos
- 🛡️ Logs de auditoria detalhados

## 📈 Comparação de Versões

| Feature | V2.2.2 | V3.0.0 | V4.0.0 |
|---------|---------|---------|---------|
| Design | Básico | Melhorado | **SaaS Premium** |
| Autenticação | Nome apenas | Token opcional | **Token obrigatório** |
| Interface | Técnica | Semi-técnica | **100% Comercial** |
| Logs | Sempre visível | Sempre visível | **Ocultável** |
| Suporte | Externo | Externo | **Integrado** |
| Animações | Poucas | Médias | **Profissionais** |
| Responsivo | Sim | Sim | **Mobile-first** |
| Tema Dark | Sim | Sim | **Aprimorado** |
| Personalização | Limitada | Média | **Total via .env** |

## 🚀 Roadmap Futuro

- [ ] Dashboard analytics avançado
- [ ] Agendamento de mensagens
- [ ] Templates de mensagens
- [ ] API REST para integrações
- [ ] Multi-idioma (PT/EN/ES)
- [ ] Webhook notifications
- [ ] Backup automático

## 🐛 Solução de Problemas

### "Credenciais inválidas"
- Verifique se está usando o identificador correto (case-sensitive)
- Confirme se o token está completo e sem espaços
- Token é único por instância na Evolution API

### Logs não aparecem
- Clique no ícone de terminal na navbar
- Verifique se `ENABLE_LOGS=true` no .env
- Permissões de escrita na pasta logs/

### Personalização não funciona
- Limpe o cache do navegador (Ctrl+F5)
- Verifique sintaxe do .env (sem aspas em cores)
- URLs de imagens devem ser HTTPS

## 🤝 Suporte Premium

### Para Clientes Finais
- Use a **Central de Ajuda** no sistema
- Canais de contato personalizados
- Sem necessidade de conhecimento técnico

### Para Administradores
- 📧 **E-mail**: contato@ebyte.net.br
- 💬 **WhatsApp**: +55 11 96391-8906
- 🌐 **Site**: https://ebyte.net.br

## 📝 Changelog Completo

### [4.0.0] - 2025-06-16
#### 🎉 Versão SaaS - Reescrita Total
- 🎨 **Novo Design**: Interface premium com gradientes e animações
- 🔐 **Token Auth**: Segurança obrigatória em todas operações
- 💼 **Linguagem Comercial**: Removidos todos termos técnicos
- 📱 **Mobile First**: Experiência perfeita em qualquer dispositivo
- 🎯 **UX Melhorada**: Fluxos simplificados e intuitivos
- 📊 **Logs Inteligentes**: Sistema ocultável com badges
- 🛠️ **Central de Ajuda**: Suporte integrado com modal elegante
- 🎨 **Personalização Total**: Tudo configurável via .env

### [3.0.0] - 2025-06-15
#### 🔒 Segurança e Autenticação
- ✅ Implementação inicial de autenticação por token
- ✅ Validação de instância antes de conectar
- ✅ Melhorias na API para suportar tokens
- ✅ Correções de segurança críticas

### [2.2.2] - 2025-06-14
#### 🛡️ Sistema de Licenciamento
- ✅ Validação online obrigatória
- ✅ Proteção contra pirataria
- ✅ Diagnóstico no instalador
- ✅ Melhorias de estabilidade

## 📄 Licença

Este software é **proprietário** e protegido por direitos autorais.

### ✅ Permitido
- Uso em projetos próprios ilimitados
- Modificações para necessidades específicas
- Deploy em múltiplos servidores próprios
- Personalização completa via configuração

### ❌ Proibido
- Revenda ou redistribuição do código
- Compartilhamento com terceiros
- Uso em produtos concorrentes
- Remoção de créditos ou licença

**Nota**: Licença de código fonte disponível para revendedores autorizados.

---

<p align="center">
  <img src="https://img.shields.io/badge/Ebyte_Manager_V4-Premium_SaaS_Solution-gradient.svg" alt="Ebyte Manager V4"><br>
  <strong>Sistema Profissional para WhatsApp Business</strong><br>
  Desenvolvido com 💜 por <a href="https://github.com/elvisfalmeida">Elvis Almeida</a><br>
  © 2025 Ebyte Soluções - Todos os direitos reservados
</p>
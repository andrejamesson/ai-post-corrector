# AI Post Corrector v1.2 - Plugin WordPress

Um plugin poderoso para WordPress que utiliza Inteligência Artificial para corrigir, melhorar e resumir o conteúdo dos seus posts.

## 🚀 Funcionalidades

- **🎯 Geração de Títulos**: Gera títulos atrativos e otimizados para SEO baseados no conteúdo
- **Correção Gramatical**: Corrige gramática, ortografia e pontuação
- **Melhoria de Estilo**: Aprimora a clareza e fluidez do texto
- **Resumo Inteligente**: Cria resumos concisos do conteúdo
- **Suporte a Múltiplas APIs**: OpenAI (GPT-3.5/GPT-4) e Google Gemini
- **Interface Intuitiva**: Integração nativa com o editor do WordPress
- **Comparação Visual**: Visualize as alterações antes de aplicá-las
- **Atalhos de Teclado**: Acesso rápido às funcionalidades
- **Compatibilidade Total**: Funciona com Gutenberg e Editor Clássico
- **🔄 Atualizações Automáticas**: Sistema de atualizações via GitHub

## 📋 Requisitos

- WordPress 5.0 ou superior
- PHP 7.4 ou superior
- Chave de API de um provedor de IA (OpenAI ou Google Gemini)

## 🔧 Instalação

1. **Download do Plugin**
   - Faça o download da pasta `ai-post-corrector`
   - Ou clone este repositório

2. **Upload para WordPress**
   - Copie a pasta `ai-post-corrector` para `/wp-content/plugins/`
   - Ou faça upload via painel do WordPress (Plugins > Adicionar Novo > Enviar Plugin)

3. **Ativação**
   - Acesse o painel do WordPress
   - Vá em Plugins > Plugins Instalados
   - Ative o "AI Post Corrector"

## ⚙️ Configuração

### 1. Configurar API Key

1. No painel do WordPress, vá em **Configurações > AI Corrector**
2. Escolha seu provedor de IA:
   - **OpenAI**: Para usar GPT-3.5 ou GPT-4
   - **Google Gemini**: Para usar o Gemini Pro
3. Insira sua chave de API
4. Clique em "Salvar Configurações"

### 2. Obter Chaves de API

#### OpenAI
1. Acesse [platform.openai.com](https://platform.openai.com)
2. Faça login ou crie uma conta
3. Vá em "API Keys" no menu lateral
4. Clique em "Create new secret key"
5. Copie a chave gerada

#### Google Gemini
1. Acesse [makersuite.google.com](https://makersuite.google.com)
2. Faça login com sua conta Google
3. Vá em "Get API Key"
4. Crie uma nova chave de API
5. Copie a chave gerada

## 🎯 Como Usar

### Interface do Editor

Após a instalação, você verá um novo painel "Corretor com Inteligência Artificial" na lateral direita do editor de posts/páginas.

### Botões Disponíveis

1. **Corrigir Gramática** 📝
   - Corrige erros de gramática, ortografia e pontuação
   - Mantém o tom original do texto

2. **Melhorar Estilo** ⭐
   - Aprimora a clareza e fluidez
   - Torna o texto mais envolvente e profissional

3. **Resumir** 📄
   - Cria um resumo conciso do conteúdo
   - Mantém os pontos principais

### Atalhos de Teclado

- **Ctrl+Shift+T**: Gerar Título
- **Ctrl+Shift+C**: Correção Gramatical
- **Ctrl+Shift+I**: Melhoria de Estilo  
- **Ctrl+Shift+S**: Resumo

### Fluxo de Trabalho

1. Escreva seu conteúdo no editor
2. Clique no botão desejado ou use o atalho
3. Aguarde o processamento da IA
4. Visualize a comparação entre original e processado
5. Aceite ou cancele as alterações

## 🛡️ Segurança

- As chaves de API são armazenadas de forma segura no banco de dados do WordPress
- Todas as comunicações com APIs externas são criptografadas (HTTPS)
- Verificação de nonce para prevenir ataques CSRF
- Sanitização de dados de entrada e saída

## 🔄 Atualizações Automáticas

O plugin possui um sistema integrado de atualizações automáticas via GitHub:

### Como Funciona:
- ✅ **Verificação Automática**: O WordPress verifica automaticamente por novas versões
- ✅ **Notificação no Admin**: Aparece na página de plugins quando há atualizações
- ✅ **Atualização com 1 Clique**: Basta clicar em "Atualizar" como qualquer plugin
- ✅ **Baseado em Releases**: Usa as releases do GitHub para controle de versão

### Configuração:
1. O plugin já vem configurado para o repositório: `andrejamesson/ai-post-corrector`
2. As atualizações aparecem automaticamente em **Plugins > Plugins Instalados**
3. Links diretos para GitHub e Releases aparecem na descrição do plugin

### Para Desenvolvedores:
- Crie uma nova **Release** no GitHub para disponibilizar atualizações
- Use versionamento semântico (ex: v1.2.0, v1.3.0)
- Inclua changelog na descrição da release

## 🎨 Personalização

### CSS Customizado

Você pode personalizar a aparência do plugin adicionando CSS customizado:

```css
/* Personalizar botões */
.aic-action-btn {
    background: #your-color !important;
}

/* Personalizar modal */
.aic-modal-content {
    border-radius: 15px !important;
}
```

### Hooks para Desenvolvedores

O plugin oferece hooks para personalização avançada:

```php
// Filtrar o prompt enviado para a IA
add_filter('aic_ai_prompt', function($prompt, $content, $action_type) {
    // Seu código personalizado
    return $prompt;
}, 10, 3);

// Filtrar a resposta da IA
add_filter('aic_ai_response', function($response, $action_type) {
    // Seu código personalizado
    return $response;
}, 10, 2);
```

## 🔍 Solução de Problemas

### Erro: "Chave de API não configurada"
- Verifique se inseriu a chave corretamente em Configurações > AI Corrector
- Certifique-se de que a chave está ativa no provedor

### Erro: "Erro ao conectar-se à API"
- Verifique sua conexão com a internet
- Confirme se a chave de API está válida
- Verifique se há créditos disponíveis na sua conta

### Plugin não aparece no editor
- Certifique-se de que o plugin está ativado
- Limpe o cache do navegador
- Verifique se não há conflitos com outros plugins

### Botões não funcionam
- Verifique se há erros no console do navegador (F12)
- Desative outros plugins temporariamente para testar conflitos
- Certifique-se de que o JavaScript está habilitado

## 💰 Custos das APIs

### OpenAI
- GPT-3.5 Turbo: ~$0.002 por 1K tokens
- GPT-4: ~$0.03 por 1K tokens
- 1K tokens ≈ 750 palavras

### Google Gemini
- Gemini Pro: Gratuito até 60 requisições por minuto
- Planos pagos disponíveis para uso intensivo

## 🤝 Contribuição

Contribuições são bem-vindas! Para contribuir:

1. Faça um fork do projeto
2. Crie uma branch para sua feature (`git checkout -b feature/AmazingFeature`)
3. Commit suas mudanças (`git commit -m 'Add some AmazingFeature'`)
4. Push para a branch (`git push origin feature/AmazingFeature`)
5. Abra um Pull Request

## 📝 Changelog

### v1.2 (2024)
- 🔄 **Nova Funcionalidade**: Sistema de atualizações automáticas via GitHub
- 🔗 Integração completa com GitHub API
- 📦 Atualizações baseadas em releases do GitHub
- 🔧 Links diretos para repositório e releases na página de plugins
- ⚡ Verificação automática de novas versões

### v1.1 (2024)
- ✨ **Nova Funcionalidade**: Geração de títulos com IA
- 🎯 Títulos otimizados para SEO (40-60 caracteres)
- ⌨️ Novo atalho de teclado: Ctrl/Cmd + Shift + T
- 🎨 Design especial para o botão de geração de título
- 🔧 Compatibilidade com editores Gutenberg e Clássico

### v1.0 (2024)
- 🚀 Lançamento inicial
- ✏️ Correção gramatical com IA
- 🎨 Melhoria de estilo de texto
- 📝 Resumo automático de conteúdo
- 🔄 Modal de comparação
- ⌨️ Atalhos de teclado
- 🎛️ Suporte para OpenAI e Google Gemini

## 📄 Licença

Este projeto está licenciado sob a Licença GPL v2 ou posterior - veja o arquivo [LICENSE](LICENSE) para detalhes.

## 🆘 Suporte

Para suporte técnico:
- Abra uma issue no GitHub
- Entre em contato através do email: [seu-email@exemplo.com]

## 🙏 Agradecimentos

- Equipe do WordPress pela plataforma incrível
- OpenAI e Google pelos serviços de IA
- Comunidade WordPress pelo feedback e sugestões

---

**Desenvolvido com ❤️ para a comunidade WordPress**
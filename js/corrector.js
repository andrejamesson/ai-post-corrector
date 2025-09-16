jQuery(document).ready(function($) {
    
    // Função para obter o conteúdo do editor (compatível com Gutenberg e Classic)
    function getEditorContent() {
        // Tentar obter conteúdo do Gutenberg primeiro
        if (typeof wp !== 'undefined' && wp.data && wp.data.select('core/editor')) {
            try {
                return wp.data.select('core/editor').getEditedPostContent();
            } catch (e) {
                console.log('Gutenberg não disponível, tentando editor clássico');
            }
        }
        
        // Fallback para editor clássico
        if (typeof tinyMCE !== 'undefined' && tinyMCE.activeEditor && !tinyMCE.activeEditor.isHidden()) {
            return tinyMCE.activeEditor.getContent();
        }
        
        // Fallback para textarea
        var $content = $('#content');
        if ($content.length) {
            return $content.val();
        }
        
        return '';
    }
    
    // Função para definir o conteúdo do editor
    function setEditorContent(content) {
        // Tentar definir no Gutenberg primeiro
        if (typeof wp !== 'undefined' && wp.data && wp.data.dispatch('core/editor')) {
            try {
                wp.data.dispatch('core/editor').editPost({ content: content });
                return true;
            } catch (e) {
                console.log('Erro ao definir conteúdo no Gutenberg, tentando editor clássico');
            }
        }
        
        // Fallback para editor clássico
        if (typeof tinyMCE !== 'undefined' && tinyMCE.activeEditor && !tinyMCE.activeEditor.isHidden()) {
            tinyMCE.activeEditor.setContent(content);
            return true;
        }
        
        // Fallback para textarea
        var $content = $('#content');
        if ($content.length) {
            $content.val(content);
            return true;
        }
        
        return false;
    }
    
    // Função para mostrar modal de comparação
    function showComparisonModal(originalContent, correctedContent, actionType) {
        var actionNames = {
            'correct': 'Correção Gramatical',
            'improve': 'Melhoria de Estilo',
            'summarize': 'Resumo'
        };
        
        var modalHtml = `
            <div id="aic-comparison-modal" class="aic-modal">
                <div class="aic-modal-content">
                    <div class="aic-modal-header">
                        <h2>Resultado da ${actionNames[actionType]}</h2>
                        <span class="aic-modal-close">&times;</span>
                    </div>
                    <div class="aic-modal-body">
                        <div class="aic-comparison-container">
                            <div class="aic-original">
                                <h3>Texto Original</h3>
                                <div class="aic-content-box">${originalContent}</div>
                            </div>
                            <div class="aic-corrected">
                                <h3>Texto Processado</h3>
                                <div class="aic-content-box">${correctedContent}</div>
                            </div>
                        </div>
                    </div>
                    <div class="aic-modal-footer">
                        <button type="button" class="button button-secondary" id="aic-cancel">Cancelar</button>
                        <button type="button" class="button button-primary" id="aic-accept">Aceitar Alterações</button>
                    </div>
                </div>
            </div>
        `;
        
        // Remover modal existente se houver
        $('#aic-comparison-modal').remove();
        
        // Adicionar modal ao body
        $('body').append(modalHtml);
        
        // Mostrar modal
        $('#aic-comparison-modal').show();
        
        // Event handlers para o modal
        $('#aic-comparison-modal .aic-modal-close, #aic-cancel').on('click', function() {
            $('#aic-comparison-modal').remove();
        });
        
        $('#aic-accept').on('click', function() {
            setEditorContent(correctedContent);
            $('#aic-comparison-modal').remove();
            showStatus('Alterações aplicadas com sucesso!', 'success');
        });
        
        // Fechar modal ao clicar fora
        $('#aic-comparison-modal').on('click', function(e) {
            if (e.target === this) {
                $(this).remove();
            }
        });
    }
    
    // Função para mostrar status
    function showStatus(message, type = 'info') {
        var $status = $('#aic-status');
        $status.removeClass('aic-success aic-error aic-info');
        $status.addClass('aic-' + type);
        $status.text(message);
        
        if (type === 'success') {
            setTimeout(function() {
                $status.text('');
            }, 3000);
        }
    }
    
    /**
     * Manipular geração de título
     */
    function handleTitleGeneration() {
        const content = getEditorContent();
        
        if (!content.trim()) {
            showStatus('Por favor, escreva algum conteúdo antes de gerar o título.', 'error');
            return;
        }
        
        showStatus('Gerando título...', 'processing');
        
        $.ajax({
            url: aic_ajax_object.ajax_url,
            type: 'POST',
            data: {
                action: 'aic_generate_title',
                content: content,
                nonce: aic_ajax_object.nonce
            },
            success: function(response) {
                if (response.success) {
                    const generatedTitle = response.data;
                    
                    // Atualizar o campo de título
                    updateTitleField(generatedTitle);
                    
                    showStatus('Título gerado com sucesso!', 'success');
                } else {
                    showStatus('Erro: ' + response.data, 'error');
                }
            },
            error: function(xhr, status, error) {
                showStatus('Erro na requisição: ' + error, 'error');
            }
        });
    }
    
    /**
     * Atualizar campo de título
     */
    function updateTitleField(title) {
        // Para o editor Gutenberg
        if (wp.data && wp.data.select('core/editor')) {
            wp.data.dispatch('core/editor').editPost({ title: title });
        }
        // Para o editor clássico
        else if ($('#title').length) {
            $('#title').val(title);
        }
        // Para outros casos
        else if ($('input[name="post_title"]').length) {
            $('input[name="post_title"]').val(title);
        }
    }

    // Event handler para os botões de ação
    $('.aic-action-btn').on('click', function() {
        var $button = $(this);
        var $allButtons = $('.aic-action-btn');
        var $spinner = $('#aic-spinner');
        var actionType = $button.data('action');
        
        if (actionType === 'generate_title') {
            handleTitleGeneration();
            return;
        }
        
        // Obter conteúdo do editor
        var postContent = getEditorContent();
        
        if (!postContent || postContent.trim() === '') {
            showStatus('Por favor, escreva algum conteúdo antes de usar esta função.', 'error');
            return;
        }
        
        // Desabilitar todos os botões e mostrar spinner
        $allButtons.prop('disabled', true);
        $spinner.css('visibility', 'visible');
        
        var actionNames = {
            'correct': 'Corrigindo gramática...',
            'improve': 'Melhorando estilo...',
            'summarize': 'Criando resumo...'
        };
        
        showStatus(actionNames[actionType], 'info');
        
        // Fazer chamada AJAX
        $.ajax({
            url: aic_ajax_object.ajax_url,
            type: 'POST',
            data: {
                action: 'aic_correct_post_content',
                content: postContent,
                action_type: actionType,
                nonce: aic_ajax_object.nonce
            },
            success: function(response) {
                if (response.success) {
                    // Mostrar modal de comparação
                    showComparisonModal(postContent, response.data.corrected_content, actionType);
                } else {
                    showStatus('Erro: ' + response.data.message, 'error');
                }
            },
            error: function(xhr, status, error) {
                console.error('Erro AJAX:', error);
                showStatus('Erro de comunicação com o servidor.', 'error');
            },
            complete: function() {
                // Reabilitar botões e esconder spinner
                $allButtons.prop('disabled', false);
                $spinner.css('visibility', 'hidden');
            }
        });
    });
    
    // Adicionar atalhos de teclado
    $(document).on('keydown', function(e) {
        // Ctrl/Cmd + Shift + C para correção
        if ((e.ctrlKey || e.metaKey) && e.shiftKey && e.keyCode === 67) {
            e.preventDefault();
            $('.aic-action-btn[data-action="correct"]').click();
        }
        
        // Ctrl/Cmd + Shift + I para melhoria
        if ((e.ctrlKey || e.metaKey) && e.shiftKey && e.keyCode === 73) {
            e.preventDefault();
            $('.aic-action-btn[data-action="improve"]').click();
        }
        
        // Ctrl/Cmd + Shift + S para resumo
        if ((e.ctrlKey || e.metaKey) && e.shiftKey && e.keyCode === 83) {
            e.preventDefault();
            $('.aic-action-btn[data-action="summarize"]').click();
        }
        
        // Ctrl/Cmd + Shift + T para gerar título
        if ((e.ctrlKey || e.metaKey) && e.shiftKey && e.keyCode === 84) {
            e.preventDefault();
            $('.aic-action-btn[data-action="generate_title"]').click();
        }
    });
    
    // Adicionar tooltip com atalhos
    $('.aic-action-btn').each(function() {
        var $btn = $(this);
        var action = $btn.data('action');
        var shortcuts = {
            'correct': 'Ctrl+Shift+C',
            'improve': 'Ctrl+Shift+I',
            'summarize': 'Ctrl+Shift+S'
        };
        
        if (shortcuts[action]) {
            $btn.attr('title', $btn.attr('title') + ' (' + shortcuts[action] + ')');
        }
    });
});
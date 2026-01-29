(function($) {
    'use strict';
    
    $(document).ready(function() {
        
        // Copiar classe CSS do botão na meta box
        $(document).on('click', '.curriculo-copiar-btn', function() {
            const classe = $(this).data('classe');
            const $btn = $(this);
            const $msg = $btn.siblings('.curriculo-copiado');
            
            // Copia para clipboard
            navigator.clipboard.writeText(classe).then(function() {
                // Mostra mensagem
                $msg.fadeIn();
                
                // Esconde após 2 segundos
                setTimeout(function() {
                    $msg.fadeOut();
                }, 2000);
            });
        });
        
        // Copiar classe CSS clicando na tag na listagem
        $(document).on('click', '.curriculo-classe-copiar', function(e) {
            e.preventDefault();
            const classe = $(this).data('classe');
            const $tag = $(this);
            const originalText = $tag.text();
            
            // Copia para clipboard
            navigator.clipboard.writeText(classe).then(function() {
                // Feedback visual
                $tag.text('✓ Copiado!');
                $tag.css({
                    'background': '#46b450',
                    'color': '#fff'
                });
                
                // Volta ao normal após 1.5 segundos
                setTimeout(function() {
                    $tag.text(originalText);
                    $tag.css({
                        'background': '',
                        'color': ''
                    });
                }, 1500);
            });
        });
        
    });
    
})(jQuery);
(function($) {
    'use strict';
    
    // Cria o HTML do modal uma vez quando a página carrega
    const criarModal = () => {
        const html = `
            <div class="curriculo-modal-overlay" id="curriculoModal">
                <div class="curriculo-modal">
                    <div class="curriculo-modal-header">
                        <h2 class="curriculo-modal-titulo" id="curriculoTitulo"></h2>
                        <button class="curriculo-modal-fechar" id="curriculoFechar">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <div class="curriculo-modal-conteudo" id="curriculoConteudo"></div>
                </div>
            </div>
        `;
        $('body').append(html);
    };
    
    // Abre o modal
    const abrirModal = (titulo, conteudo) => {
        $('#curriculoTitulo').text(titulo);
        $('#curriculoConteudo').html(conteudo);
        $('#curriculoModal').addClass('ativo');
        $('body').css('overflow', 'hidden');
    };
    
    // Fecha o modal
    const fecharModal = () => {
        $('#curriculoModal').removeClass('ativo');
        $('body').css('overflow', '');
    };
    
    // Busca currículo via API
    const buscarCurriculo = (classe) => {
        $('#curriculoTitulo').text('');
        $('#curriculoConteudo').html('<div class="curriculo-modal-loading">Carregando...</div>');
        $('#curriculoModal').addClass('ativo');
        $('body').css('overflow', 'hidden');
        
        $.ajax({
            url: wpCurriculos.apiUrl + classe,
            method: 'GET',
            success: function(data) {
                abrirModal(data.titulo, data.conteudo);
            },
            error: function() {
                $('#curriculoConteudo').html('<div class="curriculo-modal-loading">Erro ao carregar currículo. Tente novamente.</div>');
            }
        });
    };
    
    // Inicializa quando o DOM estiver pronto
    $(document).ready(function() {
        // Cria o modal
        criarModal();
        
        // Detecta cliques em qualquer elemento com classe que começa com "ver-curriculo-"
        $(document).on('click', '[class*="ver-curriculo-"]', function(e) {
            e.preventDefault();
            
            // Pega todas as classes do elemento
            const classes = $(this).attr('class').split(' ');
            
            // Encontra a classe que começa com "ver-curriculo-"
            const classecurriculo = classes.find(c => c.startsWith('ver-curriculo-'));
            
            if (classecurriculo) {
                buscarCurriculo(classecurriculo);
            }
        });
        
        // Fecha o modal ao clicar no botão fechar
        $(document).on('click', '#curriculoFechar', fecharModal);
        
        // Fecha o modal ao clicar no overlay (fora do conteúdo)
        $(document).on('click', '#curriculoModal', function(e) {
            if (e.target === this) {
                fecharModal();
            }
        });
        
        // Fecha o modal com a tecla ESC
        $(document).keydown(function(e) {
            if (e.key === 'Escape' && $('#curriculoModal').hasClass('ativo')) {
                fecharModal();
            }
        });
    });
    
})(jQuery);
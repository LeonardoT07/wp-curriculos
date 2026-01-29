(function($) {
    'use strict';
    
    console.log('WP Currículos: Script carregado');
    console.log('API URL:', wpCurriculos.apiUrl);
    
    // Cria o HTML do modal
    const criarModal = () => {
        const html = `
            <div class="curriculo-modal-overlay" id="curriculoModal">
                <div class="curriculo-modal">
                    <div class="curriculo-modal-header" id="curriculoHeader">
                        <div style="flex: 1;">
                            <h2 class="curriculo-modal-titulo" id="curriculoTitulo"></h2>
                            <p class="curriculo-modal-descricao" id="curriculoDescricao"></p>
                        </div>
                        <button class="curriculo-modal-fechar" id="curriculoFechar" aria-label="Fechar">
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
        console.log('WP Currículos: Modal criado no DOM');
    };
    
    // Abre o modal
    const abrirModal = (titulo, descricao, conteudo) => {
        console.log('Abrindo modal:', {titulo, descricao});
        $('#curriculoTitulo').text(titulo);
        
        // Exibe descrição se existir
        if (descricao && descricao.trim() !== '') {
            $('#curriculoDescricao').text(descricao).show();
            $('#curriculoHeader').addClass('com-descricao');
        } else {
            $('#curriculoDescricao').hide();
            $('#curriculoHeader').removeClass('com-descricao');
        }
        
        $('#curriculoConteudo').html(conteudo);
        $('#curriculoModal').addClass('ativo');
        $('body').css('overflow', 'hidden');
    };
    
    // Fecha o modal
    const fecharModal = () => {
        console.log('Fechando modal');
        $('#curriculoModal').removeClass('ativo');
        $('body').css('overflow', '');
    };
    
    // Busca currículo via API
    const buscarCurriculo = (classe) => {
        console.log('Buscando currículo com classe:', classe);
        
        $('#curriculoTitulo').text('');
        $('#curriculoDescricao').hide();
        $('#curriculoConteudo').html('<div class="curriculo-modal-loading">Carregando...</div>');
        $('#curriculoModal').addClass('ativo');
        $('body').css('overflow', 'hidden');
        
        $.ajax({
            url: wpCurriculos.apiUrl + classe,
            method: 'GET',
            success: function(data) {
                console.log('Currículo carregado:', data);
                abrirModal(data.titulo, data.descricao, data.conteudo);
            },
            error: function(xhr, status, error) {
                console.error('Erro ao carregar currículo:', {xhr, status, error});
                $('#curriculoConteudo').html('<div class="curriculo-modal-loading">Erro ao carregar currículo. Tente novamente.</div>');
            }
        });
    };
    
    // Inicializa quando o DOM estiver pronto
    $(document).ready(function() {
        console.log('WP Currículos: Inicializando...');
        
        // Cria o modal
        criarModal();
        
        // Detecta cliques em elementos com classe que começa com "ver-curriculo-"
        $(document).on('click', '[class*="ver-curriculo-"]', function(e) {
            e.preventDefault();
            console.log('Botão clicado:', this);
            
            const classes = $(this).attr('class');
            console.log('Classes do elemento:', classes);
            
            if (!classes) {
                console.error('Elemento não tem classes');
                return;
            }
            
            const classesArray = classes.split(' ');
            const classeCurriculo = classesArray.find(c => c.startsWith('ver-curriculo-'));
            
            console.log('Classe encontrada:', classeCurriculo);
            
            if (classeCurriculo) {
                buscarCurriculo(classeCurriculo);
            } else {
                console.error('Nenhuma classe "ver-curriculo-*" encontrada');
            }
        });
        
        // Eventos de fechar
        $(document).on('click', '#curriculoFechar', fecharModal);
        
        $(document).on('click', '#curriculoModal', function(e) {
            if (e.target === this) {
                fecharModal();
            }
        });
        
        $(document).keydown(function(e) {
            if (e.key === 'Escape' && $('#curriculoModal').hasClass('ativo')) {
                fecharModal();
            }
        });
        
        console.log('WP Currículos: Pronto!');
    });
    
})(jQuery);
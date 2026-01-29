<?php
/**
 * Plugin Name: WP Currículos
 * Description: Sistema de currículos com modal moderno para páginas de curso
 * Version: 1.2
 * Author: Leonardo Tavares
 */

if (!defined('ABSPATH')) exit;

class WP_Curriculos {
    
    public function __construct() {
        add_action('init', [$this, 'registrar_post_type']);
        add_action('add_meta_boxes', [$this, 'adicionar_meta_box']);
        add_action('save_post', [$this, 'salvar_meta_box']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('rest_api_init', [$this, 'registrar_api']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
        add_filter('use_block_editor_for_post_type', [$this, 'desabilitar_gutenberg'], 10, 2);
        add_filter('manage_curriculo_posts_columns', [$this, 'customizar_colunas']);
        add_action('manage_curriculo_posts_custom_column', [$this, 'preencher_colunas'], 10, 2);
    }
    
    public function registrar_post_type() {
        register_post_type('curriculo', [
            'labels' => [
                'name' => 'Currículos',
                'singular_name' => 'Currículo',
                'add_new' => 'Adicionar Novo',
                'add_new_item' => 'Adicionar Novo Currículo',
                'edit_item' => 'Editar Currículo',
                'all_items' => 'Todos os Currículos',
                'view_item' => 'Ver Currículo',
                'search_items' => 'Buscar Currículos',
                'not_found' => 'Nenhum currículo encontrado',
                'not_found_in_trash' => 'Nenhum currículo na lixeira',
            ],
            'public' => false,
            'show_ui' => true,
            'menu_icon' => 'dashicons-id-alt',
            'supports' => ['title', 'editor', 'revisions'],
            'show_in_rest' => false,
            'menu_position' => 20,
        ]);
    }
    
    public function desabilitar_gutenberg($use_block_editor, $post_type) {
        if ($post_type === 'curriculo') {
            return false;
        }
        return $use_block_editor;
    }
    
    public function adicionar_meta_box() {
        // Meta box para descrição curta
        add_meta_box(
            'curriculo_descricao',
            'Descrição Curta',
            [$this, 'render_descricao_box'],
            'curriculo',
            'normal',
            'high'
        );
        
        // Meta box para classe CSS
        add_meta_box(
            'curriculo_classe_css',
            'Classe CSS do Botão',
            [$this, 'render_meta_box'],
            'curriculo',
            'side',
            'high'
        );
        
        // Meta box de instruções
        add_meta_box(
            'curriculo_instrucoes',
            'Como Usar',
            [$this, 'render_instrucoes'],
            'curriculo',
            'side',
            'default'
        );
    }
    
    public function render_descricao_box($post) {
        wp_nonce_field('curriculo_descricao_box', 'curriculo_descricao_box_nonce');
        $descricao = get_post_meta($post->ID, '_curriculo_descricao', true);
        ?>
        <div class="curriculo-descricao-box">
            <p class="description" style="margin-bottom: 10px;">
                Adicione um subtítulo profissional (ex: "Fisioterapeuta Especialista em Pilates", "Mestre em Biomecânica")
            </p>
            <input 
                type="text" 
                id="curriculo_descricao" 
                name="curriculo_descricao" 
                value="<?php echo esc_attr($descricao); ?>" 
                class="widefat"
                placeholder="Ex: Fisioterapeuta | Mestre em Reabilitação"
                style="font-size: 16px; padding: 10px;"
            >
        </div>
        <?php
    }
    
    public function render_meta_box($post) {
        wp_nonce_field('curriculo_meta_box', 'curriculo_meta_box_nonce');
        $classe = get_post_meta($post->ID, '_curriculo_classe', true);
        ?>
        <div class="curriculo-meta-box">
            <p>
                <label for="curriculo_classe"><strong>Classe CSS:</strong></label>
                <input 
                    type="text" 
                    id="curriculo_classe" 
                    name="curriculo_classe" 
                    value="<?php echo esc_attr($classe); ?>" 
                    class="widefat"
                    placeholder="ex: ver-curriculo-joao"
                >
            </p>
            
            <?php if ($classe): ?>
            <div class="curriculo-copiar-box">
                <button type="button" class="button button-primary curriculo-copiar-btn" data-classe="<?php echo esc_attr($classe); ?>">
                    <span class="dashicons dashicons-clipboard" style="margin-top: 3px;"></span>
                    Copiar Classe
                </button>
                <span class="curriculo-copiado" style="display:none; color: #46b450; margin-left: 10px;">✓ Copiado!</span>
            </div>
            <?php endif; ?>
        </div>
        <?php
    }
    
    public function render_instrucoes($post) {
        ?>
        <div class="curriculo-instrucoes">
            <ol>
                <li>Preencha o <strong>nome do profissional</strong> no título</li>
                <li>Adicione uma <strong>descrição curta</strong> (cargo/especialização)</li>
                <li>Escreva o <strong>currículo completo</strong> no editor abaixo</li>
                <li>Defina uma <strong>classe CSS única</strong> ao lado</li>
                <li>Clique em <strong>Publicar</strong></li>
                <li>Adicione um botão com essa classe na sua página</li>
            </ol>
            
            <div class="curriculo-dica">
                <strong>Dica:</strong> Use negrito, listas e parágrafos para organizar melhor o currículo!
            </div>
        </div>
        <?php
    }
    
    public function salvar_meta_box($post_id) {
        // Salva a classe CSS
        if (isset($_POST['curriculo_meta_box_nonce']) && 
            wp_verify_nonce($_POST['curriculo_meta_box_nonce'], 'curriculo_meta_box')) {
            
            if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
            if (!current_user_can('edit_post', $post_id)) return;
            
            if (isset($_POST['curriculo_classe'])) {
                $classe = sanitize_title($_POST['curriculo_classe']);
                update_post_meta($post_id, '_curriculo_classe', $classe);
            }
        }
        
        // Salva a descrição
        if (isset($_POST['curriculo_descricao_box_nonce']) && 
            wp_verify_nonce($_POST['curriculo_descricao_box_nonce'], 'curriculo_descricao_box')) {
            
            if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
            if (!current_user_can('edit_post', $post_id)) return;
            
            if (isset($_POST['curriculo_descricao'])) {
                $descricao = sanitize_text_field($_POST['curriculo_descricao']);
                update_post_meta($post_id, '_curriculo_descricao', $descricao);
            }
        }
    }
    
    public function customizar_colunas($columns) {
        $new_columns = [
            'cb' => $columns['cb'],
            'title' => 'Nome do Profissional',
            'descricao' => 'Descrição',
            'classe_css' => 'Classe CSS',
            'date' => 'Data',
        ];
        return $new_columns;
    }
    
    public function preencher_colunas($column, $post_id) {
        switch ($column) {
            case 'descricao':
                $descricao = get_post_meta($post_id, '_curriculo_descricao', true);
                if ($descricao) {
                    echo '<span style="color: #646970;">' . esc_html($descricao) . '</span>';
                } else {
                    echo '<span style="color: #999;">—</span>';
                }
                break;
                
            case 'classe_css':
                $classe = get_post_meta($post_id, '_curriculo_classe', true);
                if ($classe) {
                    echo '<code class="curriculo-classe-tag curriculo-classe-copiar" data-classe="' . esc_attr($classe) . '" title="Clique para copiar">' . esc_html($classe) . '</code>';
                } else {
                    echo '<span style="color: #999;">—</span>';
                }
                break;
        }
    }
    
    public function enqueue_admin_assets($hook) {
        global $post_type;
        
        if ('curriculo' !== $post_type) return;
        
        wp_enqueue_style(
            'wp-curriculos-admin',
            plugin_dir_url(__FILE__) . 'assets/css/admin.css',
            [],
            '1.2'
        );
        
        wp_enqueue_script(
            'wp-curriculos-admin',
            plugin_dir_url(__FILE__) . 'assets/js/admin.js',
            ['jquery'],
            '1.2',
            true
        );
    }
    
    public function enqueue_assets() {
        wp_enqueue_style(
            'wp-curriculos-modal',
            plugin_dir_url(__FILE__) . 'assets/css/modal.css',
            [],
            '1.2'
        );
        
        wp_enqueue_script(
            'wp-curriculos-modal',
            plugin_dir_url(__FILE__) . 'assets/js/modal.js',
            ['jquery'],
            '1.2',
            true
        );
        
        wp_localize_script('wp-curriculos-modal', 'wpCurriculos', [
            'apiUrl' => rest_url('wp-curriculos/v1/buscar/')
        ]);
    }
    
    public function registrar_api() {
        register_rest_route('wp-curriculos/v1', '/buscar/(?P<classe>[a-zA-Z0-9-_]+)', [
            'methods' => 'GET',
            'callback' => [$this, 'buscar_curriculo_por_classe'],
            'permission_callback' => '__return_true'
        ]);
    }
    
    public function buscar_curriculo_por_classe($request) {
        $classe = $request['classe'];
        
        $query = new WP_Query([
            'post_type' => 'curriculo',
            'posts_per_page' => 1,
            'meta_query' => [
                [
                    'key' => '_curriculo_classe',
                    'value' => $classe,
                    'compare' => '='
                ]
            ]
        ]);
        
        if ($query->have_posts()) {
            $query->the_post();
            $data = [
                'titulo' => get_the_title(),
                'descricao' => get_post_meta(get_the_ID(), '_curriculo_descricao', true),
                'conteudo' => apply_filters('the_content', get_the_content())
            ];
            wp_reset_postdata();
            return $data;
        }
        
        return new WP_Error('nao_encontrado', 'Currículo não encontrado', ['status' => 404]);
    }
}

new WP_Curriculos();
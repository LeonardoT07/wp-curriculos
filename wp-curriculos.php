<?php
/**
 * Plugin Name: WP Currículos
 * Description: Sistema de currículos com modal moderno para páginas de curso
 * Version: 1.0
 * Author: EWeber
 */

if (!defined('ABSPATH')) exit;

class WP_Curriculos {
    
    public function __construct() {
        // Registra o Custom Post Type
        add_action('init', [$this, 'registrar_post_type']);
        
        // Adiciona meta box para a classe CSS
        add_action('add_meta_boxes', [$this, 'adicionar_meta_box']);
        add_action('save_post', [$this, 'salvar_meta_box']);
        
        // Enfileira scripts e estilos no frontend
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
        
        // Registra endpoint REST API
        add_action('rest_api_init', [$this, 'registrar_api']);
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
            ],
            'public' => false,
            'show_ui' => true,
            'menu_icon' => 'dashicons-id-alt',
            'supports' => ['title', 'editor'],
            'show_in_rest' => true, // Habilita Gutenberg
        ]);
    }
    
    public function adicionar_meta_box() {
        add_meta_box(
            'curriculo_classe_css',
            'Classe CSS do Botão',
            [$this, 'render_meta_box'],
            'curriculo',
            'side',
            'high'
        );
    }
    
    public function render_meta_box($post) {
        wp_nonce_field('curriculo_meta_box', 'curriculo_meta_box_nonce');
        $classe = get_post_meta($post->ID, '_curriculo_classe', true);
        ?>
        <p>
            <label for="curriculo_classe">Classe CSS (ex: ver-curriculo-joao):</label>
            <input 
                type="text" 
                id="curriculo_classe" 
                name="curriculo_classe" 
                value="<?php echo esc_attr($classe); ?>" 
                style="width: 100%;"
                placeholder="ver-curriculo-profissional"
            >
        </p>
        <p class="description">
            Adicione essa classe no botão que deve abrir este currículo.
        </p>
        <?php
    }
    
    public function salvar_meta_box($post_id) {
        if (!isset($_POST['curriculo_meta_box_nonce'])) return;
        if (!wp_verify_nonce($_POST['curriculo_meta_box_nonce'], 'curriculo_meta_box')) return;
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        if (!current_user_can('edit_post', $post_id)) return;
        
        if (isset($_POST['curriculo_classe'])) {
            $classe = sanitize_text_field($_POST['curriculo_classe']);
            update_post_meta($post_id, '_curriculo_classe', $classe);
        }
    }
    
    public function enqueue_assets() {
        // CSS
        wp_enqueue_style(
            'wp-curriculos-modal',
            plugin_dir_url(__FILE__) . 'assets/css/modal.css',
            [],
            '1.0'
        );
        
        // JS
        wp_enqueue_script(
            'wp-curriculos-modal',
            plugin_dir_url(__FILE__) . 'assets/js/modal.js',
            ['jquery'],
            '1.0',
            true
        );
        
        // Passa a URL da API para o JS
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
                'conteudo' => apply_filters('the_content', get_the_content())
            ];
            wp_reset_postdata();
            return $data;
        }
        
        return new WP_Error('nao_encontrado', 'Currículo não encontrado', ['status' => 404]);
    }
}

// Inicializa o plugin
new WP_Curriculos();
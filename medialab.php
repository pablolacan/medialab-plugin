<?php
/**
 * Plugin Name: MediaLab
 * Description: Plugin minimal para gestionar posts de video y galerías del MediaLab
 * Version: 1.0.0
 * Author: Dojo Lab
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

// Constantes básicas
define('MEDIALAB_VERSION', '1.0.0');
define('MEDIALAB_PLUGIN_URL', plugin_dir_url(__FILE__));
define('MEDIALAB_PLUGIN_PATH', plugin_dir_path(__FILE__));

class MediaLab_Plugin {
    
    public function __construct() {
        add_action('init', array($this, 'init'));
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
    }
    
    public function init() {
        // Verificar ACF
        if (!class_exists('ACF')) {
            add_action('admin_notices', array($this, 'acf_missing_notice'));
            return;
        }
        
        // Cargar módulos
        $this->load_modules();
    }
    
    private function load_modules() {
        require_once MEDIALAB_PLUGIN_PATH . 'includes/posts/video-post.php';
        require_once MEDIALAB_PLUGIN_PATH . 'includes/posts/gallery-post.php';
        require_once MEDIALAB_PLUGIN_PATH . 'documentation.php';
    }
    
    public function add_admin_menu() {
        // Menú principal
        add_menu_page(
            'MediaLab',
            'MediaLab',
            'manage_options',
            'medialab',
            array($this, 'dashboard_page'),
            'dashicons-video-alt3',
            25
        );
        
        // Submenú Posts
        add_submenu_page(
            'medialab',
            'MediaLab - Posts',
            'Posts',
            'publish_posts',
            'medialab-posts',
            array($this, 'posts_page')
        );
    }
    
    public function enqueue_admin_scripts($hook) {
        // Solo cargar en páginas de MediaLab
        $medialab_pages = array(
            'toplevel_page_medialab',           
            'medialab_page_medialab-posts',     
            'medialab_page_medialab-video',     
            'medialab_page_medialab-gallery',   
            'medialab_page_medialab-docs',      
            'medialab_page_medialab-docs-general',
            'medialab_page_medialab-docs-videos',
            'medialab_page_medialab-docs-gallery'
        );
        
        if (!in_array($hook, $medialab_pages)) {
            return;
        }
        
        // CSS minimal
        wp_enqueue_style(
            'medialab-styles',
            MEDIALAB_PLUGIN_URL . 'assets/css/styles.css',
            array(),
            MEDIALAB_VERSION
        );
        
        // JS básico
        wp_enqueue_script(
            'medialab-admin',
            MEDIALAB_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery'),
            MEDIALAB_VERSION,
            true
        );
        
        // Para formularios
        if (in_array($hook, array('medialab_page_medialab-video', 'medialab_page_medialab-gallery'))) {
            wp_enqueue_media();
            
            wp_enqueue_script('select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js', array('jquery'), '4.0.13', true);
            wp_enqueue_style('select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css', array(), '4.0.13');
        }
        
        // AJAX
        wp_localize_script('medialab-admin', 'medialab_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('medialab_nonce')
        ));
    }
    
    public function dashboard_page() {
        $stats = $this->get_stats();
        ?>
        <div class="wrap">
            <div class="medialab-wrap">
                <div class="medialab-header">
                    <h1>🎬 MediaLab Dashboard</h1>
                    <p class="description">Panel central para gestionar contenido del MediaLab</p>
                </div>
                
                <div class="medialab-content">
                    <!-- Stats -->
                    <div class="medialab-stats">
                        <div class="medialab-stat-card">
                            <div class="medialab-stat-icon">🎥</div>
                            <div class="medialab-stat-number"><?php echo $stats['videos']; ?></div>
                            <div class="medialab-stat-label">Videos</div>
                        </div>
                        <div class="medialab-stat-card">
                            <div class="medialab-stat-icon">🖼️</div>
                            <div class="medialab-stat-number"><?php echo $stats['galleries']; ?></div>
                            <div class="medialab-stat-label">Galerías</div>
                        </div>
                        <div class="medialab-stat-card">
                            <div class="medialab-stat-icon">📝</div>
                            <div class="medialab-stat-number"><?php echo $stats['total']; ?></div>
                            <div class="medialab-stat-label">Total</div>
                        </div>
                    </div>
                    
                    <!-- Módulos -->
                    <div class="medialab-modules-grid">
                        <div class="medialab-module-card">
                            <div class="medialab-module-header">
                                <span class="medialab-module-icon">🎥</span>
                                <h3 class="medialab-module-title">Video Posts</h3>
                            </div>
                            <div class="medialab-module-body">
                                <p class="medialab-module-description">Crear posts de video con enlaces de YouTube, Vimeo, etc.</p>
                            </div>
                            <div class="medialab-module-footer">
                                <a href="<?php echo admin_url('admin.php?page=medialab-video'); ?>" class="button button-primary">Crear Video</a>
                            </div>
                        </div>
                        
                        <div class="medialab-module-card">
                            <div class="medialab-module-header">
                                <span class="medialab-module-icon">🖼️</span>
                                <h3 class="medialab-module-title">Gallery Posts</h3>
                            </div>
                            <div class="medialab-module-body">
                                <p class="medialab-module-description">Crear galerías de fotos para eventos y ceremonias.</p>
                            </div>
                            <div class="medialab-module-footer">
                                <a href="<?php echo admin_url('admin.php?page=medialab-gallery'); ?>" class="button button-primary">Crear Galería</a>
                            </div>
                        </div>
                        
                        <div class="medialab-module-card">
                            <div class="medialab-module-header">
                                <h3 class="medialab-module-title">Documentación</h3>
                            </div>
                            <div class="medialab-module-body">
                                <p class="medialab-module-description">Guías y tutoriales para usar MediaLab correctamente.</p>
                            </div>
                            <div class="medialab-module-footer">
                                <a href="<?php echo admin_url('admin.php?page=medialab-docs'); ?>" class="button button-secondary">Ver Docs</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    
    public function posts_page() {
        ?>
        <div class="wrap">
            <div class="medialab-wrap">
                <div class="medialab-header">
                    <h1>📝 MediaLab Posts</h1>
                    <p class="description">Selecciona el tipo de post que quieres crear</p>
                </div>
                
                <div class="medialab-content">
                    <div class="medialab-modules-grid">
                        <div class="medialab-module-card">
                            <div class="medialab-module-header">
                                <span class="medialab-module-icon">🎥</span>
                                <h3 class="medialab-module-title">Video Post</h3>
                            </div>
                            <div class="medialab-module-body">
                                <p class="medialab-module-description">Para webinars, conferencias, seminarios, etc.</p>
                            </div>
                            <div class="medialab-module-footer">
                                <a href="<?php echo admin_url('admin.php?page=medialab-video'); ?>" class="button button-primary">Crear Video</a>
                            </div>
                        </div>
                        
                        <div class="medialab-module-card">
                            <div class="medialab-module-header">
                                <span class="medialab-module-icon">🖼️</span>
                                <h3 class="medialab-module-title">Gallery Post</h3>
                            </div>
                            <div class="medialab-module-body">
                                <p class="medialab-module-description">Para eventos, ceremonias, graduaciones, etc.</p>
                            </div>
                            <div class="medialab-module-footer">
                                <a href="<?php echo admin_url('admin.php?page=medialab-gallery'); ?>" class="button button-primary">Crear Galería</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    
    private function get_stats() {
        // Stats básicas usando meta queries
        $video_count = new WP_Query(array(
            'post_type' => 'post',
            'meta_query' => array(array('key' => 'link', 'compare' => 'EXISTS')),
            'posts_per_page' => -1,
            'fields' => 'ids'
        ));
        
        $gallery_count = new WP_Query(array(
            'post_type' => 'post',
            'meta_query' => array(
                array('key' => 'link', 'compare' => 'NOT EXISTS'),
                array('key' => 'facultad', 'compare' => 'EXISTS')
            ),
            'posts_per_page' => -1,
            'fields' => 'ids'
        ));
        
        return array(
            'videos' => $video_count->found_posts,
            'galleries' => $gallery_count->found_posts,
            'total' => $video_count->found_posts + $gallery_count->found_posts
        );
    }
    
    public function acf_missing_notice() {
        echo '<div class="notice notice-error"><p><strong>MediaLab:</strong> Este plugin requiere Advanced Custom Fields (ACF) para funcionar.</p></div>';
    }
}

// Inicializar
new MediaLab_Plugin();
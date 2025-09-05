<?php
/**
 * Plugin Name: MediaLab
 * Plugin URI: https://medialab.com
 * Description: Plugin central para gestionar todas las funcionalidades del MediaLab - Posts de video, galerías, graduaciones y más.
 * Version: 1.0.0
 * Author: MediaLab Team
 * License: GPL v2 or later
 * Text Domain: medialab
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

// Definir constantes del plugin
define('MEDIALAB_VERSION', '1.0.0');
define('MEDIALAB_PLUGIN_URL', plugin_dir_url(__FILE__));
define('MEDIALAB_PLUGIN_PATH', plugin_dir_path(__FILE__));

class MediaLab_Plugin {
    
    public function __construct() {
        add_action('init', array($this, 'init'));
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        
        // Hooks de activación
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }
    
    public function init() {
        // Verificar si ACF está activo
        if (!class_exists('ACF')) {
            add_action('admin_notices', array($this, 'acf_missing_notice'));
            return;
        }
        
        // Cargar módulos
        $this->load_modules();
    }
    
    private function load_modules() {
        // Cargar módulo de video posts
        require_once MEDIALAB_PLUGIN_PATH . 'includes/posts/video-post.php';
        
        // Cargar módulo de documentación
        require_once MEDIALAB_PLUGIN_PATH . 'documentation.php';
        
        // Futuro:
        // require_once MEDIALAB_PLUGIN_PATH . 'includes/posts/gallery-post.php';
        // require_once MEDIALAB_PLUGIN_PATH . 'includes/posts/graduation-post.php';
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
        
        // El submenú de video se agrega desde video-post.php
    }
    
    public function dashboard_page() {
        echo '<div class="wrap">';
        echo '<h1>🎬 MediaLab Dashboard</h1>';
        echo '<div class="medialab-dashboard">';
        echo '<p>Bienvenido al panel central de MediaLab</p>';
        
        // Cards de módulos
        echo '<div class="medialab-modules">';
        echo '<div class="module-card">';
        echo '<h3>📹 Posts</h3>';
        echo '<p>Gestiona posts de video, galerías y graduaciones</p>';
        echo '<a href="' . admin_url('admin.php?page=medialab-posts') . '" class="button button-primary">Ir a Posts</a>';
        echo '</div>';
        echo '</div>';
        
        echo '</div>';
        echo '</div>';
    }
    
    public function posts_page() {
        echo '<div class="wrap">';
        echo '<h1>📹 MediaLab Posts</h1>';
        echo '<p>Selecciona el tipo de post que quieres crear:</p>';
        
        echo '<div class="medialab-post-types">';
        
        // Video Post (activo)
        echo '<div class="post-type-card active">';
        echo '<h3>🎥 Video Post</h3>';
        echo '<p>Publica videos con información detallada</p>';
        echo '<a href="' . admin_url('admin.php?page=medialab-video') . '" class="button button-primary">Crear Video</a>';
        echo '</div>';
        
        // Gallery Post (próximamente)
        echo '<div class="post-type-card disabled">';
        echo '<h3>🖼️ Gallery Post</h3>';
        echo '<p>Próximamente - Galerías de imágenes</p>';
        echo '<button class="button" disabled>Próximamente</button>';
        echo '</div>';
        
        // Graduation Post (próximamente)
        echo '<div class="post-type-card disabled">';
        echo '<h3>🎓 Graduation Post</h3>';
        echo '<p>Próximamente - Posts de graduación</p>';
        echo '<button class="button" disabled>Próximamente</button>';
        echo '</div>';
        
        echo '</div>';
        echo '</div>';
    }
    
    public function enqueue_admin_scripts($hook) {
        // Solo cargar en páginas de MediaLab
        if (strpos($hook, 'medialab') === false) {
            return;
        }
        
        wp_enqueue_style(
            'medialab-admin-style',
            MEDIALAB_PLUGIN_URL . 'style.css',
            array(),
            MEDIALAB_VERSION
        );
        
        wp_enqueue_script(
            'medialab-admin-script',
            MEDIALAB_PLUGIN_URL . 'script.js',
            array('jquery'),
            MEDIALAB_VERSION,
            true
        );
        
        // Localizar script para AJAX
        wp_localize_script('medialab-admin-script', 'medialab_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('medialab_nonce')
        ));
    }
    
    public function activate() {
        // Crear opciones por defecto
        add_option('medialab_version', MEDIALAB_VERSION);
        add_option('medialab_settings', array(
            'video_post_status' => 'draft',
            'enable_notifications' => true
        ));
        
        // Limpiar permalinks
        flush_rewrite_rules();
    }
    
    public function deactivate() {
        flush_rewrite_rules();
    }
    
    public function acf_missing_notice() {
        echo '<div class="notice notice-error is-dismissible">';
        echo '<p><strong>MediaLab:</strong> Este plugin requiere Advanced Custom Fields (ACF) para funcionar correctamente.</p>';
        echo '</div>';
    }
}

// Inicializar el plugin
new MediaLab_Plugin();
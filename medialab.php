<?php
/**
 * Plugin Name: MediaLab
 * Description: Plugin minimal para gestionar posts de video y galerías del MediaLab
 * Version: 0.4.1
 * Author: Dojo Lab
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

// Constantes básicas
define('MEDIALAB_VERSION', '0.4.1');
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
        require_once MEDIALAB_PLUGIN_PATH . 'includes/posts/graduation-post.php';
    }
    
    public function add_admin_menu() {
        // Menú principal - Dashboard de bienvenida
        add_menu_page(
            'MediaLab',
            'MediaLab',
            'manage_options',
            'medialab',
            array($this, 'dashboard_page'),
            'dashicons-video-alt3',
            25
        );
        
        // Submenús directos a cada tipo de post
        add_submenu_page(
            'medialab',
            'MediaLab - Video Post',
            'Video Post',
            'publish_posts',
            'medialab-video',
            array($this, 'video_page')
        );
        
        add_submenu_page(
            'medialab',
            'MediaLab - Gallery Post',
            'Gallery Post',
            'publish_posts',
            'medialab-gallery',
            array($this, 'gallery_page')
        );
        
        add_submenu_page(
            'medialab',
            'MediaLab - Graduation Post',
            'Graduation Post',
            'publish_posts',
            'medialab-graduation',
            array($this, 'graduation_page')
        );
    }
    
    public function enqueue_admin_scripts($hook) {
        // Solo cargar en páginas de MediaLab
        $medialab_pages = array(
            'toplevel_page_medialab',           
            'medialab_page_medialab-video',     
            'medialab_page_medialab-gallery',
            'medialab_page_medialab-graduation'
        );
        
        if (!in_array($hook, $medialab_pages)) {
            return;
        }
        
        // JS básico para formularios
        if (in_array($hook, array('medialab_page_medialab-video', 'medialab_page_medialab-gallery', 'medialab_page_medialab-graduation'))) {
            wp_enqueue_media();
            
            wp_enqueue_script('select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js', array('jquery'), '4.0.13', true);
            wp_enqueue_style('select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css', array(), '4.0.13');
            
            // AJAX
            wp_enqueue_script('medialab-admin', MEDIALAB_PLUGIN_URL . 'assets/js/admin.js', array('jquery'), MEDIALAB_VERSION, true);
            wp_localize_script('medialab-admin', 'medialab_ajax', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('medialab_nonce')
            ));
        }
    }
    
    public function dashboard_page() {
        ?>
        <div class="wrap">
            <h1>Bienvenido a MediaLab</h1>
            <p class="description">Plugin para gestionar contenido multimedia del MediaLab - Versión 0.4.1 (En pruebas)</p>
            
            <div class="card-container" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 20px; margin-top: 30px;">
                
                <!-- Video Post Card -->
                <div class="card">
                    <h2 class="title">🎥 Video Post</h2>
                    <p>Publica webinars, conferencias, seminarios y eventos en video.</p>
                    
                    <div class="card-content">
                        <h4>Perfecto para:</h4>
                        <ul>
                            <li>Webinars y conferencias virtuales</li>
                            <li>Clases magistrales grabadas</li>
                            <li>Seminarios y talleres</li>
                            <li>Videos de YouTube, Vimeo o Facebook</li>
                        </ul>
                    </div>
                    
                    <div class="card-actions">
                        <a href="<?php echo admin_url('admin.php?page=medialab-video'); ?>" 
                           class="button button-primary button-large">
                            Crear Video Post
                        </a>
                    </div>
                </div>
                
                <!-- Gallery Post Card -->
                <div class="card">
                    <h2 class="title">🖼️ Gallery Post</h2>
                    <p>Documenta eventos presenciales con galerías de fotos.</p>
                    
                    <div class="card-content">
                        <h4>Perfecto para:</h4>
                        <ul>
                            <li>Eventos presenciales documentados</li>
                            <li>Inauguraciones y actos protocolarios</li>
                            <li>Actividades con múltiples fotos</li>
                            <li>Cualquier evento sin video principal</li>
                        </ul>
                    </div>
                    
                    <div class="card-actions">
                        <a href="<?php echo admin_url('admin.php?page=medialab-gallery'); ?>" 
                           class="button button-primary button-large">
                            Crear Gallery Post
                        </a>
                    </div>
                </div>
                
                <!-- Graduation Post Card -->
                <div class="card" style="border-left: 4px solid #d4a574;">
                    <h2 class="title">🎓 Graduation Post</h2>
                    <p>Contenido especial para ceremonias de graduación.</p>
                    
                    <div class="card-content">
                        <h4>Específico para:</h4>
                        <ul>
                            <li>Ceremonias de graduación completas</li>
                            <li>Video de ceremonia + galería de fotos</li>
                            <li>Solo video o solo fotos de graduación</li>
                            <li>Categorización automática</li>
                        </ul>
                    </div>
                    
                    <div class="card-actions">
                        <a href="<?php echo admin_url('admin.php?page=medialab-graduation'); ?>" 
                           class="button button-primary button-large" 
                           style="background: #d4a574; border-color: #d4a574; box-shadow: 0 1px 0 #b8935f;">
                            Crear Graduation Post
                        </a>
                    </div>
                </div>
                
            </div>
            
            <!-- Tips importantes -->
            <div class="postbox" style="margin-top: 40px;">
                <div class="postbox-header">
                    <h2>💡 Tips Importantes</h2>
                </div>
                <div class="inside">
                    <div class="notice-container" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 15px;">
                        
                        <div class="notice notice-info inline" style="margin: 0;">
                            <p><strong>📸 Imágenes optimizadas:</strong><br>
                            Máximo 2MB y 1500px por lado. Usa TinyPNG para comprimir.</p>
                        </div>
                        
                        <div class="notice notice-warning inline" style="margin: 0;">
                            <p><strong>📅 Fechas correctas:</strong><br>
                            Siempre usa la fecha del evento, no la de publicación.</p>
                        </div>
                        
                        <div class="notice notice-success inline" style="margin: 0;">
                            <p><strong>🏫 Nombres de facultad:</strong><br>
                            Usa nombres cortos: FISICC, FACTI, Medicina, etc.</p>
                        </div>
                        
                        <div class="notice inline" style="margin: 0; background: #f8f4ff; border-left-color: #9c27b0;">
                            <p><strong>🎓 Para graduaciones:</strong><br>
                            Siempre usa Graduation Post, aunque solo tengas video o fotos.</p>
                        </div>
                        
                    </div>
                </div>
            </div>
            
            <!-- Footer info -->
            <div class="notice notice-info" style="margin-top: 30px; background: #f0f6fc; border-left-color: #0073aa;">
                <p><strong>ℹ️ Versión 0.4.1 - En pruebas:</strong> 
                Esta versión está siendo probada. Reporta cualquier problema o sugerencia al equipo de desarrollo.</p>
            </div>
        </div>
        <?php
    }
    
    public function video_page() {
        include MEDIALAB_PLUGIN_PATH . 'views/posts/video-form.php';
    }
    
    public function gallery_page() {
        include MEDIALAB_PLUGIN_PATH . 'views/posts/gallery-form.php';
    }
    
    public function graduation_page() {
        include MEDIALAB_PLUGIN_PATH . 'views/posts/graduation-form.php';
    }
    
    public function acf_missing_notice() {
        ?>
        <div class="notice notice-error">
            <p>
                <strong>MediaLab:</strong> Este plugin requiere Advanced Custom Fields (ACF) para funcionar correctamente.
                <a href="<?php echo admin_url('plugin-install.php?s=advanced+custom+fields&tab=search&type=term'); ?>" class="button button-secondary">
                    Instalar ACF
                </a>
            </p>
        </div>
        <?php
    }
}

// Inicializar
new MediaLab_Plugin();
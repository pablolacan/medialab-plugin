<?php
/**
 * Plugin Name: MediaLab
 * Plugin URI: https://medialab.galileo.edu
 * Description: Plugin central para gestionar todas las funcionalidades del MediaLab - Posts de video, galerías, graduaciones y más.
 * Version: 1.0.1
 * Author: Dojo Lab
 * License: GPL v2 or later
 * Text Domain: medialab
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

// Definir constantes del plugin
define('MEDIALAB_VERSION', '1.0.1');
define('MEDIALAB_PLUGIN_URL', plugin_dir_url(__FILE__));
define('MEDIALAB_PLUGIN_PATH', plugin_dir_path(__FILE__));

// ===== FUNCIONES AUXILIARES GLOBALES =====

/**
 * Función auxiliar para padding hexadecimal
 */
function medialab_hex_pad($val) {
    return str_pad(dechex($val), 2, '0', STR_PAD_LEFT);
}

/**
 * Función auxiliar para filtrar archivos
 */
function medialab_filter_temp_files($file) {
    return !in_array(basename($file), array('.', '..', '.htaccess'));
}

/**
 * Función de limpieza diaria
 */
function medialab_daily_cleanup_function() {
    // Limpiar archivos temporales
    $upload_dir = wp_upload_dir();
    $temp_dir = $upload_dir['basedir'] . '/medialab/temp';
    
    if (is_dir($temp_dir)) {
        $files = glob($temp_dir . '/*');
        $now = time();
        
        foreach ($files as $file) {
            if (is_file($file) && ($now - filemtime($file)) > (24 * 60 * 60)) { // 24 horas
                unlink($file);
            }
        }
    }
    
    // Log de limpieza
    medialab_log('Limpieza diaria ejecutada');
}

/**
 * Función de desinstalación del plugin
 */
function medialab_uninstall_plugin() {
    // Eliminar opciones
    delete_option('medialab_version');
    delete_option('medialab_settings');
    delete_option('medialab_primary_color');
    delete_option('medialab_secondary_color');
    
    // Eliminar eventos programados
    wp_clear_scheduled_hook('medialab_daily_cleanup');
    
    // Eliminar directorio de uploads (opcional)
    $upload_dir = wp_upload_dir();
    $medialab_dir = $upload_dir['basedir'] . '/medialab';
    
    if (is_dir($medialab_dir)) {
        // Solo eliminar si está vacío o solo contiene archivos temporales
        $files = glob($medialab_dir . '/{,.}*', GLOB_BRACE);
        if ($files) {
            $files = array_filter($files, 'medialab_filter_temp_files');
        }
        
        if (empty($files)) {
            rmdir($medialab_dir);
        }
    }
}

class MediaLab_Plugin {
    
    public function __construct() {
        add_action('init', array($this, 'init'));
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        
        // Hooks de activación
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        
        // Hook de actualización
        add_action('admin_init', array($this, 'check_version'));
    }
    
    public function init() {
        // Verificar si ACF está activo
        if (!class_exists('ACF')) {
            add_action('admin_notices', array($this, 'acf_missing_notice'));
            return;
        }
        
        // Cargar traducciones
        load_plugin_textdomain('medialab', false, dirname(plugin_basename(__FILE__)) . '/languages');
        
        // Cargar módulos
        $this->load_modules();
    }
    
    private function load_modules() {
        // Cargar módulo de video posts
        require_once MEDIALAB_PLUGIN_PATH . 'includes/posts/video-post.php';
        
        // Cargar módulo de gallery posts
        require_once MEDIALAB_PLUGIN_PATH . 'includes/posts/gallery-post.php';
        
        // Cargar módulo de documentación
        require_once MEDIALAB_PLUGIN_PATH . 'documentation.php';
        
        // Futuro:
        // require_once MEDIALAB_PLUGIN_PATH . 'includes/posts/graduation-post.php';
    }
    
    public function add_admin_menu() {
        // Menú principal
        add_menu_page(
            __('MediaLab', 'medialab'),
            __('MediaLab', 'medialab'),
            'manage_options',
            'medialab',
            array($this, 'dashboard_page'),
            'dashicons-video-alt3',
            25
        );
        
        // Submenú Posts
        add_submenu_page(
            'medialab',
            __('MediaLab - Posts', 'medialab'),
            __('Posts', 'medialab'),
            'publish_posts',
            'medialab-posts',
            array($this, 'posts_page')
        );
        
        // Los submenús de video y gallery se agregan desde sus respectivos archivos
    }
    
    public function dashboard_page() {
        // Obtener estadísticas
        $stats = $this->get_dashboard_stats();
        
        // Fallback si no existe la vista
        if (file_exists(MEDIALAB_PLUGIN_PATH . 'views/dashboard.php')) {
            include MEDIALAB_PLUGIN_PATH . 'views/dashboard.php';
        } else {
            $this->render_fallback_dashboard($stats);
        }
    }
    
    public function posts_page() {
        // Fallback si no existe la vista
        if (file_exists(MEDIALAB_PLUGIN_PATH . 'views/posts.php')) {
            include MEDIALAB_PLUGIN_PATH . 'views/posts.php';
        } else {
            $this->render_fallback_posts();
        }
    }
    
    private function render_fallback_dashboard($stats) {
        echo '<div class="wrap">';
        echo '<h1>🎬 MediaLab Dashboard</h1>';
        echo '<div class="medialab-dashboard">';
        echo '<p>Bienvenido al panel central de MediaLab</p>';
        
        if ($stats) {
            echo '<div class="medialab-stats">';
            echo '<div class="stat-item">📹 Videos: ' . $stats['video_posts'] . '</div>';
            echo '<div class="stat-item">🖼️ Galerías: ' . $stats['gallery_posts'] . '</div>';
            echo '<div class="stat-item">📝 Total Posts: ' . $stats['total_posts'] . '</div>';
            echo '</div>';
        }
        
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
    
    private function render_fallback_posts() {
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
        
        // Gallery Post (activo)
        echo '<div class="post-type-card active">';
        echo '<h3>🖼️ Gallery Post</h3>';
        echo '<p>Crea galerías de imágenes con Gallery Block nativo</p>';
        echo '<a href="' . admin_url('admin.php?page=medialab-gallery') . '" class="button button-primary">Crear Galería</a>';
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
        
        // ===== ESTILOS MODULARES =====
        
        // 1. Core styles (variables CSS, base, botones, alertas) - SIEMPRE se carga
        wp_enqueue_style(
            'medialab-core',
            MEDIALAB_PLUGIN_URL . 'assets/css/core.css',
            array(),
            MEDIALAB_VERSION
        );
        
        // 2. Form styles - Para páginas con formularios
        if (strpos($hook, 'medialab-video') !== false || 
            strpos($hook, 'medialab-gallery') !== false || 
            strpos($hook, 'medialab-graduation') !== false) {
            
            wp_enqueue_style(
                'medialab-forms',
                MEDIALAB_PLUGIN_URL . 'assets/css/forms.css',
                array('medialab-core'),
                MEDIALAB_VERSION
            );
        }
        
        // 3. Dashboard styles - Para páginas principales
        if (strpos($hook, 'toplevel_page_medialab') !== false || 
            strpos($hook, 'medialab-posts') !== false) {
            
            wp_enqueue_style(
                'medialab-dashboard',
                MEDIALAB_PLUGIN_URL . 'assets/css/dashboard.css',
                array('medialab-core'),
                MEDIALAB_VERSION
            );
        }
        
        // 4. Documentation styles - Solo para páginas de documentación
        if (strpos($hook, 'medialab-docs') !== false) {
            wp_enqueue_style(
                'medialab-docs',
                MEDIALAB_PLUGIN_URL . 'assets/css/documentation.css',
                array('medialab-core'),
                MEDIALAB_VERSION
            );
        }
        
        // 5. Main overrides - SIEMPRE al final para overrides de WordPress
        $dependencies = array('medialab-core');
        
        // Agregar dependencias según la página
        if (strpos($hook, 'medialab-video') !== false || 
            strpos($hook, 'medialab-gallery') !== false || 
            strpos($hook, 'medialab-graduation') !== false) {
            $dependencies[] = 'medialab-forms';
        }
        
        if (strpos($hook, 'toplevel_page_medialab') !== false || 
            strpos($hook, 'medialab-posts') !== false) {
            $dependencies[] = 'medialab-dashboard';
        }
        
        if (strpos($hook, 'medialab-docs') !== false) {
            $dependencies[] = 'medialab-docs';
        }
        
        wp_enqueue_style(
            'medialab-main',
            MEDIALAB_PLUGIN_URL . 'assets/css/main.css',
            $dependencies,
            MEDIALAB_VERSION
        );
        
        // ===== SCRIPTS =====
        
        // Script principal
        wp_enqueue_script(
            'medialab-admin-script',
            MEDIALAB_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery'),
            MEDIALAB_VERSION,
            true
        );
        
        // Scripts específicos para formularios
        if (strpos($hook, 'medialab-video') !== false || 
            strpos($hook, 'medialab-gallery') !== false) {
            
            // Media Library (para selección de imágenes)
            wp_enqueue_media();
            
            // Select2 para categorías
            wp_enqueue_script(
                'select2',
                'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js',
                array('jquery'),
                '4.0.13',
                true
            );
            
            wp_enqueue_style(
                'select2',
                'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css',
                array(),
                '4.0.13'
            );
        }
        
        // Localizar script para AJAX
        wp_localize_script('medialab-admin-script', 'medialab_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('medialab_nonce')
        ));
    }
    
    /**
     * Obtener estadísticas para el dashboard
     */
    private function get_dashboard_stats() {
        // Obtener posts por tipo usando meta query
        $video_posts = new WP_Query(array(
            'post_type' => 'post',
            'meta_query' => array(
                array(
                    'key' => 'link',
                    'compare' => 'EXISTS'
                )
            ),
            'posts_per_page' => -1,
            'fields' => 'ids'
        ));
        
        $gallery_posts = new WP_Query(array(
            'post_type' => 'post',
            'meta_query' => array(
                array(
                    'key' => 'link',
                    'compare' => 'NOT EXISTS'
                ),
                array(
                    'key' => 'facultad',
                    'compare' => 'EXISTS'
                )
            ),
            'posts_per_page' => -1,
            'fields' => 'ids'
        ));
        
        // Posts totales publicados
        $total_posts = wp_count_posts();
        
        return array(
            'video_posts' => $video_posts->found_posts,
            'gallery_posts' => $gallery_posts->found_posts,
            'total_posts' => $total_posts->publish,
            'draft_posts' => $total_posts->draft,
            'recent_posts' => $this->get_recent_posts(5)
        );
    }
    
    /**
     * Obtener posts recientes
     */
    private function get_recent_posts($limit = 5) {
        $posts = get_posts(array(
            'numberposts' => $limit,
            'post_status' => 'publish',
            'meta_query' => array(
                array(
                    'key' => 'facultad',
                    'compare' => 'EXISTS'
                )
            )
        ));
        
        $recent = array();
        foreach ($posts as $post) {
            $type = get_post_meta($post->ID, 'link', true) ? 'video' : 'gallery';
            $recent[] = array(
                'id' => $post->ID,
                'title' => $post->post_title,
                'type' => $type,
                'date' => $post->post_date,
                'edit_url' => get_edit_post_link($post->ID)
            );
        }
        
        return $recent;
    }
    
    /**
     * Verificar versión y ejecutar actualizaciones si es necesario
     */
    public function check_version() {
        $installed_version = get_option('medialab_version', '0.0.0');
        
        if (version_compare($installed_version, MEDIALAB_VERSION, '<')) {
            $this->upgrade($installed_version);
            update_option('medialab_version', MEDIALAB_VERSION);
        }
    }
    
    /**
     * Ejecutar rutinas de actualización
     */
    private function upgrade($from_version) {
        // Futuras actualizaciones se manejarían aquí
        if (version_compare($from_version, '1.0.1', '<')) {
            // Ejemplo: migrar configuraciones antiguas
            $old_settings = get_option('medialab_old_settings', array());
            if (!empty($old_settings)) {
                // Migrar a nuevo formato
                update_option('medialab_settings', $old_settings);
                delete_option('medialab_old_settings');
            }
        }
    }
    
    public function activate() {
        // Crear opciones por defecto
        add_option('medialab_version', MEDIALAB_VERSION);
        add_option('medialab_settings', array(
            'video_post_status' => 'publish',
            'gallery_post_status' => 'publish',
            'enable_notifications' => true,
            'auto_save_drafts' => true,
            'image_optimization' => true,
            'max_gallery_images' => 50
        ));
        
        // Opciones de personalización
        add_option('medialab_primary_color', '#2563eb');
        add_option('medialab_secondary_color', '#64748b');
        
        // Crear directorio de assets si no existe
        $upload_dir = wp_upload_dir();
        $medialab_dir = $upload_dir['basedir'] . '/medialab';
        
        if (!file_exists($medialab_dir)) {
            wp_mkdir_p($medialab_dir);
            
            // Crear archivo .htaccess para proteger archivos temporales
            $htaccess_content = "Options -Indexes\n";
            $htaccess_content .= "deny from all\n";
            file_put_contents($medialab_dir . '/.htaccess', $htaccess_content);
        }
        
        // Limpiar permalinks
        flush_rewrite_rules();
        
        // Programar evento de limpieza diaria
        if (!wp_next_scheduled('medialab_daily_cleanup')) {
            wp_schedule_event(time(), 'daily', 'medialab_daily_cleanup');
        }
    }
    
    public function deactivate() {
        // Limpiar permalinks
        flush_rewrite_rules();
        
        // Cancelar eventos programados
        wp_clear_scheduled_hook('medialab_daily_cleanup');
    }
    
    public function acf_missing_notice() {
        $class = 'notice notice-error is-dismissible';
        $message = sprintf(
            __('<strong>MediaLab:</strong> Este plugin requiere %s para funcionar correctamente.', 'medialab'),
            '<a href="' . admin_url('plugin-install.php?s=advanced+custom+fields&tab=search&type=term') . '">Advanced Custom Fields (ACF)</a>'
        );
        
        printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($class), $message);
    }
    
    // ===== FUNCIONES AUXILIARES =====
    
    /**
     * Oscurecer un color hexadecimal
     */
    private function darken_color($hex, $percent) {
        $hex = str_replace('#', '', $hex);
        
        if (strlen($hex) == 3) {
            $hex = str_repeat(substr($hex, 0, 1), 2) . 
                   str_repeat(substr($hex, 1, 1), 2) . 
                   str_repeat(substr($hex, 2, 1), 2);
        }
        
        $rgb = array_map('hexdec', str_split($hex, 2));
        
        for ($i = 0; $i < 3; $i++) {
            $rgb[$i] = max(0, min(255, $rgb[$i] - ($rgb[$i] * $percent / 100)));
        }
        
        return '#' . implode('', array_map('medialab_hex_pad', $rgb));
    }
    
    /**
     * Aclarar un color hexadecimal
     */
    private function lighten_color($hex, $percent) {
        $hex = str_replace('#', '', $hex);
        
        if (strlen($hex) == 3) {
            $hex = str_repeat(substr($hex, 0, 1), 2) . 
                   str_repeat(substr($hex, 1, 1), 2) . 
                   str_repeat(substr($hex, 2, 1), 2);
        }
        
        $rgb = array_map('hexdec', str_split($hex, 2));
        
        for ($i = 0; $i < 3; $i++) {
            $rgb[$i] = max(0, min(255, $rgb[$i] + ((255 - $rgb[$i]) * $percent / 100)));
        }
        
        return '#' . implode('', array_map('medialab_hex_pad', $rgb));
    }
    
    /**
     * Obtener configuración del plugin
     */
    public static function get_setting($key, $default = null) {
        $settings = get_option('medialab_settings', array());
        return isset($settings[$key]) ? $settings[$key] : $default;
    }
    
    /**
     * Actualizar configuración del plugin
     */
    public static function update_setting($key, $value) {
        $settings = get_option('medialab_settings', array());
        $settings[$key] = $value;
        return update_option('medialab_settings', $settings);
    }
    
    /**
     * Log de errores del plugin
     */
    public static function log($message, $level = 'info') {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            $log_entry = sprintf(
                '[%s] MediaLab %s: %s',
                date('Y-m-d H:i:s'),
                strtoupper($level),
                $message
            );
            
            error_log($log_entry);
        }
    }
}

// ===== FUNCIONES GLOBALES =====

/**
 * Obtener instancia del plugin
 */
function medialab() {
    global $medialab_plugin;
    
    if (!isset($medialab_plugin)) {
        $medialab_plugin = new MediaLab_Plugin();
    }
    
    return $medialab_plugin;
}

/**
 * Obtener configuración rápida
 */
function medialab_get_setting($key, $default = null) {
    return MediaLab_Plugin::get_setting($key, $default);
}

/**
 * Actualizar configuración rápida
 */
function medialab_update_setting($key, $value) {
    return MediaLab_Plugin::update_setting($key, $value);
}

/**
 * Log rápido
 */
function medialab_log($message, $level = 'info') {
    MediaLab_Plugin::log($message, $level);
}

// ===== HOOKS ADICIONALES =====

// Limpieza diaria automática
add_action('medialab_daily_cleanup', 'medialab_daily_cleanup_function');

// Hook de desinstalación
register_uninstall_hook(__FILE__, 'medialab_uninstall_plugin');

// Inicializar el plugin
medialab();
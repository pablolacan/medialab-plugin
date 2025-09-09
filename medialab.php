<?php
/**
 * Plugin Name: MediaLab
 * Plugin URI: https://dojolab.com/plugins/medialab
 * Description: Plugin para la gestión de contenido de Medialab (BETA)
 * Version: 0.8.1
 * Requires at least: 6.8
 * Tested up to: 6.8.1
 * Requires PHP: 8.1
 * Author: Equipo de Medialab
 * Author URI: https://medialab.galileo.edu
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: medialab
 * Domain Path: /languages
 * Network: false
 * Tags: medialab, universidad-galileo, multimedia, acf-simplification, custom-posts
 * 
 * @package MediaLab
 * @category Multimedia
 * @since 0.1.0
 * @version 0.6.0
 * @author Dojo Lab <https://thedojolab.com>
 * 
 * Uso específico: Departamento MediaLab de Universidad Galileo
 * Propósito: Simplificar la creación de contenido multimedia para el equipo
 * Dependencias: Advanced Custom Fields (ACF) - Requerido
 * 
 * Copyright (C) 2025 Medialab
 * 
 * Este plugin está diseñado específicamente para las necesidades del MediaLab
 * de Universidad Galileo y no está pensado para uso general en otros sitios web.
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit('Acceso directo no permitido.');
}

// Constantes básicas del plugin
define('MEDIALAB_VERSION', '0.6.0');
define('MEDIALAB_PLUGIN_URL', plugin_dir_url(__FILE__));
define('MEDIALAB_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('MEDIALAB_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Clase principal del plugin MediaLab
 */
class MediaLab_Plugin {
    
    public function __construct() {
        add_action('init', array($this, 'init'));
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        
        // Hook de activación para verificar dependencias
        register_activation_hook(__FILE__, array($this, 'check_dependencies_on_activation'));
    }
    
    public function check_dependencies_on_activation() {
        if (!class_exists('ACF')) {
            deactivate_plugins(MEDIALAB_PLUGIN_BASENAME);
            wp_die(
                '<h1>MediaLab Plugin</h1>' .
                '<p><strong>Error:</strong> Este plugin requiere Advanced Custom Fields (ACF) para funcionar.</p>' .
                '<p>Por favor, instala y activa ACF antes de activar MediaLab.</p>' .
                '<p><a href="' . admin_url('plugins.php') . '">&larr; Volver a Plugins</a></p>'
            );
        }
    }
    
    public function init() {
        // Verificar ACF
        if (!class_exists('ACF')) {
            add_action('admin_notices', array($this, 'acf_missing_notice'));
            return;
        }
        
        // Cargar módulos específicos de MediaLab
        $this->load_modules();
    }
    
    private function load_modules() {
        $modules = array(
            // Módulos de posts individuales
            'includes/posts/video-post.php',
            'includes/posts/gallery-post.php', 
            'includes/posts/graduation-post.php',
            
            // Nuevo orquestrador para UI unificada
            'includes/admin/posts-orchestrator.php',
            
            // Gestión de material pendiente
            'includes/admin/pending-material.php'
        );
        
        foreach ($modules as $module) {
            $file_path = MEDIALAB_PLUGIN_PATH . $module;
            if (file_exists($file_path)) {
                require_once $file_path;
            }
        }
    }
    
    public function add_admin_menu() {
        // Menú principal - Dashboard de bienvenida
        add_menu_page(
            'MediaLab - Universidad Galileo',
            'MediaLab',
            'manage_options',
            'medialab',
            array($this, 'dashboard_page'),
            'dashicons-video-alt3',
            25
        );
        
        // NUEVO: Solo un submenú para todos los posts
        // El orquestador se encarga de manejar el resto
        
        // Material Pendiente se agrega automáticamente desde pending-material.php
    }
    
    public function enqueue_admin_scripts($hook) {
        // Solo cargar en páginas de MediaLab
        $medialab_pages = array(
            'toplevel_page_medialab',           
            'medialab_page_medialab-posts',      // NUEVO: Página unificada
            'medialab_page_medialab-pending'
        );
        
        if (!in_array($hook, $medialab_pages)) {
            return;
        }
        
        // Scripts básicos para formularios unificados
        if ($hook === 'medialab_page_medialab-posts') {
            // Los assets se manejan desde el orquestador según el tab activo
            // No cargar nada aquí para evitar conflictos
        }
        
        // Scripts específicos para material pendiente
        if ($hook === 'medialab_page_medialab-pending') {
            wp_enqueue_media();
            wp_enqueue_script('thickbox');
            wp_enqueue_style('thickbox');
        }
    }
    
    public function dashboard_page() {
        ?>
        <div class="wrap">
            <h1>Bienvenido a MediaLab</h1>
            <p class="description">Plugin para gestionar contenido multimedia del MediaLab - Universidad Galileo | Versión <?php echo MEDIALAB_VERSION; ?> (BETA)</p>
            
            <div class="card-container" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 20px; margin-top: 30px;">
                
                <!-- NUEVO: Card Unificada para Crear Posts -->
                <div class="card" style="border-left: 4px solid #2271b1;">
                    <h2 class="title">📝 Crear Posts</h2>
                    <p>Acceso unificado a todos los tipos de contenido multimedia.</p>
                    
                    <div class="card-content">
                        <h4>Tipos disponibles:</h4>
                        <ul>
                            <li><strong>🎥 Video Post:</strong> Webinars, conferencias, seminarios</li>
                            <li><strong>🖼️ Gallery Post:</strong> Eventos con múltiples fotos</li>
                            <li><strong>🎓 Graduation Post:</strong> Ceremonias especiales</li>
                        </ul>
                    </div>
                    
                    <div class="card-actions">
                        <a href="<?php echo admin_url('admin.php?page=medialab-posts'); ?>" 
                           class="button button-primary button-large">
                            Crear Contenido
                        </a>
                    </div>
                </div>
                
                <!-- Material Pendiente Card -->
                <div class="card" style="border-left: 4px solid #e74c3c;">
                    <h2 class="title">📋 Material Pendiente</h2>
                    <p>Gestiona graduaciones que necesitan video o fotos.</p>
                    
                    <div class="card-content">
                        <h4>Funciones:</h4>
                        <ul>
                            <li>Ver graduaciones incompletas del año</li>
                            <li>Asignar responsables rápidamente</li>
                            <li>Completar material con modales</li>
                            <li>Filtrar por estado y responsable</li>
                        </ul>
                    </div>
                    
                    <div class="card-actions">
                        <a href="<?php echo admin_url('admin.php?page=medialab-pending'); ?>" 
                           class="button button-primary button-large" 
                           style="background: #e74c3c; border-color: #e74c3c; box-shadow: 0 1px 0 #c0392b;">
                            Ver Pendientes
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
                            <p><strong>📝 Nueva interfaz:</strong><br>
                            Ahora todos los tipos de posts están en un solo lugar con tabs organizados.</p>
                        </div>
                        
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
                        
                        <div class="notice inline" style="margin: 0; background: #fdf2f2; border-left-color: #e74c3c;">
                            <p><strong>📋 Material pendiente:</strong><br>
                            Revisa regularmente para completar graduaciones incompletas.</p>
                        </div>
                        
                    </div>
                </div>
            </div>
            
            <!-- Footer info -->
            <div class="notice notice-info" style="margin-top: 30px; background: #f0f6fc; border-left-color: #0073aa;">
                <p><strong>ℹ️ Plugin MediaLab - BETA v<?php echo MEDIALAB_VERSION; ?>:</strong> 
                Nueva interfaz unificada con tabs para mejor organización. 
                Reporta bugs o sugiere mejoras a los administradores.</p>
            </div>
        </div>
        <?php
    }
    
    public function acf_missing_notice() {
        ?>
        <div class="notice notice-error is-dismissible">
            <h3>⚠️ MediaLab Plugin - Dependencia Requerida</h3>
            <p>
                <strong>Advanced Custom Fields (ACF)</strong> es requerido para que MediaLab funcione correctamente.<br>
                Este plugin simplifica el uso de ACF para el equipo del MediaLab de Universidad Galileo.
            </p>
            <p>
                <a href="<?php echo admin_url('plugin-install.php?s=advanced+custom+fields&tab=search&type=term'); ?>" 
                   class="button button-primary">
                    Instalar ACF Ahora
                </a>
                <a href="<?php echo admin_url('plugins.php'); ?>" 
                   class="button button-secondary">
                    Ver Todos los Plugins
                </a>
            </p>
        </div>
        <?php
    }
}

/**
 * Inicializar el plugin MediaLab
 */
function medialab_init() {
    new MediaLab_Plugin();
}

// Inicializar cuando WordPress esté listo
add_action('plugins_loaded', 'medialab_init');

/**
 * Hook de desactivación
 */
register_deactivation_hook(__FILE__, function() {
    delete_transient('medialab_admin_notice');
});
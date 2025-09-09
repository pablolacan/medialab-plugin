<?php
/**
 * Plugin Name: MediaLab
 * Plugin URI: https://dojolab.com/plugins/medialab
 * Description: Plugin para la gestión de contenido de Medialab (BETA)
 * Version: 0.5.2
 * Requires at least: 6.8
 * Tested up to: 6.8.1
 * Requires PHP: 8.2
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
 * @version 0.4.2
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
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit('Acceso directo no permitido.');
}

// Constantes básicas del plugin
define('MEDIALAB_VERSION', '0.4.2');
define('MEDIALAB_PLUGIN_URL', plugin_dir_url(__FILE__));
define('MEDIALAB_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('MEDIALAB_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Clase principal del plugin MediaLab
 * 
 * Gestiona la funcionalidad core del plugin específicamente
 * diseñado para el departamento MediaLab de Universidad Galileo
 * 
 * @since 0.1.0
 */
class MediaLab_Plugin {
    
    /**
     * Constructor de la clase
     * 
     * Inicializa todos los hooks y acciones necesarias
     * 
     * @since 0.1.0
     */
    public function __construct() {
        add_action('init', array($this, 'init'));
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        
        // Hook de activación para verificar dependencias
        register_activation_hook(__FILE__, array($this, 'check_dependencies_on_activation'));
    }
    
    /**
     * Verifica dependencias al activar el plugin
     * 
     * @since 0.4.1
     */
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
    
    /**
     * Inicialización del plugin
     * 
     * @since 0.1.0
     */
    public function init() {
        // Verificar ACF
        if (!class_exists('ACF')) {
            add_action('admin_notices', array($this, 'acf_missing_notice'));
            return;
        }
        
        // Cargar módulos específicos de MediaLab
        $this->load_modules();
    }
    
    /**
     * Cargar módulos del plugin
     * 
     * @since 0.1.0
     */
    private function load_modules() {
        $modules = array(
            'includes/posts/video-post.php',
            'includes/posts/gallery-post.php', 
            'includes/posts/graduation-post.php',
            'includes/admin/pending-material.php'  // NUEVO - Gestión de material pendiente
        );
        
        foreach ($modules as $module) {
            $file_path = MEDIALAB_PLUGIN_PATH . $module;
            if (file_exists($file_path)) {
                require_once $file_path;
            }
        }
    }
    
    /**
     * Agregar menús de administración
     * 
     * @since 0.1.0
     */
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
        
        // NUEVO - Submenú Material Pendiente se agrega automáticamente desde pending-material.php
    }
    
    /**
     * Cargar scripts y estilos de administración
     * 
     * @param string $hook Página actual del admin
     * @since 0.1.0
     */
    public function enqueue_admin_scripts($hook) {
        // Solo cargar en páginas de MediaLab
        $medialab_pages = array(
            'toplevel_page_medialab',           
            'medialab_page_medialab-video',     
            'medialab_page_medialab-gallery',
            'medialab_page_medialab-graduation',
            'medialab_page_medialab-pending'    // NUEVO - Página de material pendiente
        );
        
        if (!in_array($hook, $medialab_pages)) {
            return;
        }
        
        // Scripts básicos para formularios
        if (in_array($hook, array('medialab_page_medialab-video', 'medialab_page_medialab-gallery', 'medialab_page_medialab-graduation'))) {
            wp_enqueue_media();
            
            // Select2 para mejores selectores
            wp_enqueue_script('select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js', array('jquery'), '4.0.13', true);
            wp_enqueue_style('select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css', array(), '4.0.13');
            
            // Scripts personalizados de MediaLab
            wp_enqueue_script('medialab-admin', MEDIALAB_PLUGIN_URL . 'assets/js/admin.js', array('jquery'), MEDIALAB_VERSION, true);
            wp_localize_script('medialab-admin', 'medialab_ajax', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('medialab_nonce')
            ));
        }
        
        // Scripts específicos para material pendiente
        if ($hook === 'medialab_page_medialab-pending') {
            wp_enqueue_media();
            wp_enqueue_script('thickbox');
            wp_enqueue_style('thickbox');
        }
    }
    
    /**
     * Página principal del dashboard
     * 
     * @since 0.1.0
     */
    public function dashboard_page() {
        ?>
        <div class="wrap">
            <h1>Bienvenido a MediaLab</h1>
            <p class="description">Plugin para gestionar contenido multimedia del MediaLab - Universidad Galileo | Versión <?php echo MEDIALAB_VERSION; ?> (BETA)</p>
            
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
                
                <!-- NUEVO - Material Pendiente Card -->
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
                        
                        <!-- NUEVO - Tip Material Pendiente -->
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
                Plugin para gestión de contenido multimedia del MediaLab de Universidad Galileo.
                Fase de desarrollo continuo. Reporta bugs o sugiere mejoras a los administradores.</p>
            </div>
        </div>
        <?php
    }
    
    /**
     * Página de Video Posts
     * 
     * @since 0.1.0
     */
    public function video_page() {
        $form_path = MEDIALAB_PLUGIN_PATH . 'views/posts/video-form.php';
        if (file_exists($form_path)) {
            include $form_path;
        } else {
            echo '<div class="wrap"><h1>Video Post</h1><p>Archivo de formulario no encontrado.</p></div>';
        }
    }
    
    /**
     * Página de Gallery Posts
     * 
     * @since 0.1.0
     */
    public function gallery_page() {
        $form_path = MEDIALAB_PLUGIN_PATH . 'views/posts/gallery-form.php';
        if (file_exists($form_path)) {
            include $form_path;
        } else {
            echo '<div class="wrap"><h1>Gallery Post</h1><p>Archivo de formulario no encontrado.</p></div>';
        }
    }
    
    /**
     * Página de Graduation Posts
     * 
     * @since 0.1.0
     */
    public function graduation_page() {
        $form_path = MEDIALAB_PLUGIN_PATH . 'views/posts/graduation-form.php';
        if (file_exists($form_path)) {
            include $form_path;
        } else {
            echo '<div class="wrap"><h1>Graduation Post</h1><p>Archivo de formulario no encontrado.</p></div>';
        }
    }
    
    /**
     * Aviso de ACF faltante
     * 
     * @since 0.1.0
     */
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
 * 
 * @since 0.1.0
 */
function medialab_init() {
    new MediaLab_Plugin();
}

// Inicializar cuando WordPress esté listo
add_action('plugins_loaded', 'medialab_init');

/**
 * Hook de desactivación
 * 
 * @since 0.4.1
 */
register_deactivation_hook(__FILE__, function() {
    // Limpiar cualquier configuración temporal si es necesario
    delete_transient('medialab_admin_notice');
});
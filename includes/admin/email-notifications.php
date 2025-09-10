<?php
/**
 * MediaLab - Email Notifications Module (CORREGIDO Y PULIDO)
 * Sistema de notificaciones por email para graduaciones y asignaciones
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

class MediaLab_Email_Notifications {
    
    public function __construct() {
        add_action('init', array($this, 'init'));
        add_action('admin_menu', array($this, 'add_settings_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        
        // AJAX para prueba de email
        add_action('wp_ajax_medialab_test_email', array($this, 'handle_test_email'));
    }
    
    public function init() {
        // Hook para enviar email cuando se asigna responsable
        add_action('medialab_responsable_assigned', array($this, 'send_assignment_notification'), 10, 3);
        
        // Hook para enviar email cuando se publica graduación con material pendiente
        add_action('medialab_graduation_published_pending', array($this, 'send_graduation_pending_notification'), 10, 2);
        
        // DEBUG: Log cuando se registran los hooks
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('MediaLab Email: Hooks registrados');
        }
    }
    
    public function add_settings_menu() {
        add_submenu_page(
            'medialab',
            'Configuración de Emails',
            'Config. Emails',
            'manage_options',
            'medialab-email-settings',
            array($this, 'settings_page')
        );
    }
    
    public function register_settings() {
        // Grupo de configuración
        register_setting('medialab_email_settings', 'medialab_email_enabled');
        register_setting('medialab_email_settings', 'medialab_email_supervisors');
        register_setting('medialab_email_settings', 'medialab_email_from_name');
        register_setting('medialab_email_settings', 'medialab_email_from_email');
        
        // Sección de configuración
        add_settings_section(
            'medialab_email_section',
            'Configuración de Notificaciones por Email',
            array($this, 'section_callback'),
            'medialab_email_settings'
        );
        
        // Campos de configuración
        add_settings_field(
            'medialab_email_enabled',
            'Activar Notificaciones',
            array($this, 'enabled_field_callback'),
            'medialab_email_settings',
            'medialab_email_section'
        );
        
        add_settings_field(
            'medialab_email_supervisors',
            'Emails de Supervisores',
            array($this, 'supervisors_field_callback'),
            'medialab_email_settings',
            'medialab_email_section'
        );
        
        add_settings_field(
            'medialab_email_from_name',
            'Nombre del Remitente',
            array($this, 'from_name_field_callback'),
            'medialab_email_settings',
            'medialab_email_section'
        );
        
        add_settings_field(
            'medialab_email_from_email',
            'Email del Remitente',
            array($this, 'from_email_field_callback'),
            'medialab_email_settings',
            'medialab_email_section'
        );
    }
    
    public function settings_page() {
        ?>
        <div class="wrap">
            <h1>📧 Configuración de Notificaciones Email</h1>
            <p class="description">Configura las notificaciones automáticas para graduaciones y asignaciones de responsables.</p>
            
            <!-- Verificar Post SMTP -->
            <div class="notice notice-info">
                <p><strong>ℹ️ Requisito:</strong> Asegúrate de tener configurado <strong>Post SMTP</strong> para el envío de emails.</p>
                <?php if (is_plugin_active('post-smtp/postman-smtp.php')): ?>
                    <p style="color: #46b450;">✅ Post SMTP está activado</p>
                <?php else: ?>
                    <p style="color: #dc3232;">❌ Post SMTP no está activado. <a href="<?php echo admin_url('plugin-install.php?s=post+smtp&tab=search'); ?>">Instalar Post SMTP</a></p>
                <?php endif; ?>
            </div>
            
            <form action="options.php" method="post">
                <?php
                settings_fields('medialab_email_settings');
                do_settings_sections('medialab_email_settings');
                submit_button();
                ?>
            </form>
            
            <!-- Botón de prueba -->
            <div class="postbox" style="margin-top: 20px;">
                <div class="postbox-header">
                    <h2>🧪 Prueba de Email</h2>
                </div>
                <div class="inside">
                    <p>Envía un email de prueba para verificar la configuración:</p>
                    <button type="button" id="test-email-btn" class="button button-secondary">
                        📤 Enviar Email de Prueba
                    </button>
                    <div id="test-email-result" style="margin-top: 10px;"></div>
                </div>
            </div>
            
            <!-- NUEVO: Debug de configuración -->
            <div class="postbox" style="margin-top: 20px;">
                <div class="postbox-header">
                    <h2>🔍 Estado del Sistema</h2>
                </div>
                <div class="inside">
                    <?php $this->show_system_status(); ?>
                </div>
            </div>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            $('#test-email-btn').on('click', function() {
                var $btn = $(this);
                var $result = $('#test-email-result');
                
                $btn.prop('disabled', true).text('Enviando...');
                $result.empty();
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'medialab_test_email',
                        nonce: '<?php echo wp_create_nonce('medialab_email_test'); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            $result.html('<div class="notice notice-success inline"><p>✅ ' + response.data + '</p></div>');
                        } else {
                            $result.html('<div class="notice notice-error inline"><p>❌ ' + response.data + '</p></div>');
                        }
                    },
                    error: function() {
                        $result.html('<div class="notice notice-error inline"><p>❌ Error de conexión</p></div>');
                    },
                    complete: function() {
                        $btn.prop('disabled', false).text('📤 Enviar Email de Prueba');
                    }
                });
            });
        });
        </script>
        <?php
    }
    
    /**
     * NUEVA FUNCIÓN: Mostrar estado del sistema
     */
    private function show_system_status() {
        $enabled = get_option('medialab_email_enabled', 0);
        $supervisors = get_option('medialab_email_supervisors', '');
        $from_name = get_option('medialab_email_from_name', '');
        $from_email = get_option('medialab_email_from_email', '');
        
        echo '<table class="form-table">';
        echo '<tr><th>Estado de Notificaciones</th><td>' . ($enabled ? '✅ Activadas' : '❌ Desactivadas') . '</td></tr>';
        echo '<tr><th>Post SMTP</th><td>' . (is_plugin_active('post-smtp/postman-smtp.php') ? '✅ Activo' : '❌ Inactivo') . '</td></tr>';
        echo '<tr><th>Email del Remitente</th><td>' . (!empty($from_email) ? '✅ ' . esc_html($from_email) : '❌ No configurado') . '</td></tr>';
        echo '<tr><th>Nombre del Remitente</th><td>' . (!empty($from_name) ? '✅ ' . esc_html($from_name) : '❌ No configurado') . '</td></tr>';
        echo '<tr><th>Supervisores</th><td>' . (!empty($supervisors) ? '✅ Configurados' : '⚠️ No configurados') . '</td></tr>';
        echo '</table>';
    }
    
    public function section_callback() {
        echo '<p>Configura cómo y cuándo se envían las notificaciones por email del sistema MediaLab.</p>';
    }
    
    public function enabled_field_callback() {
        $enabled = get_option('medialab_email_enabled', 0);
        ?>
        <label>
            <input type="checkbox" name="medialab_email_enabled" value="1" <?php checked($enabled, 1); ?>>
            Activar notificaciones automáticas por email
        </label>
        <p class="description">Si está desactivado, no se enviarán emails automáticos.</p>
        <?php
    }
    
    public function supervisors_field_callback() {
        $supervisors = get_option('medialab_email_supervisors', '');
        ?>
        <textarea name="medialab_email_supervisors" rows="4" cols="50" class="large-text"><?php echo esc_textarea($supervisors); ?></textarea>
        <p class="description">
            <strong>Emails de supervisores que recibirán copia de todas las notificaciones.</strong><br>
            Separar múltiples emails con comas. Ejemplo: <code>supervisor1@universidad.edu, supervisor2@universidad.edu</code>
        </p>
        <?php
    }
    
    public function from_name_field_callback() {
        $from_name = get_option('medialab_email_from_name', 'MediaLab - Universidad Galileo');
        ?>
        <input type="text" name="medialab_email_from_name" value="<?php echo esc_attr($from_name); ?>" class="regular-text">
        <p class="description">Nombre que aparecerá como remitente de los emails.</p>
        <?php
    }
    
    public function from_email_field_callback() {
        $from_email = get_option('medialab_email_from_email', get_option('admin_email'));
        ?>
        <input type="email" name="medialab_email_from_email" value="<?php echo esc_attr($from_email); ?>" class="regular-text">
        <p class="description">Email que aparecerá como remitente. Debe estar configurado en Post SMTP.</p>
        <?php
    }
    
    /**
     * Enviar notificación cuando se asigna responsable (CORREGIDO)
     */
    public function send_assignment_notification($post_id, $user_id, $tipo) {
        // DEBUG
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log("MediaLab Email: send_assignment_notification llamada - Post: $post_id, Usuario: $user_id, Tipo: $tipo");
        }
        
        if (!$this->is_enabled()) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log("MediaLab Email: Notificaciones deshabilitadas");
            }
            return;
        }
        
        $post = get_post($post_id);
        $user = get_userdata($user_id);
        
        if (!$post || !$user) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log("MediaLab Email: Post o Usuario no válido - Post existe: " . ($post ? 'SI' : 'NO') . ", Usuario existe: " . ($user ? 'SI' : 'NO'));
            }
            return;
        }
        
        // Verificar que sea una graduación
        if (!has_category('graduaciones', $post_id)) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log("MediaLab Email: Post no es una graduación");
            }
            return;
        }
        
        $facultad = get_field('facultad', $post_id) ?: 'Sin especificar';
        $post_date = get_the_date('d/m/Y', $post_id);
        $tipo_texto = ($tipo === 'video') ? 'video' : 'fotografías';
        
        // Datos del email
        $subject = "📋 Asignación de {$tipo_texto} - {$post->post_title}";
        $message = $this->get_assignment_email_template($post, $user, $tipo, $facultad, $post_date);
        
        // DEBUG
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log("MediaLab Email: Enviando a " . $user->user_email);
        }
        
        // Enviar al usuario asignado
        $sent_to_user = $this->send_email($user->user_email, $subject, $message);
        
        // Enviar copia a supervisores
        $sent_to_supervisors = $this->send_copy_to_supervisors($subject, $message, $user->display_name);
        
        // DEBUG
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log("MediaLab Email: Enviado a usuario: " . ($sent_to_user ? 'SI' : 'NO') . ", Enviado a supervisores: " . ($sent_to_supervisors ? 'SI' : 'NO'));
        }
    }
    
    /**
     * Enviar notificación cuando se publica graduación con material pendiente
     */
    public function send_graduation_pending_notification($post_id, $estado) {
        if (!$this->is_enabled()) {
            return;
        }
        
        // Solo enviar si hay material pendiente
        if ($estado === 'completo') {
            return;
        }
        
        $post = get_post($post_id);
        if (!$post) {
            return;
        }
        
        $facultad = get_field('facultad', $post_id) ?: 'Sin especificar';
        $post_date = get_the_date('d/m/Y', $post_id);
        
        $subject = "🎓 Nueva Graduación Publicada - Material Pendiente";
        $message = $this->get_graduation_pending_email_template($post, $estado, $facultad, $post_date);
        
        // Enviar solo a supervisores
        $this->send_copy_to_supervisors($subject, $message, 'Sistema MediaLab');
    }
    
    /**
     * Template para email de asignación (MEJORADO)
     */
    private function get_assignment_email_template($post, $user, $tipo, $facultad, $post_date) {
        $tipo_texto = ($tipo === 'video') ? 'video' : 'fotografías';
        $accion = ($tipo === 'video') ? 'subir el video' : 'publicar las fotografías';
        $icon = ($tipo === 'video') ? '🎥' : '📷';
        
        $message = "
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #2271b1; color: white; padding: 20px; border-radius: 8px 8px 0 0; text-align: center; }
                .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 8px 8px; }
                .highlight { background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 4px; margin: 20px 0; }
                .action-box { background: #d1ecf1; border: 1px solid #bee5eb; padding: 15px; border-radius: 4px; margin: 20px 0; }
                .footer { text-align: center; margin-top: 30px; color: #666; font-size: 12px; }
                .button { background: #2271b1; color: white; padding: 12px 24px; text-decoration: none; border-radius: 4px; display: inline-block; margin: 10px 0; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>{$icon} Asignación de {$tipo_texto}</h1>
                    <p>MediaLab - Universidad Galileo</p>
                </div>
                
                <div class='content'>
                    <p><strong>Hola {$user->display_name},</strong></p>
                    
                    <p>Se te ha asignado la responsabilidad de <strong>{$accion}</strong> para la siguiente graduación:</p>
                    
                    <div class='highlight'>
                        <h3>📋 Detalles de la Graduación</h3>
                        <ul>
                            <li><strong>Evento:</strong> {$post->post_title}</li>
                            <li><strong>Facultad:</strong> {$facultad}</li>
                            <li><strong>Fecha de ceremonia:</strong> {$post_date}</li>
                            <li><strong>Tu responsabilidad:</strong> {$tipo_texto}</li>
                        </ul>
                    </div>
                    
                    <div class='action-box'>
                        <h3>⚡ Acción Requerida</h3>
                        <p>Por favor <strong>{$accion}</strong> lo más pronto posible para completar la documentación de este evento.</p>
                        
                        <p><a href='" . admin_url('admin.php?page=medialab-pending') . "' class='button'>🔗 Ir a Material Pendiente</a></p>
                        <p><small>También puedes acceder desde el panel de administración: MediaLab → Material Pendiente</small></p>
                    </div>
                    
                    <p><strong>Descripción del evento:</strong></p>
                    <p><em>" . esc_html($post->post_excerpt) . "</em></p>
                    
                    <hr>
                    <p><small>Si tienes alguna pregunta o necesitas ayuda, contacta al equipo de MediaLab.</small></p>
                </div>
                
                <div class='footer'>
                    <p>© " . date('Y') . " MediaLab - Universidad Galileo<br>
                    Este es un mensaje automático del sistema MediaLab</p>
                </div>
            </div>
        </body>
        </html>";
        
        return $message;
    }
    
    /**
     * Template para email de graduación pendiente
     */
    private function get_graduation_pending_email_template($post, $estado, $facultad, $post_date) {
        $estado_texto = '';
        switch ($estado) {
            case 'solo_video':
                $estado_texto = 'Solo video (faltan fotografías)';
                break;
            case 'solo_fotos':
                $estado_texto = 'Solo fotografías (falta video)';
                break;
            case 'pendiente_todo':
                $estado_texto = 'Pendiente todo (falta video y fotografías)';
                break;
        }
        
        $message = "
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #d63638; color: white; padding: 20px; border-radius: 8px 8px 0 0; text-align: center; }
                .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 8px 8px; }
                .warning { background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 4px; margin: 20px 0; }
                .footer { text-align: center; margin-top: 30px; color: #666; font-size: 12px; }
                .button { background: #d63638; color: white; padding: 12px 24px; text-decoration: none; border-radius: 4px; display: inline-block; margin: 10px 0; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>🎓 Nueva Graduación - Material Pendiente</h1>
                    <p>MediaLab - Universidad Galileo</p>
                </div>
                
                <div class='content'>
                    <p><strong>Equipo de Supervisión,</strong></p>
                    
                    <p>Se ha publicado una nueva graduación que requiere material adicional:</p>
                    
                    <div class='warning'>
                        <h3>📋 Detalles de la Graduación</h3>
                        <ul>
                            <li><strong>Evento:</strong> {$post->post_title}</li>
                            <li><strong>Facultad:</strong> {$facultad}</li>
                            <li><strong>Fecha de ceremonia:</strong> {$post_date}</li>
                            <li><strong>Estado actual:</strong> {$estado_texto}</li>
                        </ul>
                    </div>
                    
                    <p><strong>Descripción del evento:</strong></p>
                    <p><em>" . esc_html($post->post_excerpt) . "</em></p>
                    
                    <p><a href='" . admin_url('admin.php?page=medialab-pending') . "' class='button'>🔗 Gestionar Material Pendiente</a></p>
                    
                    <hr>
                    <p><small>Este evento aparecerá en la sección Material Pendiente hasta que se complete.</small></p>
                </div>
                
                <div class='footer'>
                    <p>© " . date('Y') . " MediaLab - Universidad Galileo<br>
                    Notificación automática del sistema MediaLab</p>
                </div>
            </div>
        </body>
        </html>";
        
        return $message;
    }
    
    /**
     * Enviar email usando wp_mail (MEJORADO con debug)
     */
    private function send_email($to, $subject, $message) {
        $from_name = get_option('medialab_email_from_name', 'MediaLab - Universidad Galileo');
        $from_email = get_option('medialab_email_from_email', get_option('admin_email'));
        
        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . $from_name . ' <' . $from_email . '>'
        );
        
        // DEBUG antes de enviar
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log("MediaLab Email: Intentando enviar email a: $to");
            error_log("MediaLab Email: Asunto: $subject");
            error_log("MediaLab Email: Remitente: $from_name <$from_email>");
        }
        
        $sent = wp_mail($to, $subject, $message, $headers);
        
        // DEBUG después de enviar
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log("MediaLab Email: Resultado wp_mail: " . ($sent ? 'ÉXITO' : 'FALLÓ'));
        }
        
        if (!$sent) {
            error_log('MediaLab Email Error: No se pudo enviar email a ' . $to);
        }
        
        return $sent;
    }
    
    /**
     * Enviar copia a supervisores (MEJORADO)
     */
    private function send_copy_to_supervisors($subject, $message, $assigned_to = '') {
        $supervisors = get_option('medialab_email_supervisors', '');
        
        if (empty($supervisors)) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log("MediaLab Email: No hay supervisores configurados");
            }
            return false;
        }
        
        $supervisor_emails = array_map('trim', explode(',', $supervisors));
        $supervisor_emails = array_filter($supervisor_emails, 'is_email');
        
        if (empty($supervisor_emails)) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log("MediaLab Email: Emails de supervisores no válidos");
            }
            return false;
        }
        
        // Modificar asunto para supervisores
        $supervisor_subject = '[COPIA] ' . $subject;
        
        // Agregar nota para supervisores
        if (!empty($assigned_to)) {
            $supervisor_note = "<div style='background: #e3f2fd; border: 1px solid #2196f3; padding: 10px; margin-bottom: 20px; border-radius: 4px;'>
                <strong>📋 Nota para Supervisores:</strong><br>
                Este email fue enviado automáticamente a <strong>{$assigned_to}</strong> y se envía copia para seguimiento.
            </div>";
            
            $message = str_replace('<div class=\'content\'>', '<div class=\'content\'>' . $supervisor_note, $message);
        }
        
        $all_sent = true;
        foreach ($supervisor_emails as $email) {
            $sent = $this->send_email($email, $supervisor_subject, $message);
            if (!$sent) {
                $all_sent = false;
            }
        }
        
        return $all_sent;
    }
    
    /**
     * Verificar si las notificaciones están habilitadas
     */
    private function is_enabled() {
        return get_option('medialab_email_enabled', 0) == 1;
    }
    
    /**
     * Handler para prueba de email (MEJORADO)
     */
    public function handle_test_email() {
        if (!wp_verify_nonce($_POST['nonce'], 'medialab_email_test')) {
            wp_send_json_error('Fallo de seguridad');
        }
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('No tienes permisos');
        }
        
        $test_email = get_option('admin_email');
        $subject = '🧪 Prueba de Email - MediaLab';
        $message = "
        <h2>✅ Email de Prueba</h2>
        <p>Si recibes este email, la configuración de MediaLab está funcionando correctamente.</p>
        <p><strong>Fecha/Hora:</strong> " . current_time('d/m/Y H:i:s') . "</p>
        <p><strong>Configuración Post SMTP:</strong> " . (is_plugin_active('post-smtp/postman-smtp.php') ? 'Activado ✅' : 'No activado ❌') . "</p>
        <p><strong>Notificaciones MediaLab:</strong> " . ($this->is_enabled() ? 'Activadas ✅' : 'Desactivadas ❌') . "</p>
        ";
        
        $sent = $this->send_email($test_email, $subject, $message);
        
        if ($sent) {
            wp_send_json_success('Email de prueba enviado correctamente a ' . $test_email);
        } else {
            wp_send_json_error('Error al enviar email. Verifica la configuración de Post SMTP.');
        }
    }
}

// Inicializar la clase
$medialab_email = new MediaLab_Email_Notifications();
<?php
/**
 * MediaLab - Email Notifications Module (SIMPLIFICADO)
 * Envía emails cuando se asignan responsables - SIN validaciones innecesarias
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

class MediaLab_Email_Notifications {
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_settings_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        
        // Hook simple: cuando se asigna responsable → enviar email
        add_action('medialab_responsable_assigned', array($this, 'send_assignment_notification'), 10, 3);
        
        // AJAX para prueba de email
        add_action('wp_ajax_medialab_test_email', array($this, 'handle_test_email'));
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
        register_setting('medialab_email_settings', 'medialab_email_enabled');
        register_setting('medialab_email_settings', 'medialab_email_supervisors');
        register_setting('medialab_email_settings', 'medialab_email_from_name');
        register_setting('medialab_email_settings', 'medialab_email_from_email');
        
        add_settings_section(
            'medialab_email_section',
            'Configuración de Notificaciones por Email',
            array($this, 'section_callback'),
            'medialab_email_settings'
        );
        
        add_settings_field('medialab_email_enabled', 'Activar Notificaciones', array($this, 'enabled_field_callback'), 'medialab_email_settings', 'medialab_email_section');
        add_settings_field('medialab_email_supervisors', 'Emails de Supervisores', array($this, 'supervisors_field_callback'), 'medialab_email_settings', 'medialab_email_section');
        add_settings_field('medialab_email_from_name', 'Nombre del Remitente', array($this, 'from_name_field_callback'), 'medialab_email_settings', 'medialab_email_section');
        add_settings_field('medialab_email_from_email', 'Email del Remitente', array($this, 'from_email_field_callback'), 'medialab_email_settings', 'medialab_email_section');
    }
    
    public function settings_page() {
        ?>
        <div class="wrap">
            <h1>📧 Configuración de Notificaciones Email</h1>
            <p class="description">Configura las notificaciones automáticas para asignaciones de responsables.</p>
            
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
            
            <!-- Estado del sistema -->
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
     * FUNCIÓN PRINCIPAL: Enviar notificación cuando se asigna responsable
     * Sin validaciones innecesarias - solo enviar email
     */
    public function send_assignment_notification($post_id, $user_id, $tipo) {
        // 1. ¿Están habilitadas las notificaciones?
        if (!get_option('medialab_email_enabled', 0)) {
            return;
        }
        
        // 2. ¿Existen el post y el usuario?
        $post = get_post($post_id);
        $user = get_userdata($user_id);
        
        if (!$post || !$user || empty($user->user_email)) {
            return;
        }
        
        // 3. Recoger datos y enviar email
        $facultad = get_field('facultad', $post_id) ?: 'Sin especificar';
        $post_date = get_the_date('d/m/Y', $post_id);
        $tipo_texto = ($tipo === 'video') ? 'video' : 'fotografías';
        
        // Crear email
        $subject = "📋 Asignación de {$tipo_texto} - {$post->post_title}";
        $message = $this->create_email_content($post, $user, $tipo, $facultad, $post_date);
        
        // Enviar al usuario asignado
        $this->send_email($user->user_email, $subject, $message);
        
        // Enviar copia a supervisores
        $this->send_to_supervisors($subject, $message, $user->display_name);
    }
    
    /**
     * Crear contenido del email
     */
    private function create_email_content($post, $user, $tipo, $facultad, $post_date) {
        $tipo_texto = ($tipo === 'video') ? 'video' : 'fotografías';
        $accion = ($tipo === 'video') ? 'subir el video' : 'publicar las fotografías';
        $icon = ($tipo === 'video') ? '🎥' : '📷';
        $admin_url = admin_url('admin.php?page=medialab-pending');
        
        return "
        <html>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
            <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
                
                <div style='background: #2271b1; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0;'>
                    <h1>{$icon} Asignación de {$tipo_texto}</h1>
                    <p>MediaLab - Universidad Galileo</p>
                </div>
                
                <div style='background: #f9f9f9; padding: 30px; border-radius: 0 0 8px 8px;'>
                    <p><strong>Hola {$user->display_name},</strong></p>
                    
                    <p>Se te ha asignado la responsabilidad de <strong>{$accion}</strong> para la siguiente graduación:</p>
                    
                    <div style='background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 4px; margin: 20px 0;'>
                        <h3>📋 Detalles de la Graduación</h3>
                        <ul>
                            <li><strong>Evento:</strong> {$post->post_title}</li>
                            <li><strong>Facultad:</strong> {$facultad}</li>
                            <li><strong>Fecha de ceremonia:</strong> {$post_date}</li>
                            <li><strong>Tu responsabilidad:</strong> {$tipo_texto}</li>
                        </ul>
                    </div>
                    
                    <div style='background: #d1ecf1; border: 1px solid #bee5eb; padding: 15px; border-radius: 4px; margin: 20px 0;'>
                        <h3>⚡ Acción Requerida</h3>
                        <p>Por favor <strong>{$accion}</strong> lo más pronto posible.</p>
                        <p><a href='{$admin_url}' style='background: #2271b1; color: white; padding: 12px 24px; text-decoration: none; border-radius: 4px; display: inline-block;'>🔗 Ir a Material Pendiente</a></p>
                    </div>
                    
                    <p><strong>Descripción del evento:</strong></p>
                    <p><em>" . esc_html($post->post_excerpt) . "</em></p>
                    
                    <hr>
                    <p><small>Si tienes alguna pregunta, contacta al equipo de MediaLab.</small></p>
                </div>
                
            </div>
        </body>
        </html>";
    }
    
    /**
     * Enviar email usando wp_mail
     */
    private function send_email($to, $subject, $message) {
        if (empty($to) || !is_email($to)) {
            return false;
        }
        
        $from_name = get_option('medialab_email_from_name', 'MediaLab - Universidad Galileo');
        $from_email = get_option('medialab_email_from_email', get_option('admin_email'));
        
        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . $from_name . ' <' . $from_email . '>'
        );
        
        return wp_mail($to, $subject, $message, $headers);
    }
    
    /**
     * Enviar copia a supervisores
     */
    private function send_to_supervisors($subject, $message, $assigned_to) {
        $supervisors = get_option('medialab_email_supervisors', '');
        
        if (empty($supervisors)) {
            return;
        }
        
        $supervisor_emails = array_map('trim', explode(',', $supervisors));
        $supervisor_emails = array_filter($supervisor_emails, 'is_email');
        
        if (empty($supervisor_emails)) {
            return;
        }
        
        // Modificar mensaje para supervisores
        $supervisor_subject = '[COPIA] ' . $subject;
        $supervisor_message = str_replace(
            '<div style=\'background: #f9f9f9;',
            '<div style=\'background: #e3f2fd; border: 1px solid #2196f3; padding: 10px; margin-bottom: 20px; border-radius: 4px;\'><strong>📋 Nota para Supervisores:</strong> Este email fue enviado automáticamente a <strong>' . $assigned_to . '</strong> y se envía copia para seguimiento.</div><div style=\'background: #f9f9f9;',
            $message
        );
        
        foreach ($supervisor_emails as $email) {
            $this->send_email($email, $supervisor_subject, $supervisor_message);
        }
    }
    
    /**
     * Handler para prueba de email
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
        <p><strong>Notificaciones MediaLab:</strong> " . (get_option('medialab_email_enabled', 0) ? 'Activadas ✅' : 'Desactivadas ❌') . "</p>
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
new MediaLab_Email_Notifications();
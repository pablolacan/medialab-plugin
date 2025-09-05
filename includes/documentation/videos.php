<?php
/**
 * MediaLab - Video Posts Documentation
 * Documentación específica para posts de video
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

class MediaLab_Video_Documentation {
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_video_docs_menu'));
    }
    
    public function add_video_docs_menu() {
        add_submenu_page(
            'medialab-docs',
            'Video Posts - Guía Completa',
            'Video Posts',
            'read',
            'medialab-docs-videos',
            array($this, 'video_docs_page')
        );
    }
    
    public function video_docs_page() {
        ?>
        <div class="wrap">
            <h1>🎥 Guía de Video Posts</h1>
            
            <!-- Navegación rápida -->
            <div class="medialab-form" style="background: #e8f4f8; border-left: 4px solid #3498db;">
                <h3>📋 Índice de contenidos</h3>
                <ul style="columns: 2; list-style: none; padding: 0;">
                    <li><a href="#campos-requeridos">📝 Campos Requeridos</a></li>
                    <li><a href="#ejemplos-practicos">💡 Ejemplos Prácticos</a></li>
                    <li><a href="#mejores-practicas">⭐ Mejores Prácticas</a></li>
                    <li><a href="#errores-comunes">⚠️ Errores Comunes</a></li>
                    <li><a href="#formatos-video">🎬 Formatos de Video</a></li>
                    <li><a href="#categorias">📂 Categorías</a></li>
                </ul>
            </div>
            
            <!-- Campos Requeridos -->
            <div id="campos-requeridos" class="medialab-form">
                <h2>📝 Campos Requeridos</h2>
                <p><strong>Todos los campos son obligatorios</strong> para crear un video post exitosamente.</p>
                
                <div style="display: grid; gap: 20px; margin-top: 20px;">
                    
                    <!-- Título -->
                    <div style="border-left: 4px solid #27ae60; padding-left: 15px;">
                        <h4>🎯 Título del Video</h4>
                        <p><strong>Propósito:</strong> Nombre principal que verán los usuarios</p>
                        <p><strong>Límite:</strong> 200 caracteres máximo</p>
                        <p><strong>Ejemplo:</strong> <code>Tutorial: Cómo usar WordPress desde cero</code></p>
                        <div style="background: #f9f9f9; padding: 10px; border-radius: 4px; margin-top: 8px;">
                            <strong>💡 Tips:</strong>
                            <ul>
                                <li>Sé descriptivo y específico</li>
                                <li>Incluye palabras clave importantes</li>
                                <li>Evita caracteres especiales innecesarios</li>
                            </ul>
                        </div>
                    </div>
                    
                    <!-- Link del Video -->
                    <div style="border-left: 4px solid #e74c3c; padding-left: 15px;">
                        <h4>🔗 Link del Video</h4>
                        <p><strong>Propósito:</strong> URL donde está alojado el video</p>
                        <p><strong>Formato:</strong> URL completa y válida</p>
                        <p><strong>Ejemplo:</strong> <code>https://www.youtube.com/watch?v=dQw4w9WgXcQ</code></p>
                        <div style="background: #fff5f5; padding: 10px; border-radius: 4px; margin-top: 8px;">
                            <strong>⚠️ Importante:</strong>
                            <ul>
                                <li>El video debe ser público y accesible</li>
                                <li>Verifica que el link funcione antes de publicar</li>
                                <li>Soporta: YouTube, Vimeo, y otros servicios</li>
                            </ul>
                        </div>
                    </div>
                    
                    <!-- Subtítulo -->
                    <div style="border-left: 4px solid #f39c12; padding-left: 15px;">
                        <h4>📄 Subtítulo</h4>
                        <p><strong>Propósito:</strong> Descripción corta adicional</p>
                        <p><strong>Límite:</strong> 200 caracteres máximo</p>
                        <p><strong>Ejemplo:</strong> <code>Aprende los fundamentos paso a paso</code></p>
                        <div style="background: #fef9e7; padding: 10px; border-radius: 4px; margin-top: 8px;">
                            <strong>💡 Uso recomendado:</strong>
                            <ul>
                                <li>Complementa el título con más detalle</li>
                                <li>Menciona el público objetivo</li>
                                <li>Indica la duración si es relevante</li>
                            </ul>
                        </div>
                    </div>
                    
                    <!-- Facultad -->
                    <div style="border-left: 4px solid #9b59b6; padding-left: 15px;">
                        <h4>🏫 Facultad</h4>
                        <p><strong>Propósito:</strong> Facultad o área académica relacionada</p>
                        <p><strong>Formato:</strong> Texto libre</p>
                        <p><strong>Ejemplo:</strong> <code>Ingeniería en Sistemas</code></p>
                        <div style="background: #f4f1f8; padding: 10px; border-radius: 4px; margin-top: 8px;">
                            <strong>📋 Ejemplos comunes:</strong>
                            <ul>
                                <li>Ingeniería</li>
                                <li>Medicina</li>
                                <li>Derecho</li>
                                <li>Administración</li>
                                <li>Diseño Gráfico</li>
                            </ul>
                        </div>
                    </div>
                    
                    <!-- Descripción -->
                    <div style="border-left: 4px solid #3498db; padding-left: 15px;">
                        <h4>📋 Extracto/Descripción</h4>
                        <p><strong>Propósito:</strong> Descripción detallada del contenido</p>
                        <p><strong>Límite:</strong> 500 caracteres máximo</p>
                        <p><strong>Ejemplo:</strong> <code>En este video aprenderás los conceptos básicos de WordPress, desde la instalación hasta la creación de tu primer post. Ideal para principiantes sin experiencia previa.</code></p>
                        <div style="background: #f0f8ff; padding: 10px; border-radius: 4px; margin-top: 8px;">
                            <strong>✍️ Consejos de redacción:</strong>
                            <ul>
                                <li>Explica qué aprenderá el usuario</li>
                                <li>Menciona prerequisitos si los hay</li>
                                <li>Usa un lenguaje claro y directo</li>
                            </ul>
                        </div>
                    </div>
                    
                    <!-- Categoría -->
                    <div style="border-left: 4px solid #e67e22; padding-left: 15px;">
                        <h4>📂 Categoría</h4>
                        <p><strong>Propósito:</strong> Clasificar el video por tema</p>
                        <p><strong>Restricción:</strong> Solo UNA categoría por video</p>
                        <p><strong>Búsqueda:</strong> Puedes escribir para filtrar las 200+ categorías</p>
                        <div style="background: #fdf2e9; padding: 10px; border-radius: 4px; margin-top: 8px;">
                            <strong>🔍 Cómo elegir:</strong>
                            <ul>
                                <li>Busca la categoría más específica</li>
                                <li>Si dudas entre varias, elige la más relevante</li>
                                <li>Usa el buscador para encontrar rápido</li>
                            </ul>
                        </div>
                    </div>
                    
                    <!-- Imagen Destacada -->
                    <div style="border-left: 4px solid #1abc9c; padding-left: 15px;">
                        <h4>🖼️ Imagen Destacada</h4>
                        <p><strong>Propósito:</strong> Miniatura que representa el video</p>
                        <p><strong>Formato:</strong> JPG, PNG recomendado</p>
                        <p><strong>Dimensiones sugeridas:</strong> 1200x630px (proporción 16:9)</p>
                        <div style="background: #e8f6f3; padding: 10px; border-radius: 4px; margin-top: 8px;">
                            <strong>📸 Mejores prácticas:</strong>
                            <ul>
                                <li>Usa imágenes de alta calidad</li>
                                <li>Que represente el contenido del video</li>
                                <li>Evita imágenes con texto pequeño</li>
                                <li>Considera el contraste y la legibilidad</li>
                            </ul>
                        </div>
                    </div>
                    
                </div>
            </div>
            
            <!-- Ejemplo Práctico -->
            <div id="ejemplos-practicos" class="medialab-form" style="background: #f8f9fa;">
                <h2>💡 Ejemplo Práctico</h2>
                <div style="border: 2px dashed #6c757d; padding: 20px; border-radius: 8px;">
                    <h4>🎯 Video: Tutorial de Photoshop para principiantes</h4>
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td style="padding: 8px; border: 1px solid #dee2e6; background: #e9ecef; font-weight: bold;">Campo</td>
                            <td style="padding: 8px; border: 1px solid #dee2e6; background: #e9ecef; font-weight: bold;">Valor de Ejemplo</td>
                        </tr>
                        <tr>
                            <td style="padding: 8px; border: 1px solid #dee2e6;">Título</td>
                            <td style="padding: 8px; border: 1px solid #dee2e6;">Tutorial de Photoshop: Edición básica de fotos</td>
                        </tr>
                        <tr>
                            <td style="padding: 8px; border: 1px solid #dee2e6;">Link</td>
                            <td style="padding: 8px; border: 1px solid #dee2e6;">https://youtube.com/watch?v=ejemplo123</td>
                        </tr>
                        <tr>
                            <td style="padding: 8px; border: 1px solid #dee2e6;">Subtítulo</td>
                            <td style="padding: 8px; border: 1px solid #dee2e6;">Aprende las herramientas esenciales en 30 minutos</td>
                        </tr>
                        <tr>
                            <td style="padding: 8px; border: 1px solid #dee2e6;">Facultad</td>
                            <td style="padding: 8px; border: 1px solid #dee2e6;">Diseño Gráfico</td>
                        </tr>
                        <tr>
                            <td style="padding: 8px; border: 1px solid #dee2e6;">Descripción</td>
                            <td style="padding: 8px; border: 1px solid #dee2e6;">Este tutorial cubre las herramientas básicas de Photoshop: capas, selecciones, pinceles y filtros. Perfecto para estudiantes que inician en diseño digital.</td>
                        </tr>
                        <tr>
                            <td style="padding: 8px; border: 1px solid #dee2e6;">Categoría</td>
                            <td style="padding: 8px; border: 1px solid #dee2e6;">Tutoriales de Diseño</td>
                        </tr>
                    </table>
                </div>
            </div>
            
            <!-- Errores Comunes -->
            <div id="errores-comunes" class="medialab-form">
                <h2>⚠️ Errores Comunes a Evitar</h2>
                <div style="display: grid; gap: 15px;">
                    
                    <div style="background: #fff5f5; border: 1px solid #fed7d7; border-radius: 6px; padding: 15px;">
                        <h4 style="color: #e53e3e; margin-top: 0;">❌ Link del video no funciona</h4>
                        <p><strong>Problema:</strong> URL incorrecta o video privado</p>
                        <p><strong>Solución:</strong> Siempre probar el link antes de guardar</p>
                    </div>
                    
                    <div style="background: #fff5f5; border: 1px solid #fed7d7; border-radius: 6px; padding: 15px;">
                        <h4 style="color: #e53e3e; margin-top: 0;">❌ Título muy genérico</h4>
                        <p><strong>Problema:</strong> "Video tutorial" o "Clase 1"</p>
                        <p><strong>Solución:</strong> Ser específico sobre el contenido</p>
                    </div>
                    
                    <div style="background: #fff5f5; border: 1px solid #fed7d7; border-radius: 6px; padding: 15px;">
                        <h4 style="color: #e53e3e; margin-top: 0;">❌ Seleccionar múltiples categorías</h4>
                        <p><strong>Problema:</strong> El sistema solo permite una</p>
                        <p><strong>Solución:</strong> Elegir la más relevante al tema principal</p>
                    </div>
                    
                    <div style="background: #fff5f5; border: 1px solid #fed7d7; border-radius: 6px; padding: 15px;">
                        <h4 style="color: #e53e3e; margin-top: 0;">❌ Imagen de baja calidad</h4>
                        <p><strong>Problema:</strong> Imagen pixelada o muy pequeña</p>
                        <p><strong>Solución:</strong> Usar imágenes de al menos 800px de ancho</p>
                    </div>
                    
                </div>
            </div>
            
            <!-- Botón de acción -->
            <div style="text-align: center; margin: 30px 0;">
                <a href="<?php echo admin_url('admin.php?page=medialab-video'); ?>" class="button button-primary" style="padding: 15px 30px; font-size: 16px;">
                    🎥 Crear Video Post Ahora
                </a>
            </div>
            
        </div>
        
        <style>
        .wrap h2 { color: #333; border-bottom: 2px solid #3498db; padding-bottom: 10px; }
        .wrap h4 { margin-top: 0; }
        .wrap code { background: #f1f1f1; padding: 2px 6px; border-radius: 3px; font-family: monospace; }
        .wrap a[href^="#"] { text-decoration: none; color: #3498db; font-weight: 500; }
        .wrap a[href^="#"]:hover { color: #2980b9; }
        </style>
        
        <?php
    }
}

// Inicializar documentación de videos
new MediaLab_Video_Documentation();
<?php
/**
 * MediaLab - Documentación General
 * Guía general que aplica a todos los tipos de posts
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

class MediaLab_General_Documentation {
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_general_docs_menu'));
    }
    
    public function add_general_docs_menu() {
        add_submenu_page(
            'medialab-docs',
            'Guía General - MediaLab',
            '📋 Guía General',
            'read',
            'medialab-docs-general',
            array($this, 'general_docs_page')
        );
    }
    
    public function general_docs_page() {
        ?>
        <div class="wrap">
            <!-- Header mejorado -->
            <div class="docs-header">
                <h1>📋 Guía General de MediaLab</h1>
                <p class="description">Normas básicas que aplican a todos los tipos de posts</p>
            </div>
            
            <!-- Navegación mejorada -->
            <div class="docs-navigation">
                <h3>🚀 Navegación Rápida</h3>
                <ul>
                    <li><a href="#imagenes-requisitos">📸 Requisitos de Imágenes</a></li>
                    <li><a href="#facultades-formato">🏫 Formato de Facultades</a></li>
                    <li><a href="#categorias-reglas">📂 Una Sola Categoría</a></li>
                    <li><a href="#fechas-eventos">📅 Fechas de Eventos</a></li>
                </ul>
            </div>

            <!-- Requisitos de Imágenes -->
            <div id="imagenes-requisitos" class="docs-section">
                <h2>📸 Requisitos de Imágenes</h2>
                
                <div class="docs-alert warning">
                    <h4>⚠️ TODAS las imágenes deben cumplir</h4>
                    <p>Estos requisitos aplican para imágenes destacadas y galerías</p>
                </div>
                
                <div class="docs-cards">
                    
                    <!-- Tamaño de archivo -->
                    <div class="docs-card error">
                        <h4>🗃️ Tamaño Máximo: 2 MB</h4>
                        <p><strong>Cómo reducir:</strong> Usar <a href="https://tinypng.com/" target="_blank">TinyPNG</a> o ajustar calidad JPEG a 80%</p>
                    </div>

                    <!-- Dimensiones -->
                    <div class="docs-card warning">
                        <h4>📐 Dimensiones Máximas: 1500px</h4>
                        <p><strong>Regla:</strong> Ningún lado puede superar 1500 píxeles</p>
                        <div class="docs-code">
✅ Válido: 1500×1000px, 1000×1500px, 1500×1500px
❌ Inválido: 2000×1200px, 1200×1800px
                        </div>
                    </div>

                    <!-- Formatos -->
                    <div class="docs-card success">
                        <h4>🎨 Formatos: JPG, PNG</h4>
                        <p><strong>JPG:</strong> Para fotografías</p>
                        <p><strong>PNG:</strong> Para gráficos con transparencia</p>
                    </div>

                </div>
            </div>

            <!-- Facultades -->
            <div id="facultades-formato" class="docs-section">
                <h2>🏫 Formato de Facultades</h2>
                
                <div class="docs-alert success">
                    <h4>📐 Usar nombres cortos oficiales</h4>
                    <p>Si hay múltiples facultades, separar con comas y espacios</p>
                </div>

                <div class="docs-cards">
                    <div class="docs-card success">
                        <h4>✅ Nombres Cortos Oficiales</h4>
                        <ul>
                            <li><strong>FISICC</strong> - Ingeniería de Sistemas, Informática y Ciencias de la Computación</li>
                            <li><strong>FACTI</strong> - Ciencias, Tecnología e Industria</li>
                            <li><strong>Medicina</strong> - Facultad de Medicina</li>
                            <li><strong>Derecho</strong> - Facultad de Derecho</li>
                            <li><strong>Diseño</strong> - Instituto de Diseño Gráfico</li>
                        </ul>
                    </div>
                    
                    <div class="docs-card info">
                        <h4>🔗 Múltiples Facultades</h4>
                        <div class="docs-code">
FISICC, FACTI
Medicina, Derecho
                        </div>
                    </div>
                </div>
            </div>

            <!-- Categorías -->
            <div id="categorias-reglas" class="docs-section">
                <h2>📂 Regla de Categorías</h2>
                
                <div class="docs-alert error">
                    <h4>🎯 Solo UNA categoría por post</h4>
                    <p>Elegir la más específica y relevante al contenido principal</p>
                </div>

                <div class="docs-cards">
                    <div class="docs-card info">
                        <h4>🧠 Cómo elegir</h4>
                        <ol>
                            <li>Identifica el tema principal</li>
                            <li>Busca la categoría más específica</li>
                            <li>Usa el buscador para filtrar</li>
                        </ol>
                    </div>
                    
                    <div class="docs-card success">
                        <h4>💡 Ejemplos</h4>
                        <ul>
                            <li>Webinar de marketing → "Marketing Digital" (no "Webinars")</li>
                            <li>Graduación → "Graduaciones" (no "Ceremonias")</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Fechas -->
            <div id="fechas-eventos" class="docs-section">
                <h2>📅 Fechas de Eventos</h2>
                
                <div class="docs-alert info">
                    <h4>⏰ Regla Principal</h4>
                    <p>Usar la fecha del evento, NO la fecha de publicación</p>
                </div>

                <div class="docs-cards">
                    <div class="docs-card success">
                        <h4>✅ Ejemplo Correcto</h4>
                        <ul>
                            <li><strong>Evento:</strong> Conferencia el 15 de marzo</li>
                            <li><strong>Publicas:</strong> 20 de marzo</li>
                            <li><strong>Fecha a usar:</strong> 15 de marzo ✅</li>
                        </ul>
                        <p><strong>💡 Razón:</strong> Los contenidos se organizan por fecha del evento</p>
                    </div>
                </div>
            </div>

            <!-- Botones de acción -->
            <div class="docs-actions">
                <h3>🚀 Crear Posts</h3>
                <a href="<?php echo admin_url('admin.php?page=medialab-video'); ?>" class="button">
                    🎥 Video Post
                </a>
                <a href="<?php echo admin_url('admin.php?page=medialab-gallery'); ?>" class="button">
                    🖼️ Gallery Post
                </a>
            </div>

        </div>
                    <li><a href="#nombres-titulos">📝 Nombres y Títulos</a></li>
                    <li><a href="#facultades-formato">🏫 Formato de Facultades</a></li>
                    <li><a href="#categorias-reglas">📂 Reglas de Categorías</a></li>
                    <li><a href="#fechas-eventos">📅 Fechas de Eventos</a></li>
                    <li><a href="#seo-basico">🔍 SEO Básico</a></li>
                </ul>
            </div>

            <!-- Requisitos de Imágenes -->
            <div id="imagenes-requisitos" class="medialab-form">
                <h2>📸 Requisitos Técnicos de Imágenes</h2>
                <div style="background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 6px; padding: 20px; margin: 15px 0;">
                    <h4 style="color: #b8860b; margin-top: 0;">⚠️ IMPORTANTE - Aplica para todas las imágenes</h4>
                    <p style="margin: 0;"><strong>Tanto para imágenes destacadas como para galerías</strong></p>
                </div>
                
                <div style="display: grid; gap: 20px; margin-top: 20px;">
                    
                    <!-- Tamaño de archivo -->
                    <div style="border-left: 4px solid #e74c3c; padding-left: 15px;">
                        <h4>🗃️ Tamaño de Archivo</h4>
                        <p><strong>Límite máximo:</strong> 2 MB por imagen</p>
                        <div style="background: #fff5f5; padding: 15px; border-radius: 4px; margin-top: 10px;">
                            <strong>🛠️ Cómo reducir el tamaño:</strong>
                            <ul>
                                <li>Usar herramientas como <a href="https://tinypng.com/" target="_blank">TinyPNG</a> o <a href="https://compressor.io/" target="_blank">Compressor.io</a></li>
                                <li>Ajustar la calidad JPEG a 80-85%</li>
                                <li>Verificar que las dimensiones sean apropiadas antes de subir</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Dimensiones -->
                    <div style="border-left: 4px solid #f39c12; padding-left: 15px;">
                        <h4>📐 Dimensiones Máximas</h4>
                        <p><strong>Regla principal:</strong> Ningún lado puede superar los 1500 píxeles</p>
                        <div style="background: #fef9e7; padding: 15px; border-radius: 4px; margin-top: 10px;">
                            <strong>📊 Ejemplos prácticos:</strong>
                            <ul>
                                <li>✅ <strong>Horizontal:</strong> 1500px × 1000px (ancho no supera 1500px)</li>
                                <li>✅ <strong>Vertical:</strong> 1000px × 1500px (alto no supera 1500px)</li>
                                <li>✅ <strong>Cuadrada:</strong> 1500px × 1500px (ningún lado supera 1500px)</li>
                                <li>❌ <strong>Incorrecta:</strong> 2000px × 1200px (ancho supera 1500px)</li>
                                <li>❌ <strong>Incorrecta:</strong> 1200px × 1800px (alto supera 1500px)</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Formatos recomendados -->
                    <div style="border-left: 4px solid #27ae60; padding-left: 15px;">
                        <h4>🎨 Formatos Recomendados</h4>
                        <p><strong>Orden de preferencia:</strong></p>
                        <div style="background: #f0fff0; padding: 15px; border-radius: 4px; margin-top: 10px;">
                            <ol>
                                <li><strong>JPEG (.jpg):</strong> Para fotografías y imágenes con muchos colores</li>
                                <li><strong>PNG (.png):</strong> Para imágenes con transparencias o pocos colores</li>
                                <li><strong>WebP:</strong> Si tienes herramientas para optimizar (mejor compresión)</li>
                            </ol>
                            <p style="margin-top: 15px;"><strong>🚫 Evitar:</strong> BMP, TIFF, GIF grandes</p>
                        </div>
                    </div>

                    <!-- Calidad visual -->
                    <div style="border-left: 4px solid #9b59b6; padding-left: 15px;">
                        <h4>✨ Calidad Visual</h4>
                        <div style="background: #f4f1f8; padding: 15px; border-radius: 4px; margin-top: 10px;">
                            <strong>📋 Checklist de calidad:</strong>
                            <ul>
                                <li>Imagen nítida y bien enfocada</li>
                                <li>Iluminación adecuada (ni muy oscura ni sobreexpuesta)</li>
                                <li>Composición centrada en el tema principal</li>
                                <li>Sin elementos distractores innecesarios</li>
                                <li>Colores representativos del evento</li>
                            </ul>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Nombres y Títulos -->
            <div id="nombres-titulos" class="medialab-form">
                <h2>📝 Estructura de Nombres y Títulos</h2>
                
                <div style="border-left: 4px solid #3498db; padding-left: 15px; margin: 20px 0;">
                    <h4>🎯 Título Principal</h4>
                    <p><strong>Fórmula:</strong> <code>Nombre del Evento + Información Específica</code></p>
                    <div style="background: #f0f8ff; padding: 15px; border-radius: 4px; margin-top: 10px;">
                        <strong>✅ Ejemplos correctos:</strong>
                        <ul>
                            <li>Ceremonia de Graduación FISICC 2024</li>
                            <li>Conferencia: Inteligencia Artificial en la Medicina</li>
                            <li>Webinar: Estrategias de Marketing Digital</li>
                            <li>Taller de Desarrollo Web con React</li>
                            <li>Simposio de Investigación en Ingeniería</li>
                        </ul>
                        <strong>❌ Ejemplos incorrectos:</strong>
                        <ul>
                            <li>Video 1</li>
                            <li>Evento de la universidad</li>
                            <li>Graduación</li>
                            <li>Clase magistral</li>
                        </ul>
                    </div>
                </div>

                <div style="border-left: 4px solid #e67e22; padding-left: 15px;">
                    <h4>📄 Subtítulo (Solo para Videos)</h4>
                    <p><strong>Propósito:</strong> Especificar el tipo de evento</p>
                    <div style="background: #fdf2e9; padding: 15px; border-radius: 4px; margin-top: 10px;">
                        <strong>🏷️ Tipos de eventos comunes:</strong>
                        <ul style="columns: 2;">
                            <li>Conferencia</li>
                            <li>Webinar</li>
                            <li>Taller</li>
                            <li>Seminario</li>
                            <li>Simposio</li>
                            <li>Mesa redonda</li>
                            <li>Ceremonia</li>
                            <li>Graduación</li>
                            <li>Evento académico</li>
                            <li>Presentación</li>
                            <li>Congreso</li>
                            <li>Foro</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Facultades -->
            <div id="facultades-formato" class="medialab-form">
                <h2>🏫 Formato de Facultades</h2>
                
                <div style="background: #e8f6f3; border-left: 4px solid #1abc9c; padding: 20px; border-radius: 4px;">
                    <h4 style="margin-top: 0;">📐 Regla Principal</h4>
                    <p><strong>Usar siempre el nombre corto oficial de la facultad</strong></p>
                    <p>Si hay múltiples facultades, separar con comas y espacios</p>
                </div>

                <div style="display: grid; gap: 20px; margin-top: 20px;">
                    
                    <div style="border-left: 4px solid #27ae60; padding-left: 15px;">
                        <h4>✅ Nombres Cortos Oficiales</h4>
                        <div style="background: #f0fff0; padding: 15px; border-radius: 4px;">
                            <table style="width: 100%; border-collapse: collapse;">
                                <tr style="background: #e8f5e8;">
                                    <th style="padding: 8px; text-align: left; border: 1px solid #ddd;">Facultad Completa</th>
                                    <th style="padding: 8px; text-align: left; border: 1px solid #ddd;">Nombre Corto</th>
                                </tr>
                                <tr>
                                    <td style="padding: 8px; border: 1px solid #ddd;">Facultad de Ingeniería de Sistemas, Informática y Ciencias de la Computación</td>
                                    <td style="padding: 8px; border: 1px solid #ddd;"><strong>FISICC</strong></td>
                                </tr>
                                <tr>
                                    <td style="padding: 8px; border: 1px solid #ddd;">Facultad de Ciencias, Tecnología e Industria</td>
                                    <td style="padding: 8px; border: 1px solid #ddd;"><strong>FACTI</strong></td>
                                </tr>
                                <tr>
                                    <td style="padding: 8px; border: 1px solid #ddd;">Facultad de Medicina</td>
                                    <td style="padding: 8px; border: 1px solid #ddd;"><strong>Medicina</strong></td>
                                </tr>
                                <tr>
                                    <td style="padding: 8px; border: 1px solid #ddd;">Facultad de Derecho</td>
                                    <td style="padding: 8px; border: 1px solid #ddd;"><strong>Derecho</strong></td>
                                </tr>
                                <tr>
                                    <td style="padding: 8px; border: 1px solid #ddd;">Instituto de Diseño Gráfico</td>
                                    <td style="padding: 8px; border: 1px solid #ddd;"><strong>Diseño</strong></td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <div style="border-left: 4px solid #f39c12; padding-left: 15px;">
                        <h4>🔗 Múltiples Facultades</h4>
                        <div style="background: #fef9e7; padding: 15px; border-radius: 4px;">
                            <strong>📝 Ejemplos correctos:</strong>
                            <ul>
                                <li><code>FISICC, FACTI</code></li>
                                <li><code>Medicina, Derecho</code></li>
                                <li><code>FISICC, Diseño, FACTI</code></li>
                            </ul>
                            <strong>❌ Incorrecto:</strong>
                            <ul>
                                <li><code>FISICC y FACTI</code> (usar comas, no "y")</li>
                                <li><code>FISICC,FACTI</code> (falta espacio después de coma)</li>
                                <li><code>FISICC / FACTI</code> (no usar barras)</li>
                            </ul>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Categorías -->
            <div id="categorias-reglas" class="medialab-form">
                <h2>📂 Reglas de Categorización</h2>
                
                <div style="background: #fff5f5; border: 1px solid #fed7d7; border-radius: 6px; padding: 20px; margin: 15px 0;">
                    <h4 style="color: #e53e3e; margin-top: 0;">🎯 Regla Fundamental</h4>
                    <p style="margin: 0;"><strong>Solo UNA categoría por post</strong> - Elegir la más específica y relevante al contenido principal</p>
                </div>

                <div style="border-left: 4px solid #9b59b6; padding-left: 15px; margin-top: 20px;">
                    <h4>🧠 Cómo Elegir la Categoría Correcta</h4>
                    <div style="background: #f4f1f8; padding: 15px; border-radius: 4px;">
                        <strong>🔍 Proceso de selección:</strong>
                        <ol>
                            <li><strong>Identifica el tema principal:</strong> ¿De qué trata principalmente el evento?</li>
                            <li><strong>Busca la más específica:</strong> Entre varias opciones, elige la más detallada</li>
                            <li><strong>Considera el público objetivo:</strong> ¿A quién está dirigido principalmente?</li>
                            <li><strong>Usa el buscador:</strong> Escribe palabras clave para filtrar las 200+ categorías</li>
                        </ol>
                        
                        <strong>💡 Ejemplos de selección:</strong>
                        <ul>
                            <li><strong>Webinar de marketing:</strong> "Marketing Digital" (no "Webinars")</li>
                            <li><strong>Ceremonia de graduación:</strong> "Graduaciones" (no "Ceremonias")</li>
                            <li><strong>Taller de programación:</strong> "Programación" (no "Talleres")</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Fechas -->
            <div id="fechas-eventos" class="medialab-form">
                <h2>📅 Gestión de Fechas</h2>
                
                <div style="background: #e8f4f8; border-left: 4px solid #3498db; padding: 20px; border-radius: 4px;">
                    <h4 style="margin-top: 0;">⏰ Concepto Importante</h4>
                    <p><strong>La fecha de publicación debe ser la fecha del evento, NO la fecha de cuando publicas</strong></p>
                </div>

                <div style="display: grid; gap: 20px; margin-top: 20px;">
                    
                    <div style="border-left: 4px solid #27ae60; padding-left: 15px;">
                        <h4>✅ Fecha Correcta</h4>
                        <div style="background: #f0fff0; padding: 15px; border-radius: 4px;">
                            <strong>📝 Ejemplos:</strong>
                            <ul>
                                <li><strong>Evento:</strong> Conferencia el 15 de marzo de 2024</li>
                                <li><strong>Publicación:</strong> 20 de marzo de 2024</li>
                                <li><strong>Fecha a usar:</strong> 15 de marzo de 2024 ✅</li>
                            </ul>
                            <p><strong>💡 Razón:</strong> Los contenidos se organizan cronológicamente por fecha del evento, no de publicación</p>
                        </div>
                    </div>

                    <div style="border-left: 4px solid #f39c12; padding-left: 15px;">
                        <h4>⏰ Hora del Evento</h4>
                        <div style="background: #fef9e7; padding: 15px; border-radius: 4px;">
                            <strong>🕐 Recomendaciones:</strong>
                            <ul>
                                <li>Si conoces la hora exacta del evento, úsala</li>
                                <li>Si no la conoces, usa una hora estándar como 09:00 AM</li>
                                <li>Para eventos de todo el día, usa 08:00 AM</li>
                                <li>Para eventos vespertinos, usa 02:00 PM</li>
                            </ul>
                        </div>
                    </div>

                </div>
            </div>

            <!-- SEO Básico -->
            <div id="seo-basico" class="medialab-form">
                <h2>🔍 SEO Básico para MediaLab</h2>
                
                <div style="display: grid; gap: 20px;">
                    
                    <div style="border-left: 4px solid #e74c3c; padding-left: 15px;">
                        <h4>📝 Títulos Optimizados</h4>
                        <div style="background: #fff5f5; padding: 15px; border-radius: 4px;">
                            <strong>✅ Buenas prácticas:</strong>
                            <ul>
                                <li>Entre 50-60 caracteres para máxima visibilidad</li>
                                <li>Incluir palabras clave importantes al inicio</li>
                                <li>Ser descriptivo y específico</li>
                                <li>Incluir el año si es relevante</li>
                            </ul>
                            <strong>💡 Ejemplo optimizado:</strong>
                            <p><code>Webinar Inteligencia Artificial en Medicina 2024 - Universidad Galileo</code></p>
                        </div>
                    </div>

                    <div style="border-left: 4px solid #27ae60; padding-left: 15px;">
                        <h4>📄 Descripciones Efectivas</h4>
                        <div style="background: #f0fff0; padding: 15px; border-radius: 4px;">
                            <strong>📐 Estructura recomendada:</strong>
                            <ol>
                                <li><strong>Primera oración:</strong> ¿Qué es y para quién?</li>
                                <li><strong>Segunda oración:</strong> Temas principales o beneficios</li>
                                <li><strong>Tercera oración:</strong> Contexto adicional (fecha, speaker, etc.)</li>
                            </ol>
                            <strong>💡 Ejemplo:</strong>
                            <p><em>Webinar dirigido a profesionales de la salud sobre aplicaciones de IA en diagnóstico médico. Aprende sobre machine learning, procesamiento de imágenes y casos de éxito reales. Presentado por el Dr. Juan Pérez, especialista en informática médica, el 15 de marzo de 2024.</em></p>
                        </div>
                    </div>

                    <div style="border-left: 4px solid #9b59b6; padding-left: 15px;">
                        <h4>🏷️ Palabras Clave</h4>
                        <div style="background: #f4f1f8; padding: 15px; border-radius: 4px;">
                            <strong>🎯 Incluir naturalmente:</strong>
                            <ul>
                                <li>Nombre de la universidad (Universidad Galileo)</li>
                                <li>Tipo de evento (webinar, conferencia, taller)</li>
                                <li>Tema principal del contenido</li>
                                <li>Facultad o área académica</li>
                                <li>Año del evento</li>
                            </ul>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Enlaces de Acceso Rápido -->
            <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 30px 0; text-align: center;">
                <h3>🚀 Acceso Rápido a Formularios</h3>
                <div style="display: flex; gap: 15px; justify-content: center; flex-wrap: wrap;">
                    <a href="<?php echo admin_url('admin.php?page=medialab-video'); ?>" class="button button-primary">
                        🎥 Crear Video Post
                    </a>
                    <a href="<?php echo admin_url('admin.php?page=medialab-gallery'); ?>" class="button button-primary">
                        🖼️ Crear Gallery Post
                    </a>
                    <a href="<?php echo admin_url('admin.php?page=medialab-docs-videos'); ?>" class="button">
                        📖 Guía de Videos
                    </a>
                    <a href="<?php echo admin_url('admin.php?page=medialab-docs-gallery'); ?>" class="button">
                        📖 Guía de Galerías
                    </a>
                </div>
            </div>

        </div>
        
        <style>
        .wrap h2 { 
            color: #333; 
            border-bottom: 2px solid #3498db; 
            padding-bottom: 10px; 
            margin-top: 30px;
        }
        .wrap h4 { margin-top: 0; }
        .wrap code { 
            background: #f1f1f1; 
            padding: 3px 8px; 
            border-radius: 4px; 
            font-family: 'Courier New', monospace;
            font-size: 13px;
            color: #c7254e;
        }
        .wrap a[href^="#"] { 
            text-decoration: none; 
            color: #3498db; 
            font-weight: 500; 
        }
        .wrap a[href^="#"]:hover { 
            color: #2980b9; 
            text-decoration: underline;
        }
        .wrap table {
            font-size: 14px;
        }
        .wrap table th {
            font-weight: 600;
            color: #2c3e50;
        }
        </style>
        
        <?php
    }
}

// Inicializar documentación general
new MediaLab_General_Documentation();
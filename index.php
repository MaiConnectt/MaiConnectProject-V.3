<!-- Define que el documento es HTML5 y establece el español como idioma principal 
 para los navegadores y el SEO -->
<!DOCTYPE html>
<html lang="es">

<head>
    <!-- Codificación de caracteres para mostrar tildes y eñes correctamente -->
    <meta charset="UTF-8">

    <!-- Configuración para que la página sea responsiva en dispositivos móviles -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Descripción de la página (útil para el SEO y resultados de búsqueda en Google) -->
    <meta name="description"
        content="Mai Shop - Repostería artesanal de alta calidad. Tortas, cupcakes, galletas y más delicias hechas con amor.">

    <!-- Título de la pestaña en el navegador -->
    <title>Mai Shop - Repostería Artesanal</title>

    <!-- Fuentes de Google: Playfair Display para títulos y Poppins para textos -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=Poppins:wght@300;400;500;600;700&family=Dancing+Script:wght@600&display=swap"
        rel="stylesheet">

    <!-- Iconos Font Awesome: Librería para importar íconos gráficos (menús, iconos, etc.) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer">

    <!-- Hoja de estilos principal de la landing page (v=2.6 evita mantener cargar caché antiguo) -->
    <link rel="stylesheet" href="styles/landing.css?v=2.8">
</head>


<body>
    <!-- Navegación: Barra superior que permite al usuario moverse por el sitio web -->
    <nav class="navbar" id="navbar">
        <div class="nav-container">

            <!-- Logo de la tienda -->
            <div class="logo">
                <img src="src/img/mai.png" alt="Mai Shop" style="height: 50px; width: auto;">
            </div>

            <!-- Menú de navegación con enlaces (anclajes) hacia las diferentes secciones dentro de esta misma página -->
            <div class="nav-menu" id="navMenu">
                <a href="#inicio" class="nav-link">Inicio</a>
                <a href="#nosotros" class="nav-link">Nosotros</a>
                <a href="#productos" class="nav-link">Productos</a>
                <a href="#contacto" class="nav-link">Contacto</a>

                <!-- Enlace real para ir a la pantalla de inicio de sesión de administrador/equipo -->
                <a href="src/Php/login/login.php" class="btn btn-secondary"
                    style="padding: 0.5rem 1.2rem; border-radius: 50px; font-size: 0.9rem; margin-left: 15px;">
                    <i class="fas fa-user" style="margin-right: 5px;"></i> Iniciar Sesión
                </a>
            </div>

            <!-- Botón de menú hamburguesa: Solo visible en pantallas de celulares o dispositivos pequeños -->
            <button class="hamburger" id="hamburger">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>
    </nav>

    <!-- Sección Hero: La primera pantalla grande que ve el visitante, diseñada para causar impacto inmediato -->
    <header class="hero" id="inicio">
        <!-- Filtro semitransparente sobre la imagen de fondo para que el texto resalte y sea legible -->
        <div class="hero-overlay"></div>
        <div class="container hero-content"
            style="z-index: 2; position: relative; display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 85vh; padding-top: 60px;">
            <div class="hero-logo-container" style="margin-bottom: 25px;">
                <img src="src/img/mai.png" alt="Logo Hero" class="hero-logo"
                    style="width: 140px; filter: drop-shadow(0 4px 15px rgba(0,0,0,0.3));">
            </div>

            <h1 class="hero-title" style="margin-bottom: 20px;">
                <span class="motto-text"
                    style="display:block; font-weight: 400; font-style: italic; font-size: clamp(2rem, 5vw, 4.2rem); line-height: 1.1; text-align: center; letter-spacing: 0.5px; color: #fff;">Repostería
                    hecha con el corazón</span>
            </h1>

            <p class="hero-description"
                style="max-width: 750px; font-size: clamp(1.1rem, 2.2vw, 1.5rem); opacity: 0.95; margin: 15px auto 35px; color: #fff; font-weight: 300; line-height: 1.5;">
                Horneando momentos inolvidables con ingredientes naturales y mucho amor en toda ocasión.
            </p>

            <div class="hero-buttons">
                <a href="#productos" class="btn btn-primary"
                    style="padding: 1.1rem 3rem; font-size: 1.2rem; border-radius: 50px; box-shadow: 0 8px 25px rgba(0,0,0,0.2);">
                    <i class="fas fa-cookie-bite" style="margin-right: 12px;"></i> Explorar el Menú
                </a>
            </div>
        </div>

        <div class="scroll-indicator">
            <i class="fas fa-chevron-down"></i>
        </div>

        <div class="section-divider">
            <svg data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 120"
                preserveAspectRatio="none" style="fill: var(--cream); height: 80px; width: 100%;">
                <path
                    d="M321.39,56.44c58-10.79,114.16-30.13,172-41.86,82.39-16.72,168.19-17.73,250.45-.39C823.78,31,906.67,72,985.66,92.83c70.05,18.48,146.53,26.09,214.34,3V120H0V95.8C54.66,106.31,118,103,173.34,91,228.67,79,271.39,70.52,321.39,56.44Z">
                </path>
            </svg>
        </div>
    </header>

    <!-- Sección de Características: Muestra visualmente las 4 ventajas principales (Propuesta de Valor) de Mai Shop -->
    <section class="features">
        <!-- Contenedor que agrupa las tarjetas de ventajas, centrándolas y organizándolas en la pantalla -->
        <div class="container">

            <!-- Primera ventaja: Calidad (Usa el ícono de una medalla) -->
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-award"></i>
                </div>
                <h3>Calidad Premium</h3>
                <p>Ingredientes seleccionados de la más alta calidad</p>
            </div>

            <!-- Segunda ventaja: Dedicación (Usa el ícono de un corazón) -->
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-heart"></i>
                </div>
                <h3>Hecho con Amor</h3>
                <p>Cada producto es elaborado con dedicación y pasión</p>
            </div>

            <!-- Tercera ventaja: Domicilios (Usa el ícono de un camión) -->
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-truck"></i>
                </div>
                <h3>Entrega Rápida</h3>
                <p>Llevamos tus pedidos frescos a tu puerta</p>
            </div>

            <!-- Cuarta ventaja: Personalización (Usa el ícono de una paleta de pintura) -->
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-palette"></i>
                </div>
                <h3>Diseños Únicos</h3>
                <p>Personalizamos según tus preferencias</p>
            </div>
        </div>
    </section>

    <!-- Sección Nosotros: Cuenta la historia de Mai Shop, la experiencia en el mercado y una invitación a trabajar -->
    <section class="about" id="nosotros">
        <!-- Contenedor que agrupa la imagen y el texto descriptivo -->
        <div class="container">
            <div class="about-content">
                <div class="about-image">
                    <img src="https://images.unsplash.com/photo-1578985545062-69928b1d9587?w=800&q=80"
                        alt="Repostería artesanal">
                    <div class="about-badge">
                        <i class="fas fa-star"></i>
                        <span>+5 Años</span>
                    </div>
                </div>

                <div class="about-text">
                    <span class="section-label">Nuestra Historia</span>
                    <h2 class="section-title">Quiénes Somos</h2>
                    <p class="about-description">
                        En <strong>Mai Shop</strong>, transformamos momentos ordinarios en experiencias extraordinarias
                        a través de la repostería artesanal. Desde 2019, nos hemos dedicado a crear delicias únicas
                        que endulzan los momentos más especiales de nuestros clientes.
                    </p>
                    <p class="about-description">
                        Cada torta, cupcake y postre es elaborado con ingredientes premium, técnicas tradicionales
                        y un toque de creatividad que nos distingue. Nuestro compromiso es superar tus expectativas
                        en cada bocado.
                    </p>

                    <!-- Estadísticas rápidas para dar confianza al cliente sobre la trayectoria y resultados del negocio -->
                    <div class="about-stats">
                        <div class="stat">
                            <h3>500+</h3>
                            <p>Clientes Felices</p>
                        </div>
                        <div class="stat">
                            <h3>1000+</h3>
                            <p>Productos Creados</p>
                        </div>
                        <div class="stat">
                            <h3>100%</h3>
                            <p>Satisfacción</p>
                        </div>
                    </div>


                </div>
            </div>
        </div>
    </section>

    <!-- Sección de Productos: Muestra el catálogo principal interactivo (Tortas, Cupcakes, etc.) -->
    <section class="products" id="productos">
        <div class="container">
            <div class="section-header">
                <span class="section-label">Nuestros Productos</span>
                <h2 class="section-title">Delicias Artesanales</h2>
                <p class="section-description">
                    Descubre nuestra selección de productos elaborados con los mejores ingredientes
                </p>
            </div>

            <!-- Cuadrícula dinámica para mostrar productos; se adapta desde 1 columna en celulares hasta 3 en PC -->
            <div class="products-grid">

                <!-- Tarjeta de Producto: Contiene foto, título, precio básico y un Call to Action (CTA) hacia WhatsApp -->
                <div class="product-card">
                    <div class="product-image">
                        <img src="https://images.unsplash.com/photo-1578985545062-69928b1d9587?w=600&q=80"
                            alt="Tortas Personalizadas">
                        <div class="product-overlay">
                            <a href="https://wa.me/573244917185?text=Quiero%20información%20sobre%20tortas"
                                class="btn-order" target="_blank">
                                <i class="fab fa-whatsapp"></i> Pedir
                            </a>
                        </div>
                    </div>
                    <div class="product-info">
                        <h3>Tortas Personalizadas</h3>
                        <p>Diseños únicos para tus celebraciones especiales</p>
                        <div class="product-price">Desde $35.000</div>
                    </div>
                </div>

                <div class="product-card">
                    <div class="product-image">
                        <img src="https://images.unsplash.com/photo-1614707267537-b85aaf00c4b7?w=600&q=80"
                            alt="Cupcakes">
                        <div class="product-overlay">
                            <a href="https://wa.me/573244917185?text=Quiero%20información%20sobre%20cupcakes"
                                class="btn-order" target="_blank">
                                <i class="fab fa-whatsapp"></i> Pedir
                            </a>
                        </div>
                    </div>
                    <div class="product-info">
                        <h3>Cupcakes Gourmet</h3>
                        <p>Deliciosos cupcakes con decoraciones creativas</p>
                        <div class="product-price">$5.000 c/u</div>
                    </div>
                </div>

                <div class="product-card">
                    <div class="product-image">
                        <img src="https://images.unsplash.com/photo-1558961363-fa8fdf82db35?w=600&q=80"
                            alt="Galletas Decoradas">
                        <div class="product-overlay">
                            <a href="https://wa.me/573244917185?text=Quiero%20información%20sobre%20galletas"
                                class="btn-order" target="_blank">
                                <i class="fab fa-whatsapp"></i> Pedir
                            </a>
                        </div>
                    </div>
                    <div class="product-info">
                        <h3>Galletas Decoradas</h3>
                        <p>Galletas artesanales con glaseado personalizado</p>
                        <div class="product-price">$2.500 c/u</div>
                    </div>
                </div>

                <div class="product-card">
                    <div class="product-image">
                        <img src="https://images.unsplash.com/photo-1624353365286-3f8d62daad51?w=600&q=80"
                            alt="Brownies">
                        <div class="product-overlay">
                            <a href="https://wa.me/573244917185?text=Quiero%20información%20sobre%20brownies"
                                class="btn-order" target="_blank">
                                <i class="fab fa-whatsapp"></i> Pedir
                            </a>
                        </div>
                    </div>
                    <div class="product-info">
                        <h3>Brownies Premium</h3>
                        <p>Brownies de chocolate con nueces y más</p>
                        <div class="product-price">$4.000 c/u</div>
                    </div>
                </div>

                <div class="product-card">
                    <div class="product-image">
                        <img src="https://images.unsplash.com/photo-1565958011703-44f9829ba187?w=600&q=80"
                            alt="Cheesecake">
                        <div class="product-overlay">
                            <a href="https://wa.me/573244917185?text=Quiero%20información%20sobre%20cheesecake"
                                class="btn-order" target="_blank">
                                <i class="fab fa-whatsapp"></i> Pedir
                            </a>
                        </div>
                    </div>
                    <div class="product-info">
                        <h3>Cheesecake</h3>
                        <p>Suave y cremoso con salsa de frutos rojos</p>
                        <div class="product-price">$28.000</div>
                    </div>
                </div>

                <div class="product-card">
                    <div class="product-image">
                        <img src="https://images.unsplash.com/photo-1586985289688-ca3cf47d3e6e?w=600&q=80"
                            alt="Alfajores">
                        <div class="product-overlay">
                            <a href="https://wa.me/573244917185?text=Quiero%20información%20sobre%20alfajores"
                                class="btn-order" target="_blank">
                                <i class="fab fa-whatsapp"></i> Pedir
                            </a>
                        </div>
                    </div>
                    <div class="product-info">
                        <h3>Alfajores</h3>
                        <p>Rellenos de arequipe y cubiertos de coco</p>
                        <div class="product-price">$2.000 c/u</div>
                    </div>
                </div>
            </div>
        </div>
    </section>


    <!-- Sección de Testimonios: Muestra reseñas de clientes reales. Estrategia de prueba social para dar confianza -->
    <section class="testimonials">
        <div class="container">
            <div class="section-header">
                <span class="section-label">Testimonios</span>
                <h2 class="section-title">Lo Que Dicen Nuestros Clientes</h2>
            </div>

            <!-- Cuadrícula que organiza las tarjetas de reseñas de los clientes -->
            <div class="testimonials-grid">

                <!-- Tarjeta individual: Contiene 5 estrellas ficticias (o reales), el comentario y quién lo dijo -->
                <div class="testimonial-card">
                    <div class="stars">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                    <p class="testimonial-text">
                        "La torta de cumpleaños que pedí superó todas mis expectativas. ¡Deliciosa y hermosa!"
                    </p>
                    <div class="testimonial-author">
                        <strong>María González</strong>
                        <span>Cliente Frecuente</span>
                    </div>
                </div>

                <div class="testimonial-card">
                    <div class="stars">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                    <p class="testimonial-text">
                        "Los cupcakes son increíbles. Perfectos para eventos corporativos. Muy recomendados."
                    </p>
                    <div class="testimonial-author">
                        <strong>Carlos Ramírez</strong>
                        <span>Empresario</span>
                    </div>
                </div>

                <div class="testimonial-card">
                    <div class="stars">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                    <p class="testimonial-text">
                        "Excelente servicio y productos de calidad. Mi familia quedó encantada con el cheesecake."
                    </p>
                    <div class="testimonial-author">
                        <strong>Liliana Ascanio</strong>
                        <span>Ama de Casa</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Sección de Contacto: Información vital para comunicarse, horarios y redes sociales de Mai Shop -->
    <section class="contact" id="contacto">
        <div class="container">
            <div class="contact-content">
                <div class="contact-info">
                    <span class="section-label">Contáctanos</span>
                    <h2 class="section-title">Haz Tu Pedido</h2>
                    <p class="contact-description">
                        Estamos listos para endulzar tu próximo evento especial.
                        Contáctanos por WhatsApp y cuéntanos qué necesitas.
                    </p>

                    <!-- Datos de contacto estructurados con iconos gráficos que los representan (Teléfono, correo electrónico, etc.) -->
                    <div class="contact-details">
                        <div class="contact-item">
                            <i class="fas fa-phone"></i>
                            <div>
                                <h4>Teléfono</h4>
                                <p>+57 324 491 7185</p>
                            </div>
                        </div>

                        <div class="contact-item">
                            <i class="fas fa-envelope"></i>
                            <div>
                                <h4>Email</h4>
                                <p>maira.sierra@email.com</p>
                            </div>
                        </div>

                        <div class="contact-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <div>
                                <h4>Ubicación</h4>
                                <p>Bucaramanga, Santander</p>
                            </div>
                        </div>

                        <div class="contact-item">
                            <i class="fas fa-clock"></i>
                            <div>
                                <h4>Horario</h4>
                                <p>Lun - Sáb: 8:00 AM - 6:00 PM</p>
                            </div>
                        </div>
                    </div>

                    <div class="social-links">
                        <a href="https://wa.me/573244917185" target="_blank" class="social-link">
                            <i class="fab fa-whatsapp"></i>
                        </a>
                        <a href="#" class="social-link">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="#" class="social-link">
                            <i class="fab fa-facebook"></i>
                        </a>
                    </div>
                </div>

                <!-- Tarjeta grande de Llamado a la Acción (CTA) para enviar al usuario directo al chat de WhatsApp al instante -->
                <div class="contact-cta">
                    <div class="cta-card">
                        <i class="fab fa-whatsapp"></i>
                        <h3>¿Listo para hacer tu pedido?</h3>
                        <p>Chatea con nosotros en WhatsApp y te ayudaremos a crear el postre perfecto</p>

                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Pie de Página (Footer): La última sección inferior. Contiene accesos rápidos, información legal y créditos -->
    <footer class="footer" id="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-brand">
                    <div class="logo">
                        <img src="src/img/mai.png" alt="Mai Shop"
                            style="height: 50px; width: auto; filter: brightness(0) invert(1);">
                    </div>
                    <p>Endulzando momentos especiales desde 2019</p>
                </div>

                <div class="footer-links">
                    <h4>Enlaces Rápidos</h4>
                    <a href="#inicio">Inicio</a>
                    <a href="#nosotros">Nosotros</a>
                    <a href="#productos">Productos</a>
                    <a href="src/Php/unete/unete.php">Sé parte del equipo</a>
                    <a href="#contacto">Contacto</a>
                </div>

                <div class="footer-contact">
                    <h4>Contacto</h4>
                    <p><i class="fas fa-phone"></i> +57 324 491 7185</p>
                    <p><i class="fas fa-envelope"></i> maira.sierra@email.com</p>
                    <p><i class="fas fa-map-marker-alt"></i> Bucaramanga, Santander</p>
                </div>
            </div>

            <div class="footer-bottom">
                <p>&copy; 2025 Mai Shop. Todos los derechos reservados.</p>
                <p>Desarrollado con <i class="fas fa-heart"></i> por Maira Alejandra David</p>
            </div>
        </div>
    </footer>

    <!-- Botón Flotante de WhatsApp: Un icono verde circular que "persigue" al usuario sin importar donde haga scroll -->
    <a href="https://wa.me/573244917185?text=Hola%20Mai,%20quiero%20información" class="whatsapp-float" target="_blank">
        <i class="fab fa-whatsapp"></i>
    </a>

    <!-- Botón Volver Arriba: Emerge cuando el usuario baja mucho y le permite regresar al cabezote con un solo clic -->
    <button class="scroll-top" id="scrollTop">
        <i class="fas fa-arrow-up"></i>
    </button>

    <script src="src/JavaScript/landing.js?v=2.7"></script>
</body>

</html>

<!-- Modal Iniciar Sesión: Una ventana emergente superpuesta que se activa al darle clic iniciar sesión. Te pregunta tu rol ANTES de dejarte intentar entrar  -->
<div class='login-modal-overlay' id='loginModal'>
    <div class='login-modal-content'>
        <div class='modal-cookie-shape'>
            <div class='cookie-bite'></div>
            <h2 class='modal-title'>MAI CONNECT</h2>
            <div class='modal-buttons'>
                <a href='src/Php/login/login.php?role=admin' class='btn-modal'>Admi</a>
                <a href='src/Php/login/login.php?role=team' class='btn-modal'>Equipo</a>
            </div>
        </div>
        <button class='modal-close' id='closeModal'>&times;</button>
    </div>
</div>
@extends ('layouts.empresas') 
@section ('contenido')

<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }
    
    body {
        background: #f8f9ff;
    }

    /* Animaciones */
    @keyframes slideInDown {
        from {
            opacity: 0;
            transform: translateY(-30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes slideInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
        }
        to {
            opacity: 1;
        }
    }

    @keyframes float {
        0%, 100% {
            transform: translateY(0px);
        }
        50% {
            transform: translateY(-10px);
        }
    }

    /* Contenedor Principal */
    .support-wrapper {
        width: 100%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        position: relative;
        overflow: hidden;
        animation: fadeIn 0.6s ease-out;
    }

    .support-wrapper::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="20" cy="20" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="80" cy="80" r="1.5" fill="rgba(255,255,255,0.1)"/><circle cx="50" cy="50" r="1" fill="rgba(255,255,255,0.1)"/></svg>') repeat;
        pointer-events: none;
    }

    /* Header */
    .support-header {
        position: relative;
        z-index: 2;
        padding: 50px 40px;
        text-align: center;
        color: white;
        max-width: 900px;
        margin: 0 auto;
    }

    .support-header-logo {
        display: inline-block;
        margin-bottom: 25px;
        animation: float 3s ease-in-out infinite;
    }

    .support-header-logo img {
        height: 80px;
        width: auto;
        filter: drop-shadow(0 4px 15px rgba(0, 0, 0, 0.2));
    }

    .support-header h1 {
        font-size: 3em;
        font-weight: 700;
        margin-bottom: 15px;
        text-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        animation: slideInDown 0.8s ease-out;
        letter-spacing: -1px;
    }

    .support-header p {
        font-size: 1.1em;
        opacity: 0.95;
        max-width: 600px;
        margin: 0 auto;
        line-height: 1.6;
        animation: slideInUp 0.8s ease-out 0.2s both;
    }

    /* Cuerpo Principal */
    .support-body {
        position: relative;
        z-index: 2;
        background: white;
        padding: 0;
    }

    .intro-section {
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.05) 0%, rgba(118, 75, 162, 0.05) 100%);
        padding: 50px 40px;
        text-align: center;
        border-bottom: 1px solid rgba(102, 126, 234, 0.1);
    }

    .intro-text {
        font-size: 1.15em;
        color: #555;
        margin-bottom: 0;
        line-height: 1.8;
        max-width: 700px;
        margin-left: auto;
        margin-right: auto;
    }

    .intro-text i {
        color: #667eea;
        margin-right: 10px;
        font-size: 1.3em;
    }

    /* Contenedor de Tarjetas */
    .cards-container {
        padding: 60px 40px;
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 40px;
    }

    /* Tarjeta de Contacto */
    .contact-card {
        background: white;
        border-radius: 15px;
        padding: 40px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
        border: 1px solid rgba(102, 126, 234, 0.1);
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        position: relative;
        overflow: hidden;
        animation: slideInUp 0.8s ease-out;
    }

    .contact-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 4px;
        background: linear-gradient(90deg, transparent, #667eea, transparent);
        transition: left 0.5s ease;
    }

    .contact-card:hover::before {
        left: 100%;
    }

    .contact-card:hover {
        transform: translateY(-15px);
        box-shadow: 0 20px 50px rgba(102, 126, 234, 0.25);
        border-color: #667eea;
    }

    .contact-card h4 {
        color: #667eea;
        margin-bottom: 30px;
        font-size: 1.6em;
        display: flex;
        align-items: center;
        font-weight: 700;
    }

    .contact-card h4 i {
        margin-right: 12px;
        font-size: 1.4em;
        width: 40px;
        height: 40px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
    }

    .contact-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .contact-list li {
        padding: 18px 0;
        border-bottom: 1px solid #f0f0f0;
        display: flex;
        align-items: flex-start;
        transition: all 0.3s ease;
    }

    .contact-list li:last-child {
        border-bottom: none;
    }

    .contact-list li:hover {
        padding-left: 10px;
        background: rgba(102, 126, 234, 0.03);
        border-radius: 8px;
        padding: 18px 15px;
        margin: 0 -15px;
    }

    .contact-list li i {
        width: 35px;
        color: #667eea;
        margin-right: 15px;
        font-size: 1.2em;
        flex-shrink: 0;
        text-align: center;
        padding-top: 3px;
    }

    .contact-info {
        flex: 1;
    }

    .contact-info strong {
        color: #333;
        display: block;
        margin-bottom: 5px;
        font-weight: 600;
        font-size: 0.95em;
    }

    .contact-list li a {
        color: #667eea;
        text-decoration: none;
        font-weight: 500;
        transition: all 0.3s ease;
        display: inline-block;
    }

    .contact-list li a:hover {
        color: #764ba2;
        text-decoration: underline;
        transform: translateX(3px);
    }

    .contact-list li:not(:has(a)) .contact-info {
        color: #666;
        font-size: 0.95em;
    }

    /* Tarjeta de Formulario de Contacto */
    .form-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 15px;
        padding: 40px;
        box-shadow: 0 10px 40px rgba(102, 126, 234, 0.3);
        animation: slideInUp 0.8s ease-out 0.1s both;
        position: relative;
        overflow: hidden;
    }

    .form-card::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="50" r="2" fill="rgba(255,255,255,0.05)"/><circle cx="25" cy="25" r="1" fill="rgba(255,255,255,0.03)"/></svg>') repeat;
        pointer-events: none;
    }

    .form-card h4 {
        margin-bottom: 30px;
        font-size: 1.6em;
        position: relative;
        z-index: 2;
        font-weight: 700;
        display: flex;
        align-items: center;
    }

    .form-card h4 i {
        margin-right: 12px;
        font-size: 1.4em;
    }

    .form-group {
        margin-bottom: 20px;
        position: relative;
        z-index: 2;
    }

    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        font-size: 0.95em;
        color: rgba(255, 255, 255, 0.95);
    }

    .form-group input,
    .form-group textarea {
        width: 100%;
        padding: 12px 15px;
        border: 2px solid rgba(255, 255, 255, 0.2);
        border-radius: 8px;
        font-size: 1em;
        background: rgba(255, 255, 255, 0.1);
        color: white;
        font-family: inherit;
        transition: all 0.3s ease;
        backdrop-filter: blur(10px);
    }

    .form-group input::placeholder,
    .form-group textarea::placeholder {
        color: rgba(255, 255, 255, 0.6);
    }

    .form-group input:focus,
    .form-group textarea:focus {
        outline: none;
        border-color: rgba(255, 255, 255, 0.5);
        background: rgba(255, 255, 255, 0.15);
        box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.1);
    }

    .form-group textarea {
        resize: vertical;
        min-height: 100px;
    }

    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 15px;
    }

    .form-row .form-group {
        margin-bottom: 0;
    }

    .btn-submit {
        width: 100%;
        background: rgba(255, 255, 255, 0.25);
        color: white;
        border: 2px solid rgba(255, 255, 255, 0.4);
        padding: 14px 32px;
        border-radius: 50px;
        font-size: 1.05em;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        backdrop-filter: blur(10px);
        margin-top: 15px;
        position: relative;
        z-index: 2;
    }

    .btn-submit:hover {
        background: rgba(255, 255, 255, 0.35);
        transform: translateY(-3px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.25);
        border-color: rgba(255, 255, 255, 0.6);
    }

    .btn-submit:active {
        transform: translateY(-1px);
    }

    .btn-submit:disabled {
        opacity: 0.7;
        cursor: not-allowed;
        transform: none;
    }

    .btn-submit i {
        margin-right: 8px;
    }

    /* Mensajes de Alerta */
    .alert {
        padding: 15px 20px;
        border-radius: 8px;
        margin-bottom: 20px;
        position: relative;
        z-index: 2;
        animation: slideInDown 0.4s ease-out;
    }

    .alert-success {
        background: rgba(76, 175, 80, 0.2);
        color: #4CAF50;
        border-left: 4px solid #4CAF50;
    }

    .alert-error {
        background: rgba(244, 67, 54, 0.2);
        color: #f44336;
        border-left: 4px solid #f44336;
    }

    .alert i {
        margin-right: 10px;
    }

    /* Tarjeta de Desarrollador */
    .developer-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 15px;
        padding: 40px;
        box-shadow: 0 10px 40px rgba(102, 126, 234, 0.3);
        animation: slideInUp 0.8s ease-out 0.2s both;
        position: relative;
        overflow: hidden;
        text-align: center;
    }

    .developer-card::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="50" r="2" fill="rgba(255,255,255,0.08)"/></svg>') repeat;
        animation: float 25s infinite linear;
        pointer-events: none;
    }

    .developer-card h4 {
        margin-bottom: 15px;
        font-size: 1.5em;
        position: relative;
        z-index: 2;
        font-weight: 700;
    }

    .developer-card h4 i {
        margin-right: 10px;
    }

    .developer-card .company-name {
        font-size: 2.2em;
        font-weight: 800;
        margin-bottom: 20px;
        text-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        position: relative;
        z-index: 2;
        letter-spacing: -0.5px;
    }

    .developer-card p {
        margin-bottom: 20px;
        font-size: 1.05em;
        position: relative;
        z-index: 2;
        line-height: 1.8;
        opacity: 0.95;
    }

    .developer-card a {
        display: inline-block;
        background: rgba(255, 255, 255, 0.25);
        color: white;
        padding: 14px 32px;
        border-radius: 50px;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        backdrop-filter: blur(10px);
        border: 2px solid rgba(255, 255, 255, 0.4);
        position: relative;
        z-index: 2;
        margin-top: 10px;
        font-size: 1.02em;
    }

    .developer-card a:hover {
        background: rgba(255, 255, 255, 0.35);
        transform: translateY(-3px) scale(1.05);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.25);
        color: white;
        text-decoration: none;
        border-color: rgba(255, 255, 255, 0.6);
    }

    /* Footer */
    .footer-actions {
        text-align: center;
        padding: 40px;
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.05) 0%, rgba(118, 75, 162, 0.05) 100%);
        border-top: 1px solid rgba(102, 126, 234, 0.1);
        position: relative;
        z-index: 2;
    }

    .btn-back {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        padding: 14px 35px;
        border-radius: 50px;
        font-size: 1.05em;
        font-weight: 600;
        text-decoration: none;
        display: inline-block;
        transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
        cursor: pointer;
        border: none;
    }

    .btn-back:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 35px rgba(102, 126, 234, 0.4);
        color: white;
        text-decoration: none;
    }

    .btn-back:active {
        transform: translateY(-2px);
    }

    .btn-back i {
        margin-right: 10px;
    }

    /* Responsive Design */
    @media (max-width: 1024px) {
        .cards-container {
            grid-template-columns: 1fr;
            gap: 30px;
            padding: 40px 30px;
        }

        .support-header h1 {
            font-size: 2.5em;
        }

        .contact-card,
        .form-card {
            padding: 30px;
        }

        .developer-card {
            padding: 40px 30px;
        }
    }

    @media (max-width: 768px) {
        .support-wrapper {
            width: 100%;
        }

        .support-header {
            padding: 35px 25px;
        }

        .support-header h1 {
            font-size: 2em;
        }

        .support-header-logo img {
            height: 60px;
        }

        .intro-section {
            padding: 35px 25px;
        }

        .intro-text {
            font-size: 1em;
        }

        .cards-container {
            grid-template-columns: 1fr;
            gap: 25px;
            padding: 30px 20px;
        }

        .contact-card,
        .form-card {
            padding: 25px;
        }

        .contact-card h4,
        .form-card h4 {
            font-size: 1.4em;
            margin-bottom: 20px;
        }

        .contact-list li {
            padding: 15px 0;
            flex-direction: column;
        }

        .contact-list li i {
            margin-right: 0;
            margin-bottom: 8px;
            width: auto;
        }

        .form-row {
            grid-template-columns: 1fr;
        }

        .developer-card {
            padding: 30px 25px;
        }

        .developer-card .company-name {
            font-size: 1.8em;
        }

        .developer-card p {
            font-size: 1em;
        }

        .footer-actions {
            padding: 30px 20px;
        }

        .btn-back {
            width: 100%;
            max-width: 300px;
        }
    }

    @media (max-width: 480px) {
        .support-header {
            padding: 30px 20px;
        }

        .support-header h1 {
            font-size: 1.7em;
            margin-bottom: 10px;
        }

        .support-header-logo img {
            height: 50px;
        }

        .support-header p {
            font-size: 1em;
        }

        .intro-section {
            padding: 25px 20px;
        }

        .intro-text {
            font-size: 0.95em;
        }

        .cards-container {
            padding: 25px 15px;
            gap: 20px;
        }

        .contact-card,
        .form-card {
            padding: 20px;
            border-radius: 12px;
        }

        .contact-card h4,
        .form-card h4 {
            font-size: 1.2em;
            margin-bottom: 15px;
        }

        .contact-list li {
            padding: 12px 0;
        }

        .developer-card {
            padding: 25px 20px;
            border-radius: 12px;
        }

        .developer-card .company-name {
            font-size: 1.5em;
            margin-bottom: 15px;
        }

        .developer-card p {
            font-size: 0.95em;
            margin-bottom: 15px;
        }

        .developer-card a {
            padding: 12px 25px;
            font-size: 0.95em;
        }

        .footer-actions {
            padding: 25px 15px;
        }

        .btn-back {
            padding: 12px 25px;
            font-size: 1em;
        }

        .form-group input,
        .form-group textarea {
            font-size: 16px;
        }
    }

    /* Mejoras visuales adicionales */
    .support-container {
        position: relative;
    }

    .support-container::after {
        content: '';
        position: absolute;
        bottom: 0;
        right: 0;
        width: 300px;
        height: 300px;
        background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
        pointer-events: none;
    }
</style>

<div class="row">
    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
        <div class="support-container">
            <div class="support-wrapper">
                <div class="support-header">
                    <div class="support-header-logo">
                        <img src="{{ asset('img/logo_inicio.png') }}" alt="Logo DEVSOFT">
                    </div>
                    <h1>Soporte Técnico</h1>
                    <p>Estamos aquí para ayudarte en cada paso del camino</p>
                </div>

                <div class="support-body">
                    <div class="intro-section">
                        <p class="intro-text">
                            <i class="fa fa-comments-o"></i> 
                            Si necesitas ayuda o tienes alguna consulta sobre el sistema, nuestro equipo especializado está disponible para apoyarte. Usa el formulario o contáctanos directamente a través de nuestros canales.
                        </p>
                    </div>

                    <div class="cards-container">
                        <div class="contact-card">
                            <h4><i class="fa fa-phone-square"></i> Contacto Principal</h4>
                            <ul class="contact-list">
                                <li>
                                    <i class="fa fa-envelope"></i>
                                    <div class="contact-info">
                                        <strong>Correo Electrónico</strong>
                                        <a href="mailto:holapesac@gmail.com">holapesac@gmail.com</a>
                                    </div>
                                </li>
                                <li>
                                    <i class="fa fa-user-circle"></i>
                                    <div class="contact-info">
                                        <strong>Jacker</strong>
                                        <a href="tel:+51928396147">+51 928 396 147</a>
                                    </div>
                                </li>
                                
                                <li>
                                    <i class="fa fa-calendar"></i>
                                    <div class="contact-info">
                                        <strong>Horario de Atención</strong>
                                        Lunes a Viernes · 9:00 AM - 6:00 PM
                                    </div>
                                </li>
                            </ul>
                        </div>

                        <div class="form-card">
                            <h4><i class="fa fa-paper-plane"></i> Envía tu Mensaje</h4>
                            
                            <div id="message-container"></div>

                            <form id="contactForm" method="POST">
                                {{ csrf_field() }}
                                
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="nombre"><i class="fa fa-user"></i> Nombre Completo</label>
                                        <input 
                                            type="text" 
                                            id="nombre" 
                                            name="nombre" 
                                            placeholder="Tu nombre completo"
                                        >
                                    </div>
                                    <div class="form-group">
                                        <label for="email"><i class="fa fa-envelope"></i> Correo Electrónico</label>
                                        <input 
                                            type="email" 
                                            id="email" 
                                            name="email" 
                                            placeholder="tu@correo.com"
                                        >
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="telefono"><i class="fa fa-phone"></i> Teléfono</label>
                                    <input 
                                        type="tel" 
                                        id="telefono" 
                                        name="telefono" 
                                        placeholder="Tu número de teléfono"
                                    >
                                </div>

                                <div class="form-group">
                                    <label for="asunto"><i class="fa fa-lightbulb-o"></i> Asunto</label>
                                    <input 
                                        type="text" 
                                        id="asunto" 
                                        name="asunto" 
                                        placeholder="¿En qué podemos ayudarte?"
                                    >
                                </div>

                                <div class="form-group">
                                    <label for="mensaje"><i class="fa fa-comments"></i> Mensaje</label>
                                    <textarea 
                                        id="mensaje" 
                                        name="mensaje" 
                                        placeholder="Cuéntanos con detalle tu consulta..."
                                    ></textarea>
                                </div>

                                <button type="submit" class="btn-submit" id="submitBtn">
                                    <i class="fa fa-send"></i> Enviar Mensaje
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="cards-container" style="grid-template-columns: 1fr; margin-top: -20px;">
                        <div class="developer-card">
                            <h4><i class="fa fa-code"></i> Desarrollado por</h4>
                            <div class="company-name">DEVSOFT by HolaPe</div>
                            <p>
                                Especialistas en desarrollo de software a medida y soluciones tecnológicas innovadoras. 
                                Transformamos tus ideas en realidad digital.
                            </p>
                            <a href="https://web.holape.app" target="_blank">
                                <i class="fa fa-external-link"></i> Visitar Sitio Web
                            </a>
                        </div>
                    </div>
                </div>

                <div class="footer-actions">
                    <a href="{{ url()->previous() }}" class="btn-back">
                        <i class="fa fa-arrow-left"></i> Volver Atrás
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('contactForm');
    const messageContainer = document.getElementById('message-container');
    const submitBtn = document.getElementById('submitBtn');

    form.addEventListener('submit', function(e) {
        e.preventDefault();

        // Obtener datos del formulario
        const formData = new FormData(form);
        const data = Object.fromEntries(formData);

        // Validación básica del cliente
        if (!data.nombre || !data.email || !data.telefono || !data.asunto || !data.mensaje) {
            showMessage('Por favor completa todos los campos', 'error');
            return;
        }

        // Validar email
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(data.email)) {
            showMessage('Por favor ingresa un correo electrónico válido', 'error');
            return;
        }

        // Deshabilitar botón mientras se envía
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Enviando...';

        // Enviar el formulario
        fetch('{{ route("soporte.store") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showMessage(data.message, 'success');
                form.reset();
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fa fa-send"></i> Enviar Mensaje';
                
                // Limpiar el mensaje después de 5 segundos
                setTimeout(() => {
                    messageContainer.innerHTML = '';
                }, 5000);
            } else {
                showMessage(data.message || 'Error al enviar el mensaje', 'error');
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fa fa-send"></i> Enviar Mensaje';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showMessage('Error al enviar el mensaje. Por favor intenta nuevamente.', 'error');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fa fa-send"></i> Enviar Mensaje';
        });
    });

    function showMessage(message, type) {
        const alertClass = type === 'success' ? 'alert-success' : 'alert-error';
        const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
        
        messageContainer.innerHTML = `
            <div class="alert ${alertClass}">
                <i class="fa ${icon}"></i> ${message}
            </div>
        `;
    }
});
</script>

@endsection

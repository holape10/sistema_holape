<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: 'Poppins', Arial, sans-serif;
            background-color: #f8f9ff;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h2 {
            margin: 0;
            font-size: 1.8em;
            font-weight: 700;
        }
        .content {
            padding: 30px;
        }
        .content p {
            margin: 15px 0;
            line-height: 1.6;
            color: #333;
        }
        .highlight {
            background: #f0f4ff;
            padding: 20px;
            border-left: 4px solid #667eea;
            margin: 20px 0;
            border-radius: 5px;
        }
        .highlight strong {
            color: #667eea;
        }
        .footer {
            background: #f8f9ff;
            padding: 20px;
            text-align: center;
            color: #999;
            font-size: 0.9em;
            border-top: 1px solid #eee;
        }
        .contact-info {
            text-align: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
        .contact-info p {
            margin: 8px 0;
        }
        .contact-info a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }
        .contact-info a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>✅ ¡Mensaje Recibido!</h2>
            <p style="margin: 10px 0 0 0; opacity: 0.9;">DEVSOFT by HolaPe</p>
        </div>
        
        <div class="content">
            <p>Hola <strong>{{ $nombre }}</strong>,</p>
            
            <p>Gracias por contactarnos. Hemos recibido tu mensaje correctamente y nuestro equipo de soporte lo está revisando.</p>
            
            <div class="highlight">
                <p><strong>📋 Resumen de tu solicitud:</strong></p>
                <p><strong>Asunto:</strong> {{ $asunto }}</p>
                <p><strong>Fecha:</strong> {{ $fecha }}</p>
            </div>
            
            <p>Nos comprometemos a responderte lo antes posible. Generalmente, respondemos a todos los mensajes dentro de 24 horas.</p>
            
            <p>Si tu consulta es urgente, puedes comunicarte directamente con nosotros:</p>
            
            <div class="contact-info">
                <p>📧 <a href="mailto:holapesac@gmail.com">holapesac@gmail.com</a></p>
                <p>📱 <strong>Jacker:</strong> <a href="tel:+51928396147">+51 928 396 147</a></p>
                <p>⏰ Lunes a Viernes · 9:00 AM - 6:00 PM</p>
            </div>
        </div>
        
        <div class="footer">
            <p>Este es un correo de confirmación automático. Por favor no respondas a este correo.</p>
            <p>&copy; 2025 DEVSOFT by HolaPe. Todos los derechos reservados.</p>
        </div>
    </div>
</body>
</html>
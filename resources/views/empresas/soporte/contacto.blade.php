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
            margin: 10px 0;
            line-height: 1.6;
            color: #333;
        }
        .field {
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        .field:last-child {
            border-bottom: none;
        }
        .field-label {
            font-weight: 700;
            color: #667eea;
            margin-bottom: 5px;
            font-size: 0.9em;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .field-value {
            color: #555;
            word-break: break-word;
            font-size: 1em;
        }
        .footer {
            background: #f8f9ff;
            padding: 20px;
            text-align: center;
            color: #999;
            font-size: 0.9em;
            border-top: 1px solid #eee;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>📨 Nuevo Mensaje de Soporte</h2>
            <p style="margin: 10px 0 0 0; opacity: 0.9;">DEVSOFT by HolaPe</p>
        </div>
        
        <div class="content">
            <p style="color: #667eea; font-weight: 700; font-size: 1.1em;">¡Has recibido un nuevo mensaje de contacto!</p>
            
            <div class="field">
                <div class="field-label">👤 Nombre:</div>
                <div class="field-value">{{ $nombre }}</div>
            </div>
            
            <div class="field">
                <div class="field-label">📧 Correo Electrónico:</div>
                <div class="field-value">
                    <a href="mailto:{{ $email }}" style="color: #667eea; text-decoration: none;">{{ $email }}</a>
                </div>
            </div>
            
            <div class="field">
                <div class="field-label">📱 Teléfono:</div>
                <div class="field-value">
                    <a href="tel:{{ $telefono }}" style="color: #667eea; text-decoration: none;">{{ $telefono }}</a>
                </div>
            </div>
            
            <div class="field">
                <div class="field-label">🎯 Asunto:</div>
                <div class="field-value">{{ $asunto }}</div>
            </div>
            
            <div class="field">
                <div class="field-label">💬 Mensaje:</div>
                <div class="field-value" style="white-space: pre-wrap; background: #f5f5f5; padding: 15px; border-radius: 8px; margin-top: 8px;">{{ $mensaje }}</div>
            </div>

            <div class="field">
                <div class="field-label">⏰ Fecha de Envío:</div>
                <div class="field-value">{{ $fecha }}</div>
            </div>
        </div>
        
        <div class="footer">
            <p>Este correo fue enviado desde el formulario de soporte técnico de DEVSOFT by HolaPe</p>
            <p>Responde al cliente directamente utilizando sus datos de contacto</p>
        </div>
    </div>
</body>
</html>
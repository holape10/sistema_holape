<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sistema Bloqueado</title>
    <link rel="shortcut icon" href="img/icono_hp.ico">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
        }
        
        .container {
            text-align: center;
            padding: 50px 40px;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 500px;
            width: 100%;
            animation: slideDown 0.5s ease-out;
        }
        
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .icon {
            font-size: 80px;
            margin-bottom: 20px;
            animation: pulse 2s ease-in-out infinite;
        }
        
        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.1);
            }
        }
        
        h1 {
            color: #d32f2f;
            margin-bottom: 20px;
            font-size: 32px;
            font-weight: 700;
        }
        
        .message {
            color: #555;
            font-size: 18px;
            line-height: 1.6;
            margin-bottom: 15px;
        }
        
        .contact {
            color: #777;
            font-size: 14px;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
        
        .support-info {
            background: #f5f5f5;
            padding: 15px;
            border-radius: 10px;
            margin-top: 20px;
            font-size: 14px;
            color: #666;
        }
        
        .support-info strong {
            color: #333;
        }
        
        /* Botón de verificación */
        .verify-btn {
            margin-top: 30px;
            padding: 15px 40px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 50px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }
        
        .verify-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.6);
        }
        
        .verify-btn:active {
            transform: translateY(0);
        }
        
        .verify-btn:disabled {
            background: #ccc;
            cursor: not-allowed;
            box-shadow: none;
        }
        
        /* Spinner de carga */
        .spinner {
            display: none;
            width: 20px;
            height: 20px;
            border: 3px solid #ffffff;
            border-top: 3px solid transparent;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .loading .spinner {
            display: inline-block;
        }
        
        /* Mensajes de resultado */
        .result-message {
            margin-top: 20px;
            padding: 15px;
            border-radius: 10px;
            font-size: 14px;
            display: none;
        }
        
        .result-message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            display: block;
        }
        
        .result-message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            display: block;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon">🔒</div>
        <h1>Sistema Bloqueado por falta de pago</h1>
        <p class="message">{{ $message }}</p>
        <p class="contact">{{ $contact }}</p>
        
        <div class="support-info">
            <strong>¿Ya realizó su pago?</strong><br>
            Haga clic en el botón "Verificar Estado" para<br>
            comprobar si su sistema puede ser reactivado.
        </div>
        
        <button id="verifyBtn" class="verify-btn" onclick="verificarEstado()">
            <span id="btnText">🔄 Verificar Estado</span>
            <div class="spinner"></div>
        </button>
        
        <div id="resultMessage" class="result-message"></div>
    </div>
    
    <script>
        function verificarEstado() {
            const btn = document.getElementById('verifyBtn');
            const btnText = document.getElementById('btnText');
            const resultMessage = document.getElementById('resultMessage');
            
            // Deshabilitar botón y mostrar carga
            btn.disabled = true;
            btn.classList.add('loading');
            btnText.style.display = 'none';
            resultMessage.style.display = 'none';
            resultMessage.className = 'result-message';
            
            // Obtener token CSRF
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            // Hacer petición AJAX
            fetch('/verificar-sistema', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token
                }
            })
            .then(response => response.json())
            .then(data => {
                btn.disabled = false;
                btn.classList.remove('loading');
                btnText.style.display = 'inline';
                
                if (data.success && !data.blocked) {
                    // Sistema desbloqueado - recargar página
                    resultMessage.textContent = '✅ ' + data.message + ' Recargando...';
                    resultMessage.className = 'result-message success';
                    
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                } else if (data.success && data.blocked) {
                    // Sistema sigue bloqueado
                    resultMessage.textContent = '⚠️ ' + data.message;
                    resultMessage.className = 'result-message error';
                } else {
                    // Error en la verificación
                    resultMessage.textContent = '❌ ' + data.message;
                    resultMessage.className = 'result-message error';
                }
            })
            .catch(error => {
                btn.disabled = false;
                btn.classList.remove('loading');
                btnText.style.display = 'inline';
                
                resultMessage.textContent = '❌ Error de conexión. Verifique su internet.';
                resultMessage.className = 'result-message error';
            });
        }
    </script>
</body>
</html>
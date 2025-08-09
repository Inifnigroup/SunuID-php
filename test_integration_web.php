<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test d'Intégration SunuID Socket.IO</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .header h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
        }

        .header p {
            font-size: 1.1em;
            opacity: 0.9;
        }

        .content {
            padding: 30px;
        }

        .test-section {
            margin-bottom: 30px;
            border: 1px solid #e0e0e0;
            border-radius: 15px;
            overflow: hidden;
        }

        .test-header {
            background: #f8f9fa;
            padding: 20px;
            border-bottom: 1px solid #e0e0e0;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .test-header:hover {
            background: #e9ecef;
        }

        .test-header h3 {
            margin: 0;
            color: #333;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .test-status {
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.9em;
            font-weight: bold;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        .status-success {
            background: #d4edda;
            color: #155724;
        }

        .status-error {
            background: #f8d7da;
            color: #721c24;
        }

        .test-content {
            padding: 20px;
            display: none;
        }

        .test-content.active {
            display: block;
        }

        .qr-container {
            text-align: center;
            margin: 20px 0;
        }

        .qr-code {
            max-width: 300px;
            margin: 20px auto;
            border: 2px solid #ddd;
            border-radius: 10px;
            padding: 10px;
        }

        .progress-container {
            margin: 20px 0;
        }

        .progress-bar {
            width: 100%;
            height: 20px;
            background-color: #f0f0f0;
            border-radius: 10px;
            overflow: hidden;
            margin: 10px 0;
        }

        .progress {
            height: 100%;
            background: linear-gradient(90deg, #667eea, #764ba2);
            transition: width 0.3s ease;
            border-radius: 10px;
        }

        .log-container {
            background: #f8f9fa;
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            padding: 15px;
            margin: 20px 0;
            max-height: 300px;
            overflow-y: auto;
            font-family: 'Courier New', monospace;
            font-size: 0.9em;
        }

        .log-entry {
            margin: 5px 0;
            padding: 5px;
            border-radius: 5px;
        }

        .log-success {
            background: #d4edda;
            color: #155724;
        }

        .log-error {
            background: #f8d7da;
            color: #721c24;
        }

        .log-info {
            background: #d1ecf1;
            color: #0c5460;
        }

        .log-warning {
            background: #fff3cd;
            color: #856404;
        }

        .button {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 25px;
            cursor: pointer;
            font-size: 1em;
            font-weight: bold;
            transition: transform 0.2s, box-shadow 0.2s;
            margin: 5px;
        }

        .button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .button:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }

        .metric-card {
            background: #f8f9fa;
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
        }

        .metric-value {
            font-size: 2em;
            font-weight: bold;
            color: #667eea;
        }

        .metric-label {
            color: #666;
            margin-top: 5px;
        }

        .status-indicator {
            display: inline-block;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin-right: 10px;
        }

        .status-connected {
            background: #28a745;
        }

        .status-disconnected {
            background: #dc3545;
        }

        .status-connecting {
            background: #ffc107;
            animation: pulse 1s infinite;
        }

        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }

        .footer {
            background: #f8f9fa;
            padding: 20px;
            text-align: center;
            border-top: 1px solid #e0e0e0;
            color: #666;
        }

        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid #f3f3f3;
            border-top: 3px solid #667eea;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🧪 Test d'Intégration SunuID Socket.IO</h1>
            <p>Interface de test interactive pour valider toutes les fonctionnalités</p>
        </div>

        <div class="content">
            <!-- Configuration -->
            <div class="test-section">
                <div class="test-header" onclick="toggleSection('config')">
                    <h3>
                        ⚙️ Configuration
                        <span class="test-status status-pending" id="config-status">En attente</span>
                    </h3>
                </div>
                <div class="test-content" id="config-content">
                    <div class="metrics-grid">
                        <div class="metric-card">
                            <div class="metric-value" id="client-id">-</div>
                            <div class="metric-label">Client ID</div>
                        </div>
                        <div class="metric-card">
                            <div class="metric-value" id="partner-name">-</div>
                            <div class="metric-label">Partner Name</div>
                        </div>
                        <div class="metric-card">
                            <div class="metric-value" id="websocket-url">-</div>
                            <div class="metric-label">WebSocket URL</div>
                        </div>
                        <div class="metric-card">
                            <div class="metric-value" id="socketio-version">-</div>
                            <div class="metric-label">Socket.IO Version</div>
                        </div>
                    </div>
                    <button class="button" onclick="loadConfiguration()">Charger la configuration</button>
                </div>
            </div>

            <!-- Connexion Socket.IO -->
            <div class="test-section">
                <div class="test-header" onclick="toggleSection('connection')">
                    <h3>
                        <span class="status-indicator status-disconnected" id="connection-indicator"></span>
                        🔗 Connexion Socket.IO
                        <span class="test-status status-pending" id="connection-status">En attente</span>
                    </h3>
                </div>
                <div class="test-content" id="connection-content">
                    <div class="log-container" id="connection-log"></div>
                    <button class="button" onclick="initWebSocket()">Initialiser Socket.IO</button>
                    <button class="button" onclick="connectWebSocket()">Se connecter</button>
                    <button class="button" onclick="disconnectWebSocket()">Se déconnecter</button>
                    <button class="button" onclick="checkConnectionStatus()">Vérifier le statut</button>
                </div>
            </div>

            <!-- Génération QR Code -->
            <div class="test-section">
                <div class="test-header" onclick="toggleSection('qr')">
                    <h3>
                        📱 Génération QR Code
                        <span class="test-status status-pending" id="qr-status">En attente</span>
                    </h3>
                </div>
                <div class="test-content" id="qr-content">
                    <div class="qr-container" id="qr-container">
                        <p>Cliquez sur "Générer QR Code" pour commencer</p>
                    </div>
                    <div class="log-container" id="qr-log"></div>
                    <button class="button" onclick="generateQRCode()">Générer QR Code</button>
                    <button class="button" onclick="generateQRWithWebSocket()">Générer QR avec Socket.IO</button>
                </div>
            </div>

            <!-- Événements Socket.IO -->
            <div class="test-section">
                <div class="test-header" onclick="toggleSection('events')">
                    <h3>
                        🎭 Événements Socket.IO
                        <span class="test-status status-pending" id="events-status">En attente</span>
                    </h3>
                </div>
                <div class="test-content" id="events-content">
                    <div class="log-container" id="events-log"></div>
                    <button class="button" onclick="setupEventCallbacks()">Configurer les callbacks</button>
                    <button class="button" onclick="simulateEvents()">Simuler des événements</button>
                    <button class="button" onclick="sendTestMessage()">Envoyer un message test</button>
                </div>
            </div>

            <!-- Gestion des sessions -->
            <div class="test-section">
                <div class="test-header" onclick="toggleSection('sessions')">
                    <h3>
                        📋 Gestion des sessions
                        <span class="test-status status-pending" id="sessions-status">En attente</span>
                    </h3>
                </div>
                <div class="test-content" id="sessions-content">
                    <div class="log-container" id="sessions-log"></div>
                    <button class="button" onclick="subscribeToSession()">S'abonner à une session</button>
                    <button class="button" onclick="unsubscribeFromSession()">Se désabonner</button>
                    <button class="button" onclick="getActiveSessions()">Sessions actives</button>
                </div>
            </div>

            <!-- Métriques de performance -->
            <div class="test-section">
                <div class="test-header" onclick="toggleSection('metrics')">
                    <h3>
                        📊 Métriques de performance
                        <span class="test-status status-pending" id="metrics-status">En attente</span>
                    </h3>
                </div>
                <div class="test-content" id="metrics-content">
                    <div class="metrics-grid" id="metrics-grid">
                        <div class="metric-card">
                            <div class="metric-value" id="messages-sent">0</div>
                            <div class="metric-label">Messages envoyés</div>
                        </div>
                        <div class="metric-card">
                            <div class="metric-value" id="messages-received">0</div>
                            <div class="metric-label">Messages reçus</div>
                        </div>
                        <div class="metric-card">
                            <div class="metric-value" id="errors-count">0</div>
                            <div class="metric-label">Erreurs</div>
                        </div>
                        <div class="metric-card">
                            <div class="metric-value" id="reconnections">0</div>
                            <div class="metric-label">Reconnexions</div>
                        </div>
                    </div>
                    <button class="button" onclick="getMetrics()">Actualiser les métriques</button>
                </div>
            </div>

            <!-- Test complet -->
            <div class="test-section">
                <div class="test-header" onclick="toggleSection('complete')">
                    <h3>
                        🚀 Test complet
                        <span class="test-status status-pending" id="complete-status">En attente</span>
                    </h3>
                </div>
                <div class="test-content" id="complete-content">
                    <div class="log-container" id="complete-log"></div>
                    <button class="button" onclick="runCompleteTest()">Lancer le test complet</button>
                    <button class="button" onclick="exportResults()">Exporter les résultats</button>
                </div>
            </div>
        </div>

        <div class="footer">
            <p>🧪 Test d'Intégration SunuID Socket.IO - <?php echo date('Y-m-d H:i:s'); ?></p>
        </div>
    </div>

    <script>
        let sunuidSDK = null;
        let testResults = {};
        let currentSessionId = null;

        // Fonction pour basculer l'affichage des sections
        function toggleSection(sectionId) {
            const content = document.getElementById(sectionId + '-content');
            content.classList.toggle('active');
        }

        // Fonction pour ajouter un log
        function addLog(containerId, message, type = 'info') {
            const container = document.getElementById(containerId);
            const logEntry = document.createElement('div');
            logEntry.className = `log-entry log-${type}`;
            logEntry.innerHTML = `[${new Date().toLocaleTimeString()}] ${message}`;
            container.appendChild(logEntry);
            container.scrollTop = container.scrollHeight;
        }

        // Fonction pour mettre à jour le statut
        function updateStatus(sectionId, status, message = '') {
            const statusElement = document.getElementById(sectionId + '-status');
            statusElement.textContent = message || status;
            statusElement.className = `test-status status-${status}`;
        }

        // Fonction pour charger la configuration
        async function loadConfiguration() {
            try {
                updateStatus('config', 'pending', 'Chargement...');
                
                const response = await fetch('test_integration_api.php?action=load_config');
                const data = await response.json();
                
                if (data.success) {
                    document.getElementById('client-id').textContent = data.config.client_id;
                    document.getElementById('partner-name').textContent = data.config.partner_name;
                    document.getElementById('websocket-url').textContent = data.config.websocket_url;
                    document.getElementById('socketio-version').textContent = data.config.websocket_socketio_version;
                    
                    updateStatus('config', 'success', 'Configuration chargée');
                    addLog('config-log', 'Configuration chargée avec succès', 'success');
                } else {
                    updateStatus('config', 'error', 'Erreur de chargement');
                    addLog('config-log', 'Erreur: ' + data.error, 'error');
                }
            } catch (error) {
                updateStatus('config', 'error', 'Erreur réseau');
                addLog('config-log', 'Erreur réseau: ' + error.message, 'error');
            }
        }

        // Fonction pour initialiser Socket.IO
        async function initWebSocket() {
            try {
                updateStatus('connection', 'pending', 'Initialisation...');
                addLog('connection-log', 'Initialisation de Socket.IO...', 'info');
                
                const response = await fetch('test_integration_api.php?action=init_websocket');
                const data = await response.json();
                
                if (data.success) {
                    updateStatus('connection', 'success', 'Initialisé');
                    addLog('connection-log', 'Socket.IO initialisé avec succès', 'success');
                } else {
                    updateStatus('connection', 'error', 'Échec d\'initialisation');
                    addLog('connection-log', 'Erreur: ' + data.error, 'error');
                }
            } catch (error) {
                updateStatus('connection', 'error', 'Erreur réseau');
                addLog('connection-log', 'Erreur réseau: ' + error.message, 'error');
            }
        }

        // Fonction pour se connecter
        async function connectWebSocket() {
            try {
                updateStatus('connection', 'pending', 'Connexion...');
                document.getElementById('connection-indicator').className = 'status-indicator status-connecting';
                addLog('connection-log', 'Tentative de connexion...', 'info');
                
                const response = await fetch('test_integration_api.php?action=connect');
                const data = await response.json();
                
                if (data.success) {
                    updateStatus('connection', 'success', 'Connecté');
                    document.getElementById('connection-indicator').className = 'status-indicator status-connected';
                    addLog('connection-log', 'Connexion réussie', 'success');
                } else {
                    updateStatus('connection', 'error', 'Échec de connexion');
                    document.getElementById('connection-indicator').className = 'status-indicator status-disconnected';
                    addLog('connection-log', 'Erreur: ' + data.error, 'error');
                }
            } catch (error) {
                updateStatus('connection', 'error', 'Erreur réseau');
                document.getElementById('connection-indicator').className = 'status-indicator status-disconnected';
                addLog('connection-log', 'Erreur réseau: ' + error.message, 'error');
            }
        }

        // Fonction pour se déconnecter
        async function disconnectWebSocket() {
            try {
                addLog('connection-log', 'Déconnexion...', 'info');
                
                const response = await fetch('test_integration_api.php?action=disconnect');
                const data = await response.json();
                
                if (data.success) {
                    updateStatus('connection', 'pending', 'Déconnecté');
                    document.getElementById('connection-indicator').className = 'status-indicator status-disconnected';
                    addLog('connection-log', 'Déconnexion réussie', 'success');
                } else {
                    addLog('connection-log', 'Erreur: ' + data.error, 'error');
                }
            } catch (error) {
                addLog('connection-log', 'Erreur réseau: ' + error.message, 'error');
            }
        }

        // Fonction pour vérifier le statut de connexion
        async function checkConnectionStatus() {
            try {
                const response = await fetch('test_integration_api.php?action=status');
                const data = await response.json();
                
                if (data.success) {
                    const isConnected = data.connected;
                    const indicator = document.getElementById('connection-indicator');
                    
                    if (isConnected) {
                        indicator.className = 'status-indicator status-connected';
                        addLog('connection-log', 'Statut: Connecté', 'success');
                    } else {
                        indicator.className = 'status-indicator status-disconnected';
                        addLog('connection-log', 'Statut: Déconnecté', 'warning');
                    }
                } else {
                    addLog('connection-log', 'Erreur: ' + data.error, 'error');
                }
            } catch (error) {
                addLog('connection-log', 'Erreur réseau: ' + error.message, 'error');
            }
        }

        // Fonction pour générer un QR code
        async function generateQRCode() {
            try {
                updateStatus('qr', 'pending', 'Génération...');
                addLog('qr-log', 'Génération du QR code...', 'info');
                
                const response = await fetch('test_integration_api.php?action=generate_qr');
                const data = await response.json();
                
                if (data.success) {
                    updateStatus('qr', 'success', 'QR généré');
                    addLog('qr-log', 'QR code généré avec succès', 'success');
                    
                    const container = document.getElementById('qr-container');
                    container.innerHTML = `
                        <h3>QR Code généré</h3>
                        <img src="data:image/png;base64,${data.qr_code}" alt="QR Code" class="qr-code">
                        <p><strong>Session ID:</strong> ${data.session_id}</p>
                        <p><strong>URL:</strong> ${data.url}</p>
                    `;
                } else {
                    updateStatus('qr', 'error', 'Erreur de génération');
                    addLog('qr-log', 'Erreur: ' + data.error, 'error');
                }
            } catch (error) {
                updateStatus('qr', 'error', 'Erreur réseau');
                addLog('qr-log', 'Erreur réseau: ' + error.message, 'error');
            }
        }

        // Fonction pour générer un QR code avec Socket.IO
        async function generateQRWithWebSocket() {
            try {
                updateStatus('qr', 'pending', 'Génération avec Socket.IO...');
                addLog('qr-log', 'Génération du QR code avec Socket.IO...', 'info');
                
                const response = await fetch('test_integration_api.php?action=generate_qr_websocket');
                const data = await response.json();
                
                if (data.success) {
                    updateStatus('qr', 'success', 'QR avec Socket.IO généré');
                    addLog('qr-log', 'QR code avec Socket.IO généré avec succès', 'success');
                    
                    const container = document.getElementById('qr-container');
                    container.innerHTML = `
                        <h3>QR Code avec Socket.IO</h3>
                        <img src="data:image/png;base64,${data.qr_code}" alt="QR Code" class="qr-code">
                        <p><strong>Session ID:</strong> ${data.session_id}</p>
                        <p><strong>URL:</strong> ${data.url}</p>
                        <p><strong>Socket.IO:</strong> Activé</p>
                    `;
                    
                    currentSessionId = data.session_id;
                } else {
                    updateStatus('qr', 'error', 'Erreur de génération');
                    addLog('qr-log', 'Erreur: ' + data.error, 'error');
                }
            } catch (error) {
                updateStatus('qr', 'error', 'Erreur réseau');
                addLog('qr-log', 'Erreur réseau: ' + error.message, 'error');
            }
        }

        // Fonction pour configurer les callbacks d'événements
        async function setupEventCallbacks() {
            try {
                updateStatus('events', 'pending', 'Configuration...');
                addLog('events-log', 'Configuration des callbacks d\'événements...', 'info');
                
                const response = await fetch('test_integration_api.php?action=setup_callbacks');
                const data = await response.json();
                
                if (data.success) {
                    updateStatus('events', 'success', 'Callbacks configurés');
                    addLog('events-log', `${data.callback_count} callbacks configurés`, 'success');
                } else {
                    updateStatus('events', 'error', 'Erreur de configuration');
                    addLog('events-log', 'Erreur: ' + data.error, 'error');
                }
            } catch (error) {
                updateStatus('events', 'error', 'Erreur réseau');
                addLog('events-log', 'Erreur réseau: ' + error.message, 'error');
            }
        }

        // Fonction pour simuler des événements
        async function simulateEvents() {
            try {
                addLog('events-log', 'Simulation d\'événements...', 'info');
                
                const events = ['auth_success', 'kyc_complete', 'auth_failure'];
                
                for (const event of events) {
                    const response = await fetch('test_integration_api.php?action=simulate_event', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ event: event })
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        addLog('events-log', `Événement simulé: ${event}`, 'success');
                    } else {
                        addLog('events-log', `Erreur simulation ${event}: ${data.error}`, 'error');
                    }
                    
                    // Pause entre les événements
                    await new Promise(resolve => setTimeout(resolve, 1000));
                }
                
                addLog('events-log', 'Simulation terminée', 'success');
            } catch (error) {
                addLog('events-log', 'Erreur réseau: ' + error.message, 'error');
            }
        }

        // Fonction pour envoyer un message test
        async function sendTestMessage() {
            try {
                addLog('events-log', 'Envoi d\'un message test...', 'info');
                
                const response = await fetch('test_integration_api.php?action=send_message', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        event: 'test_message',
                        data: {
                            message: 'Hello Socket.IO!',
                            timestamp: Date.now(),
                            test_id: 'web_test_' + Math.random().toString(36).substr(2, 9)
                        }
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    addLog('events-log', 'Message test envoyé avec succès', 'success');
                } else {
                    addLog('events-log', 'Erreur: ' + data.error, 'error');
                }
            } catch (error) {
                addLog('events-log', 'Erreur réseau: ' + error.message, 'error');
            }
        }

        // Fonction pour s'abonner à une session
        async function subscribeToSession() {
            try {
                const sessionId = currentSessionId || 'test_session_' + Math.random().toString(36).substr(2, 9);
                addLog('sessions-log', `Abonnement à la session: ${sessionId}`, 'info');
                
                const response = await fetch('test_integration_api.php?action=subscribe_session', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ session_id: sessionId })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    addLog('sessions-log', 'Abonnement réussi', 'success');
                } else {
                    addLog('sessions-log', 'Erreur: ' + data.error, 'error');
                }
            } catch (error) {
                addLog('sessions-log', 'Erreur réseau: ' + error.message, 'error');
            }
        }

        // Fonction pour se désabonner d'une session
        async function unsubscribeFromSession() {
            try {
                const sessionId = currentSessionId || 'test_session_' + Math.random().toString(36).substr(2, 9);
                addLog('sessions-log', `Désabonnement de la session: ${sessionId}`, 'info');
                
                const response = await fetch('test_integration_api.php?action=unsubscribe_session', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ session_id: sessionId })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    addLog('sessions-log', 'Désabonnement réussi', 'success');
                } else {
                    addLog('sessions-log', 'Erreur: ' + data.error, 'error');
                }
            } catch (error) {
                addLog('sessions-log', 'Erreur réseau: ' + error.message, 'error');
            }
        }

        // Fonction pour obtenir les sessions actives
        async function getActiveSessions() {
            try {
                addLog('sessions-log', 'Récupération des sessions actives...', 'info');
                
                const response = await fetch('test_integration_api.php?action=get_sessions');
                const data = await response.json();
                
                if (data.success) {
                    addLog('sessions-log', `${data.sessions.length} sessions actives trouvées`, 'success');
                    
                    if (data.sessions.length > 0) {
                        data.sessions.forEach(session => {
                            addLog('sessions-log', `Session: ${session.id} - Statut: ${session.status}`, 'info');
                        });
                    }
                } else {
                    addLog('sessions-log', 'Erreur: ' + data.error, 'error');
                }
            } catch (error) {
                addLog('sessions-log', 'Erreur réseau: ' + error.message, 'error');
            }
        }

        // Fonction pour obtenir les métriques
        async function getMetrics() {
            try {
                updateStatus('metrics', 'pending', 'Chargement...');
                
                const response = await fetch('test_integration_api.php?action=get_metrics');
                const data = await response.json();
                
                if (data.success) {
                    document.getElementById('messages-sent').textContent = data.metrics.messages_sent || 0;
                    document.getElementById('messages-received').textContent = data.metrics.messages_received || 0;
                    document.getElementById('errors-count').textContent = data.metrics.errors || 0;
                    document.getElementById('reconnections').textContent = data.metrics.reconnections || 0;
                    
                    updateStatus('metrics', 'success', 'Métriques actualisées');
                } else {
                    updateStatus('metrics', 'error', 'Erreur de chargement');
                }
            } catch (error) {
                updateStatus('metrics', 'error', 'Erreur réseau');
            }
        }

        // Fonction pour lancer le test complet
        async function runCompleteTest() {
            try {
                updateStatus('complete', 'pending', 'Test en cours...');
                addLog('complete-log', 'Démarrage du test complet...', 'info');
                
                const response = await fetch('test_integration_api.php?action=run_complete_test');
                const data = await response.json();
                
                if (data.success) {
                    updateStatus('complete', 'success', 'Test terminé');
                    addLog('complete-log', 'Test complet terminé avec succès', 'success');
                    addLog('complete-log', `Temps d'exécution: ${data.execution_time}s`, 'info');
                    addLog('complete-log', `Taux de succès: ${data.success_rate}%`, 'info');
                    
                    // Afficher les résultats détaillés
                    Object.entries(data.results).forEach(([test, result]) => {
                        const status = result === true || (typeof result === 'number' && result > 0) ? 'success' : 'error';
                        addLog('complete-log', `${test}: ${result}`, status);
                    });
                } else {
                    updateStatus('complete', 'error', 'Erreur de test');
                    addLog('complete-log', 'Erreur: ' + data.error, 'error');
                }
            } catch (error) {
                updateStatus('complete', 'error', 'Erreur réseau');
                addLog('complete-log', 'Erreur réseau: ' + error.message, 'error');
            }
        }

        // Fonction pour exporter les résultats
        async function exportResults() {
            try {
                addLog('complete-log', 'Export des résultats...', 'info');
                
                const response = await fetch('test_integration_api.php?action=export_results');
                const data = await response.json();
                
                if (data.success) {
                    addLog('complete-log', `Résultats exportés: ${data.filename}`, 'success');
                    
                    // Télécharger le fichier
                    const link = document.createElement('a');
                    link.href = data.filename;
                    link.download = data.filename;
                    link.click();
                } else {
                    addLog('complete-log', 'Erreur: ' + data.error, 'error');
                }
            } catch (error) {
                addLog('complete-log', 'Erreur réseau: ' + error.message, 'error');
            }
        }

        // Initialisation au chargement de la page
        document.addEventListener('DOMContentLoaded', function() {
            addLog('connection-log', 'Interface de test chargée', 'info');
            addLog('events-log', 'Prêt pour les tests d\'événements', 'info');
            addLog('sessions-log', 'Prêt pour les tests de sessions', 'info');
            addLog('complete-log', 'Prêt pour le test complet', 'info');
        });
    </script>
</body>
</html>


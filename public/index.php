<?php
session_start();

// Check if user is logged in, if not, redirect to login page
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id']; // ID текущего пользователя для WebSocket
$username = isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'User'; // Sanitize username for display
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Мессенджер</title>
    <!-- <link rel="stylesheet" href="css/style.css"> -->
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            margin: 0;
            display: flex;
            flex-direction: column;
            height: 100vh;
            background-color: #f4f7f6;
            color: #333;
        }
        header {
            background-color: #007bff; /* Primary color */
            color: white;
            padding: 10px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #0056b3; /* Darker shade for border */
        }
        header h1 { margin: 0; font-size: 1.5em; }
        header .user-info { font-size: 0.9em; }
        header a.logout-btn { color: white; text-decoration: none; padding: 8px 15px; background-color: #0056b3; border-radius: 5px; }
        header a.logout-btn:hover { background-color: #004085; }

        .container {
            display: flex;
            flex-grow: 1;
            overflow: hidden; /* Important for controlling scrolling within panes */
        }
        .sidebar {
            width: 300px; /* Slightly wider for better username display */
            border-right: 1px solid #ccc;
            padding: 15px;
            background-color: #ffffff;
            overflow-y: auto; /* Allow scrolling for contact list */
            box-shadow: 2px 0 5px rgba(0,0,0,0.05);
        }
        .sidebar h3 { margin-top: 0; color: #007bff; border-bottom: 1px solid #eee; padding-bottom: 10px;}
        .user-list { padding-left: 0; margin-top: 0;}
        .user-list li {
            list-style: none;
            padding: 10px 5px;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        /* .user-list li:hover { background-color: #f9f9f9; } */ /* Can be distracting */
        .user-list li:last-child { border-bottom: none; }
        .user-list .call-btn {
            padding: 5px 10px;
            font-size: 0.85em;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        .user-list .call-btn:hover { background-color: #218838; }
        .user-list .call-btn.disabled, .user-list .call-btn:disabled { background-color: #aaa; cursor: not-allowed; opacity: 0.7;}


        .chat-area {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            background-color: #e9ecef; /* Light gray for chat background */
        }
        .chat-window {
            flex-grow: 1;
            padding: 20px;
            overflow-y: auto; /* Allow scrolling for messages */
            background-color: #ffffff; /* White background for messages */
            min-height: 200px; /* Ensure it has some height */
        }
        .message-input-area {
            padding: 15px;
            background-color: #f8f9fa; /* Slightly different background for input area */
            border-top: 1px solid #ccc;
            display: flex;
            align-items: center;
        }
        .message-input-area textarea {
            flex-grow: 1;
            padding: 10px;
            border: 1px solid #ced4da;
            border-radius: 5px;
            resize: none; /* Disable textarea resizing by user */
            min-height: 40px;
            margin-right: 10px;
        }
        .message-input-area button {
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1em;
        }
        .message-input-area button:hover { background-color: #0056b3; }

        .placeholder-text { color: #6c757d; text-align: center; margin-top: 20px; font-style: italic; }
        .future-features-note { font-size: 0.85em; color: #555; text-align: center; margin-top: 10px; padding: 5px; background-color: #e9ecef; }

        /* Call UI Elements Styling */
        .call-ui { display: none; /* Hidden by default, shown by JavaScript */ }

        #incoming-call-modal {
            position: fixed;
            top: 30px; /* Adjusted for better visibility */
            left: 50%;
            transform: translateX(-50%);
            background-color: #ffffff;
            padding: 25px;
            border: 1px solid #adb5bd; /* Softer border */
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            z-index: 1050; /* Ensure it's above other content */
            border-radius: 8px;
            text-align: center;
            width: 300px;
        }
        #incoming-call-modal p { margin-top: 0; margin-bottom: 15px; font-size: 1.1em; }
        #incoming-call-modal .btn-group button {
            margin: 0 8px;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            border: none;
        }
        #btn-accept-call { background-color: #28a745; color: white; }
        #btn-accept-call:hover { background-color: #218838; }
        #btn-decline-call { background-color: #dc3545; color: white; }
        #btn-decline-call:hover { background-color: #c82333; }

        #active-call-view {
            padding: 15px;
            background-color: #fff3cd; /* Light yellow, Bootstrap warning background */
            border-bottom: 1px solid #ffeeba;
            text-align: center;
            color: #856404; /* Bootstrap warning text color */
        }
        #active-call-view p { margin-top: 0; margin-bottom: 10px; font-size: 1.1em;}
        #active-call-view button {
            margin: 5px;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            border: 1px solid transparent;
        }
        #btn-mute-call { background-color: #ffc107; border-color: #ffc107; color: #212529;}
        #btn-mute-call:hover { background-color: #e0a800; }
        #btn-end-call { background-color: #dc3545; color: white; border-color: #dc3545; }
        #btn-end-call:hover { background-color: #c82333; }

        /* Hidden audio elements for WebRTC call streams */
        #localAudio, #remoteAudio { display: none; }
    </style>
</head>
<body>
    <header>
        <div>
            <h1>Мессенджер</h1>
            <p class="user-info">Добро пожаловать, <span id="headerCurrentUsername"><?php echo $username; ?></span>!
                (Ваш ID: <span id="headerCurrentUserId"><?php echo $userId; ?></span>)
            </p>
        </div>
        <a href="../src/auth/logout_handler.php" class="logout-btn">Выйти</a>
    </header>

    <!-- Audio elements for local and remote streams in a WebRTC call -->
    <audio id="localAudio" autoplay muted></audio> <!-- Local audio is muted to prevent echo -->
    <audio id="remoteAudio" autoplay></audio>

    <!-- Modal dialog for incoming call notifications -->
    <div id="incoming-call-modal" class="call-ui">
        <p>Входящий звонок от <strong id="caller-username-modal"></strong>...</p>
        <div class="btn-group">
            <button id="btn-accept-call">Принять</button>
            <button id="btn-decline-call">Отклонить</button>
        </div>
    </div>

    <!-- View for an active call, showing callee and call control buttons -->
    <div id="active-call-view" class="call-ui">
        <p>Разговор с <strong id="active-callee-username-view"></strong></p>
        <button id="btn-mute-call" data-muted="false">Выкл. микрофон</button>
        <button id="btn-end-call">Завершить звонок</button>
    </div>

    <div class="container">
        <aside class="sidebar">
            <h3>Пользователи онлайн</h3>
            <ul class="user-list" id="user-list-container">
                <!-- User list will be populated by JavaScript -->
            </ul>
            <div class="placeholder-text" id="user-list-placeholder">(Загрузка списка пользователей...)</div>
        </aside>

        <main class="chat-area">
            <section class="chat-window" id="chat-window-main">
                <div class="placeholder-text">(Выберите пользователя для начала чата или звонка)</div>
                <!-- Chat messages will appear here -->
            </section>

            <section class="message-input-area">
                <textarea id="message-text-input" placeholder="Напишите сообщение..." rows="2"></textarea>
                <button id="send-text-message-btn">Отправить</button>
            </section>
            <div class="future-features-note">
                 <p><em>Функции голосовых сообщений будут добавлены позже.</em></p>
            </div>
        </main>
    </div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Эти переменные берутся из PHP и вставляются прямо в JS.
    // json_encode используется для корректной обработки строк (например, имен с кавычками).
    const currentUserId = <?php echo json_encode($userId); ?>;
    const currentUsername = <?php echo json_encode($username); ?>;

    // Обновляем информацию в header на случай, если PHP ее не вставил (хотя должен)
    document.getElementById('headerCurrentUserId').textContent = currentUserId;
    document.getElementById('headerCurrentUsername').textContent = currentUsername;

    const localAudio = document.getElementById('localAudio');
    const remoteAudio = document.getElementById('remoteAudio');

    const incomingCallModal = document.getElementById('incoming-call-modal');
    const callerUsernameModal = document.getElementById('caller-username-modal');
    const btnAcceptCall = document.getElementById('btn-accept-call');
    const btnDeclineCall = document.getElementById('btn-decline-call');

    const activeCallView = document.getElementById('active-call-view');
    const activeCalleeUsernameView = document.getElementById('active-callee-username-view');
    const btnMuteCall = document.getElementById('btn-mute-call');
    const btnEndCall = document.getElementById('btn-end-call');

    const userListContainer = document.getElementById('user-list-container');
    const userListPlaceholder = document.getElementById('user-list-placeholder');

    let ws;
    let peerConnection;
    let localStream;
    // let remoteStream; // remoteAudio.srcObject handles this directly with ontrack event

    let targetUserIdForCall = null;
    let targetUsernameForCall = null;
    let makingOffer = false;
    // let isPolite = true; // For "perfect negotiation" - advanced topic

    const iceServers = [{ urls: 'stun:stun.l.google.com:19302' }];

    function log(message, ...optionalParams) {
        console.log(`[UserID: ${currentUserId}] ${message}`, ...optionalParams);
    }
    function logError(message, ...optionalParams) {
        console.error(`[UserID: ${currentUserId}] ${message}`, ...optionalParams);
    }


    function connectWebSocket() {
        const wsProtocol = window.location.protocol === 'https:' ? 'wss://' : 'ws://';
        const wsPort = window.location.protocol === 'https:' ? '' : ':8080'; // No port for standard WSS (443)
        // Assuming WebSocket server is on the same host, adjust if different.
        // For local dev, explicitly ws://localhost:8080 might be needed if PHP serves on a different port.
        const wsHost = window.location.hostname;

        // Pass username for better logging on server and for other clients to see
        ws = new WebSocket(`${wsProtocol}${wsHost}${wsPort}?userId=${currentUserId}&username=${encodeURIComponent(currentUsername)}`);

        ws.onopen = () => {
            log('WebSocket подключен.');
            // Запрос списка пользователей может быть не нужен, если сервер шлет его по событию onOpen в SignalingServer
        };

        ws.onmessage = async (message) => {
            let data;
            try {
                data = JSON.parse(message.data);
            } catch (e) {
                logError('Ошибка парсинга JSON от сервера:', message.data);
                return;
            }

            log('Сообщение от WebSocket:', data);

            switch (data.type) {
                case 'update_user_list':
                    updateUserList(data.users);
                    break;
                case 'offer':
                    handleOffer(data);
                    break;
                case 'answer':
                    handleAnswer(data);
                    break;
                case 'candidate':
                    handleCandidate(data);
                    break;
                case 'call_declined':
                    alert(`Пользователь ${data.fromUsername || data.fromUserId} отклонил ваш звонок.`);
                    resetCallState();
                    break;
                case 'call_ended':
                    alert(`Пользователь ${data.fromUsername || data.fromUserId} завершил звонок.`);
                    resetCallState();
                    break;
                case 'error':
                    logError('Ошибка от сервера:', data.message);
                    if (data.message && data.message.includes("User not online") && targetUserIdForCall) {
                        alert(`Пользователь ${targetUsernameForCall || targetUserIdForCall} не в сети.`);
                        resetCallState();
                    }
                    break;
                case 'connection_replaced': // Сообщение от сервера, что это соединение закрыто
                    alert(data.message);
                    ws.close(); // Закрыть это соединение, т.к. новое было открыто
                    break;
                default:
                    log('Неизвестный тип сообщения:', data.type);
            }
        };

        ws.onclose = (event) => {
            log(`WebSocket соединение закрыто. Код: ${event.code}, Причина: ${event.reason}`);
            // Не пытаться переподключиться, если сервер сам закрыл соединение из-за замены
            if (event.code !== 1000 || !event.reason.includes("Connection replaced")) {
                 setTimeout(connectWebSocket, 3000 + Math.random() * 1000); // Случайная задержка для предотвращения "стада"
            }
        };

        ws.onerror = (error) => {
            logError('WebSocket ошибка:', error);
            // onclose будет вызван автоматически после ошибки, что вызовет переподключение
        };
    }

    function updateUserList(users) {
        userListContainer.innerHTML = '';
        let onlineUsersFound = false;
        if (users && typeof users === 'object' && Object.keys(users).length > 0) {
            for (const userId in users) {
                const numericUserId = parseInt(userId);
                if (numericUserId !== currentUserId) {
                    onlineUsersFound = true;
                    const user = users[userId];
                    const username = user.username || `User ${numericUserId}`; // Fallback if username missing

                    const listItem = document.createElement('li');
                    listItem.innerHTML = `<span>${username} (ID: ${numericUserId})</span>`;

                    const callButton = document.createElement('button');
                    callButton.className = 'call-btn';
                    callButton.textContent = 'Позвонить';
                    callButton.dataset.userId = numericUserId;
                    callButton.dataset.username = username;
                    callButton.onclick = () => initiateCall(numericUserId, username);

                    listItem.appendChild(callButton);
                    userListContainer.appendChild(listItem);
                }
            }
        }

        if (onlineUsersFound) {
            userListPlaceholder.style.display = 'none';
        } else {
            userListPlaceholder.textContent = '(Нет других пользователей онлайн)';
            userListPlaceholder.style.display = 'block';
        }
    }

    async function createPeerConnection() {
        if (peerConnection) {
            log("PeerConnection уже существует. Закрываем старый.");
            peerConnection.close();
        }

        peerConnection = new RTCPeerConnection({ iceServers });
        log("RTCPeerConnection создан.");

        peerConnection.onicecandidate = (event) => {
            if (event.candidate) {
                log('Отправка ICE candidate:', event.candidate);
                if (ws && ws.readyState === WebSocket.OPEN) {
                    ws.send(JSON.stringify({
                        type: 'candidate',
                        candidate: event.candidate,
                        targetUserId: targetUserIdForCall
                        // fromUserId is added by server or not strictly needed if server maps conn to user
                    }));
                } else {
                    logError("WebSocket не открыт, не могу отправить ICE candidate.");
                }
            }
        };

        peerConnection.ontrack = (event) => {
            log('Получен удаленный трек:', event.streams);
            if (event.streams && event.streams[0]) {
                remoteAudio.srcObject = event.streams[0];
            } else {
                // Fallback для старых браузеров или если stream не сразу доступен
                let inboundStream = new MediaStream();
                inboundStream.addTrack(event.track);
                remoteAudio.srcObject = inboundStream;
            }
        };

        peerConnection.onnegotiationneeded = async () => {
            log("Сработало onnegotiationneeded");
            // Это часть "perfect negotiation"
            try {
                if (makingOffer || (peerConnection && peerConnection.signalingState !== 'stable')) {
                    log("Пропуск onnegotiationneeded: makingOffer=true или signalingState не stable (" + (peerConnection ? peerConnection.signalingState : 'N/A') + ")");
                    return;
                }

                makingOffer = true;
                const offer = await peerConnection.createOffer();
                await peerConnection.setLocalDescription(offer);

                log('Отправка offer (из onnegotiationneeded):', offer);
                if (ws && ws.readyState === WebSocket.OPEN) {
                     ws.send(JSON.stringify({
                        type: 'offer',
                        offer: peerConnection.localDescription,
                        targetUserId: targetUserIdForCall,
                        // fromUserId: currentUserId, // Добавляется на сервере
                        fromUsername: currentUsername
                    }));
                } else {
                    logError("WebSocket не открыт, не могу отправить offer.");
                }
            } catch (err) {
                logError('Ошибка при onnegotiationneeded:', err);
            } finally {
                makingOffer = false;
            }
        };

        peerConnection.oniceconnectionstatechange = () => {
            if (peerConnection) {
                log('Состояние ICE:', peerConnection.iceConnectionState);
                if (peerConnection.iceConnectionState === 'failed' ||
                    peerConnection.iceConnectionState === 'disconnected' ||
                    peerConnection.iceConnectionState === 'closed') {
                    log(`ICE соединение ${peerConnection.iceConnectionState}. Сброс звонка.`);
                    // resetCallState(); // Может быть слишком агрессивно для 'disconnected'
                }
                 if (peerConnection.iceConnectionState === 'connected') {
                    log("ICE соединение установлено успешно.");
                }
            }
        };

        // Добавляем локальный стрим, если он уже есть (например, при принятии звонка)
        if (localStream) {
            log("Добавление треков из существующего localStream в новый peerConnection");
            localStream.getTracks().forEach(track => peerConnection.addTrack(track, localStream));
        }
    }

    async function getLocalMedia() {
        if (localStream) { // Если стрим уже есть, переиспользуем
            log("Использование существующего localStream");
            return localStream;
        }
        try {
            log("Запрос доступа к медиа устройствам (микрофон)...");
            localStream = await navigator.mediaDevices.getUserMedia({ audio: true, video: false });
            localAudio.srcObject = localStream;
            log("Доступ к микрофону получен.");
            return localStream;
        } catch (error) {
            logError('Ошибка при navigator.mediaDevices.getUserMedia:', error);
            alert('Не удалось получить доступ к микрофону. Проверьте разрешения в браузере.');
            throw error; // Передаем ошибку дальше для обработки
        }
    }


    async function initiateCall(toUserId, toUsername) {
        if (peerConnection && peerConnection.signalingState !== 'closed') {
            alert('Вы уже в процессе звонка. Завершите текущий перед началом нового.');
            return;
        }
        log(`Инициация звонка пользователю ${toUsername} (ID: ${toUserId})`);
        targetUserIdForCall = parseInt(toUserId);
        targetUsernameForCall = toUsername;

        disableCallButtons(true); // Блокируем все кнопки "Позвонить"

        try {
            await getLocalMedia(); // Получаем медиа перед созданием peer connection
            await createPeerConnection(); // Создаем PC, onnegotiationneeded должен сработать после добавления треков.

            showActiveCallView(toUsername);
        } catch (error) {
            logError('Не удалось инициировать звонок:', error);
            resetCallState(); // Сброс, если не удалось получить медиа или создать PC
        }
    }

    async function handleOffer(data) {
        log('Получен offer от', data.fromUsername || data.fromUserId);
        if (!data.offer) {
            logError("Получен 'offer', но нет SDP (Session Description).");
            return;
        }

        // Проверка, не в звонке ли мы уже или не обрабатываем ли другой оффер
        if (peerConnection && peerConnection.signalingState !== 'stable') {
            logWarn(`Получен offer в состоянии ${peerConnection.signalingState}. Возможно, "гонка состояний" (glare). Пока игнорируем.`);
            // TODO: Implement glare handling - polite peer backs off.
            // For now, if an offer comes in while we're not stable, we might just ignore it or send a busy signal.
            // ws.send(JSON.stringify({ type: 'busy', targetUserId: data.fromUserId, fromUserId: currentUserId }));
            return;
        }

        targetUserIdForCall = data.fromUserId;
        targetUsernameForCall = data.fromUsername;

        await createPeerConnection(); // Создаем PC, если его нет

        try {
            log('Установка remote description из offer:', data.offer);
            await peerConnection.setRemoteDescription(new RTCSessionDescription(data.offer));
        } catch (e) {
            logError("Ошибка при setRemoteDescription (offer):", e);
            return; // Не можем продолжать, если не установили remote description
        }

        callerUsernameModal.textContent = targetUsernameForCall || `User ${targetUserIdForCall}`;
        incomingCallModal.style.display = 'block';
        disableCallButtons(true); // Блокируем кнопки звонков другим пользователям

        btnAcceptCall.onclick = async () => {
            log("Звонок принят");
            incomingCallModal.style.display = 'none';
            try {
                await getLocalMedia(); // Получаем доступ к микрофону
                if (localStream) { // Добавляем треки в peerConnection, если еще не добавлены
                    localStream.getTracks().forEach(track => {
                        if (!peerConnection.getSenders().find(sender => sender.track === track)) {
                            peerConnection.addTrack(track, localStream);
                        }
                    });
                }

                log('Создание answer...');
                const answer = await peerConnection.createAnswer();
                await peerConnection.setLocalDescription(answer);

                log('Отправка answer:', answer);
                if (ws && ws.readyState === WebSocket.OPEN) {
                    ws.send(JSON.stringify({
                        type: 'answer',
                        answer: peerConnection.localDescription,
                        targetUserId: targetUserIdForCall,
                        // fromUserId: currentUserId, // Добавляется на сервере
                        fromUsername: currentUsername
                    }));
                }
                showActiveCallView(targetUsernameForCall);
            } catch (error) {
                logError('Ошибка при принятии звонка:', error);
                alert('Не удалось принять звонок.');
                resetCallState(); // Важно сбросить состояние при ошибке
            }
        };

        btnDeclineCall.onclick = () => {
            log("Звонок отклонен");
            incomingCallModal.style.display = 'none';
            if (ws && ws.readyState === WebSocket.OPEN) {
                ws.send(JSON.stringify({
                    type: 'call_declined',
                    targetUserId: targetUserIdForCall,
                    // fromUserId: currentUserId, // Добавляется на сервере
                    fromUsername: currentUsername
                }));
            }
            resetCallState(); // Сброс состояния, так как мы отклонили
        };
    }

    async function handleAnswer(data) {
        log('Получен answer от', data.fromUsername || data.fromUserId);
        if (!data.answer || !peerConnection) { //  || peerConnection.signalingState !== 'have-local-offer' - это состояние может быстро меняться
            logWarn('Получен answer, но состояние некорректно, или нет peerConnection, или нет answer SDP.', peerConnection ? `State: ${peerConnection.signalingState}` : 'N/A');
            return;
        }
        log('Установка remote description из answer:', data.answer);
        try {
            await peerConnection.setRemoteDescription(new RTCSessionDescription(data.answer));
            log("Remote description (answer) установлен. Соединение в процессе установки ICE.");
        } catch (e) {
            logError("Ошибка при setRemoteDescription (answer):", e);
        }
    }

    async function handleCandidate(data) {
        log('Получен ICE candidate от', data.fromUserId);
        if (!data.candidate || !peerConnection || peerConnection.signalingState === 'closed') {
            logWarn('Получен candidate, но нет peerConnection, candidate SDP, или соединение закрыто.');
            return;
        }
        try {
            // Добавляем кандидата только если remoteDescription уже установлен
            // Это помогает избежать ошибок "Error: Failed to execute 'addIceCandidate' on 'RTCPeerConnection': RTCPeerConnection is gone."
            if (peerConnection.remoteDescription) {
                log('Добавление ICE candidate:', data.candidate);
                await peerConnection.addIceCandidate(new RTCIceCandidate(data.candidate));
            } else {
                logWarn('ICE candidate получен до установки remoteDescription. Кандидат временно сохранен или будет проигнорирован (зависит от браузера).');
                // Можно добавить в очередь и применить после установки remoteDescription, но обычно браузеры справляются
            }
        } catch (error) {
            logError('Ошибка при добавлении ICE candidate:', error);
        }
    }

    function showActiveCallView(calleeName) {
        activeCalleeUsernameView.textContent = calleeName || 'Собеседник';
        activeCallView.style.display = 'block';
        incomingCallModal.style.display = 'none';
    }

    function resetCallState() {
        log("Сброс состояния звонка.");
        if (peerConnection) {
            peerConnection.onicecandidate = null;
            peerConnection.ontrack = null;
            peerConnection.onnegotiationneeded = null;
            peerConnection.oniceconnectionstatechange = null;
            peerConnection.close();
            peerConnection = null;
        }
        if (localStream) {
            localStream.getTracks().forEach(track => track.stop());
            localStream = null;
        }
        localAudio.srcObject = null;
        remoteAudio.srcObject = null;

        activeCallView.style.display = 'none';
        incomingCallModal.style.display = 'none';
        targetUserIdForCall = null;
        targetUsernameForCall = null;
        makingOffer = false;

        btnMuteCall.textContent = 'Выкл. микрофон';
        btnMuteCall.dataset.muted = 'false';

        enableCallButtons(); // Разблокируем кнопки "Позвонить"
    }

    function disableCallButtons(calling = false) {
        document.querySelectorAll('.call-btn').forEach(btn => {
            // Если мы в процессе звонка (calling=true), то все кнопки "Позвонить" должны быть неактивны
            // Если просто скрываем модалку входящего звонка, то кнопки остаются активными (если не приняли звонок)
            if (calling || activeCallView.style.display === 'block' || incomingCallModal.style.display === 'block') {
                 btn.classList.add('disabled');
                 btn.disabled = true;
            }
        });
    }

    function enableCallButtons() {
         document.querySelectorAll('.call-btn').forEach(btn => {
            btn.classList.remove('disabled');
            btn.disabled = false;
        });
    }

    btnEndCall.onclick = () => {
        log("Кнопка 'Завершить звонок' нажата.");
        if (ws && ws.readyState === WebSocket.OPEN && targetUserIdForCall) {
             ws.send(JSON.stringify({
                type: 'call_ended',
                targetUserId: targetUserIdForCall,
                // fromUserId: currentUserId, // Добавляется на сервере
                fromUsername: currentUsername
            }));
        }
        resetCallState();
    };

    btnMuteCall.onclick = () => {
        if (!localStream) {
            logWarn("Попытка выключить микрофон, но localStream отсутствует.");
            return;
        }
        const audioTracks = localStream.getAudioTracks();
        if (audioTracks.length === 0) {
            logWarn("В localStream нет аудио треков.");
            return;
        }

        const isMuted = btnMuteCall.dataset.muted === 'true';
        audioTracks.forEach(track => track.enabled = isMuted); // Если было muted, то включаем (isMuted=true -> enabled=true)
        btnMuteCall.textContent = isMuted ? 'Выкл. микрофон' : 'Вкл. микрофон';
        btnMuteCall.dataset.muted = (!isMuted).toString();
        log(`Микрофон ${isMuted ? "включен" : "выключен"}`);
    };

    // Инициализация WebSocket соединения
    connectWebSocket();

    // --- Заглушка для текстового чата (требует доработки на сервере) ---
    const chatWindow = document.getElementById('chat-window-main');
    const messageInput = document.getElementById('message-text-input');
    const sendMsgBtn = document.getElementById('send-text-message-btn');

    sendMsgBtn.onclick = () => {
        const message = messageInput.value.trim();
        if (message) {
             appendChatMessage(`Вы (${currentUsername})`, message);
             messageInput.value = '';
            // Текущий SignalingServer не поддерживает текстовые сообщения.
            // Для реализации нужно будет добавить обработку типа 'text_message' на сервере
            // и соответствующую логику пересылки.
            if (ws && ws.readyState === WebSocket.OPEN) {
                /*
                ws.send(JSON.stringify({
                    type: 'text_message',
                    // targetUserId: ID_ПОЛЬЗОВАТЕЛЯ_ДЛЯ_ЧАТА, // Нужно определить, с кем чат
                    content: message,
                    fromUserId: currentUserId,
                    fromUsername: currentUsername
                }));
                */
                log("Отправка текстового сообщения (демо, сервер не обработает):", message);
                alert("Функционал текстового чата через WebSocket в данный момент не реализован на стороне сервера.");
            } else {
                alert("WebSocket не подключен. Невозможно отправить сообщение.");
            }
        }
    };

    function appendChatMessage(senderName, messageContent, isSystem = false) {
        const msgDiv = document.createElement('div');
        msgDiv.classList.add('message');
        if (isSystem) msgDiv.classList.add('system-message');

        const senderSpan = document.createElement('strong');
        senderSpan.textContent = `${senderName}: `;

        const contentSpan = document.createElement('span');
        contentSpan.textContent = messageContent; // TODO: Sanitize this if displaying HTML from others

        msgDiv.appendChild(senderSpan);
        msgDiv.appendChild(contentSpan);

        chatWindow.appendChild(msgDiv);
        // Убираем плейсхолдер, если это первое сообщение
        const placeholder = chatWindow.querySelector('.placeholder-text');
        if (placeholder) placeholder.style.display = 'none';

        chatWindow.scrollTop = chatWindow.scrollHeight;
    }
    // Для получения сообщений чата, WebSocket сервер должен их пересылать,
    // и здесь должен быть case 'text_message': в ws.onmessage, который вызывает appendChatMessage.
});
</script>
</body>
</html>

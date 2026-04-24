<!DOCTYPE html>
<html>
<head>
    <title>IA School Manager</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <style>
        #chat-box { height: 400px; border: 1px solid #ccc; overflow-y: scroll; padding: 10px; margin-bottom: 10px; background: #f9f9f9; }
        .user { color: blue; text-align: right; }
        .ai { color: green; text-align: left; }
    </style>
</head>
<body>
    <div style="width: 50%; margin: auto;">
        <h2>Assistant IA Scolaire</h2>
        <div id="chat-box"></div>
        <input type="text" id="user-input" style="width: 80%" placeholder="Posez une question...">
        <button onclick="send()">Envoyer</button>
    </div>

    <script>
        async function send() {
            let input = document.getElementById('user-input');
            let chatBox = document.getElementById('chat-box');
            let msg = input.value;
            if(!msg) return;

            chatBox.innerHTML += `<p class="user"><b>Vous:</b> ${msg}</p>`;
            input.value = '';

            try {
                let res = await axios.post('/chat/send', { message: msg });
                chatBox.innerHTML += `<p class="ai"><b>IA:</b> ${res.data.reply}</p>`;
            } catch (e) {
                chatBox.innerHTML += `<p style="color:red">Erreur de connexion au serveur IA.</p>`;
            }
            chatBox.scrollTop = chatBox.scrollHeight;
        }
    </script>
</body>
</html>
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
        function appendMessage(chatBox, className, label, text, color) {
            let p = document.createElement('p');
            if (className) p.className = className;
            if (color) p.style.color = color;

            let b = document.createElement('b');
            b.textContent = label + ' ';
            p.appendChild(b);
            p.appendChild(document.createTextNode(text));

            chatBox.appendChild(p);
        }

        async function send() {
            let input = document.getElementById('user-input');
            let chatBox = document.getElementById('chat-box');
            let msg = input.value;
            if(!msg) return;

            appendMessage(chatBox, 'user', 'Vous:', msg);
            input.value = '';

            try {
                let res = await axios.post('/chat/send', { message: msg });
                appendMessage(chatBox, 'ai', 'IA:', res.data.reply);
            } catch (e) {
                appendMessage(chatBox, null, '', 'Erreur de connexion au serveur IA.', 'red');
            }
            chatBox.scrollTop = chatBox.scrollHeight;
        }
    </script>
</body>
</html>
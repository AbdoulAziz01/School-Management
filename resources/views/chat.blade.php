@extends('layouts.app')

@section('title', 'Assistant IA - Chat')

@section('content')
<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <nav class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
            <div class="position-sticky pt-3">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-history me-2"></i>Historique
                        </h5>
                    </div>
                    <div class="card-body">
                        <div id="chat-history" class="text-muted text-center py-3">
                            <small>Aucune conversation précédente</small>
                        </div>
                    </div>
                </div>

                <div class="card mt-3">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-bolt me-2"></i>Questions rapides
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button class="quick-question btn btn-sm btn-outline-primary text-start" data-question="Combien d'élèves sont inscrits cette année ?">
                                📊 Nombre d'élèves inscrits
                            </button>
                            <button class="quick-question btn btn-sm btn-outline-primary text-start" data-question="Quels sont les professeurs de mathématiques ?">
                                👨‍🏫 Professeurs de maths
                            </button>
                            <button class="quick-question btn btn-sm btn-outline-primary text-start" data-question="Montre-moi les classes de 3ème année">
                                🏫 Classes de 3ème
                            </button>
                            <button class="quick-question btn btn-sm btn-outline-primary text-start" data-question="Quelle est la moyenne générale des notes ?">
                                📈 Moyenne générale
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <div class="d-flex align-items-center">
                            <div id="status-indicator" class="bg-secondary rounded-circle me-2" style="width: 10px; height: 10px;"></div>
                            <span id="status-text" class="text-muted">Vérification...</span>
                        </div>
                    </div>
                    <div>
                        <h1 class="h2 mb-0">Assistant IA</h1>
                        <small class="text-muted">Votre assistant pour le système de gestion scolaire</small>
                    </div>
                </div>
            </div>

            <!-- Chat container -->
            <div class="card">
                <div class="card-body p-0" style="height: 500px;">
                    <!-- Messages area -->
                    <div id="chat-messages" class="p-3 overflow-auto" style="height: 420px;">
                        <!-- Welcome message -->
                        <div class="d-flex align-items-start mb-3">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px;">
                                <i class="fas fa-robot"></i>
                            </div>
                            <div class="bg-light rounded p-2" style="max-width: 80%;">
                                <p class="mb-0 small">
                                    Bonjour ! Je suis votre assistant IA pour le système de gestion scolaire. 
                                    Je peux vous aider à trouver des informations sur les élèves, les professeurs, 
                                    les classes, les notes et bien plus encore. Posez-moi votre question !
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Typing indicator -->
                    <div id="typing-indicator" class="d-none px-3 py-2 border-top">
                        <div class="d-flex align-items-center text-muted small">
                            <div class="spinner-border spinner-border-sm me-2" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <span>L'IA analyse la base de données...</span>
                        </div>
                    </div>

                    <!-- Input area -->
                    <div class="border-top p-3">
                        <form id="chat-form" class="d-flex gap-2">
                            <textarea 
                                id="message-input" 
                                name="message"
                                class="form-control"
                                rows="1"
                                placeholder="Tapez votre question ici..."
                                required
                                style="resize: none;"
                            ></textarea>
                            <button 
                                type="submit"
                                id="send-button"
                                class="btn btn-primary"
                                disabled
                            >
                                <i class="fas fa-paper-plane"></i>
                                Envoyer
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const chatForm = document.getElementById('chat-form');
    const messageInput = document.getElementById('message-input');
    const chatMessages = document.getElementById('chat-messages');
    const sendButton = document.getElementById('send-button');
    const typingIndicator = document.getElementById('typing-indicator');
    const statusIndicator = document.getElementById('status-indicator');
    const statusText = document.getElementById('status-text');

    // Auto-resize du textarea
    messageInput.addEventListener('input', function() {
        this.style.height = 'auto';
        this.style.height = Math.min(this.scrollHeight, 100) + 'px';
        sendButton.disabled = !this.value.trim();
    });

    // Vérifier l'état du service
    async function checkServiceHealth() {
        try {
            const response = await axios.get('/chat/health');
            if (response.data.status === 'healthy') {
                statusIndicator.className = 'bg-success rounded-circle me-2';
                statusText.textContent = 'En ligne';
            } else {
                statusIndicator.className = 'bg-warning rounded-circle me-2';
                statusText.textContent = 'Partiellement disponible';
            }
        } catch (error) {
            statusIndicator.className = 'bg-danger rounded-circle me-2';
            statusText.textContent = 'Hors ligne';
        }
    }

    // Vérifier l'état au chargement et toutes les 30 secondes
    checkServiceHealth();
    setInterval(checkServiceHealth, 30000);

    // Questions rapides
    document.querySelectorAll('.quick-question').forEach(button => {
        button.addEventListener('click', function() {
            const question = this.getAttribute('data-question');
            messageInput.value = question;
            messageInput.focus();
            messageInput.dispatchEvent(new Event('input'));
            chatForm.dispatchEvent(new Event('submit'));
        });
    });

    // Fonction pour ajouter un message
    function addMessage(content, isUser = false) {
        const messageDiv = document.createElement('div');
        messageDiv.className = 'd-flex align-items-start mb-3';
        
        if (isUser) {
            messageDiv.classList.add('justify-content-end');
        }
        
        const innerDiv = document.createElement('div');
        innerDiv.className = 'd-flex align-items-start';
        
        if (isUser) {
            innerDiv.classList.add('flex-row-reverse');
        }
        
        const avatarDiv = document.createElement('div');
        avatarDiv.className = 'rounded-circle d-flex align-items-center justify-content-center ' + 
                           (isUser ? 'bg-primary text-white ms-2' : 'bg-light text-primary me-2');
        avatarDiv.style.width = '32px';
        avatarDiv.style.height = '32px';
        
        const avatar = document.createElement('i');
        avatar.className = 'fas ' + (isUser ? 'fa-user' : 'fa-robot');
        
        avatarDiv.appendChild(avatar);
        
        const bubbleDiv = document.createElement('div');
        bubbleDiv.className = 'rounded p-2 ' + 
                            (isUser ? 'bg-primary text-white' : 'bg-light');
        bubbleDiv.style.maxWidth = isUser ? '80%' : '60%';
        
        const contentP = document.createElement('p');
        contentP.className = 'mb-0 small';
        contentP.textContent = content;
        
        bubbleDiv.appendChild(contentP);
        
        innerDiv.appendChild(avatarDiv);
        innerDiv.appendChild(bubbleDiv);
        messageDiv.appendChild(innerDiv);
        
        chatMessages.appendChild(messageDiv);
        
        // Scroll vers le bas
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    // Gestion de l'envoi du formulaire
    chatForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const message = messageInput.value.trim();
        if (!message) return;
        
        // Désactiver le formulaire
        sendButton.disabled = true;
        messageInput.disabled = true;
        
        // Ajouter le message de l'utilisateur
        addMessage(message, true);
        
        // Vider le champ et réinitialiser la hauteur
        messageInput.value = '';
        messageInput.style.height = 'auto';
        
        // Afficher l'indicateur de chargement
        typingIndicator.classList.remove('d-none');
        
        try {
            const response = await axios.post('/chat/send', {
                message: message
            });
            
            // Masquer l'indicateur de chargement
            typingIndicator.classList.add('d-none');
            
            if (response.data.response) {
                addMessage(response.data.response, false);
            }
            
        } catch (error) {
            // Masquer l'indicateur de chargement
            typingIndicator.classList.add('d-none');
            
            let errorMessage = 'Une erreur est survenue. Veuillez réessayer.';
            
            if (error.response && error.response.data.error) {
                errorMessage = error.response.data.error;
            } else if (error.request) {
                errorMessage = 'Impossible de contacter le service IA. Vérifiez que le service est bien démarré.';
            }
            
            addMessage(`❌ ${errorMessage}`, false);
        } finally {
            // Réactiver le formulaire
            sendButton.disabled = false;
            messageInput.disabled = false;
            messageInput.focus();
        }
    });

    // Focus sur le champ de saisie au chargement
    messageInput.focus();
});
</script>
@endsection

import { useCallback, useEffect, useRef, useState } from 'react';

const agentBaseUrl = (import.meta.env.VITE_AI_AGENT_URL || 'http://localhost:3000').replace(
    /\/$/,
    ''
);
const agentEndpoint = `${agentBaseUrl}/api/agent`; // POST uniquement

function formatAgentReply(data) {
    if (data?.analysis) {
        return String(data.analysis);
    }
    return data?.response ?? data?.message ?? data?.reply ?? 'Réponse reçue sans contenu exploitable.';
}

function buildHistory(messages) {
    return messages
        .filter(
            (m) =>
                m.id !== 'welcome' &&
                (m.role === 'user' || m.role === 'assistant') &&
                !String(m.content).startsWith('❌')
        )
        .map((m) => ({ role: m.role, content: m.content }));
}

function buildApiUserContext(userContext) {
    if (userContext.role === 'super_admin') {
        return { role: 'super_admin' };
    }

    return {
        role: 'admin_ecole',
        school_id: userContext.school_id,
    };
}

/**
 * Widget flottant d'assistant IA.
 * @param {{ userContext: { role: 'super_admin' | 'admin_ecole', school_id: number|null } }} props
 */
export default function AgentChat({ userContext }) {
    const [open, setOpen] = useState(false);
    const [messages, setMessages] = useState([
        {
            id: 'welcome',
            role: 'assistant',
            content:
                "Bonjour ! Je suis votre assistant pour le système de gestion scolaire. Posez-moi votre question.",
        },
    ]);
    const [input, setInput] = useState('');
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState(null);
    const messagesEndRef = useRef(null);

    useEffect(() => {
        if (open) {
            messagesEndRef.current?.scrollIntoView({ behavior: 'smooth' });
        }
    }, [messages, open, loading]);

    const sendMessage = useCallback(
        async (event) => {
            event.preventDefault();
            const text = input.trim();
            if (!text || loading) {
                return;
            }

            if (userContext.role === 'admin_ecole' && userContext.school_id == null) {
                const msg =
                    "Contexte établissement manquant (school_id). Reconnectez-vous ou contactez l'administrateur.";
                setError(msg);
                setMessages((prev) => [
                    ...prev,
                    { id: `e-${Date.now()}`, role: 'assistant', content: `❌ ${msg}` },
                ]);
                return;
            }

            const userMessage = { id: `u-${Date.now()}`, role: 'user', content: text };
            const history = buildHistory(messages);

            setMessages((prev) => [...prev, userMessage]);
            setInput('');
            setLoading(true);
            setError(null);

            try {
                const response = await fetch(agentEndpoint, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        question: text,
                        history,
                        userContext: buildApiUserContext(userContext),
                    }),
                });

                let data = {};
                try {
                    data = await response.json();
                } catch {
                    data = {};
                }

                if (!response.ok) {
                    const apiError =
                        data?.error ??
                        data?.message ??
                        `Erreur ${response.status} lors de l'appel à l'agent.`;
                    throw new Error(apiError);
                }

                const reply = formatAgentReply(data);

                setMessages((prev) => [
                    ...prev,
                    { id: `a-${Date.now()}`, role: 'assistant', content: reply },
                ]);
            } catch (err) {
                const isNetworkFailure =
                    err instanceof TypeError ||
                    (err instanceof Error && /failed to fetch/i.test(err.message));

                const message = isNetworkFailure
                    ? `Le serveur Node (server.js) ne répond pas sur ${agentBaseUrl}. Démarrez-le puis relancez npm run dev si vous avez modifié le .env.`
                    : err.message || "Erreur inattendue lors de l'appel à l'agent.";

                setError(message);
                setMessages((prev) => [
                    ...prev,
                    { id: `e-${Date.now()}`, role: 'assistant', content: `❌ ${message}` },
                ]);
            } finally {
                setLoading(false);
            }
        },
        [input, loading, messages, userContext]
    );

    const roleLabel =
        userContext.role === 'super_admin'
            ? 'Super administrateur'
            : `Admin école${userContext.school_id ? ` · #${userContext.school_id}` : ''}`;

    return (
        <div className="ai-agent-root" aria-live="polite">
            {open && (
                <div className="ai-agent-panel" role="dialog" aria-label="Assistant IA">
                    <header className="ai-agent-header">
                        <div>
                            <strong>Assistant IA</strong>
                            <small className="ai-agent-meta">{roleLabel}</small>
                        </div>
                        <button
                            type="button"
                            className="ai-agent-icon-btn"
                            onClick={() => setOpen(false)}
                            aria-label="Fermer l'assistant"
                        >
                            ×
                        </button>
                    </header>

                    <div className="ai-agent-messages">
                        {messages.map((msg) => (
                            <div
                                key={msg.id}
                                className={`ai-agent-bubble ai-agent-bubble--${msg.role}`}
                            >
                                {msg.content}
                            </div>
                        ))}
                        {loading && (
                            <div className="ai-agent-bubble ai-agent-bubble--assistant ai-agent-typing">
                                Analyse en cours…
                            </div>
                        )}
                        <div ref={messagesEndRef} />
                    </div>

                    {error && <p className="ai-agent-error">{error}</p>}

                    <form className="ai-agent-form" onSubmit={sendMessage}>
                        <textarea
                            value={input}
                            onChange={(e) => setInput(e.target.value)}
                            placeholder="Votre question…"
                            rows={2}
                            disabled={loading}
                            onKeyDown={(e) => {
                                if (e.key === 'Enter' && !e.shiftKey) {
                                    e.preventDefault();
                                    sendMessage(e);
                                }
                            }}
                        />
                        <button type="submit" disabled={loading || !input.trim()}>
                            Envoyer
                        </button>
                    </form>
                </div>
            )}

            <button
                type="button"
                className="ai-agent-fab"
                onClick={() => setOpen((v) => !v)}
                aria-expanded={open}
                aria-label={open ? "Réduire l'assistant" : "Ouvrir l'assistant IA"}
            >
                {open ? '▼' : '💬'}
            </button>

            <style>{`
                .ai-agent-root {
                    position: fixed;
                    right: 1.25rem;
                    bottom: 1.25rem;
                    z-index: 1050;
                    font-family: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
                    font-size: 0.9rem;
                }
                .ai-agent-fab {
                    width: 3.25rem;
                    height: 3.25rem;
                    border-radius: 50%;
                    border: none;
                    background: #0d6efd;
                    color: #fff;
                    font-size: 1.25rem;
                    cursor: pointer;
                    box-shadow: 0 4px 14px rgba(13, 110, 253, 0.45);
                }
                .ai-agent-fab:hover { filter: brightness(1.05); }
                .ai-agent-panel {
                    position: absolute;
                    right: 0;
                    bottom: 4rem;
                    width: min(380px, calc(100vw - 2rem));
                    height: 480px;
                    display: flex;
                    flex-direction: column;
                    background: #fff;
                    border-radius: 12px;
                    box-shadow: 0 8px 30px rgba(0,0,0,0.18);
                    overflow: hidden;
                    border: 1px solid #dee2e6;
                }
                .ai-agent-header {
                    display: flex;
                    justify-content: space-between;
                    align-items: flex-start;
                    padding: 0.75rem 1rem;
                    background: #0d6efd;
                    color: #fff;
                }
                .ai-agent-meta { display: block; opacity: 0.85; font-size: 0.75rem; }
                .ai-agent-icon-btn {
                    background: transparent;
                    border: none;
                    color: inherit;
                    font-size: 1.4rem;
                    line-height: 1;
                    cursor: pointer;
                }
                .ai-agent-messages {
                    flex: 1;
                    overflow-y: auto;
                    padding: 0.75rem;
                    display: flex;
                    flex-direction: column;
                    gap: 0.5rem;
                    background: #f8f9fa;
                }
                .ai-agent-bubble {
                    max-width: 88%;
                    padding: 0.5rem 0.75rem;
                    border-radius: 10px;
                    line-height: 1.4;
                    white-space: pre-wrap;
                }
                .ai-agent-bubble--user {
                    align-self: flex-end;
                    background: #0d6efd;
                    color: #fff;
                }
                .ai-agent-bubble--assistant {
                    align-self: flex-start;
                    background: #fff;
                    border: 1px solid #dee2e6;
                }
                .ai-agent-typing { font-style: italic; opacity: 0.8; }
                .ai-agent-error {
                    margin: 0 0.75rem;
                    color: #b02a37;
                    font-size: 0.8rem;
                }
                .ai-agent-form {
                    display: flex;
                    gap: 0.5rem;
                    padding: 0.75rem;
                    border-top: 1px solid #dee2e6;
                    background: #fff;
                }
                .ai-agent-form textarea {
                    flex: 1;
                    resize: none;
                    border: 1px solid #ced4da;
                    border-radius: 8px;
                    padding: 0.5rem;
                    font: inherit;
                }
                .ai-agent-form button {
                    align-self: flex-end;
                    border: none;
                    border-radius: 8px;
                    background: #0d6efd;
                    color: #fff;
                    padding: 0.5rem 0.85rem;
                    cursor: pointer;
                }
                .ai-agent-form button:disabled { opacity: 0.6; cursor: not-allowed; }
                @media print {
                    .ai-agent-root { display: none !important; }
                }
            `}</style>
        </div>
    );
}

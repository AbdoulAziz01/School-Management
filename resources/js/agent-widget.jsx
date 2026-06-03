import { createRoot } from 'react-dom/client';
import AgentChat from './components/AgentChat';

/**
 * Mappe le rôle Laravel vers le format attendu par le microservice Node.
 * @param {string} laravelRole
 * @param {string|undefined} schoolId
 * @returns {{ role: 'super_admin' | 'admin_ecole', school_id: number|null }}
 */
export function mapLaravelUserContext(laravelRole, schoolId) {
    if (laravelRole === 'super_admin') {
        return { role: 'super_admin', school_id: null };
    }

    const parsed =
        schoolId !== undefined && schoolId !== '' && schoolId != null
            ? Number(schoolId)
            : null;

    return { role: 'admin_ecole', school_id: Number.isFinite(parsed) ? parsed : null };
}

const mountEl = document.getElementById('ai-agent-widget');

if (mountEl) {
    const { role: laravelRole, schoolId } = mountEl.dataset;
    const userContext = mapLaravelUserContext(laravelRole ?? '', schoolId);

    createRoot(mountEl).render(<AgentChat userContext={userContext} />);
}

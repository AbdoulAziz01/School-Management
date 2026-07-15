<style>
    .portal-top-navbar {
        position: sticky;
        top: 0;
        z-index: 1025;
        margin: 0;
        padding: 0;
        width: 100%;
        background: #fff;
        border-bottom: 1px solid #e7e5e4;
        box-shadow: 0 1px 0 rgba(245, 158, 11, 0.15), 0 8px 24px rgba(28, 25, 23, 0.06);
    }

    .portal-navbar-inner {
        display: flex;
        align-items: center;
        gap: 0.75rem 1rem;
        min-height: 56px;
        padding: 0.45rem 1rem;
    }

    .portal-navbar-brand {
        align-items: center;
        gap: 0.65rem;
        min-width: 0;
        max-width: 220px;
    }

    .portal-navbar-school-logo {
        width: 36px;
        height: 36px;
        object-fit: contain;
        border-radius: 8px;
        flex-shrink: 0;
    }

    .portal-navbar-school-icon {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
        color: #b45309;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .portal-navbar-school-name {
        font-size: 0.85rem;
        font-weight: 600;
        color: #44403c;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .portal-navbar-year-form {
        flex: 1 1 auto;
        min-width: 0;
        max-width: 420px;
    }

    .portal-navbar-year {
        display: flex;
        align-items: center;
        gap: 0.4rem;
        flex-wrap: nowrap;
        background: #fffbeb;
        border: 1px solid #fde68a;
        border-radius: 10px;
        padding: 0.3rem 0.55rem;
        min-width: 0;
    }

    .portal-navbar-year-label {
        display: flex;
        align-items: center;
        gap: 0.3rem;
        margin: 0;
        font-size: 0.75rem;
        font-weight: 600;
        color: #92400e;
        white-space: nowrap;
        flex-shrink: 0;
    }

    .portal-navbar-year-label i {
        color: #d97706;
        font-size: 0.8rem;
    }

    .portal-navbar-year-select {
        flex: 1 1 auto;
        min-width: 0;
        max-width: 100%;
        border: none;
        background: transparent;
        font-size: 0.8rem;
        font-weight: 600;
        color: #1c1917;
        padding: 0.15rem 0.2rem;
        cursor: pointer;
        outline: none;
    }

    .portal-navbar-year-status {
        font-size: 0.6rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        padding: 0.15rem 0.4rem;
        border-radius: 5px;
        white-space: nowrap;
        flex-shrink: 0;
    }

    .portal-navbar-year-status.is-current { background: #dcfce7; color: #166534; }
    .portal-navbar-year-status.is-closed { background: #f5f5f4; color: #57534e; }
    .portal-navbar-year-status.is-past { background: #fef3c7; color: #92400e; }

    .portal-navbar-spacer {
        flex: 1 1 0;
        min-width: 0.25rem;
    }

    .portal-navbar-user {
        flex-shrink: 0;
    }

    .portal-navbar-notif {
        flex-shrink: 0;
        margin-right: 0.5rem;
    }

    .portal-navbar-notif-trigger {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        border: 1px solid #e7e5e4;
        border-radius: 12px;
        background: #fafaf9;
        color: #57534e;
        cursor: pointer;
        transition: background 0.2s ease;
    }

    .portal-navbar-notif-trigger:hover,
    .portal-navbar-notif-trigger:focus {
        background: #fffbeb;
        outline: none;
    }

    .portal-navbar-notif-badge {
        position: absolute;
        top: -4px;
        right: -4px;
        min-width: 18px;
        height: 18px;
        padding: 0 4px;
        border-radius: 999px;
        background: #dc2626;
        color: #fff;
        font-size: 0.65rem;
        font-weight: 700;
        line-height: 18px;
        text-align: center;
        border: 2px solid #fff;
    }

    .portal-navbar-notif-dropdown {
        width: 340px;
        max-width: calc(100vw - 2rem);
        padding: 0;
        overflow: hidden;
    }

    .portal-navbar-notif-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0.75rem 1rem;
        border-bottom: 1px solid #f5f5f4;
        background: #fafaf9;
    }

    .portal-navbar-notif-list {
        max-height: 340px;
        overflow-y: auto;
    }

    .portal-navbar-notif-item {
        display: block;
        width: 100%;
        text-align: left;
        padding: 0.65rem 1rem;
        border: none;
        border-bottom: 1px solid #f5f5f4;
        background: #fffbeb;
        font-size: 0.82rem;
        color: #1c1917;
        line-height: 1.35;
    }

    .portal-navbar-notif-item:hover {
        background: #fef3c7;
    }

    .portal-navbar-notif-item-time {
        display: block;
        margin-top: 0.15rem;
        font-size: 0.72rem;
        color: #a8a29e;
    }

    .portal-navbar-notif-empty {
        padding: 1.5rem 1rem;
        text-align: center;
        font-size: 0.82rem;
        color: #a8a29e;
    }

    .portal-navbar-user-trigger {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.2rem 0.35rem 0.2rem 0.2rem;
        border: 1px solid #e7e5e4;
        border-radius: 12px;
        background: #fafaf9;
        cursor: pointer;
        transition: background 0.2s ease;
    }

    .portal-navbar-user-trigger:hover,
    .portal-navbar-user-trigger:focus {
        background: #fffbeb;
        outline: none;
    }

    .portal-navbar-avatar {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        color: #1c1917;
        font-weight: 700;
        font-size: 0.85rem;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .portal-navbar-identity {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.1rem;
        min-width: 0;
        max-width: 200px;
    }

    .portal-navbar-name {
        font-weight: 600;
        font-size: 0.85rem;
        color: #1c1917;
        line-height: 1.15;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 200px;
    }

    .portal-navbar-meta-line {
        display: flex;
        align-items: center;
        gap: 0.35rem;
        flex-wrap: wrap;
    }

    .portal-navbar-badge {
        display: inline-block;
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        color: #1c1917;
        font-size: 0.58rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        padding: 0.12rem 0.4rem;
        border-radius: 4px;
        line-height: 1.2;
    }

    .portal-navbar-sublabel {
        font-size: 0.68rem;
        font-weight: 600;
        color: #78716c;
        line-height: 1.2;
    }

    .portal-navbar-chevron {
        font-size: 0.65rem;
        color: #a8a29e;
        margin-left: 0.15rem;
    }

    .portal-navbar-dropdown {
        min-width: 220px;
    }

    .portal-navbar-dropdown-header {
        white-space: normal;
        max-width: 260px;
    }

    .portal-navbar-toggle {
        display: none;
        flex-shrink: 0;
        background: none;
        border: none;
        color: #92400e;
        font-size: 1.1rem;
        width: 38px;
        height: 38px;
        padding: 0;
        border-radius: 8px;
    }

    .portal-navbar-toggle:hover {
        background: #fef3c7;
    }

    /* Mobile / tablette : une seule barre, pas de double header */
    @media (max-width: 991.98px) {
        .portal-top-navbar {
            top: 0;
        }

        .portal-navbar-toggle {
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .portal-navbar-inner {
            flex-wrap: nowrap;
            gap: 0.5rem;
            min-height: 52px;
            padding: 0.4rem 0.65rem;
        }

        .portal-navbar-year-form {
            flex: 1 1 auto;
            min-width: 0;
            max-width: none;
            order: unset;
        }

        .portal-navbar-year {
            width: 100%;
            padding: 0.28rem 0.45rem;
        }

        .portal-navbar-year-label-text {
            display: none;
        }

        .portal-navbar-year-status {
            font-size: 0.55rem;
            padding: 0.1rem 0.3rem;
        }

        .portal-navbar-spacer {
            display: none;
        }

        .portal-navbar-user {
            margin-left: 0;
        }

        .portal-navbar-user-trigger {
            padding: 0.15rem;
            border-radius: 10px;
        }

        .portal-navbar-avatar {
            width: 34px;
            height: 34px;
            font-size: 0.8rem;
        }
    }

    @media (max-width: 575.98px) {
        .portal-navbar-year-status {
            display: none;
        }

        .portal-navbar-year-select {
            font-size: 0.75rem;
        }
    }
</style>

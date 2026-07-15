<style>
        /* ===== DESIGN SIDEBAR AMBRE & CUIVRE ===== */
        #sidebar {
            background: linear-gradient(180deg, #1c1917 0%, #292524 50%, #1c1917 100%) !important;
            border-right: 1px solid rgba(251, 191, 36, 0.12) !important;
            box-shadow: none !important;
        }
        
        /* Logo avec effet néon */
        .sidebar-logo {
            background: transparent;
            padding: 25px 20px;
            margin: 0;
            position: relative;
            border-bottom: 1px solid rgba(251, 191, 36, 0.2);
        }
        
        .sidebar-logo .logo-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            gap: 10px;
        }
        
        .sidebar-logo .logo-icon-box {
            width: 52px;
            height: 52px;
            min-width: 52px;
            min-height: 52px;
            flex-shrink: 0;
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
            box-shadow: 0 0 20px rgba(245, 158, 11, 0.4);
            animation: glow-pulse 2s ease-in-out infinite;
            overflow: hidden;
            padding: 0;
        }

        .sidebar-logo .logo-icon-box.has-school-logo {
            background: #ffffff;
            padding: 4px;
            animation: none;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.25);
        }

        .sidebar-logo .logo-icon-box .school-logo-img {
            width: 100%;
            height: 100%;
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
            object-position: center;
            display: block;
        }

        .sidebar-logo .logo-text {
            width: 100%;
        }
        
        .sidebar-logo .logo-text h5 {
            color: #fbbf24;
            font-weight: 700;
            font-size: 1.05rem;
            line-height: 1.35;
            margin: 0;
            letter-spacing: 0.3px;
            white-space: normal;
            word-wrap: break-word;
            overflow-wrap: anywhere;
        }
        
        .sidebar-logo .logo-text small {
            color: rgba(251, 191, 36, 0.6);
            font-size: 0.65rem;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            display: block;
            margin-top: 4px;
        }

        @keyframes glow-pulse {
            0%, 100% { box-shadow: 0 0 20px rgba(245, 158, 11, 0.4); }
            50% { box-shadow: 0 0 35px rgba(245, 158, 11, 0.6); }
        }
        
        /* User Card - Design Horizontal style Spotify */
        .user-card {
            background: linear-gradient(135deg, rgba(251, 191, 36, 0.15) 0%, rgba(217, 119, 6, 0.1) 100%);
            margin: 15px;
            padding: 20px;
            border-radius: 18px;
            display: flex;
            align-items: center;
            gap: 16px;
            border: 1px solid rgba(251, 191, 36, 0.2);
            position: relative;
            overflow: hidden;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            min-height: 100px;
            height: auto;
        }
        
        .user-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(251, 191, 36, 0.1), transparent);
            animation: card-shine 3s infinite;
        }
        
        @keyframes card-shine {
            0% { left: -100%; }
            50%, 100% { left: 100%; }
        }
        
        .user-card:hover {
            background: linear-gradient(135deg, rgba(251, 191, 36, 0.25) 0%, rgba(217, 119, 6, 0.15) 100%);
            border-color: rgba(251, 191, 36, 0.4);
            transform: scale(1.02);
            transition: all 0.3s ease;  
            box-shadow: 0 8px 25px rgba(245, 158, 11, 0.3);
            transform: scale(1.02);
            transition: all 0.3s ease;
            box-shadow: 0 8px 25px rgba(245, 158, 11, 0.3);
            transform: scale(1.02);
            transition: all 0.3s ease;
            box-shadow: 0 8px 25px rgba(245, 158, 11, 0.3);
            transform: scale(1.02);
            transition: all 0.3s ease;
            box-shadow: 0 8px 25px rgba(245, 158, 11, 0.3);
            transform: scale(1.02);
            transition: all 0.3s ease;
            box-shadow: 0 8px 25px rgba(245, 158, 11, 0.3);
            transform: scale(1.02);
            transition: all 0.3s ease;
            box-shadow: 0 8px 25px rgba(245, 158, 11, 0.3);
        }
        
        .user-avatar {
            width: 65px;
            height: 65px;
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            font-weight: 700;
            color: #1c1917;
            flex-shrink: 0;
            position: relative;
            box-shadow: 0 4px 15px rgba(245, 158, 11, 0.3);
            animation: avatar-float 3s ease-in-out infinite;
        }
        
        @keyframes avatar-float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-3px); }
        }
        
        .user-avatar::after {
            content: '';
            position: absolute;
            bottom: -2px;
            right: -2px;
            width: 16px;
            height: 16px;
            background: #fbbf24;
            border: 2px solid #1c1917;
            border-radius: 50%;
        }
        
        .user-info {
            flex: 1;
            min-width: 0;
        }
        
        .user-info h6 {
            color: #fef3c7;
            font-weight: 600;
            margin: 0 0 6px 0;
            font-size: 1.05rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .user-info .badge-admin {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: #1c1917;
            padding: 6px 14px;
            border-radius: 9px;
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        /* Menu Section Title */
        .menu-section {
            padding: 20px 20px 10px;
            font-size: 0.65rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: rgba(251, 191, 36, 0.5);
        }
        
        /* Navigation Links - Style Minimal Dark */
        #sidebar .nav-link {
            display: flex;
            align-items: center;
            color: #a8a29e !important;
            background: transparent;
            margin: 3px 12px;
            padding: 12px 16px;
            border-radius: 12px;
            border-left: none !important;
            position: relative;
            overflow: hidden;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            font-weight: 500;
            font-size: 0.9rem;
        }
        
        #sidebar .nav-link:hover {
            background: rgba(251, 191, 36, 0.1) !important;
            color: #fbbf24 !important;
            padding-left: 22px;
        }
        
        #sidebar .nav-link.active {
            background: linear-gradient(90deg, rgba(251, 191, 36, 0.2) 0%, transparent 100%) !important;
            color: #fbbf24 !important;
            font-weight: 600;
        }
        
        #sidebar .nav-link.active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 4px;
            height: 24px;
            background: linear-gradient(180deg, #fbbf24 0%, #f59e0b 100%);
            border-radius: 0 4px 4px 0;
            box-shadow: 0 0 10px rgba(251, 191, 36, 0.5);
        }
        
        #sidebar .nav-link i {
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(251, 191, 36, 0.1);
            border-radius: 10px;
            margin-right: 12px;
            font-size: 0.95rem;
            color: #a8a29e;
            transition: all 0.3s ease;
        }
        
        #sidebar .nav-link:hover i,
        #sidebar .nav-link.active i {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: #1c1917;
            transform: scale(1.1);
            box-shadow: 0 0 15px rgba(245, 158, 11, 0.4);
        }
        
        /* Badge notification */
        #sidebar .badge-notif {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
            padding: 3px 8px;
            border-radius: 8px;
            font-size: 0.7rem;
            font-weight: 600;
            margin-left: auto;
            animation: notif-pulse 2s infinite;
        }
        
        @keyframes notif-pulse {
            0%, 100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.05); opacity: 0.9; }
        }
        
        /* Separator */
        .menu-separator {
            height: 1px;
            background: linear-gradient(90deg, transparent 0%, rgba(251, 191, 36, 0.2) 50%, transparent 100%);
            margin: 15px 20px;
            border: none;
        }
        
        /* Logout button special */
        #sidebar .nav-link.logout-link {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.2);
            color: #fca5a5 !important;
        }
        
        #sidebar .nav-link.logout-link i {
            background: rgba(239, 68, 68, 0.15);
            color: #fca5a5;
        }
        
        #sidebar .nav-link.logout-link:hover {
            background: rgba(239, 68, 68, 0.2) !important;
            border-color: rgba(239, 68, 68, 0.4);
        }
        
        #sidebar .nav-link.logout-link:hover i {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
        }
</style>

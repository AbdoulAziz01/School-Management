{{--
    Feuille de style unique du "look" bulletin — incluse une seule fois par
    document (dans le <head>), réutilisée par tous les bulletins imprimés
    (élève et admin) via partials.bulletin-print. Ne pas dupliquer ces
    règles ailleurs : toute retouche visuelle du bulletin se fait ici.
--}}
<style>
    @page { size: A4 portrait; margin: 12mm 14mm; }

    .bulletin-sheet {
        width: 100%;
        max-width: 182mm;
        margin: 0 auto;
        font-family: 'DejaVu Sans', sans-serif;
        color: #1f2937;
        font-size: 10.5px;
        line-height: 1.35;
        page-break-after: always;
    }
    .bulletin-sheet:last-child { page-break-after: auto; }

    /* ---------- En-tête ---------- */
    .doc-header {
        display: table;
        width: 100%;
        table-layout: fixed;
        margin-bottom: 6px;
    }
    .doc-header .col { display: table-cell; vertical-align: middle; }
    .doc-header .col-flag { width: 74px; }
    .doc-header .col-center { text-align: center; }
    .doc-header .col-logo { width: 64px; text-align: right; }

    .flag { width: 66px; height: 44px; border: 1px solid #9ca3af; overflow: hidden; }
    .flag img { display: block; width: 100%; height: 100%; }

    .logo-box { width: 56px; height: 56px; border-radius: 50%; border: 2px solid #1a5f2a; display: inline-block; text-align: center; vertical-align: middle; overflow: hidden; }
    .logo-box img { width: 100%; height: 100%; object-fit: cover; }
    .logo-box .logo-fallback { font-size: 22px; line-height: 52px; color: #1a5f2a; }

    .country-name { margin: 0; font-size: 11px; font-weight: bold; letter-spacing: 0.5px; color: #1a5f2a; }
    .country-motto { margin: 1px 0; font-size: 8.5px; color: #4b5563; }
    .header-divider { margin: 2px 0; font-size: 9px; color: #9ca3af; letter-spacing: 2px; }
    .school-name { margin: 2px 0 0; font-size: 15px; font-weight: bold; color: #1a5f2a; text-transform: uppercase; }
    .school-motto { margin: 1px 0; font-size: 8.5px; letter-spacing: 1px; color: #4b5563; text-transform: uppercase; }
    .school-contact { margin: 2px 0 0; font-size: 8px; color: #6b7280; }

    /* ---------- Bandeau titre ---------- */
    .title-banner {
        background: #1a5f2a;
        color: #fff;
        text-align: center;
        font-size: 14px;
        font-weight: bold;
        letter-spacing: 1px;
        padding: 7px 0;
        margin: 10px 0 8px;
        border-radius: 3px;
    }

    /* ---------- Ligne méta (année / période / niveau / classe) ---------- */
    .meta-line { text-align: center; margin-bottom: 10px; font-size: 10px; }
    .meta-line p { margin: 1px 0; }
    .meta-line strong { color: #1a5f2a; }

    /* ---------- Bloc identité élève ---------- */
    .student-box {
        display: table;
        width: 100%;
        table-layout: fixed;
        border: 1px solid #cbd5e1;
        border-radius: 4px;
        padding: 8px 12px;
        margin-bottom: 10px;
    }
    .student-box .student-col { display: table-cell; width: 50%; vertical-align: top; }
    .student-box p { margin: 3px 0; font-size: 10px; }
    .student-box .label { color: #4b5563; }
    .moy-highlight { color: #0e7490; font-size: 12px; font-weight: bold; }

    /* ---------- Tableau des notes ---------- */
    table.grades-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
    table.grades-table th {
        background: #1a5f2a;
        color: #fff;
        font-size: 9px;
        font-weight: bold;
        padding: 6px 4px;
        border: 1px solid #155724;
        text-align: center;
    }
    table.grades-table th .sub { font-weight: normal; font-size: 8px; opacity: 0.9; }
    table.grades-table td {
        border: 1px solid #e2e8f0;
        padding: 5px 4px;
        text-align: center;
        font-size: 9.5px;
    }
    table.grades-table td.col-subject { text-align: left; font-weight: 600; padding-left: 8px; }
    table.grades-table tbody tr:nth-child(even) { background: #f8fafc; }
    table.grades-table tfoot .total-row td {
        background: #1a5f2a;
        color: #fff;
        font-weight: bold;
        font-size: 10px;
        padding: 6px 4px;
    }

    /* ---------- Appréciation générale ---------- */
    .appreciation-box {
        border: 1px solid #cbd5e1;
        border-radius: 4px;
        padding: 8px 12px;
        margin-bottom: 14px;
        min-height: 50px;
    }
    .appreciation-title { margin: 0 0 5px; font-size: 10px; font-weight: bold; color: #1a5f2a; letter-spacing: 0.5px; }
    .appreciation-text { margin: 0; font-size: 9.5px; color: #374151; }

    /* ---------- Signatures ---------- */
    .signature-row { display: table; width: 100%; table-layout: fixed; margin-top: 6px; }
    .signature-row .sig-col { display: table-cell; width: 38%; vertical-align: top; }
    .signature-row .stamp-col { display: table-cell; width: 24%; vertical-align: middle; text-align: center; }
    .signature-row .sig-col.text-end { text-align: right; }
    .signature-label { margin: 0 0 26px; font-size: 9.5px; font-weight: bold; color: #374151; }
    .signature-date { margin-top: 6px; font-size: 9px; color: #6b7280; }

    .stamp-circle {
        width: 64px;
        height: 64px;
        border-radius: 50%;
        border: 1.5px dashed #1a5f2a;
        display: inline-block;
        text-align: center;
        vertical-align: middle;
        overflow: hidden;
        padding: 2px;
    }
    .stamp-circle img { width: 100%; height: 100%; }
    .stamp-circle .stamp-text { font-size: 7px; color: #1a5f2a; line-height: 1.2; display: block; padding-top: 22px; }

    /* ---------- Pied de page ---------- */
    .footer-quote { text-align: center; font-size: 8.5px; font-style: italic; color: #6b7280; margin-top: 14px; }
</style>

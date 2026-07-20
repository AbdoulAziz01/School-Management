{{--
    Feuille bulletin unique, réutilisée par tous les bulletins imprimés
    (élève et admin) — voir bulletin-print-styles.blade.php pour le CSS.
    Attend une variable $sheet (array) avec la forme documentée dans
    App\Support\Reports\BulletinSheetFormatter::format().
--}}
<div class="bulletin-sheet">
    <div class="doc-header">
        <div class="col col-flag">
            <div class="flag">
                {{-- Drapeau du Sénégal en SVG vectoriel (3 bandes verticales
                     égales + étoile verte à 5 branches centrée dans la bande
                     jaune), servi en data-URI via <img> — DomPDF ne rend pas
                     les <svg> inline de façon fiable (voir SenegalFlagSvg). --}}
                <img src="{{ \App\Support\Reports\SenegalFlagSvg::dataUri() }}" alt="Drapeau du Sénégal">
            </div>
        </div>
        <div class="col col-center">
            <p class="country-name">RÉPUBLIQUE DU SÉNÉGAL</p>
            <p class="country-motto">Un Peuple – Un But – Une Foi</p>
            <p class="header-divider">— — — — — — — — — —</p>
            <h1 class="school-name">{{ $sheet['school']['name'] }}</h1>
            <p class="school-motto">{{ $sheet['school']['motto'] }}</p>
            @if($sheet['school']['contactLine'])
                <p class="school-contact">{{ $sheet['school']['contactLine'] }}</p>
            @endif
        </div>
        <div class="col col-logo">
            <div class="logo-box">
                @if($sheet['school']['logoUri'])
                    <img src="{{ $sheet['school']['logoUri'] }}" alt="Logo">
                @else
                    <span class="logo-fallback">&#127891;</span>
                @endif
            </div>
        </div>
    </div>

    <div class="title-banner">BULLETIN DE NOTES</div>

    <div class="meta-line">
        <p>Année Scolaire : <strong>{{ $sheet['academicYearName'] }}</strong></p>
        @if($sheet['periodLabel'])
            <p>{{ $sheet['periodLabel'] }}</p>
        @endif
        <p>Niveau : <strong>{{ $sheet['niveau'] }}</strong> &nbsp; | &nbsp; Classe : <strong>{{ $sheet['classe'] }}</strong></p>
    </div>

    <div class="student-box">
        <div class="student-col">
            <p><span class="label">Nom &amp; Prénom :</span> <strong>{{ $sheet['student']['name'] }}</strong></p>
            <p><span class="label">Date de naissance :</span> {{ $sheet['student']['dob'] }}</p>
            <p><span class="label">Matricule :</span> {{ $sheet['student']['matricule'] }}</p>
        </div>
        <div class="student-col">
            <p><span class="label">Effectif Classe :</span> {{ $sheet['effectif'] }}</p>
            <p><span class="label">Rang :</span> {{ $sheet['rang'] }}</p>
            <p><span class="label">Moyenne générale :</span> <span class="moy-highlight">{{ $sheet['moyenneGenerale'] }} / {{ $sheet['maxLabel'] }}</span></p>
        </div>
    </div>

    <table class="grades-table">
        <thead>
            <tr>
                <th class="col-subject">Disciplines</th>
                <th>Coef.</th>
                <th>Notes<br><span class="sub">(Sur {{ $sheet['maxLabel'] }})</span></th>
                <th>Moyennes<br>Pondérées</th>
                <th>Appréciations</th>
            </tr>
        </thead>
        <tbody>
            @forelse($sheet['rows'] as $row)
                <tr>
                    <td class="col-subject">{{ $row['subject'] }}</td>
                    <td>{{ $row['coefficient'] }}</td>
                    <td>{{ $row['note'] ?? '—' }}</td>
                    <td>{{ $row['points'] ?? '—' }}</td>
                    <td>{{ $row['appreciation'] ?? '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="5">Aucune note enregistrée pour cette période.</td></tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td class="col-subject">TOTAL</td>
                <td>{{ $sheet['totalCoef'] }}</td>
                <td></td>
                <td>{{ $sheet['moyenneGenerale'] }} / {{ $sheet['maxLabel'] }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    <div class="appreciation-box">
        <p class="appreciation-title">APPRÉCIATION GÉNÉRALE</p>
        <p class="appreciation-text">{{ $sheet['appreciationGenerale'] }}</p>
    </div>

    <div class="signature-row">
        <div class="sig-col">
            <p class="signature-label">Visa du Directeur</p>
            <div class="signature-space"></div>
        </div>
        <div class="stamp-col">
            <div class="stamp-circle">
                @if($sheet['qrCodeUri'])
                    <img src="{{ $sheet['qrCodeUri'] }}" alt="QR de vérification">
                @else
                    <span class="stamp-text">{{ $sheet['school']['name'] }}</span>
                @endif
            </div>
        </div>
        <div class="sig-col text-end">
            <p class="signature-label">Signature des Parents / Tuteur</p>
            <div class="signature-space"></div>
            <p class="signature-date">Date : ……………………</p>
        </div>
    </div>

    <p class="footer-quote">« {{ $sheet['footerQuote'] }} »</p>
</div>

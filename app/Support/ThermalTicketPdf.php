<?php

namespace App\Support;

use Dompdf\Dompdf;

/**
 * Génère un PDF ticket 80mm dont la hauteur de page épouse exactement le
 * contenu réel, au lieu d'une hauteur fixe généreuse qui laisse une zone
 * blanche en bas quand le contenu est court.
 *
 * DomPDF exige une taille de page avant le rendu (impossible de mesurer le
 * contenu puis fixer la hauteur en un seul passage) : on procède donc par
 * recherche dichotomique sur la hauteur, en utilisant le moteur de mise en
 * page de DomPDF lui-même comme juge de vérité (get_page_count() === 1
 * signifie que le contenu tient sur la hauteur testée) plutôt que de
 * dépendre d'API internes fragiles (FrameTree) ou de recalculer les
 * hauteurs CSS à la main.
 */
class ThermalTicketPdf
{
    private const WIDTH_MM = 80;
    private const MM_TO_PT = 2.83465;

    /** Marge de sécurité ajoutée après la hauteur minimale trouvée, en pt. */
    private const BOTTOM_SAFETY_PT = 6;

    private const MIN_HEIGHT_PT = 80;
    private const MAX_HEIGHT_PT = 2000;

    public static function render(string $html): Dompdf
    {
        $width = self::WIDTH_MM * self::MM_TO_PT;

        $height = self::findMinimalHeight($html, $width);

        $pdf = new Dompdf();
        $pdf->setPaper([0, 0, $width, $height], 'portrait');
        $pdf->loadHtml($html);
        $pdf->render();

        return $pdf;
    }

    private static function findMinimalHeight(string $html, float $width): float
    {
        $low = self::MIN_HEIGHT_PT;
        $high = self::MAX_HEIGHT_PT;

        if (! self::fitsOnOnePage($html, $width, $high)) {
            // Contenu inhabituellement long (beaucoup de lignes de
            // paiement) : on part sur la hauteur généreuse d'origine plutôt
            // que de risquer de couper du contenu.
            return $high;
        }

        while ($high - $low > 2) {
            $mid = (int) (($low + $high) / 2);
            if (self::fitsOnOnePage($html, $width, $mid)) {
                $high = $mid;
            } else {
                $low = $mid;
            }
        }

        return $high + self::BOTTOM_SAFETY_PT;
    }

    private static function fitsOnOnePage(string $html, float $width, float $height): bool
    {
        $pdf = new Dompdf();
        $pdf->setPaper([0, 0, $width, $height], 'portrait');
        $pdf->loadHtml($html);
        $pdf->render();

        return $pdf->getCanvas()->get_page_count() === 1;
    }
}

<?php
require_once __DIR__ . '/../../lib/fpdf.php';

class CatalogController
{
    public function download()
    {
        $products = db()->query('SELECT name, internal_code, stock, price FROM ' . table_name('products') . ' ORDER BY id DESC LIMIT 300')->fetchAll();
        $date = date('Ymd_His');
        $filename = 'catalogo_' . $date . '.pdf';

        $lines = array();
        $lines[] = 'Catalogo Marketplace v1';
        $lines[] = 'Fecha: ' . date('d/m/Y H:i');
        $lines[] = '----------------------------------------';
        foreach ($products as $p) {
            $lines[] = $p['name'] . ' | ' . $p['internal_code'] . ' | Stock: ' . $p['stock'] . ' | VES ' . number_format($p['price'], 2);
        }

        $textOps = array();
        for ($i = 0; $i < count($lines); $i++) {
            $textOps[] = '(' . $this->escapePdfText($lines[$i]) . ') Tj';
        }

        $stream = "BT\n/F1 10 Tf\n12 TL\n40 800 Td\n" . implode("\nT*\n", $textOps) . "\nET";
        $objects = array();
        $objects[] = "1 0 obj<< /Type /Catalog /Pages 2 0 R >>endobj";
        $objects[] = "2 0 obj<< /Type /Pages /Kids [3 0 R] /Count 1 >>endobj";
        $objects[] = "3 0 obj<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >>endobj";
        $objects[] = "4 0 obj<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>endobj";
        $objects[] = "5 0 obj<< /Length " . strlen($stream) . " >>stream\n" . $stream . "\nendstream\nendobj";

        $pdf = "%PDF-1.4\n";
        $offsets = array();
        for ($i = 0; $i < count($objects); $i++) {
            $offsets[] = strlen($pdf);
            $pdf .= $objects[$i] . "\n";
        }
        $xref = strlen($pdf);
        $pdf .= "xref\n0 " . (count($objects) + 1) . "\n";
        $pdf .= "0000000000 65535 f \n";
        for ($i = 0; $i < count($offsets); $i++) {
            $pdf .= sprintf('%010d 00000 n ', $offsets[$i]) . "\n";
        }
        $pdf .= "trailer<< /Size " . (count($objects) + 1) . " /Root 1 0 R >>\nstartxref\n" . $xref . "\n%%EOF";

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        echo $pdf;
        exit;
    }

    public function download2()
    {
        $products = db()->query('SELECT name, internal_code, price_retail, price_wholesale, image_path FROM ' . table_name('products') . ' WHERE price_retail > 0 ORDER BY id DESC LIMIT 300')->fetchAll();
        $date = date('Ymd_His');
        $filename = 'catalogo_usd_' . $date . '.pdf';

        $pdf = new FPDF('P', 'mm', 'A4');
        $pdf->SetAutoPageBreak(true, 15);
        $pdf->AddPage();

        $pdf->SetFont('Arial', 'B', 14);
        $pdf->Cell(0, 10, 'www.tutiendaonlinelq.com', 0, 1, 'C');
        $pdf->SetFont('Arial', '', 11);
        $pdf->Cell(0, 8, 'WhatsApp: +58 412 016 1515', 0, 1, 'C');
        $pdf->Cell(0, 8, 'Catalogo en USD', 0, 1, 'C');
        $pdf->Cell(0, 8, 'Fecha: ' . date('d/m/Y H:i'), 0, 1, 'C');
        $pdf->Ln(5);

        $cols = 3;
        $colWidth = 63;
        $rowHeight = 50;
        $margin = 10;
        $x = $margin;
        $y = $pdf->GetY();
        $col = 0;

        foreach ($products as $p) {
            $usdRetail = $p['price_retail'] / get_exchange_rate();
            $usdWholesale = $p['price_wholesale'] > 0 ? $p['price_wholesale'] / get_exchange_rate() : 0;
            $images = array_filter(array_map('trim', explode('|', $p['image_path'])));
            $imagePath = count($images) > 0 ? $images[0] : '/logo.jpg';
            $filePath = $_SERVER['DOCUMENT_ROOT'] . '/' . ltrim($imagePath, '/');
            if (!file_exists($filePath)) {
                $filePath = $_SERVER['DOCUMENT_ROOT'] . '/logo.jpg';
            }

            if ($col === 0) {
                $x = $margin;
            } else {
                $x = $margin + $col * $colWidth;
            }
            $pdf->SetXY($x, $y);

            if (file_exists($filePath)) {
                $pdf->Image($filePath, $x + 1, $y, $colWidth - 2, 28);
            }

            $pdf->SetXY($x, $y + 30);
            $pdf->SetFont('Arial', 'B', 8);
            $pdf->MultiCell($colWidth, 4, 'Cod: ' . $p['internal_code'], 0, 'C');
            $pdf->SetFont('Arial', '', 8);
            $pdf->MultiCell($colWidth, 4, 'Detal: $' . number_format($usdRetail, 2), 0, 'C');
            $pdf->MultiCell($colWidth, 4, 'Mayor: $' . number_format($usdWholesale, 2), 0, 'C');

            $col++;
            if ($col === $cols) {
                $col = 0;
                $y += $rowHeight;
                if ($y + $rowHeight > 280) {
                    $pdf->AddPage();
                    $y = $pdf->GetY();
                }
            }
        }

        $pdf->Output('D', $filename);
        exit;
    }

    private function escapePdfText($value)
    {
        return str_replace(array('\\', '(', ')', "\r", "\n"), array('\\\\', '\\(', '\\)', '', ''), $value);
    }
}

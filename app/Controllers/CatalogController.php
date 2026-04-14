<?php

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

    private function escapePdfText($value)
    {
        return str_replace(array('\\', '(', ')', "\r", "\n"), array('\\\\', '\\(', '\\)', '', ''), $value);
    }
}

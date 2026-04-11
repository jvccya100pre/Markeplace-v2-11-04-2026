<?php
require_once dirname(__DIR__) . '/Models/OrderModel.php';

class OrderPdfController extends BaseController
{
    public function handle()
    {
        require_admin_login();
        $model = new OrderModel();
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        $order = $model->findOrderWithUser($id);
        if (!$order) {
            exit('Pedido no encontrado');
        }

        $items = $model->orderItems($id);
        $txt = 'Pedido #' . $order['id'] . ' Cliente: ' . $order['full_name'] . ' Correo: ' . $order['email'] . ' Total: $' . number_format($order['total'], 2) . ' ';
        foreach ($items as $it) {
            $txt .= $it['name'] . ' x' . $it['quantity'] . ' ';
        }

        $stream = "BT /F1 11 Tf 30 780 Td (" . str_replace(array('\\\\', '(', ')'), array('\\\\\\\\', '\\(', '\\)'), $txt) . ") Tj ET";
        $objs = array();
        $objs[] = '1 0 obj<< /Type /Catalog /Pages 2 0 R >>endobj';
        $objs[] = '2 0 obj<< /Type /Pages /Kids [3 0 R] /Count 1 >>endobj';
        $objs[] = '3 0 obj<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >>endobj';
        $objs[] = '4 0 obj<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>endobj';
        $objs[] = "5 0 obj<< /Length " . strlen($stream) . " >>stream\n" . $stream . "\nendstream\nendobj";

        $pdf = "%PDF-1.4\n";
        $off = array();
        for ($i = 0; $i < count($objs); $i++) {
            $off[] = strlen($pdf);
            $pdf .= $objs[$i] . "\n";
        }

        $xref = strlen($pdf);
        $pdf .= "xref\n0 " . (count($objs) + 1) . "\n0000000000 65535 f \n";
        for ($i = 0; $i < count($off); $i++) {
            $pdf .= sprintf('%010d 00000 n ', $off[$i]) . "\n";
        }

        $pdf .= "trailer<< /Size " . (count($objs) + 1) . " /Root 1 0 R >>\nstartxref\n" . $xref . "\n%%EOF";

        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="pedido_' . $id . '.pdf"');
        echo $pdf;
        exit;
    }
}

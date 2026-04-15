<?php
require_once dirname(__DIR__) . '/Models/DashboardModel.php';

class DashboardController extends BaseController
{
    public function handle()
    {
        require_admin_login();
        $model = new DashboardModel();
        $message = '';

        if (is_post() && isset($_POST['save_payment'])) {
            $model->savePaymentMethods($_POST);
            $message = 'Datos de pago actualizados correctamente.';
        }

        if (is_post() && isset($_POST['save_exchange'])) {
            if (isset($_POST['usd_to_ves']) && is_numeric($_POST['usd_to_ves'])) {
                $model->saveExchangeRate($_POST['usd_to_ves']);
                $message = 'Tasa de cambio actualizada correctamente.';
            } else {
                $message = 'Valor inválido para la tasa de cambio.';
            }
        }

        if (is_post() && isset($_POST['generate_backup'])) {
            $this->generateBackup();
            exit;
        }

        if (is_post() && isset($_POST['clean_products'])) {
            db()->exec('DELETE FROM ' . table_name('products'));
            $message = 'Todos los productos han sido eliminados.';
        }

        $stats = $model->getStats();
        $payment = $model->getPaymentMethods();
        $currentRate = $model->getExchangeRate();

        $this->render('dashboard', array(
            'message' => $message,
            'topPages' => $model->getTopPages(),
            'totalVisits' => $stats['totalVisits'],
            'uniqueIp' => $stats['uniqueIp'],
            'totalUsers' => $stats['totalUsers'],
            'totalOrders' => $stats['totalOrders'],
            'monthGain' => $stats['monthGain'],
            'currentBank' => $payment['currentBank'],
            'currentIdentityType' => $payment['currentIdentityType'],
            'currentIdentityNumber' => $payment['currentIdentityNumber'],
            'currentPhonePrefix' => $payment['currentPhonePrefix'],
            'currentPhoneNumber' => $payment['currentPhoneNumber'],
            'currentBinance' => $payment['currentBinance'],
            'currentPaypal' => $payment['currentPaypal'],
            'currentWholesaleEmails' => $payment['currentWholesaleEmails'],
            'currentRate' => $currentRate,
        ));
    }

    private function generateBackup()
    {
        $date = date('Y-m-d_H-i-s');
        $filename = 'backup_' . $date . '.sql';

        $tables = db()->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);

        $sql = "-- Backup generado el " . date('Y-m-d H:i:s') . "\n\n";

        foreach ($tables as $table) {
            $sql .= "-- Estructura de la tabla `$table`\n";
            $create = db()->query("SHOW CREATE TABLE `$table`")->fetch();
            $sql .= $create['Create Table'] . ";\n\n";

            $rows = db()->query("SELECT * FROM `$table`")->fetchAll(PDO::FETCH_ASSOC);
            if ($rows) {
                $sql .= "-- Datos de la tabla `$table`\n";
                foreach ($rows as $row) {
                    $values = array_map(function($v) { return is_null($v) ? 'NULL' : db()->quote($v); }, $row);
                    $sql .= "INSERT INTO `$table` VALUES (" . implode(', ', $values) . ");\n";
                }
                $sql .= "\n";
            }
        }

        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        echo $sql;
        exit;
    }
}

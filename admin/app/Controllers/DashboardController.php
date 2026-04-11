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

        $stats = $model->getStats();
        $payment = $model->getPaymentMethods();

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
        ));
    }
}

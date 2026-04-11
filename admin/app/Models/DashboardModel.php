<?php

class DashboardModel
{
    public function getStats()
    {
        $row = db()->query('SELECT COUNT(*) c FROM ' . table_name('visits'))->fetch();
        $totalVisits = $row ? (int)$row['c'] : 0;

        $row = db()->query('SELECT COUNT(DISTINCT ip_address) c FROM ' . table_name('visits'))->fetch();
        $uniqueIp = $row ? (int)$row['c'] : 0;

        $row = db()->query('SELECT COUNT(*) c FROM ' . table_name('users') . ' WHERE role = "cliente"')->fetch();
        $totalUsers = $row ? (int)$row['c'] : 0;

        $row = db()->query('SELECT COUNT(*) c FROM ' . table_name('orders'))->fetch();
        $totalOrders = $row ? (int)$row['c'] : 0;

        $row = db()->query('SELECT COALESCE(SUM(total),0) s FROM ' . table_name('orders') . ' WHERE MONTH(created_at)=MONTH(NOW()) AND YEAR(created_at)=YEAR(NOW())')->fetch();
        $monthGain = $row ? (float)$row['s'] : 0;

        return array(
            'totalVisits' => $totalVisits,
            'uniqueIp' => $uniqueIp,
            'totalUsers' => $totalUsers,
            'totalOrders' => $totalOrders,
            'monthGain' => $monthGain,
        );
    }

    public function getTopPages()
    {
        return db()->query('SELECT page_visited, COUNT(*) c FROM ' . table_name('visits') . ' GROUP BY page_visited ORDER BY c DESC LIMIT 8')->fetchAll();
    }

    public function savePaymentMethods($data)
    {
        set_setting('pm_bank', trim($data['pm_bank']));
        set_setting('pm_identity_type', trim($data['pm_identity_type']));
        set_setting('pm_identity_number', trim($data['pm_identity_number']));
        set_setting('pm_phone_prefix', trim($data['pm_phone_prefix']));
        set_setting('pm_phone_number', trim($data['pm_phone_number']));
        set_setting('binance_uid', trim($data['binance_uid']));
        set_setting('paypal_email', trim($data['paypal_email']));
    }

    public function getPaymentMethods()
    {
        return array(
            'currentBank' => get_setting('pm_bank'),
            'currentIdentityType' => get_setting('pm_identity_type', 'V'),
            'currentIdentityNumber' => get_setting('pm_identity_number'),
            'currentPhonePrefix' => get_setting('pm_phone_prefix', '+58'),
            'currentPhoneNumber' => get_setting('pm_phone_number'),
            'currentBinance' => get_setting('binance_uid'),
            'currentPaypal' => get_setting('paypal_email'),
        );
    }
}

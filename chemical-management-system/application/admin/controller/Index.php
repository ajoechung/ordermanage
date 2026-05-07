<?php

// +----------------------------------------------------------------------
// | 首页控制器
// +----------------------------------------------------------------------
namespace app\admin\controller;

class Index extends Base
{
    
    public function index()
    {
        return $this->fetch();
    }
    
    public function dashboard()
    {
        $this->assign('menu', 'dashboard');
        
        $monthStart = date('Y-m-01 00:00:00');
        $monthEnd = date('Y-m-t 23:59:59');
        
        $thisMonthSales = db('orders')
            ->where('order_time', 'between', [$monthStart, $monthEnd])
            ->where('fulfill_status', 'IN', [2, 3, 4, 5])
            ->sum('amount');
        
        $thisMonthOrders = db('orders')
            ->where('order_time', 'between', [$monthStart, $monthEnd])
            ->count();
        
        $completedCustomers = db('orders')
            ->where('order_time', 'between', [$monthStart, $monthEnd])
            ->where('fulfill_status', 5)
            ->distinct(true)
            ->count('customer_id');
        
        $newCustomers = db('customer')
            ->where('create_time', 'between', [$monthStart, $monthEnd])
            ->count();
        
        $totalCustomers = db('customer')->count();
        $totalOrders = db('orders')->count();
        $totalProducts = db('product')->count();
        
        $recentOrders = db('orders')
            ->alias('o')
            ->join('customer c', 'c.id = o.customer_id', 'LEFT')
            ->field('o.*, c.customer_name')
            ->order('o.create_time DESC')
            ->limit(10)
            ->select();
        
        $orderStatus = [
            1 => '待确认',
            2 => '已确认',
            3 => '生产中',
            4 => '已发货',
            5 => '已完成',
            6 => '已取消'
        ];
        
        foreach ($recentOrders as &$order) {
            $order['status_text'] = isset($orderStatus[$order['fulfill_status']]) ? $orderStatus[$order['fulfill_status']] : '未知';
        }
        unset($order);
        
        $salesData = [];
        for ($i = 29; $i >= 0; $i--) {
            $day = date('Y-m-d', strtotime("-{$i} days"));
            $dayStart = $day . ' 00:00:00';
            $dayEnd = $day . ' 23:59:59';
            
            $salesData[] = [
                'date' => $day,
                'sales' => db('orders')
                    ->where('order_time', 'between', [$dayStart, $dayEnd])
                    ->where('fulfill_status', 'IN', [2, 3, 4, 5])
                    ->sum('amount')
            ];
        }
        
        $this->assign('thisMonthSales', $thisMonthSales ?: 0);
        $this->assign('thisMonthOrders', $thisMonthOrders ?: 0);
        $this->assign('completedCustomers', $completedCustomers ?: 0);
        $this->assign('newCustomers', $newCustomers ?: 0);
        $this->assign('totalCustomers', $totalCustomers ?: 0);
        $this->assign('totalOrders', $totalOrders ?: 0);
        $this->assign('totalProducts', $totalProducts ?: 0);
        $this->assign('recentOrders', $recentOrders ?: []);
        $this->assign('salesData', json_encode($salesData));
        
        return $this->fetch();
    }
}

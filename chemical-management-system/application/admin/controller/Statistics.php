<?php

namespace app\admin\controller;

class Statistics extends Base
{
    
    public function index()
    {
        return $this->redirect('admin/statistics/customer');
    }
    
    public function customer()
    {
        $this->assign('menu', 'statistics');
        $this->assign('sub_menu', 'customer');
        
        $monthStart = date('Y-m-01 00:00:00');
        $monthEnd = date('Y-m-t 23:59:59');
        
        $totalCustomers = db('customer')->count();
        $newCustomers = db('customer')
            ->where('create_time', 'between', [$monthStart, $monthEnd])
            ->count();
        
        $completedCustomers = db('orders')
            ->where('order_time', 'between', [$monthStart, $monthEnd])
            ->where('fulfill_status', 5)
            ->distinct(true)
            ->count('customer_id');
        
        $totalDealRate = $totalCustomers > 0 ? round(($completedCustomers / $totalCustomers) * 100, 2) : 0;
        
        $customerByIndustry = db('customer')
            ->field('industry, COUNT(*) as count')
            ->group('industry')
            ->select();
        
        $customerBySource = db('customer')
            ->field('source, COUNT(*) as count')
            ->group('source')
            ->select();
        
        $customerByStatus = db('customer')
            ->field('status, COUNT(*) as count')
            ->group('status')
            ->select();
        
        $statusMap = [
            1 => '正常',
            2 => '潜在',
            3 => '流失'
        ];
        
        foreach ($customerByStatus as &$item) {
            $item['status_text'] = isset($statusMap[$item['status']]) ? $statusMap[$item['status']] : '未知';
        }
        unset($item);
        
        $recentCustomers = db('customer')
            ->alias('c')
            ->join('admin_user u', 'u.id = c.user_id', 'LEFT')
            ->field('c.*, u.realname as user_name')
            ->order('c.create_time DESC')
            ->limit(10)
            ->select();
        
        $this->assign('totalCustomers', $totalCustomers);
        $this->assign('newCustomers', $newCustomers);
        $this->assign('completedCustomers', $completedCustomers);
        $this->assign('totalDealRate', $totalDealRate);
        $this->assign('customerByIndustry', json_encode($customerByIndustry ?: []));
        $this->assign('customerBySource', json_encode($customerBySource ?: []));
        $this->assign('customerByStatus', json_encode($customerByStatus ?: []));
        $this->assign('recentCustomers', $recentCustomers ?: []);
        
        return $this->fetch();
    }
    
    public function order()
    {
        $this->assign('menu', 'statistics');
        $this->assign('sub_menu', 'order');
        
        $monthStart = date('Y-m-01 00:00:00');
        $monthEnd = date('Y-m-t 23:59:59');
        
        $totalOrders = db('orders')->count();
        $monthOrders = db('orders')
            ->where('order_time', 'between', [$monthStart, $monthEnd])
            ->count();
        
        $monthSales = db('orders')
            ->where('order_time', 'between', [$monthStart, $monthEnd])
            ->where('fulfill_status', 'IN', [2, 3, 4, 5])
            ->sum('amount');
        
        $totalSales = db('orders')
            ->where('fulfill_status', 'IN', [2, 3, 4, 5])
            ->sum('amount');
        
        $orderByStatus = db('orders')
            ->field('fulfill_status, COUNT(*) as count, SUM(amount) as total_amount')
            ->group('fulfill_status')
            ->select();
        
        $statusMap = [
            1 => '待确认',
            2 => '已确认',
            3 => '生产中',
            4 => '已发货',
            5 => '已完成',
            6 => '已取消'
        ];
        
        foreach ($orderByStatus as &$item) {
            $item['status_text'] = isset($statusMap[$item['fulfill_status']]) ? $statusMap[$item['fulfill_status']] : '未知';
        }
        unset($item);
        
        $salesTrend = [];
        for ($i = 29; $i >= 0; $i--) {
            $day = date('Y-m-d', strtotime("-{$i} days"));
            $dayStart = $day . ' 00:00:00';
            $dayEnd = $day . ' 23:59:59';
            
            $salesTrend[] = [
                'date' => $day,
                'sales' => db('orders')
                    ->where('order_time', 'between', [$dayStart, $dayEnd])
                    ->where('fulfill_status', 'IN', [2, 3, 4, 5])
                    ->sum('amount') ?: 0,
                'count' => db('orders')
                    ->where('order_time', 'between', [$dayStart, $dayEnd])
                    ->count() ?: 0
            ];
        }
        
        $topCustomers = db('orders')
            ->alias('o')
            ->join('customer c', 'c.id = o.customer_id', 'LEFT')
            ->field('c.customer_name, COUNT(*) as order_count, SUM(o.amount) as total_amount')
            ->group('o.customer_id')
            ->order('total_amount DESC')
            ->limit(10)
            ->select();
        
        $this->assign('totalOrders', $totalOrders);
        $this->assign('monthOrders', $monthOrders);
        $this->assign('monthSales', $monthSales ?: 0);
        $this->assign('totalSales', $totalSales ?: 0);
        $this->assign('orderByStatus', json_encode($orderByStatus ?: []));
        $this->assign('salesTrend', json_encode($salesTrend));
        $this->assign('topCustomers', $topCustomers ?: []);
        
        return $this->fetch();
    }
}

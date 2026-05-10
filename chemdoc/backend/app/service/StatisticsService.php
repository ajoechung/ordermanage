<?php
namespace app\service;

use think\facade\Db;

class StatisticsService
{
    public function getDashboard(): array
    {
        $userId = request()->user_id ?? 0;

        $currentMonth = date('Y-m');
        $lastMonth = date('Y-m', strtotime('-1 month'));

        $monthStart = $currentMonth . '-01 00:00:00';
        $monthEnd = date('Y-m-d 23:59:59');

        $monthData = [
            'sales_amount' => Db::name('order')
                ->where('order_status', 5)
                ->whereBetweenTime('order_time', $monthStart, $monthEnd)
                ->sum('actual_amount'),
            'order_count' => Db::name('order')
                ->where('order_status', 5)
                ->whereBetweenTime('order_time', $monthStart, $monthEnd)
                ->count(),
            'deal_customer_count' => Db::name('order')
                ->where('order_status', 5)
                ->whereBetweenTime('order_time', $monthStart, $monthEnd)
                ->distinct(true)
                ->count('customer_id'),
            'new_customer_count' => Db::name('customer')
                ->whereBetweenTime('create_time', $monthStart, $monthEnd)
                ->count(),
        ];

        $lastMonthStart = $lastMonth . '-01 00:00:00';
        $lastMonthEnd = date('Y-m-t 23:59:59', strtotime($lastMonth));

        $lastMonthData = [
            'sales_amount' => Db::name('order')
                ->where('order_status', 5)
                ->whereBetweenTime('order_time', $lastMonthStart, $lastMonthEnd)
                ->sum('actual_amount'),
            'order_count' => Db::name('order')
                ->where('order_status', 5)
                ->whereBetweenTime('order_time', $lastMonthStart, $lastMonthEnd)
                ->count(),
        ];

        $monthData['sales_amount_trend'] = $lastMonthData['sales_amount'] > 0
            ? round(($monthData['sales_amount'] - $lastMonthData['sales_amount']) / $lastMonthData['sales_amount'] * 100, 2)
            : 0;
        $monthData['order_count_trend'] = $lastMonthData['order_count'] > 0
            ? round(($monthData['order_count'] - $lastMonthData['order_count']) / $lastMonthData['order_count'] * 100, 2)
            : 0;

        $pendingData = [
            'pending_order_count' => Db::name('order')
                ->whereIn('order_status', [1, 2, 3])
                ->whereNull('delete_time')
                ->count(),
            'pending_purchase_count' => Db::name('purchase_order')
                ->whereIn('status', [1, 2, 3])
                ->whereNull('delete_time')
                ->count(),
            'need_follow_count' => Db::name('customer')
                ->where('status', 1)
                ->whereNull('delete_time')
                ->count(),
        ];

        $latestOrders = Db::name('order')
            ->alias('o')
            ->join('customer c', 'o.customer_id = c.customer_id')
            ->field('o.order_id,o.order_no,o.total_amount,o.order_status,o.order_time,c.name as customer_name')
            ->whereNull('o.delete_time')
            ->order('o.create_time', 'desc')
            ->limit(5)
            ->select()
            ->toArray();

        foreach ($latestOrders as &$order) {
            $statusMap = [1 => '待确认', 2 => '已确认', 3 => '生产中', 4 => '已发货', 5 => '已完成', 6 => '已取消'];
            $order['status_text'] = $statusMap[$order['order_status']] ?? '未知';
        }

        return Result::success([
            'month_data' => $monthData,
            'pending_data' => $pendingData,
            'latest_orders' => $latestOrders,
        ]);
    }

    public function getCustomerStats(array $params): array
    {
        $dateRange = $params['date_range'] ?? [];

        $query = Db::name('customer')->whereNull('delete_time');

        if (!empty($dateRange) && is_array($dateRange) && count($dateRange) == 2) {
            $query->whereBetweenTime('create_time', $dateRange[0], $dateRange[1]);
        }

        $totalCount = $query->count();
        $normalCount = $query->where('status', 1)->count();

        $industryData = Db::name('customer')
            ->field('industry, count(*) as count')
            ->whereNull('delete_time')
            ->group('industry')
            ->select()
            ->toArray();

        $levelData = Db::name('customer')
            ->field('level, count(*) as count')
            ->whereNull('delete_time')
            ->group('level')
            ->select()
            ->toArray();

        $levelMap = [1 => '普通客户', 2 => '重要客户', 3 => '核心客户'];
        foreach ($levelData as &$item) {
            $item['level_text'] = $levelMap[$item['level']] ?? '未知';
        }

        $monthlyData = Db::name('customer')
            ->field('DATE_FORMAT(create_time, "%Y-%m") as month, count(*) as count')
            ->whereNull('delete_time')
            ->group('month')
            ->order('month', 'desc')
            ->limit(12)
            ->select()
            ->toArray();

        return Result::success([
            'total_count' => $totalCount,
            'normal_count' => $normalCount,
            'industry_data' => $industryData,
            'level_data' => $levelData,
            'monthly_data' => $monthlyData,
        ]);
    }

    public function getOrderStats(array $params): array
    {
        $dateRange = $params['date_range'] ?? [];

        $query = Db::name('order')->whereNull('delete_time');

        if (!empty($dateRange) && is_array($dateRange) && count($dateRange) == 2) {
            $query->whereBetweenTime('order_time', $dateRange[0], $dateRange[1]);
        }

        $totalAmount = $query->where('order_status', 5)->sum('actual_amount');
        $totalCount = $query->count();
        $completedCount = $query->where('order_status', 5)->count();
        $avgAmount = $completedCount > 0 ? round($totalAmount / $completedCount, 2) : 0;

        $statusData = Db::name('order')
            ->field('order_status, count(*) as count')
            ->whereNull('delete_time')
            ->group('order_status')
            ->select()
            ->toArray();

        $statusMap = [
            1 => '待确认', 2 => '已确认', 3 => '生产中',
            4 => '已发货', 5 => '已完成', 6 => '已取消'
        ];
        foreach ($statusData as &$item) {
            $item['status_text'] = $statusMap[$item['order_status']] ?? '未知';
        }

        $monthlyData = Db::name('order')
            ->field('DATE_FORMAT(order_time, "%Y-%m") as month, sum(actual_amount) as amount, count(*) as count')
            ->where('order_status', 5)
            ->whereNull('delete_time')
            ->group('month')
            ->order('month', 'desc')
            ->limit(12)
            ->select()
            ->toArray();

        $productData = Db::name('order_item')
            ->alias('oi')
            ->join('product p', 'oi.product_id = p.product_id')
            ->field('oi.product_name, sum(oi.quantity) as total_quantity, sum(oi.subtotal) as total_amount')
            ->group('oi.product_id')
            ->order('total_amount', 'desc')
            ->limit(10)
            ->select()
            ->toArray();

        return Result::success([
            'total_amount' => round((float)$totalAmount, 2),
            'total_count' => $totalCount,
            'completed_count' => $completedCount,
            'avg_amount' => $avgAmount,
            'status_data' => $statusData,
            'monthly_data' => $monthlyData,
            'product_data' => $productData,
        ]);
    }
}

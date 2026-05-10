<?php
namespace app\controller\api;

use app\BaseController;
use app\service\StatisticsService;
use app\service\Result;
use think\App;

class Statistics extends BaseController
{
    protected StatisticsService $statisticsService;

    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->statisticsService = new StatisticsService();
    }

    public function dashboard()
    {
        return json($this->statisticsService->getDashboard());
    }

    public function customer()
    {
        $params = $this->request->param();
        return json($this->statisticsService->getCustomerStats($params));
    }

    public function order()
    {
        $params = $this->request->param();
        return json($this->statisticsService->getOrderStats($params));
    }
}

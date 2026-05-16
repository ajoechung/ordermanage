<?php
use think\migration\Migrator;
use think\migration\db\Column;

class AddOrderIdToPurchaseOrder extends Migrator
{
    public function up()
    {
        $table = $this->table('purchase_order');
        if (!$table->hasColumn('order_id')) {
            $table->addColumn('order_id', 'integer', [
                'limit' => 11,
                'default' => 0,
                'comment' => '关联订单ID',
                'null' => true,
            ])->update();
        }
    }

    public function down()
    {
        $table = $this->table('purchase_order');
        if ($table->hasColumn('order_id')) {
            $table->removeColumn('order_id')->update();
        }
    }
}

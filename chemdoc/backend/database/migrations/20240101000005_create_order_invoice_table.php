<?php
use think\migration\Migrator;
use think\migration\db\Column;

class CreateOrderInvoiceTable extends Migrator
{
    public function up()
    {
        $table = $this->table('order_invoice', ['engine' => 'InnoDB', 'charset' => 'utf8mb4']);
        $table->addColumn('order_id', 'integer', ['limit' => 11, 'default' => 0, 'comment' => '订单ID'])
              ->addColumn('file_name', 'string', ['limit' => 255, 'default' => '', 'comment' => '文件名'])
              ->addColumn('file_path', 'string', ['limit' => 500, 'default' => '', 'comment' => '文件路径'])
              ->addColumn('file_size', 'integer', ['limit' => 11, 'default' => 0, 'comment' => '文件大小'])
              ->addColumn('file_type', 'string', ['limit' => 100, 'default' => '', 'comment' => '文件类型'])
              ->addColumn('create_time', 'datetime', ['comment' => '创建时间'])
              ->addIndex('order_id')
              ->create();
    }

    public function down()
    {
        $this->dropTable('order_invoice');
    }
}

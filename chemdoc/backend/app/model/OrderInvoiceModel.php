<?php
namespace app\model;

use think\Model;

class OrderInvoiceModel extends Model
{
    protected $name = 'order_invoice';
    protected $pk = 'invoice_id';
    
    protected $autoWriteTimestamp = false;
    
    protected $schema = [
        'invoice_id' => 'int',
        'order_id' => 'int',
        'file_name' => 'varchar',
        'file_path' => 'varchar',
        'file_size' => 'int',
        'file_type' => 'varchar',
        'create_time' => 'datetime',
    ];
}

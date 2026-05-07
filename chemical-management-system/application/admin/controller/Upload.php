<?php

namespace app\admin\controller;

class Upload extends Base
{
    public function index()
    {
        if ($this->request->isPost()) {
            $file = $this->request->file('file');
            
            if (!$file) {
                return json(['code' => 0, 'msg' => '请选择文件']);
            }
            
            // 验证文件大小和类型
            $info = $file->validate(['size' => 1024 * 1024 * 10, 'ext' => 'jpg,jpeg,png,gif,pdf,doc,docx,xls,xlsx'])->move(ROOT_PATH . 'public' . DS . 'uploads');
            
            if ($info) {
                $filePath = '/uploads/' . $info->getSaveName();
                
                $this->writeLog('文件上传', '上传文件：' . $filePath, '上传');
                
                return json(['code' => 1, 'msg' => '上传成功', 'data' => ['path' => $filePath, 'name' => $info->getFilename()]]);
            } else {
                return json(['code' => 0, 'msg' => $file->getError()]);
            }
        }
    }
    
    public function image()
    {
        if ($this->request->isPost()) {
            $file = $this->request->file('file');
            
            if (!$file) {
                return json(['code' => 0, 'msg' => '请选择图片']);
            }
            
            $info = $file->validate(['size' => 1024 * 1024 * 5, 'ext' => 'jpg,jpeg,png,gif'])->move(ROOT_PATH . 'public' . DS . 'uploads' . DS . 'images');
            
            if ($info) {
                $filePath = '/uploads/images/' . $info->getSaveName();
                return json(['code' => 1, 'msg' => '上传成功', 'data' => ['path' => $filePath]]);
            } else {
                return json(['code' => 0, 'msg' => $file->getError()]);
            }
        }
    }
}

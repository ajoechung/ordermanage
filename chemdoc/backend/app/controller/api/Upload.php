<?php
namespace app\controller\api;

use app\BaseController;
use app\service\UploadService;
use app\service\Result;
use think\facade\Filesystem;

class Upload extends BaseController
{
    protected UploadService $uploadService;

    public function __construct()
    {
        parent::__construct();
        $this->uploadService = new UploadService();
    }

    public function image()
    {
        $file = $this->request->file('file');
        
        if (!$file) {
            return json(Result::validateError('请选择要上传的文件'));
        }

        return json($this->uploadService->uploadImage($file));
    }

    public function file()
    {
        $file = $this->request->file('file');
        
        if (!$file) {
            return json(Result::validateError('请选择要上传的文件'));
        }

        return json($this->uploadService->uploadFile($file));
    }

    public function delete()
    {
        $path = $this->request->param('path', '');
        
        if (empty($path)) {
            return json(Result::validateError('文件路径不能为空'));
        }

        return json($this->uploadService->delete($path));
    }
}

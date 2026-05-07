<?php
namespace app\controller\api;

use app\BaseController;

class Captcha extends BaseController
{
    public function index()
    {
        $captcha = $this->generateCaptcha();
        $key = $this->generateKey();

        cache('captcha_' . $key, $captcha['code'], 300);

        return json([
            'code' => 200,
            'msg' => 'success',
            'data' => [
                'key' => $key,
                'image' => $captcha['image']
            ]
        ]);
    }

    protected function generateCaptcha(): array
    {
        $code = $this->generateCode(4);
        $width = 120;
        $height = 40;

        $image = imagecreate($width, $height);
        $bgColor = imagecolorallocate($image, 255, 255, 255);
        $textColor = imagecolorallocate($image, mt_rand(0, 100), mt_rand(0, 100), mt_rand(0, 150));

        imagefill($image, 0, 0, $bgColor);

        for ($i = 0; $i < 5; $i++) {
            $lineColor = imagecolorallocate($image, mt_rand(150, 220), mt_rand(150, 220), mt_rand(150, 220));
            imageline($image, mt_rand(0, $width), mt_rand(0, $height), mt_rand(0, $width), mt_rand(0, $height), $lineColor);
        }

        for ($i = 0; $i < strlen($code); $i++) {
            $x = 20 + $i * 25 + mt_rand(-5, 5);
            $y = mt_rand(20, 30);
            imagestring($image, 5, $x, $y, $code[$i], $textColor);
        }

        ob_start();
        imagepng($image);
        $imageData = ob_get_contents();
        ob_end_clean();
        imagedestroy($image);

        $base64 = 'data:image/png;base64,' . base64_encode($imageData);

        return [
            'code' => strtolower($code),
            'image' => $base64
        ];
    }

    protected function generateCode(int $length): string
    {
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789';
        $code = '';
        for ($i = 0; $i < $length; $i++) {
            $code .= $chars[mt_rand(0, strlen($chars) - 1)];
        }
        return $code;
    }

    protected function generateKey(): string
    {
        return md5(uniqid((string)mt_rand(), true) . microtime());
    }
}

<?php


namespace Sparrow;



use JetBrains\PhpStorm\Pure;

class Captcha
{
    /**
     * @param int $length
     * @return string
     * @throws \Exception
     */

    public function getCaptchaCode(int $length): string
    {
        $randomAlpha = md5(random_bytes(64));
        $captchaCode = substr($randomAlpha, 0, $length);
        Session::set('captchaCode', $captchaCode);
        return $captchaCode;
    }

    /**
     * @param $captchaCode
     * @return \GdImage|bool
     */

    public function createCaptchaImage($captchaCode): \GdImage|bool
    {
        $targetLayer = imagecreatetruecolor(72, 28);
        $captchaBackground = imagecolorallocate($targetLayer, 204, 204, 204);
        imagefill($targetLayer, 0, 0, $captchaBackground);
        $captchaTextColor = imagecolorallocate($targetLayer, 0, 0, 0);
        imagestring($targetLayer, 5, 10, 5, $captchaCode, $captchaTextColor);

        return $targetLayer;
    }

    /**
     * @param $imageData
     */

    public function renderCaptchaImages($imageData) {
        header('Content-type: image/jpeg');
        imagejpeg($imageData);
    }

    /**
     * @param $formData
     * @return bool
     */

    #[Pure]
    public function validateCaptcha($formData): bool
    {
        $isValid = false;
        $captchaSessionData = Session::get('captchaCode');

        if ($captchaSessionData == $formData)
            $isValid = true;

        return $isValid;
    }
}
<?php
namespace x51\yii2\modules\editorjs\classes;

class Compare
{
    const READ_LEN = 4096;
    public static function identicalFiles($fn1, $fn2)
    {

        if (filetype($fn1) !== filetype($fn2)) {
            return false;
        }

        if (filesize($fn1) !== filesize($fn2)) {
            return false;
        }

        if (!$fp1 = fopen($fn1, 'rb')) {
            return false;
        }

        if (!$fp2 = fopen($fn2, 'rb')) {
            fclose($fp1);
            return false;
        }

        $same = true;
        while (!feof($fp1) && !feof($fp2)) {
            if (fread($fp1, static::READ_LEN) !== fread($fp2, static::READ_LEN)) {
                $same = false;
                break;
            }
        }

        if (feof($fp1) !== feof($fp2)) {
            $same = false;
        }

        fclose($fp1);
        fclose($fp2);

        return $same;
    } // end identicalFiles
}

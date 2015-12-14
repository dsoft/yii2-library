<?php

namespace abcms\library\fields;

use Yii;
use yii\helpers\StringHelper;
use yii\helpers\FileHelper;
use abcms\library\helpers\Image;
use yii\helpers\Html;

/**
 * Image File Field
 */
class ImageFileField extends FileField
{

    /**
     * @inherit
     */
    public $folder = 'uploads/files/images/';

    /**
     * @inherit
     */
    public $extensions = ['png', 'jpg'];

    /**
     * @var array additional image sizes
     * Example:
     * [
     *   'thumbs' => [
     *       'width' => 280,
     *       'height' => 280,
     *   ],
     *   'main' => [
     *       'width' => 460,
     *   ],
     * ]
     */
    public $sizes = [];

    /**
     * @inherit
     */
    protected function returnFileName()
    {
        return 'image';
    }

    /**
     * @inherit
     */
    protected function afterFileSave()
    {
        parent::afterFileSave();
        $folder = Yii::getAlias('@webroot/'.$this->folder);
        $this->saveSizes($folder, $this->value);
    }

    /**
     * Save additional sizes
     * @param string $mainFolder
     * @param string $imageName
     * @throws ErrorException if can't create folders
     */
    private function saveSizes($mainFolder, $imageName)
    {
        $options = array();
        if(StringHelper::endsWith($imageName, 'jpg', false) || StringHelper::endsWith($imageName, 'jpeg', false)) {
            // Keep good quality if image is jpeg
            $options = array('quality' => 95);
        }
        $sizes = (array) $this->sizes;
        foreach($sizes as $name => $size) {
            if(isset($size['width']) || isset($size['height'])) {
                $folderName = $mainFolder.$name.'/';
                if(FileHelper::createDirectory($folderName)) {
                    $width = (isset($size['width'])) ? $size['width'] : 0;
                    $height = (isset($size['height'])) ? $size['height'] : 0;
                    if(!$width || !$height) {
                        Image::resize($mainFolder.$imageName, $width, $height)->save($folderName.$imageName, $options);
                    }
                    else {
                        Image::thumbnail($mainFolder.$imageName, $width, $height)->save($folderName.$imageName, $options);
                    }
                }
                else {
                    throw new ErrorException('Unable to create directoy.');
                }
            }
        }
    }

    /**
     * @inherit
     */
    public function detailViewAttribute()
    {
        $link = $this->getFileLink();
        if(!$link) {
            return [
                'attribute' => $this->attribute,
                'value' => NULL,
            ];
        }
        $array = [
            'attribute' => $this->attribute,
            'value' => $link,
            'format' => ['image', ['width' => 200]],
        ];
        return $array;
    }

    /**
     * @inherit
     */
    public function renderInput()
    {
        $html = parent::renderInput();
        if($this->value) {
            $html .= Html::img($this->getFileLink(), ['width' => '100']);
        }
        return $html;
    }

}
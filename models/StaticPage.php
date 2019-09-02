<?php
namespace x51\yii2\modules\editorjs\models;
use \yii\base\DynamicModel;
use \yii\helpers\FileHelper;
use \Yii;

class StaticPage extends DynamicModel{
    
    protected $staticPageDir;
    protected $ext;
    protected $isNewFile = true;
    protected $_name;
    protected $_saved = false;

    
    public function __construct($staticPageDir, $ext, $name) {
        $this->staticPageDir = FileHelper::normalizePath(Yii::getAlias($staticPageDir));
        $this->ext = $ext;
        $name = $this->cleanupStaticPageName($name);
        $this->_name = $name;

        if (is_file($staticPageDir . DIRECTORY_SEPARATOR . $name . $ext)) {
            $this->isNewFile = false;
        }
        parent::__construct(['name' => $name, 'content' => null]);
    } // end construct

     /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'content'], 'required'],
            [['content', 'name'], 'string'],
            [['name'], 'string', 'max' => 120],            
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'name' => Yii::t('module/editorjs', 'Name'),
            'content' => Yii::t('module/editorjs', 'Content'),            
        ];
    }

    public function __get($name)
    {
        if ($name == 'content') {
            $content = parent::__get($name);
            if ($content == null) {
                $content = $this->getFileContent();
                parent::__set('content', $content);
            }
            return $content;
        }        
        return parent::__get($name);
    }

    public function __set($name, $value) {
        if ($name == 'name') {
            $value = $this->cleanupStaticPageName($value);
        }
        parent::__set($name, $value);
    }

    
    /**
     * {@inheritdoc}
     */
    public function save($runValidation = true, $attributeNames = null)
    {
        if ($runValidation && !$this->validate($attributeNames)) {
            return false;
        }

        $dirSepPos = strrpos($this['name'], DIRECTORY_SEPARATOR);
        if ($dirSepPos != false) {
            $subdir = substr($this['name'], 0, $dirSepPos);
            $fullDir = $this->staticPageDir . DIRECTORY_SEPARATOR . $subdir;
            if (!is_dir($fullDir)) {
                mkdir($fullDir, 0755, true);
            }
        }
        
        file_put_contents($this->staticPageDir . DIRECTORY_SEPARATOR . $this['name'] . $this->ext, $this['content'], LOCK_EX);
        if (!$this->isNewFile && $this->_name != $this['name']) {
            unlink($this->staticPageDir . DIRECTORY_SEPARATOR . $this->_name . $this->ext);
        }
        $this->_saved = true;
        return true;
    } // end save

    public function exists() {
        $f = $this->staticPageDir . DIRECTORY_SEPARATOR . $this['name'] . $this->ext;
        return is_file($f);
    }

    public function delete() {
        if ($this->_saved) {
            $f = $this->staticPageDir . DIRECTORY_SEPARATOR . $this['name'] . $this->ext;
        } else {
            $f = $this->staticPageDir . DIRECTORY_SEPARATOR . $this->_name . $this->ext;
        }
        unlink($f);
    }

    public function cleanupStaticPageName($name) {
        if ($name) {
            if (substr($name, 0, 1) == DIRECTORY_SEPARATOR) {
                $name = substr($name, 1);
            }
            return str_replace(['../'], [''], $name);
        }
        return $name;
    }

    protected function getFileContent() {
        $f = $this->staticPageDir . DIRECTORY_SEPARATOR . $this['name'] . $this->ext;
        if (is_file($f)) {
            return file_get_contents($f, false);    
        }
        return '';
    }





} // end class
<?php

class KindEditor extends CInputWidget
{
    /**
     * @var string
     */
    private $language = 'zh_CN';

    /**
     * @var string
     *
     */
    public $textAreaId;

    /**
     * @var array
     */
    public $options = array();

    /**
     * @var array
     */
    protected $defaultOptions = array();

    /**
     *
     */
    public function init()
    {
        $assets = self::getAssetsPath();

        parent::init();

        $this->defaultOptions = array(
            'cssPath' => $assets . '/plugins/code/prettify.css',
            'allowFileManager' => true,
            'afterCreate' => 'js:function() {
			var self = this;
			KindEditor.ctrl(document, 13, function() {
				self.sync();
				KindEditor("form")[0].submit();
			});
			KindEditor.ctrl(self.edit.doc, 13, function() {
				self.sync();
				KindEditor("form")[0].submit();
			});
		}'
        );
    }

    /**
     * @static
     * @return string
     */
    public static function getAssetsPath()
    {
        $baseDir = dirname(__FILE__);
        return Yii::app()->getAssetManager()->publish($baseDir . DIRECTORY_SEPARATOR . 'assets');
    }

    /**
     * @return KindEditor
     */
    public function publishAssets()
    {
        $assets = self::getAssetsPath();

        $cs = Yii::app()->getClientScript();
        $cs->registerCssFile($assets . '/themes/default/default.css')
            ->registerCssFile($assets . '/plugins/code/prettify.css')
            ->registerScriptFile($assets . '/kindeditor.js', CClientScript::POS_HEAD)
            ->registerScriptFile($assets . '/lang/zh_CN.js', CClientScript::POS_HEAD)
            ->registerScriptFile($assets . '/plugins/code/prettify.js', CClientScript::POS_HEAD);

        return $this;
    }

    /**
     *
     */
    public function  run()
    {
        $this->publishAssets();

        /**
         * you can use it just for giving a textAreaId for the existing textArea input
         */
        if (!isset($this->textAreaId)) {
            if ($this->hasModel()) {
                echo CHtml::activeTextArea($this->model, $this->attribute, $this->htmlOptions);
                // list($name, $id) = $this->resolveNameID();
                $this->textAreaId = CHtml::activeId($this->model, $this->attribute);

            } elseif (!isset($this->textAreaId)) {
                echo CHtml::textArea($this->name, $this->value, $this->htmlOptions);
                $this->textAreaId = CHtml::getIdByName($this->name);
            }
            if (isset($this->htmlOptions['id'])) {
                $this->textAreaId = $this->htmlOptions['id'];
            }
        }


        $options = CJavaScript::encode(CMap::mergeArray($this->defaultOptions, $this->options));

        $script = <<<EOP

$(function() {
	var editor = KindEditor.create('textarea[id="{$this->textAreaId}"]', {$options});
	prettyPrint();
	});

EOP;

        Yii::app()->getClientScript()->registerScript(__CLASS__ . '#' . $this->textAreaId, $script, CClientScript::POS_HEAD);
    }

    /**
     * @param $name
     * @param $value
     */
    public function __set($name, $value)
    {
        try {
            //shouldn't swallow the parent ' __set operation
            parent::__set($name, $value);
        } catch (Exception $e) {
            $this->options[$name] = $value;
        }
    }

}

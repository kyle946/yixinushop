<?php

/**
 * 扩展标签库
 *
 * @author kyle <316686606@qq.com>
 */

namespace models;

class taglib {

    //模板变量
    public $tplvar = array();
    public $tags = array(
        //attr 属性列表 ，type 标签类型：1为<list></list>； 2为<list />
        'pr' => array('attr' => '', 'type' => 2), //随机打印颜色标签
        '2pr' => array('attr' => '', 'type' => 2), //随机打印颜色标签
    );

    /**
     * 输出随机颜色
     * @return string
     */
    public function _pr() {
        return '<?php echo printColor(); ?>';
    }

    public function _2pr() {
        return '<?php echo printColor2(); ?>';
    }

    public function getall() {
        return $this->tags;
    }

}

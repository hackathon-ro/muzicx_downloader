<?php

/**
 * Controller is the customized base controller class.
 * All controller classes for this application should extend from this base class.
 */
class Controller extends CController {

    /**
     * @var string the default layout for the controller view. Defaults to '//layouts/column1',
     * meaning using a single column layout. See 'protected/views/layouts/column1.php'.
     */
    public $layout = '//layouts/column1';

    /**
     * @var array context menu items. This property will be assigned to {@link CMenu::items}.
     */
    public $menu = array();

    /**
     * @var array the breadcrumbs of the current page. The value of this property will
     * be assigned to {@link CBreadcrumbs::links}. Please refer to {@link CBreadcrumbs::links}
     * for more details on how to specify this property.
     */
    public $breadcrumbs = array();
    public $startTime;

    public function init() {
        $this->startTime = microtime(true);

        parent::init();
    }

    /**
     * @return array action filters
     */
    public function filters() {
        return array(
            'accessControl',
        );
    }

    public function filterAccessControl($filterChain) {
        $filter = new CAccessControlFilter;
        $rules = $this->accesRulesByAction($filterChain->action);
        $filter->setRules($rules);
        $filter->filter($filterChain);
    }

    public function accesRulesByAction($action) {
        $allowed = false;

        $amap = self::$accessMap;

        $controller = Yii::app()->controller->id;
        $action = trim($action->getId());

        if (isset($amap[$controller]) && isset($amap[$controller][$action]) && $amap[$controller][$action] != '') {
            $allowed = UserIdentity::check($amap[$controller][$action]);
        }

        if ($allowed)
            return array(array('allow', 'users' => array('*')));
        else
            return array(array('deny', 'users' => array('*')));
    }

    public function hideInaccessibleItems($items) {
        foreach ($items as $i => $item) {
            if (isset($item['items'])) {
                $items[$i]['items'] = $this->hideInaccessibleItems($item['items']);
            }
            if (empty($items[$i]['items']) && !$this->itemAccessible($item)) {
                unset($items[$i]);
            }
        }
        return array_values($items);
    }

    public function itemAccessible($item) {
        if (!isset($item['url'][0]))
            return false;

        $route = trim($item['url'][0], '/');

        if ($route === '')
            $route = $this->getId() . '/' . $this->getAction()->getId();
        else if (strpos($route, '/') === false)
            $route = $this->getId() . '/' . $route;
        list($controller, $action) = explode('/', $route, 2);

        $controller = strtolower($controller);

        $amap = self::$accessMap;
        $allowed = false;
        if (isset($amap[$controller]) && isset($amap[$controller][$action]) && $amap[$controller][$action] != '') {
            $allowed = UserIdentity::check($amap[$controller][$action]);
        }
        return $allowed;
    }

    public static $accessMap = array(
    );

}
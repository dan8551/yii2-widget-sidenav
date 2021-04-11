<?php

/**
 * @copyright Copyright &copy; Daniel Orton, 2021
 * @package yii2-widgets
 * @subpackage yii2-widget-sidenav
 * @version 1.0.0
 */

namespace dan8551\sidenav;

use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;

/**
 * A custom extended side navigation menu extending Yii Menu
 *
 * For example:
 *
 * ```php
 * echo SideNav::widget([
 *     'items' => [
 *         [
 *             'url' => ['/site/index'],
 *             'label' => 'Home',
 *             'icon' => 'home'
 *         ],
 *         [
 *             'url' => ['/site/about'],
 *             'label' => 'About',
 *             'icon' => 'info-sign',
 *             'items' => [
 *                  ['url' => '#', 'label' => 'Item 1'],
 *                  ['url' => '#', 'label' => 'Item 2'],
 *             ],
 *         ],
 *     ],
 * ]);
 * ```
 *
 * @author Daniel Orton <dan.orton84@gmail.com>
 */
class SideNav extends \yii\widgets\Menu
{
    public $heading;
    
    public $headingOptions = ['class' => 'rounded-corners'];
    
    public $containerOptions = ['class' => 'border-right', 'id' => 'sidebar-wrapper'];
    
    public $bodyOptions = ['class' => 'list-group-flush rounded-corners'];
    
    public $itemOptions = ['class' => 'list-group-item list-group-item-action '];
    
    public $expanding = false;
    public $expandingOptions = [];
    
    /**
     * @var string the template used to render the body of a menu which is a link.
     * In this template, the token `{url}` will be replaced with the corresponding link URL;
     * while `{label}` will be replaced with the link text.
     * This property will be overridden by the `template` option set in individual menu items via [[items]].
     */
    public $linkTemplate = '{label}';
    
    /**
     * @var string prefix for the icon in [[items]]. This string will be prepended
     * before the icon name to get the icon CSS class. This defaults to `glyphicon glyphicon-`
     * for usage with glyphicons available with Bootstrap.
     */
    public $iconPrefix = 'glyphicon glyphicon-';
    
    /**
     * @var string indicator for a menu sub-item
     */
    public $indItem = '&raquo; ';
    
    public function init()
    {
        parent::init();
        SideNavAsset::register($this->getView());
    }
    /**
     * Renders the side navigation menu.
     * with the heading and panel containers
     */
    public function run()
    {
        $heading = '';
        if (isset($this->heading) && $this->heading != '') {
            $this->headingOptions['class'] = (array_key_exists('class', $this->headingOptions)) ? $this->headingOptions['class'] . ' sidebar-heading' : 'sidebar-heading';
            if($this->expanding)
            {
                $this->headingOptions['data-toggle'] = 'collapse';
                $this->headingOptions['href'] = '#'.$this->expandingOptions['name'];
                $this->bodyOptions['class'] .= ' collapse';
                $this->bodyOptions['id'] = $this->expandingOptions['name'];
                $heading = Html::tag('a', $this->heading, $this->headingOptions);
            }
            else
                $heading = Html::tag('div', $this->heading, $this->headingOptions);
        }
        $body = Html::tag('div', $this->renderMenu(), $this->bodyOptions);
        echo Html::tag('div', $heading . $body, $this->containerOptions);
    }
    
    /**
     * Renders the main menu
     */
    protected function renderMenu()
    {
        if ($this->route === null && Yii::$app->controller !== null) {
            $this->route = Yii::$app->controller->getRoute();
        }
        if ($this->params === null) {
            $this->params = $_GET;
        }
        $items = $this->normalizeItems($this->items, $hasActiveChild);
        $options = $this->options;
        $tag = ArrayHelper::remove($options, 'tag', 'ul');
//        return Html::tag($tag, $this->renderItems($items), $options);
        return $this->renderItems($items);
    }
    
    protected function renderItems($items)
    {
        $n = count($items);
        $lines = [];
        foreach ($items as $i => $item) {
            $options = array_merge($this->itemOptions, ArrayHelper::getValue($item, 'options', []));
            $tag = ArrayHelper::remove($options, 'tag', 'a');
            $class = [];
            if ($item['active']) {
                $class[] = $this->activeCssClass;
            }
            if ($i === 0 && $this->firstItemCssClass !== null) {
                $class[] = $this->firstItemCssClass;
            }
            if ($i === $n - 1 && $this->lastItemCssClass !== null) {
                $class[] = $this->lastItemCssClass;
            }
            Html::addCssClass($options, $class);

//            $menu = $this->renderItem($item);
            $menu = $item['label'];
            $options['href'] = Url::to($item['url']);
            if (!empty($item['items'])) {
                $submenuTemplate = ArrayHelper::getValue($item, 'submenuTemplate', $this->submenuTemplate);
                $menu .= strtr($submenuTemplate, [
                    '{items}' => $this->renderItems($item['items']),
                ]);
            }
            $lines[] = Html::tag($tag, $menu, $options);
        }
        return implode("\n", $lines);
    }
    
    /**
     * Renders the content of a side navigation menu item.
     *
     * @param array $item the menu item to be rendered. Please refer to [[items]] to see what data might be in the item.
     * @return string the rendering result
     * @throws InvalidConfigException
     */
    protected function renderItem($item)
    {
        $template = ArrayHelper::getValue($item, 'template', $this->linkTemplate);
        $url = Url::to(ArrayHelper::getValue($item, 'url', '#'));
        if (empty($item['top'])) {
            if (empty($item['items'])) {
                $template = str_replace('{icon}', $this->indItem . '{icon}', $template);
            } else {
                $template = isset($item['template']) ? $item['template'] :'<a href="{url}" class="kv-toggle">{icon}{label}</a>';
                $openOptions = ($item['active']) ? ['class' => 'opened'] : ['class' => 'opened', 'style' => 'display:none'];
                $closeOptions = ($item['active']) ? ['class' => 'closed', 'style' => 'display:none'] : ['class' => 'closed'];
                $indicator = Html::tag('span', $this->indMenuOpen, $openOptions) . Html::tag('span', $this->indMenuClose, $closeOptions);
                $template = str_replace('{icon}', $indicator . '{icon}', $template);
            }
        }
        $icon = empty($item['icon']) ? '' : '<span class="' . $this->iconPrefix . $item['icon'] . '"></span> &nbsp;';
        unset($item['icon'], $item['top']);
        return strtr($template, [
            '{url}' => $url,
            '{label}' => $item['label'],
            '{icon}' => $icon
        ]);
    }
}

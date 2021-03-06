<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2018
 * @package Client
 * @subpackage Html
 * @author Marian Deimel
 */


namespace Aimeos\Client\Html\Catalog\Compare;


/**
 * Default implementation of catalog compare HTML client
 *
 * @package Client
 * @subpackage Html
 */
class Standard
    extends \Aimeos\Client\Html\Catalog\Base
    implements \Aimeos\Client\Html\Common\Client\Factory\Iface
{
    /** client/html/catalog/compare/standard/subparts
     * List of HTML sub-clients rendered within the catalog compare section
     *
     * The output of the frontend is composed of the code generated by the HTML
     * clients. Each HTML client can consist of serveral (or none) sub-clients
     * that are responsible for rendering certain sub-parts of the output. The
     * sub-clients can contain HTML clients themselves and therefore a
     * hierarchical tree of HTML clients is composed. Each HTML client creates
     * the output that is placed inside the container of its parent.
     *
     * At first, always the HTML code generated by the parent is printed, then
     * the HTML code of its sub-clients. The order of the HTML sub-clients
     * determines the order of the output of these sub-clients inside the parent
     * container. If the configured list of clients is
     *
     *  array( "subclient1", "subclient2" )
     *
     * you can easily change the order of the output by reordering the subparts:
     *
     *  client/html/<clients>/subparts = array( "subclient1", "subclient2" )
     *
     * You can also remove one or more parts if they shouldn't be rendered:
     *
     *  client/html/<clients>/subparts = array( "subclient1" )
     *
     * As the clients only generates structural HTML, the layout defined via CSS
     * should support adding, removing or reordering content by a fluid like
     * design.
     *
     * @param array List of sub-client names
     * @since 2018.04
     * @category Developer
     */
    private $subPartPath = 'client/html/catalog/compare/standard/subparts';
    private $subPartNames = [];
    private $expire;
    private $tags = [];
    private $view;

    public function getBody($uid = '')
    {
        $context = $this->getContext();
        $view = $this->getView();

        try {
            if (!isset($this->view)) {
                $view = $this->view = $this->getObject()->addData($view, $this->tags, $this->expire);
            }

            $html = '';
            foreach ($this->getSubClients() as $subClient) {
                $html .= $subClient->setView($view)->getBody($uid);
            }
            $view->detailBody = $html;
        } catch (\Aimeos\Client\Html\Exception $e) {
            $error = [$context->getI18n()->dt('client/html', $e->getMessage())];
            $view->compareErrorList = $view->get('compareErrorList', []) + $error;
        } catch (\Aimeos\Controller\Frontend\Exception $e) {
            $error = [$context->getI18n()->dt('controller/frontend', $e->getMessage())];
            $view->compareErrorList = $view->get('compareErrorList', []) + $error;
        } catch (\Aimeos\MShop\Exception $e) {
            $error = [$context->getI18n()->dt('mshop', $e->getMessage())];
            $view->compareErrorList = $view->get('compareErrorList', []) + $error;
        } catch (Exception $e) {
            $context->getLogger()->log($e->getMessage() . PHP_EOL . $e->getTraceAsString());

            $error = [$context->getI18n()->dt('client/html', 'A non-recoverable error occured')];
            $view->compareErrorList = $view->get('compareErrorList', []) + $error;
        }

        $tplConf = 'client/html/catalog/compare/standard/template-body';
        $default = 'catalog/compare/body-standard';

        $html = $view->render($view->config($tplConf, $default));

        return $html;
    }

    public function getHeader($uid = '')
    {
        $view = $this->getView();

        try {
            if (!isset($this->view)) {
                $view = $this->view = $this->getObject()->addData($view, $this->tags, $this->expire);
            }

            $html = '';
            foreach ($this->getSubClients() as $subClient) {
                $html .= $subClient->setView($view)->getHeader($uid);
            }
            $view->detailHeader = $html;
        } catch (Exception $e) {
            $this->getContext()->getLogger()->log($e->getMessage() . PHP_EOL . $e->getTraceAsString());
            return;
        }

        $tplConf = 'client/html/catalog/compare/standard/template-header';
        $default = 'catalog/compare/header-standard';

        $html = $view->render($view->config($tplConf, $default));

        return $html;
    }

    /**
     * Returns the sub-client given by its name.
     *
     * @param string $type Name of the client type
     * @param string|null $name Name of the sub-client (Default if null)
     * @return \Aimeos\Client\Html\Iface Sub-client object
     */
    public function getSubClient($type, $name = null)
    {
        /** client/html/catalog/compare/decorators/excludes
         * Excludes decorators added by the "common" option from the catalog filter compare html client
         *
         * Decorators extend the functionality of a class by adding new aspects
         * (e.g. log what is currently done), executing the methods of the underlying
         * class only in certain conditions (e.g. only for logged in users) or
         * modify what is returned to the caller.
         *
         * This option allows you to remove a decorator added via
         * "client/html/common/decorators/default" before they are wrapped
         * around the html client.
         *
         *  client/html/catalog/compare/decorators/excludes = array( 'decorator1' )
         *
         * This would remove the decorator named "decorator1" from the list of
         * common decorators ("\Aimeos\Client\Html\Common\Decorator\*") added via
         * "client/html/common/decorators/default" to the html client.
         *
         * @param array List of decorator names
         * @since 2018.04
         * @category Developer
         * @see client/html/common/decorators/default
         * @see client/html/catalog/compare/decorators/global
         * @see client/html/catalog/compare/decorators/local
         */
        /** client/html/catalog/compare/decorators/global
         * Adds a list of globally available decorators only to the catalog filter compare html client
         *
         * Decorators extend the functionality of a class by adding new aspects
         * (e.g. log what is currently done), executing the methods of the underlying
         * class only in certain conditions (e.g. only for logged in users) or
         * modify what is returned to the caller.
         *
         * This option allows you to wrap global decorators
         * ("\Aimeos\Client\Html\Common\Decorator\*") around the html client.
         *
         *  client/html/catalog/compare/decorators/global = array( 'decorator1' )
         *
         * This would add the decorator named "decorator1" defined by
         * "\Aimeos\Client\Html\Common\Decorator\Decorator1" only to the html client.
         *
         * @param array List of decorator names
         * @since 2018.04
         * @category Developer
         * @see client/html/common/decorators/default
         * @see client/html/catalog/compare/decorators/excludes
         * @see client/html/catalog/compare/decorators/local
         */
        /** client/html/catalog/compare/decorators/local
         * Adds a list of local decorators only to the catalog filter compare html client
         *
         * Decorators extend the functionality of a class by adding new aspects
         * (e.g. log what is currently done), executing the methods of the underlying
         * class only in certain conditions (e.g. only for logged in users) or
         * modify what is returned to the caller.
         *
         * This option allows you to wrap local decorators
         * ("\Aimeos\Client\Html\Catalog\Decorator\*") around the html client.
         *
         *  client/html/catalog/compare/decorators/local = array( 'decorator2' )
         *
         * This would add the decorator named "decorator2" defined by
         * "\Aimeos\Client\Html\Catalog\Decorator\Decorator2" only to the html client.
         *
         * @param array List of decorator names
         * @since 2018.04
         * @category Developer
         * @see client/html/common/decorators/default
         * @see client/html/catalog/compare/decorators/excludes
         * @see client/html/catalog/compare/decorators/global
         */
        return parent::getSubClient($type, $name);
    }

    public function process()
    {
        $context = $this->getContext();
        $session = $context->getSession();
        $view = $this->getView();
        $compareList = $session->get('aimeos/catalog/session/compare/list', []);
        $refresh = false;
        try {
            switch ($view->param('comp_action', '')) {
                case 'add':

                    foreach ((array)$view->param('comp_id', []) as $id) {
                        $compareList[$id] = $id;
                    }

                    /** client/html/catalog/session/compare/standard/maxitems
                     * Maximum number of products displayed in the "compare" section
                     *
                     * This option limits the number of products that are shown in the
                     * "compare" section after the users added the product to their list
                     * of compared products. It must be a positive integer value greater
                     * than 0.
                     *
                     * Note: The higher the value is the more data has to be transfered
                     * to the client each time the user loads a page with the list of
                     * compared products.
                     *
                     * @param integer Number of products
                     * @since 2019.07
                     * @category User
                     * @category Developer
                     */
                    $max = $context->getConfig()->get('client/html/catalog/session/compare/standard/maxitems', 4);

                    $compareList = array_slice($compareList, -$max, $max, true);
                    $refresh = true;
                    break;
                case 'delete':
                    foreach ((array)$view->param('comp_id', []) as $id) {
                        unset($compareList[$id]);
                    }
                    $refresh = true;
                    break;
            }

            if ($refresh) {
                $session->set('aimeos/catalog/session/compare/list', $compareList);
            }

            parent::process();
        } catch (\Exception $e) {
            $context->getLogger()->log($e->getMessage() . PHP_EOL . $e->getTraceAsString());

            $error = [$context->getI18n()->dt('client/html', 'A non-recoverable error occured')];
            $view->compareErrorList = $view->get('compareErrorList', []) + $error;
        }
    }

    /**
     * Returns the names of the subpart clients
     *
     * @return array List of client names
     */
    protected function getSubClientNames()
    {
        return $this->getContext()->getConfig()->get($this->subPartPath, $this->subPartNames);
    }

    public function addData(\Aimeos\MW\View\Iface $view, array &$tags = [], &$expire = null)
    {
        $context = $this->getContext();
        $config = $context->getConfig();
        $session = $context->getSession();
        $domains = ['attribute', 'media', 'price', 'product', 'text'];

        /** client/html/catalog/session/compare/domains
         * A list of domain names whose items should be available in the compare view template for the product
         *
         * The templates rendering product details usually add the images,
         * prices and texts, etc. associated to the product
         * item. If you want to display additional or less content, you can
         * configure your own list of domains (attribute, media, price, product,
         * text, etc. are domains) whose items are fetched from the storage.
         * Please keep in mind that the more domains you add to the configuration,
         * the more time is required for fetching the content!
         *
         * @param array List of domain names
         * @since 2019.07
         * @category Developer
         * @see client/html/catalog/domains
         * @see client/html/catalog/lists/domains
         * @see client/html/catalog/detail/domains
         */
        $domains = $config->get('client/html/catalog/session/compare/domains', $domains);

        /** client/html/catalog/compare/attributes/type-map
         * A list of attribute types whose items should be displayed in the compare view template for the product
         *
         * @param array List of Attribute type names
         * @since 2019.07
         * @category Developer
         */
        $typeMap = $config->get('client/html/catalog/compare/attributes/type-map', []);

        $attributeTypeMap = [];
        $comparedProductItems = [];
        if (($compared = $session->get('aimeos/catalog/session/compare/list', [])) !== [] && $typeMap !== []) {

            /** @var \Aimeos\Controller\Frontend\Product\Standard $productController */
            $productController = \Aimeos\Controller\Frontend::create($context, 'product');
            $result = $productController->uses($domains)->product($compared)->slice(0, count($compared))->search();
            foreach (array_reverse($compared) as $id) {
                /** @var \Aimeos\MShop\Product\Item\Standard $product */
                if ($product = $result[$id]) {
                    $comparedProductItems[$id] = $product;

                    foreach (array_reverse($typeMap) as $type) {
                        /** @var \Aimeos\MShop\Attribute\Item\Standard $attribute */
                        $attribute = current($product->getRefItems('attribute', $type));
                        $attributeTypeMap[$id][$type] = $attribute;
                    }
                }
            }
        }

        $view->attributeTypeMap = $attributeTypeMap;
        $view->comparedProductItems = $comparedProductItems;

        return parent::addData($view, $tags, $expire);
    }
}

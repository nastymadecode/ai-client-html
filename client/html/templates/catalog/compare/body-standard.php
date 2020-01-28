<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Metaways Infosystems GmbH, 2012
 * @copyright Aimeos (aimeos.org), 2015-2018
 * @author Marian Deimel
 */
$enc = $this->encoder();

$optionTarget = $this->config('client/jsonapi/url/target');
$optionController = $this->config('client/jsonapi/url/controller', 'jsonapi');
$optionAction = $this->config('client/jsonapi/url/action', 'options');
$optionConfig = $this->config('client/jsonapi/url/config', []);

$detailTarget = $this->config('client/html/catalog/detail/url/target');
$detailController = $this->config('client/html/catalog/detail/url/controller', 'catalog');
$detailAction = $this->config('client/html/catalog/detail/url/action', 'detail');
$detailConfig = $this->config('client/html/catalog/detail/url/config', []);
$detailFilter = array_flip($this->config('client/html/catalog/detail/url/filter', ['d_prodid']));

$compareTarget = $this->config('client/html/catalog/compare/url/target');
$compareController = $this->config('client/html/catalog/compare/url/controller', 'catalog');
$compareAction = $this->config('client/html/catalog/compare/url/action', 'compare');
$compareConfig = $this->config('client/html/catalog/compare/url/config', []);

$typeMap = $this->config('client/html/catalog/compare/attributes/type-map', []);

$comparedProductItems = $this->get('comparedProductItems', []);
$attributeTypeMap = $this->get('attributeTypeMap', []);

/** client/html/basket/require-stock
 * Customers can order products only if there are enough products in stock
 *
 * Checks that the requested product quantity is in stock before
 * the customer can add them to his basket and order them. If there
 * are not enough products available, the customer will get a notice.
 *
 * @param boolean True if products must be in stock, false if products can be sold without stock
 * @since 2014.03
 * @category Developer
 * @category User
 */
$reqstock = (int)$this->config('client/html/basket/require-stock', true);

?>
<section class="aimeos catalog-compare" itemscope="" itemtype="http://schema.org/Product" data-jsonurl="<?= $enc->attr($this->url($optionTarget, $optionController, $optionAction, [], [], $optionConfig)); ?>">

    <?php if (isset($this->comparisonErrorList)) : ?>
        <ul class="error-list">
            <?php foreach ((array)$this->comparisonErrorList as $errMsg) : ?>
                <li class="error-item"><?= $enc->html($errMsg); ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <article class="compare-table-wrapper">
        <table class="compare-table">
            <thead>
            <tr>
                <th scope="row" class="blank">
                    <span class="head">Vergleichsliste</span>
                </th>
                <?php /** @var $productItem \Aimeos\MShop\Product\Item\Standard */
                foreach ($comparedProductItems as $prodId => $productItem): ?>
                    <th>
                        <div class="product-header">
                            <span class="head"><?= $enc->html($productItem->getName(), $enc::TRUST); ?></span>
                            <a class="delete" href="<?= $enc->attr($this->url($compareTarget, $compareController, $compareAction, ['comp_action' => 'delete', 'comp_id' => $prodId], [], $compareConfig)); ?>">X</a>
                        </div>
                    </th>
                <?php endforeach; ?>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($typeMap as $index => $type): ?>
                <tr>
                    <td><?= $enc->html($type, $enc::TRUST); ?></td>
                    <?php /** @var $productItem \Aimeos\MShop\Product\Item\Standard */
                    foreach ($comparedProductItems as $prodId => $productItem): ?>
                        <td>
                            <?php /** @var \Aimeos\MShop\Attribute\Item\Standard $attribute */
                            if ($attribute = $attributeTypeMap[$prodId][$type]): ?>
                                <?= $enc->html($attribute->getName(), $enc::TRUST); ?>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </article>

</section>

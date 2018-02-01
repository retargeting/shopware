<?php
/**
 * @category    Retargeting
 * @package     Retargeting_Tracker
 * @author      Retargeting Team <info@retargeting.biz>
 * @copyright   Copyright (c) Retargeting Biz
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
use Shopware\Bundle\StoreFrontBundle;

class Shopware_Plugins_Frontend_Retargeting_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
    /**
     * Install plugin method
     *
     * @return array|bool
     */
    public function install()
    {

        $this->subscribeEvents();

        $this->registerController('frontend', 'retargeting');

        $this->createConfig();


        return true;
    }

    private function subscribeEvents()
    {
        $this->subscribeEvent('Enlight_Controller_Action_PostDispatchSecure_Frontend',
            //Frontend listener
            'onFrontEndPostDispatch');

        $this->subscribeEvent('Shopware_Modules_Admin_SaveRegister_Successful',
            //Customer successfully registered
            'onModulesAdminSaveRegisterSuccessful');

        $this->subscribeEvent( //Newsletter successfully registered
            'Shopware_Modules_Admin_Newsletter_Registration_Success', 'onModulesAdminNewsletterRegistrationSuccess');

        $this->subscribeEvent('Enlight_Controller_Action_PostDispatchSecure_Frontend_Account',
            //Account Frontend listener
            'onActionPostDispatchSecureFrontendAccount');

        $this->subscribeEvent('Enlight_Controller_Action_PostDispatchSecure_Frontend_Checkout',
            //Checkout Frontend listener
            'onActionPostDispatchSecureFrontendCheckout');

        $this->subscribeEvent('Enlight_Controller_Action_PostDispatchSecure_Frontend_Newsletter',
            'onActionPostDispatchSecureFrontendNewsletter');

        $this->subscribeEvent('Enlight_Controller_Action_PostDispatchSecure_Frontend_Forms',
            //Contact Form Frontend listener
            'onActionPostDispatchSecureFrontendForms');

        $this->subscribeEvent('Enlight_Controller_Action_PostDispatch_Frontend_Ticket', //Contact Form redirect
            'onActionPostDispatchFrontendTicket');

        $this->subscribeEvent('Enlight_Controller_Action_PostDispatchSecure_Frontend_Detail',
            'onActionPostDispatchSecureFrontendDetail');

        $this->subscribeEvent('Shopware_Modules_Basket_AddArticle_Start', 'onModulesBasketAddArticleStart');

        $this->subscribeEvent('Shopware_Modules_Order_SaveOrder_ProcessDetails',
            'onModulesOrderSaveOrderProcessDetails');

        $this->subscribeEvent('Enlight_Controller_Action_PostDispatchSecure_Frontend_Note',
            'onActionPostDispatchSecureFrontendNote');

        $this->subscribeEvent('Enlight_Controller_Action_PostDispatchSecure_Frontend_Custom',
            'onActionPostDispatchSecureFrontendCustom');

        $this->subscribeEvent('Shopware_Controllers_Frontend_Checkout::deleteArticleAction::before',
            'onControllersFrontendCheckoutDeleteArticleActionBefore');
    }

    /**
     * @param \Enlight_Hook_HookArgs $args
     */
    public function onControllersFrontendCheckoutDeleteArticleActionBefore(Enlight_Hook_HookArgs $args)
    {
        /** @var \Shopware_Controllers_Frontend_Checkout $subject */
        $subject = $args->getSubject();
        $request = $subject->Request();
        $itemId = $request->getParam('sDelete');
        $return = $args->getReturn();
        $sql = "SELECT articleID, articlename, quantity
                FROM s_order_basket
                WHERE id=?
                GROUP BY id";
        $params = array($itemId);
        $articleDB = Shopware()->Db()->fetchRow($sql, $params);
        $article = array(
            "product_id" => $articleDB["articleID"],
            "quantity" => $articleDB["quantity"],
            "variation" => false
        );
        Shopware()->Session()->offsetSet("delete", json_encode($article));
        $args->setReturn($return);
    }

    /**
     * @param \Enlight_Event_EventArgs $args
     */
    public function onActionPostDispatchSecureFrontendCustom(Enlight_Event_EventArgs $args)
    {
        /** @var \Shopware_Controllers_Frontend_Custom $subject */
        $subject = $args->getSubject();
        $view = $subject->View();
        $view->addTemplateDir($this->Path() . 'Views/');
        $view->extendsTemplate('frontend/plugins/retargeting/helpPage.tpl');
    }

    /**
     * @param \Enlight_Event_EventArgs $args
     */
    public function onActionPostDispatchSecureFrontendNote(Enlight_Event_EventArgs $args)
    {
        /** @var \Shopware_Controllers_Frontend_Note $subject */
        $subject = $args->getSubject();
        $request = $subject->Request();
        $view = $subject->View();
        $action = $request->getActionName();
        $orderNumber = $request->getParam('ordernumber');
        $server = $request->getServer();

        //sendProduct info before addToWishlist
        $article = Shopware()->Modules()->Articles()->sGetProductByOrdernumber($orderNumber);
        $stock = $article['instock'];
        $articleId = $article['articleID'];
        $articleName = htmlspecialchars($article['articleName']);
        $articleUrl = "http://" . $server['HTTP_HOST'] . $request->getBaseUrl() . '/' . $article['linkDetails'];
        $articleImage = $article['image']['source'];
        $articlePrice = $article['price_numeric'];          //if promo this is the actual price
        $articlePromoPrice = $article['pseudoprice_numeric'];         //if not assign 0
        $articleBrandId = $article['supplierID'];
        $articleBrandName = $article['supplierName'];
        $articleName = htmlspecialchars($article['articleName']);

        $sql = "SELECT ac.categoryID
                FROM s_articles_categories ac
                WHERE ac.articleId=?
                GROUP BY ac.categoryID";
        $params = array($articleId);
        $getCategories = Shopware()->Db()->fetchAll($sql, $params);     //get all Categories for product

        $categoryId = $article['categoryID'];
        $allCategories = array();

        foreach ($getCategories as $categoryId) {
            $categoryId = $categoryId['categoryID'];
            $_categoryParent = 'false'; // if category has no parent, then set it to false
            $_categoryBreadcrumb = '[]'; // and breadCrumb to []  , check documentation for more info
            $categories = Shopware()->Modules()->Categories()->sGetCategoriesByParent($categoryId); // get all categories in path
            $articleCategory = Shopware()->Modules()->Categories()->sGetCategoryContent($categoryId);
            $categoryName = '"' . htmlspecialchars($articleCategory['name']) . '"';
            $categories_no = count($categories);
            if ($categories_no > 1) {
                $_categoryBreadcrumb = array();
                for ($i = 1; $i < $categories_no - 1; $i++) {
                    $id = $categories[$i]['id'];
                    $name = $categories[$i]['name'];
                    $parent_id = $categories[$i + 1]['id'];
                    $_categoryBreadcrumb[] = '{
                            "id": ' . $id . ',
                            "name": "' . $name . '",
                            "parent": ' . $parent_id . '
                        }';
                }
                $_categoryParent = $categories[1]['id'];

                $id = $categories[$categories_no - 1]['id'];
                $name = $categories[$categories_no - 1]['name'];
                $parent_id = "false";

                $_categoryBreadcrumb[] = '{
                        "id": ' . $id . ',
                        "name": "' . $name . '",
                        "parent": ' . $parent_id . '
                    }';

                $_categoryBreadcrumb = '[' . implode(', ', $_categoryBreadcrumb) . ']';
                // $_categoryParent = '[{ ' . $_categoryParent . ', breadcrumb: ' . $_categoryBreadcrumb . ' }]';
            }

            $categoryIds = array("id" => $categoryId);
            $categoryNames = array("name" => $categoryName);
            $categoryParents = array("parent" => $_categoryParent);
            $categoryBreadcrumbs = array("breadcrumb" => $_categoryBreadcrumb);
            $categorii = array(
                $categoryIds,
                $categoryNames,
                $categoryParents,
                $categoryBreadcrumbs
            );
            array_push($allCategories, $categorii);
        }

        if ($action === 'ajaxAdd') {
            $referer = Shopware()->Session()->offsetGet('referer');

            if ($referer === 'category') {
                Shopware()->Session()->offsetUnset('referer');
                $view->extendsTemplate('frontend/plugins/retargeting/clickImage.tpl');
                $product = array(
                    "id" => $articleId,
                    "name" => $articleName,
                    "url" => $articleUrl,
                    "img" => $articleImage,
                    "price",
                    "promo",
                    "brand_id" => $articleBrandId,
                    "brand_name" => $articleBrandName,
                    "category" => $allCategories,
                    "stock"
                );

                if ($articlePromoPrice != 0 && $articlePromoPrice > $articlePrice) {
                    $product['price'] = $articlePromoPrice;
                    $product['promo'] = $articlePrice;
                } else {
                    $product['price'] = $articlePrice;
                    $product['promo'] = 0;
                }
                if ($stock != 0) {
                    $product['stock'] = 'true';
                } else {
                    $product['stock'] = 'false';
                }
                Shopware()->Session()->offsetSet("article", $product);
            } else {
                if ($referer === 'product') {
                    Shopware()->Session()->offsetUnset('referer');
                    Shopware()->Session()->offsetSet("article", $articleId);
                }
            }
        } else {
            if ($action === 'index') {
                $view->addTemplateDir($this->Path() . 'Views/');
                $view->extendsTemplate('frontend/plugins/retargeting/wishlist.tpl');
                $view->assign("article", Shopware()->Session()->offsetGet("article"));
                Shopware()->Session()->offsetUnset("article");
            }
        }
    }


    private function createConfig()
    {
        $this->Form()->setElement('text', 'TrackingAPIKey', array(
                'label' => 'Tracking API Key',
                'required' => true,
            ));

        $this->Form()->setElement('text', 'RESTAPIKey', array(
                'label' => 'REST API Key',
                'required' => true,
            ));

        $this->Form()->setElement('checkbox', 'RecomengCategory', array(
            'label' => 'Recommendation Engine Category Page',
            'required' => false,
            'value' => true,
            'description' => 'Displays Recommendation Engine products carousel on Category Page',
        ));

        $this->Form()->setElement('checkbox', 'RecomengProduct', array(
            'label' => 'Recommendation Engine Product Page',
            'required' => false,
            'value' => true,
            'description' => 'Displays Recommendation Engine products carousel on Product Page',
        ));

        $this->Form()->setElement('checkbox', 'RecomengCheckout', array(
            'label' => 'Recommendation Engine Checkout Page',
            'required' => false,
            'value' => true,
            'description' => 'Displays Recommendation Engine products carousel on Checkout Page',
        ));

        $this->Form()->setElement('checkbox', 'RecomengThankYou', array(
            'label' => 'Recommendation Engine Thank You Page',
            'required' => false,
            'value' => true,
            'description' => 'Displays Recommendation Engine products carousel on Thank You Page',
        ));

        $this->Form()->setElement('text', 'ProductsFeedURL', array(
                'label' => 'Products Feed URL',
                'value' => '/retargeting/products',
                'description' => '/retargeting/products',
                'required' => true,
                'disabled' => true
            ));
    }

    /**
     * @param \Enlight_Event_EventArgs $args
     */
    public function onModulesOrderSaveOrderProcessDetails(Enlight_Event_EventArgs $args)
    {
        $articles = $args->get('details');
        $sOrder = $args->get('subject');
        $sUserData = Shopware()->Modules()->Admin()->sGetUserData();
        $order_no = $sOrder->sOrderNumber;
        $discount = 0;
        $discount_code = '';
        $orderNumberDiscount = '1';
        $shipping = $sOrder->sShippingcostsNumeric;
        $total = $sOrder->sAmount;
        $products = array();
        $paramsAPI = array('orderInfo' => null, 'orderProducts' => array());

        foreach ($articles as $article) {
            if ($article['modus'] === "2") {
                $discount = $article['price'];
                $discount = substr($discount, 1);
                $discount = str_replace(',', '.', $discount);
                $orderNumberDiscount = $article['ordernumber'];
            } else {
                $articleId = $article['articleID'];
                $articlePrice = $article['price'];
                $articlePrice = str_replace(',', '.', $articlePrice);
                $articleQuantity = $article['quantity'];
                $articleName = $article['articlename'];
                $variationCode = '';

                // variations

//                $container = Shopware()->Container();
//                $additionalTextService = Shopware()->Container()->get('shopware_storefront.additional_text_service');
//                $context = $container->get('shopware_storefront.context_service')->getShopContext();
//                $product = Shopware()->Container()->get('shopware_storefront.list_product_service')->get($article['ordernumber'], $context);
//                $product = $additionalTextService->buildAdditionalText($product, $context);

                $products[] = '{
                    "id": ' . $articleId . ',
                    "quantity": ' . $articleQuantity . ',
                    "price": ' . $articlePrice . ',
                    "variation_code": "' . $variationCode . '"}';
                $paramsAPI['orderProducts'][] = array(
                    "id" => $articleId,
                    "quantity" => $articleQuantity,
                    "price" => $articlePrice,
                    "variation_code" => $variationCode
                );
            }
        }
        $voucherModel = Shopware()->Models()->getRepository('Shopware\Models\Voucher\Voucher')->findOneBy(array('orderCode' => $orderNumberDiscount));
        if ($voucherModel) {
            $discount_code = $voucherModel->getVoucherCode();
        }

        $userData = array(
            "order_no" => $order_no,
            "lastname" => $sUserData["billingaddress"]["lastname"],
            "firstname" => $sUserData["billingaddress"]["firstname"],
            "email" => $sUserData["additional"]["user"]["email"],
            "phone" => $sUserData["billingaddress"]["phone"] ? $sUserData["billingaddress"]["phone"] : '',
            "state" => $sUserData["additional"]["state"]["statename"] ? $sUserData["additional"]["state"]["statename"] : '',
            "city" => $sUserData["billingaddress"]["city"] ? $sUserData["billingaddress"]["city"] : '',
            "address" => $sUserData["billingaddress"]["street"],
            "birthday" => $sUserData["additional"]["user"]["birthday"] ? date_format(date_create_from_format('Y-m-d',
                $sUserData["additional"]["user"]["birthday"]), 'd-m-Y') : '',
            "discount" => $discount,
            "discount_code" => $discount_code,
            "shipping" => $shipping,
            "total" => $total,
            "products" => "[" . implode(", ", $products) . "]"
        );

        $paramsAPI['orderInfo'] = array(
            "order_no" => $order_no,
            "lastname" => $sUserData["billingaddress"]["lastname"],
            "firstname" => $sUserData["billingaddress"]["firstname"],
            "email" => $sUserData["additional"]["user"]["email"],
            "phone" => $sUserData["billingaddress"]["phone"] ? $sUserData["billingaddress"]["phone"] : '',
            "state" => $sUserData["additional"]["state"]["statename"] ? $sUserData["additional"]["state"]["statename"] : '',
            "city" => $sUserData["billingaddress"]["city"] ? $sUserData["billingaddress"]["city"] : '',
            "address" => $sUserData["billingaddress"]["street"],
            "birthday" => $sUserData["additional"]["user"]["birthday"] ? date_format(date_create_from_format('Y-m-d',
                $sUserData["additional"]["user"]["birthday"]), 'd-m-Y') : '',
            "discount" => $discount,
            "discount_code" => $discount_code,
            "shipping" => $shipping,
            "total" => $total
        );

        $apiKey = $this->Config()->get('TrackingAPIKey');
        $token = $this->Config()->get('RESTAPIKey');
        if ($apiKey && $apiKey != "" && $token && $token != "") {
            //            require_once "lib/api/Retargeting_REST_API_Client.php";
            require_once $this->Path() . "lib/api/Retargeting_REST_API_Client.php";  //safer

            $retargetingClient = new Retargeting_REST_API_Client($token);
            $retargetingClient->setResponseFormat("json");
            $retargetingClient->setDecoding(false);
            $response = $retargetingClient->order->save($paramsAPI['orderInfo'], $paramsAPI['orderProducts']);
            //            error_log(print_r($response, true)."\n", 3, Shopware()->DocPath() . '/response.log');
        }

        Shopware()->Session()->offsetSet("userData", $userData);
    }

    /**
     * @param \Enlight_Event_EventArgs $args
     */
    public function onModulesBasketAddArticleStart(Enlight_Event_EventArgs $args)
    {
        $subject = $args->get('subject');

        $orderNumber = $args->get('id');
        $quantity = $args->get('quantity');

        $articleId = Shopware()->Modules()->Articles()->sGetArticleIdByOrderNumber($orderNumber);
        $selected = Shopware()->Session()->offsetGet("selected");
        
        $cartData = array(
            "id" => $articleId,
            "quantity" => $quantity,
            "variation" => 'false'
        );
        if ($selected) {
            $old_selected = $selected;
            $variationsCode = array();
            $instock = 0;
            $_variationsDetails = array();
            foreach ($old_selected as $selection) {
                $variationsCode[] = $selection['option_name'];
                $instock = $selection['stock'];
                $_variationsDetails[] = '"' . $selection['option_name'] . '": {
                    "category_name": "' . htmlspecialchars($selection['group_name']) . '",
                    "category" : "' . htmlspecialchars($selection['group_name']) . '",
                    "value" : "' . htmlspecialchars($selection['option_name']) . '"
                }';
            }
            $stock = false;
            if ($instock) {
                $stock = true;
            }
            $code = implode('-', $variationsCode);
            $variationsDetails = implode(', ', $_variationsDetails);
            $cartData['variation'] = '{
                 "code": "' . $code . '",
                 "stock": "' . $stock . '",
                 "details": {
                    ' . $variationsDetails . '
                 }
            }';
        } else {
            $cartData['variation'] = 'false';
        }
        Shopware()->Session()->offsetSet("cartData", $cartData);
    }

    /**
     * @param \Enlight_Event_EventArgs $args
     */
    public function onActionPostDispatchSecureFrontendDetail(Enlight_Event_EventArgs $args)
    {
        /** @var \Shopware_Controllers_Frontend_Detail $subject */
        $subject = $args->getSubject();
        $request = $subject->Request();
        $view = $subject->View();
        $article = $view->getAssign('sArticle');
        $stock = $article['instock'];
        $selected = array();
        $cartDataProducts = array();
        foreach ($article['sConfigurator'] as $configuratorGroup) {
            foreach ($configuratorGroup['values'] as $option) {
                if ($option['selected']) {
                    $selection = array(
                        "group_name" => $configuratorGroup['groupname'],
                        "option_name" => $option['optionname'],
                        "option_selected" => $option['selected'],
                        "stock" => $stock
                    );
                    array_push($selected, $selection);
                }
            }
        }
        array_push($cartDataProducts, $selected);
        Shopware()->Session()->offsetSet("finish", $cartDataProducts);
        Shopware()->Session()->offsetSet("selected", $selected);
        $articleId = $article['articleID'];
        $articleName = htmlspecialchars($article['articleName']);
        //$articleUrl = $article['linkDetailsRewrited']; //directly with {url sArticle=$sArticle.articleID title=$sArticle.articleName}
        $articleImage = $article['image']['source'];
        $articlePrice = $article['price_numeric'];          //if promo this is the actual price
        $articlePromoPrice = $article['pseudoprice_numeric'];         //if not assign 0
        $articleBrandId = $article['supplierID'];
        $articleBrandName = $article['supplierName'];
        $articleName = htmlspecialchars($article['articleName']);

        $sql = "SELECT ac.categoryID
                FROM s_articles_categories ac
                WHERE ac.articleId=?
                GROUP BY ac.categoryID";
        $params = array($articleId);
        $getCategories = Shopware()->Db()->fetchAll($sql, $params);     //get all Categories for product

        $categoryId = $article['categoryID'];
        $allCategories = array();     //to product.tpl

        foreach ($getCategories as $categoryId) {
            $categoryId = $categoryId['categoryID'];
            $_categoryParent = 'false';                     // if category has no parent, then set it to false
            $_categoryBreadcrumb = '[]';                    // and breadCrumb to []  , check documentation for more info
            $categories = Shopware()->Modules()->Categories()->sGetCategoriesByParent($categoryId); // get all categories in path
            $articleCategory = Shopware()->Modules()->Categories()->sGetCategoryContent($categoryId);
            $categoryName = '"' . htmlspecialchars($articleCategory['name']) . '"';
            $categories_no = count($categories);
            if ($categories_no > 1) {
                $_categoryBreadcrumb = array();
                for ($i = 1; $i < $categories_no - 1; $i++) {
                    $id = $categories[$i]['id'];
                    $name = $categories[$i]['name'];
                    $parent_id = $categories[$i + 1]['id'];
                    $_categoryBreadcrumb[] = '{
                            "id": ' . $id . ',
                            "name": "' . $name . '",
                            "parent": ' . $parent_id . '
                        }';
                }
                $_categoryParent = $categories[1]['id'];

                $id = $categories[$categories_no - 1]['id'];
                $name = $categories[$categories_no - 1]['name'];
                $parent_id = "false";

                $_categoryBreadcrumb[] = '{
                        "id": ' . $id . ',
                        "name": "' . $name . '",
                        "parent": ' . $parent_id . '
                    }';

                $_categoryBreadcrumb = '[' . implode(', ', $_categoryBreadcrumb) . ']';
                //            $_categoryParent = '[{ ' . $_categoryParent . ', breadcrumb: ' . $_categoryBreadcrumb . ' }]';
            }

            $categoryIds = array("id" => $categoryId);
            $categoryNames = array("name" => $categoryName);
            $categoryParents = array("parent" => $_categoryParent);
            $categoryBreadcrumbs = array("breadcrumb" => $_categoryBreadcrumb);
            $categorii = array(
                $categoryIds,
                $categoryNames,
                $categoryParents,
                $categoryBreadcrumbs
            );
            array_push($allCategories, $categorii);
        }

        $view->addTemplateDir($this->Path() . 'Views/');
        $view->extendsTemplate('frontend/plugins/retargeting/product.tpl');
        $view->assign('recomengProductPage', $this->Config()->get('RecomengProduct'));
        $view->extendsTemplate('frontend/plugins/recomeng/product.tpl');
        $view->extendsTemplate('frontend/plugins/retargeting/clickImage.tpl');
        if ($article['sConfigurator']) {
            $view->extendsTemplate('frontend/plugins/retargeting/setVariation.tpl');
        }
        $view->assign("product_id", $articleId);
        $view->assign("product_name", $articleName);
        //$view->assign("product_url", $articleUrl);
        $view->assign("product_main_image_src", $articleImage);
        if ($articlePromoPrice != 0 && $articlePromoPrice > $articlePrice) {
            $view->assign("product_price", $articlePromoPrice);
            $view->assign("product_promotional_price", $articlePrice);
        } else {
            $view->assign("product_price", $articlePrice);
            $view->assign("product_promotional_price", 0);
        }
        $view->assign("brand_id", $articleBrandId);
        $view->assign("brand_name", $articleBrandName);
        $view->assign("allCategories", $allCategories);
        if ($stock != 0) {
            $view->assign("stock", 'true');
        } else {
            $view->assign("stock", 'false');
        }
        $cartData = Shopware()->Session()->offsetGet("cartData");
        if ($cartData) {
            $view->assign("cartData", $cartData);
            $view->extendsTemplate('frontend/plugins/retargeting/addCart.tpl');
            Shopware()->Session()->offsetUnset("cartData");
        }
        $wishlist = Shopware()->Session()->offsetGet("article");
        if ($wishlist) {
            $view->assign("article", $wishlist);
            $view->extendsTemplate('frontend/plugins/retargeting/wishlist.tpl');
            Shopware()->Session()->offsetUnset("article");
        }
        Shopware()->Session()->offsetSet("referer", "product");
    }

    /**
     * @param \Enlight_Event_EventArgs $args
     */
    public function onActionPostDispatchFrontendTicket(Enlight_Event_EventArgs $args)   //Dispatch, not Dispatch Secure
    {                                                                                   //no template available
        /** @var \Shopware_Controllers_Frontend_Ticket $subject */
        $subject = $args->getSubject();
        $request = $subject->Request();
        $action = $request->getActionName();
        if ($action === 'index') {
            $id = $request->getParam('id');
            if ($id === '18') {
                $post = $request->getPost();
                $contactData = array(
                    "email" => $post['email'],
                    "name" => $post['vorname'] . " " . $post['nachname'],
                    "phone" => $post['telefon']
                );
                Shopware()->Session()->offsetSet("setEmail", $contactData);
            }
        }
    }

    /**
     * @param \Enlight_Event_EventArgs $args
     */
    public function onActionPostDispatchSecureFrontendForms(Enlight_Event_EventArgs $args)
    {
        /** @var \Shopware_Controllers_Frontend_Forms $subject */
        $subject = $args->getSubject();
        $view = $subject->View();
        $view->addTemplateDir($this->Path() . 'Views/');
        $request = $subject->Request();
        $success = $request->getParam('success');
        $setEmailData = Shopware()->Session()->offsetGet("setEmail"); //get session variable for setEmail function
        if ($setEmailData) { //if set
            $view->assign("setEmail", $setEmailData); //assign variables to the template setEmail.tpl
        }
        if ($success === '1') { //if form has been sent successfully
            Shopware()->Session()->offsetUnset("setEmail"); //unset the session variable
        }
        $view->extendsTemplate('frontend/plugins/retargeting/setEmail.tpl'); //extendsTemplate setEmail.tpl with javascript function setEmail
    }

    /**
     * @param \Enlight_Event_EventArgs $args
     */
    public function onActionPostDispatchSecureFrontendNewsletter(Enlight_Event_EventArgs $args)
    {
        /** @var \Shopware_Controllers_Frontend_Newsletter $subject */
        $subject = $args->getSubject();
        $request = $subject->Request();
        $view = $subject->View();
        $view->addTemplateDir($this->Path() . 'Views/');

        $setEmailData = Shopware()->Session()->offsetGet("setEmail");       //get session variable for setEmail function
        $userData = array(
            "email" => $setEmailData,
            "name" => $request->getPost('firstname') . " " . $request->getPost('lastname'),
            "city" => $request->getPost('city')
        );

        if ($setEmailData) { //if set
            $view->assign("setEmail", $setEmailData); //assign only the email to the setEmail.tpl template
            if ($userData) {
                $view->assign("setEmail", $userData); //assign name, email, city to the setEmail.tpl template
            }
        }
        Shopware()->Session()->offsetUnset("setEmail");
        $view->extendsTemplate('frontend/plugins/retargeting/setEmail.tpl'); //extendsTemplate setEmail.tpl with javascript function setEmail
    }

    /**
     * @param \Enlight_Event_EventArgs $args
     */
    public function onModulesAdminNewsletterRegistrationSuccess(Enlight_Event_EventArgs $args)
    {
        $email = $args->get('email');
        Shopware()->Session()->offsetSet("setEmail", $email);
    }

    /**
     * @param \Enlight_Event_EventArgs $args
     */
    public function onModulesAdminSaveRegisterSuccessful(Enlight_Event_EventArgs $args)
    {
        $sUserData = Shopware()->Modules()->Admin()->sGetUserData();
        $userData = array(
            "email" => $sUserData["additional"]["user"]["email"],
            "name" => $sUserData["billingaddress"]["firstname"] . " " . $sUserData["billingaddress"]["lastname"],
            "phone" => $sUserData["billingaddress"]["phone"] ? $sUserData["billingaddress"]["phone"] : '',
            "city" => $sUserData["billingaddress"]["city"] ? $sUserData["billingaddress"]["city"] : '',
            "birthday" => $sUserData["additional"]["user"]["birthday"] ? date_format(date_create_from_format('Y-m-d',
                $sUserData["additional"]["user"]["birthday"]), 'd-m-Y') : ''
        );

        Shopware()->Session()->offsetSet("setEmail", $userData); //set session variable for setEmail function
    }

    /**
     * @param \Enlight_Event_EventArgs $args
     */
    public function onActionPostDispatchSecureFrontendCheckout(Enlight_Event_EventArgs $args) //Checkout page
    {
        /** @var \Shopware_Controllers_Frontend_Checkout $subject */

        $subject = $args->getSubject();
        $view = $subject->View();
        $view->addTemplateDir($this->Path() . 'Views/');

        $setEmailData = Shopware()->Session()->offsetGet("setEmail"); //get session variable for setEmail function
        if ($setEmailData) { //if set
            $view->assign("setEmail", $setEmailData); //assign variables to the template setEmail.tpl
        }
        Shopware()->Session()->offsetUnset("setEmail"); //unset session variable, if not seEmail function is called anytime when Account Controller is dispatched
        $view->extendsTemplate('frontend/plugins/retargeting/setEmail.tpl'); //extendsTemplate setEmail.tpl with javascript function setEmail

        $request = $subject->Request();
        $action = $request->getActionName();

        if ($action === 'cart') {
            $target = $request->getParam('sTargetAction');
            if ($target) {
                $delete = Shopware()->Session()->offsetGet("delete");
                if ($delete) {
                    $view->assign("delete", $delete);
                    $view->extendsTemplate('frontend/plugins/retargeting/removeFromCart.tpl');
                }
            }
            Shopware()->Session()->offsetUnset("delete");
        }

        if ($action === 'confirm') {
            $target = $request->getParam('sTargetAction');
            if ($target) {
                $delete = Shopware()->Session()->offsetGet("delete");
                if ($delete) {
                    $view->assign("delete", $delete);
                    $view->extendsTemplate('frontend/plugins/retargeting/removeFromCart.tpl');
                }
            }
            Shopware()->Session()->offsetUnset("delete");
            $cartData = Shopware()->Session()->offsetGet("cartData");
            if ($cartData) {
                $view->assign("cartData", $cartData);
                $view->extendsTemplate('frontend/plugins/retargeting/addCart.tpl');
            }
            Shopware()->Session()->offsetUnset("cartData");
        }

        if ($action === 'finish') {
            $userData = Shopware()->Session()->offsetGet("userData");
            if ($userData) {
                $view->assign("userData", $userData);
                $view->extendsTemplate('frontend/plugins/retargeting/order.tpl');
                $view->assign('recomengThankYou', $this->Config()->get('RecomengThankYou'));
                $view->extendsTemplate('frontend/plugins/recomeng/order.tpl');
            }
            Shopware()->Session()->offsetUnset("userData");
        }
    }

    /**
     * @param \Enlight_Event_EventArgs $args
     */
    public function onActionPostDispatchSecureFrontendAccount(Enlight_Event_EventArgs $args) // Account page
    {
        /** @var \Shopware_Controllers_Frontend_Account $subject */

        $subject = $args->getSubject();
        $view = $subject->View();
        $view->addTemplateDir($this->Path() . 'Views/');
        $request = $subject->Request();
        $action = $request->getActionName();
        $setEmailData = Shopware()->Session()->offsetGet("setEmail");

        if ($setEmailData) {
            $view->assign("setEmail", $setEmailData);
        }

        Shopware()->Session()->offsetUnset("setEmail");
        if ($action === 'login') { //if action is login
            $sUserData = Shopware()->Modules()->Admin()->sGetUserData();
            $userData = array(
                "email" => $sUserData["additional"]["user"]["email"],
                "name" => $sUserData["billingaddress"]["firstname"] . " " . $sUserData["billingaddress"]["lastname"],
                "phone" => $sUserData["billingaddress"]["phone"] ? $sUserData["billingaddress"]["phone"] : '',
                "city" => $sUserData["billingaddress"]["city"] ? $sUserData["billingaddress"]["city"] : '',
                "birthday" => $sUserData["additional"]["user"]["birthday"] ? date_format(date_create_from_format('Y-m-d',
                    $sUserData["additional"]["user"]["birthday"]), 'd-m-Y') : ''
            );

            Shopware()->Session()->offsetSet("setEmail", $userData); //then set the session variable for setEmail
        }
        $view->extendsTemplate('frontend/plugins/retargeting/setEmail.tpl');
    }

    /**
     * @param \Enlight_Event_EventArgs $args
     */
    public function onFrontEndPostDispatch(Enlight_Event_EventArgs $args) //All Frontend pages
    {

        /** @var \Enlight_Controller_Action $controller */

        $controller = $args->get('subject');
        $request = $controller->Request();
        $view = $controller->View();

        $controllerName = $request->getControllerName();
        $action = $request->getActionName();

//        $this->displayRecomengHome();

        $view->addTemplateDir($this->Path() . 'Views/');

        if ($controllerName === 'listing' && $action === 'index') { // category page
            Shopware()->Session()->offsetSet("referer", "category");
            $categoryId = $request->getParam('sCategory');  // current Category Id
            $_categoryParent = 'false'; // if category has no parent, then set it to false
            $_categoryBreadcrumb = '[]'; // and breadCrumb to []  , check documentation for more info
            $categories = Shopware()->Modules()->Categories()->sGetCategoriesByParent($categoryId); // get all categories in path

            $categories_no = count($categories);
            if ($categories_no > 1) {
                $_categoryBreadcrumb = array();
                for ($i = 1; $i < $categories_no - 1; $i++) {
                    $id = $categories[$i]['id'];
                    $name = $categories[$i]['name'];
                    $parent_id = $categories[$i + 1]['id'];
                    $_categoryBreadcrumb[] = '{
                        "id": ' . $id . ',
                        "name": "' . $name . '",
                        "parent": ' . $parent_id . '
                    }';
                }
                $_categoryParent = $categories[1]['id'];

                $id = $categories[$categories_no - 1]['id'];
                $name = $categories[$categories_no - 1]['name'];
                $parent_id = "false";

                $_categoryBreadcrumb[] = '{
                    "id": ' . $id . ',
                    "name": "' . $name . '",
                    "parent": ' . $parent_id . '
                }';

                $_categoryBreadcrumb = '[' . implode(', ', $_categoryBreadcrumb) . ']';
            }

            $view->extendsTemplate('frontend/plugins/retargeting/category.tpl');
            $view->assign('_categoryParent', $_categoryParent);     // send to template
            $view->assign('_categoryBreadcrumb', $_categoryBreadcrumb);

            $view->assign('recomengCategoryPage', $this->Config()->get('RecomengCategory'));
            $view->extendsTemplate('frontend/plugins/recomeng/category.tpl');

            $wishlist = Shopware()->Session()->offsetGet("article");
            if ($wishlist) {
                $view->assign("article", $wishlist);
                $view->extendsTemplate('frontend/plugins/retargeting/wishlist.tpl');
                Shopware()->Session()->offsetUnset("article");
            }
            $cartData = Shopware()->Session()->offsetGet("cartData");
            if ($cartData) {
                $view->assign("cartData", $cartData);
                $view->extendsTemplate('frontend/plugins/retargeting/addCart.tpl');
                Shopware()->Session()->offsetUnset("cartData");
            }
        }

        if ($controllerName === 'listing' && $action === 'manufacturer') {      // brand(manufacturer) page

            $brandId = $request->getParam('sSupplier');
            $supplier = Shopware()->Modules()->Articles()->sGetSupplierById($brandId);      // supplier by supplierId
            $brandName = $supplier['name'];

            $view->extendsTemplate('frontend/plugins/retargeting/brand.tpl');
            $view->assign('brand_id', $brandId);
            $view->assign('brand_name', $brandName);
            
            $cartData = Shopware()->Session()->offsetGet("cartData");
            if ($cartData) {
                $view->assign("cartData", $cartData);
                $view->extendsTemplate('frontend/plugins/retargeting/addCart.tpl');
                Shopware()->Session()->offsetUnset("cartData");
            }
        }

        if ($controllerName === 'detail') {
            $action = $request->getParam('action');
            if ($action === 'rating') {
                $productId = $request->getParam('sArticle');
                $view->extendsTemplate('frontend/plugins/retargeting/comment.tpl');
                $view->assign('product_id', $productId);
            }
        }

        if ($controllerName === 'checkout') {
            if ($action === 'cart') {               // used for setCartUrl
                $view->assign('cart', $action);
                
                $cartData = Shopware()->Session()->offsetGet("cartData");
                if ($cartData) {
                    $view->assign("cartData", $cartData);
                    $view->extendsTemplate('frontend/plugins/retargeting/addCart.tpl');
                    Shopware()->Session()->offsetUnset("cartData");
                }
            }

            $products = array();
            $content = Shopware()->Modules()->Basket()->sGetBasket();
            
            foreach ($content as $items) {
                foreach ($items as $item) {
                    $productIds = (int)$item['articleID'];
                    $products[] = $productIds;
                }
            }
            
            $checkoutid = '[' . implode(", ", $products) . ']';
            $view->assign('checkoutid', $checkoutid);
            $view->extendsTemplate('frontend/plugins/retargeting/checkout.tpl');

            if ($request->getActionName() != 'finish') {
                $view->assign('recomengCheckoutPage', $this->Config()->get('RecomengCheckout'));
                $view->extendsTemplate('frontend/plugins/recomeng/checkout.tpl');
            }

        }

        $view->extendsTemplate('frontend/plugins/retargeting/header.tpl');
        $view->assign('retargeting_domain_api', $this->Config()->get('TrackingAPIKey'));

    }

    /**
     * Returns plugin version
     *
     * @return string
     */
    public function getVersion()
    {
        return '1.0.2';
    }

    /**
     * Returns plugin name
     *
     * @return string
     */

    public function getLabel()
    {
        return "Retargeting Tracker";
    }

    /**
     * Returns Plugin Info
     * @return array
     */
    public function getInfo()
    {
        return array(
            'label' => $this->getLabel(),
            'version' => $this->getVersion(),
            'copyright' => 'Copyright © ' . date('Y') . ', Retargeting Biz SRL',
            'author' => 'Retargeting Biz SRL <info@retargeting.biz>',
            'support' => 'info@retargeting.biz',
            'revision' => '1',
            'link' => 'https://retargeting.biz/',
            'description' => 'Personalized email content + Personalized live messages + SMS triggers to deliver to your customers the products they want to buy.'
        );
    }

//    public function validateEvent($controller, $ctrl = null)
//    {
//        $request = $controller->Request();
//        $response = $controller->Response();
//        $view = $controller->View();
//
//        if (!$request->isDispatched()
//            || $response->isException()
//            || (!is_null($ctrl) && $request->getControllerName() != $ctrl)
//            || !$view->hasTemplate()
//        ) {
//            return false;
//        }
//
//        return true;
//    }
//
//    public function displayRecomengHome(Enlight_Controller_ActionEventArgs $args)
//    {
//        if (!$this->validateEvent($args->getSubject(), 'index')) {
//            return;
//        }
//
//        $view = $args->getSubject()->View();
//        $view->addTemplateDir($this->Path() . 'Views/');
//        $view->extendsTemplate('frontend/plugins/recomeng/homepage.tpl');
//    }
}

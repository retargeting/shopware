<?php
class Shopware_Controllers_Frontend_Retargeting extends Enlight_Controller_Action
{
    public function indexAction()
    {
        //retargeting/index.tpl
    }

    public function productsAction(){

        $params = $this->request->getParams();
        $token = Shopware()->Config()->get("RESTAPIKey");
        if (isset($params['key']) && $params['key'] != '' && $params['key'] == $token ) {
            header('Content-Type: text/xml');

            $output = "
        <products>";

            $articleResource = \Shopware\Components\Api\Manager::getResource('article');
            $detailRepository = $articleResource->getDetailRepository();
            $listArticles = $articleResource->getList(0, null);

            foreach ($listArticles as $list) {
                foreach ($list as $article) {

                    $product_id = $article['id'];
                    $output .= "
				<product>
					<id>" . $product_id . "</id>";

                    $product_details = Shopware()->Modules()->Articles()->sGetArticleById($product_id);
                    $product_price = $product_details['price_numeric'];
                    $product_pseudo_price = $product_details['pseudoprice_numeric'];
                    if ($product_pseudo_price != 0 and $product_pseudo_price > $product_price) {
                        $aux_price = $product_price;
                        $product_price = $product_pseudo_price;
                        $product_pseudo_price = $aux_price;
                    } else {
                        $product_pseudo_price = 0;
                    }
                    $output .= "
                    <price>" . $product_price . "</price>
                    <promo>" . $product_pseudo_price . "</promo>
                    <inventory>";

                    $product_config = $detailRepository->getArticleWithVariantsAndOptionsQuery($product_id);
                    $listVariations = $product_config->getArrayResult();

                    $vector_first = "";
                    $vector_second = "";
                    $vector_stock = "";

                    foreach ($listVariations as $variations) {
                        if (($variations['details'][0]['configuratorOptions'][0]['groupId'])) {
                            $output .= "
                        <variations>1</variations>";
                            foreach ($variations['details'] as $variation) {
                                if (!$variation['configuratorOptions'][1]['groupId']) {

                                    $vector_first = $variation['configuratorOptions'][0]['name'];
                                    $output .= "
                            <variation>
                                <code>" . $vector_first . "</code>";
                                    $vector_stoc = $variation['inStock'];
                                    if ($vector_stoc != 0)
                                        $vector_stoc = 1;
                                    $output .= "
                                <stock>" . $vector_stoc . "</stock>
                            </variation>";
                                } elseif ($variation['configuratorOptions'][0]['groupId'] != $variation['configuratorOptions'][1]['groupId']) {

                                    $vector_first = $variation['configuratorOptions'][0]['name'];
                                    $vector_second = $variation['configuratorOptions'][1]['name'];

                                    $vector_stoc = $variation['inStock'];
                                    if ($vector_stoc != 0)
                                        $vector_stoc = 1;

                                    if (strlen($vector_first) >= strlen($vector_second)) {
                                        $output .= "
                            <variation>
                                <code>" . $vector_first . "-" . $vector_second . "</code>";
                                    } else {
                                        $output .= "<variation>
													<code>" . $vector_second . "-" . $vector_first . "</code>";
                                    }
                                    $output .= "<stock>" . $vector_stoc . "</stock>
                                </variation>";
                                }
                            }

                            $output .= "
                    </inventory>
                </product>";
                        } else {
                            $output .= "
                        <variations>0</variations>
                        <stock>1</stock>
                    </inventory>
                </product>";
                        }
                    }
                }
            }

            $output .= "
        </products>";

            echo $output;
            die();
        }
    }

    public function addDiscountCodeAction() {

        require_once(dirname(__FILE__) . '/../../lib/api/Retargeting_REST_API_Client.php');
        $params = $this->request->getParams();
//        $token = Shopware()->Config()->get("RESTAPIKey");
//
//        $client = new Retargeting_REST_API_Client($params['key']);
//        $client->setResponseFormat("json");
//        $client->setDecoding(false);
//        $response = $client->discount->create("percentage", 10);
//        var_dump($response);die;
        $conn = Shopware()->Container()->get('dbal_connection');
        $queryBuilder = $conn->createQueryBuilder();
        $values = array();
        //wrote in standard sql cause in this case its way faster than doctrine models
        $sql = "INSERT IGNORE INTO s_emarketing_vouchers (vouchercode) VALUES";
        $values[] = Shopware()->Db()->quoteInto("(?)", array('SADS2q121'));
        Shopware()->Db()->query($sql . implode(',', $values));
        $values = array();
        $result = $queryBuilder
            ->select('id', 'description')
            ->from('s_emarketing_vouchers');
        $result = $result->execute()->fetch(PDO::FETCH_BOTH);
        var_dump($result);
        die("asad");

    }

}
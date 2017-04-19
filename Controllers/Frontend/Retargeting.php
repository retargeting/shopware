<?php
class Shopware_Controllers_Frontend_Retargeting extends Enlight_Controller_Action
{
    public function indexAction()
    {
        //retargeting/index.tpl
    }

    public function productsAction(){

//        header('Content-Type: text/xml');

        $output = "
        <products>";
        $productId = '';
        $productS = '';
        $productP = '';
        $productPr = '';
        $product_url = '';
        $product_image = '';


        $articleResource = \Shopware\Components\Api\Manager::getResource('article');
        $detailRepository = $articleResource->getDetailRepository();
        $listArticles = $articleResource->getList(0,null);   // luam toate articolele din Shopware, de aia am pus null, sau poti limita in blocuri

        foreach ($listArticles as $list) {
            foreach($list as $article) {
                var_dump($article['id']);die;
                $abc = $detailRepository->getArticleWithVariantsAndOptionsQuery(123);   // ii dam parametru $article['id']
                $listVariations = $abc->getArrayResult();        // intoarce info despre variatii
                foreach ($listVariations as $variations){
                    foreach($variations['details'] as $variation) {
                        var_dump($variation['configuratorOptions']);   // aici iei efectiv variatiile si le unesti sub forma white/black-XS
                        var_dump($variation['inStock']);   // aici iei stocul  VARIATIEI, nu a produsului, si pe asta o trimiti, aici practic ia cantitatea, dar trimiti 1 sau 0 tu, sau true, false
                        die;
                    }
                }
            }
        }


//        }
        $caca = Shopware()->Modules()->Articles()->sGetArticleById(123); // dai paramtru $article['id']
        var_dump($caca);die;        // uita-te aici sa vezi ce trebuie selectat, de aici iei price, url si image, te mai uiti in Bootstrap.php al plugin-ului cum am luat corect preturile, linia 208 de exemplu


        //dupa construiesti xml-u, ai id-u, pretul, url, inventory care are variatiile si stocul mai sus si atat si dai echo, dai die ca sa vezi ceva.
        // trebuie bagat prin niste foreach-uri codul asta
        $output .= "
            <product>
                <id>{$productId}</id>
                <stock>{$productS}</stock>
                <price>{$productP}</price>
                <promo>{$productPr}</promo>
                <url>{$product_url}</url>
                <image>{$product_image}</image>
            </product>";
        $output .= "
        </products>";

        // pana aici

        echo $output;


    }

    public function addDiscountCodeAction() {

        require_once(dirname(__FILE__) . '/../../lib/api/Retargeting_REST_API_Client.php');
        $params = $this->request->getParams();
        $token = Shopware()->Config()->get("RESTAPIKey");

        $client = new Retargeting_REST_API_Client($params['key']);
        $client->setResponseFormat("json");
        $client->setDecoding(false);
        $response = $client->discount->create("percentage", 10);
        var_dump($response);die;
    }

}
{extends file="parent:frontend/index/header.tpl"}
{block name="frontend_index_header_javascript_tracking"}
    <script>
        var _ra = _ra || {};
        _ra.sendProductInfo = {
            "id": {$product_id},
            "name": "{$product_name}",
            "url": "{url sArticle=$sArticle.articleID title=$sArticle.articleName}",
            "img": "{$product_main_image_src}",
            "price": {$product_price},
            "promo": {$product_promotional_price},
            "brand": {
                "id": {$brand_id},
                "name": "{$brand_name}"
            },
            "category": [
                {foreach name=outer item=categories from=$allCategories}
                    {
                        {foreach name=outer2 item=category from=$categories}
                            {foreach key=key item=item from=$category}
                                {$key}: {$item},
                            {/foreach}
                        {/foreach}
                    },
                {/foreach}
            ],
            "inventory": {
                    "variations": false,
                    "stock" : {$stock}
            }
            };
        if (_ra.ready !== undefined) {
            _ra.sendProduct(_ra.sendProductInfo);
        }

    </script>
    {$smarty.block.parent}
{/block}

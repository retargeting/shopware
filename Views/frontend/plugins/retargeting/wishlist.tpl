{extends file="parent:frontend/index/header.tpl"}
{block name="frontend_index_header_javascript_tracking" append}
    <script>
        var _ra = _ra || {};
        {if $article|is_array}
            _ra.sendProductInfo = {
                "id": {$article.id},
                "name": "{$article.name}",
                "url": "{$article.url}",
                "img": "{$article.img}",
                "price": {$article.price},
                "promo": {$article.promo},
                "brand": {
                    "id": {$article.brand_id},
                    "name": "{$article.brand_name}"
                },
                "category": [
                    {foreach name=outer item=categories from=$article.category}
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
                    "stock" : {$article.stock}
            }
            };
            if (_ra.ready !== undefined) {
                _ra.sendProduct(_ra.sendProductInfo);
            }
            _ra.addToWishlistInfo = {
                "product_id": {$article.id}
            };
            if (_ra.ready !== undefined) {
                _ra.addToWishlist(_ra.addToWishlistInfo.product_id);
            }
        {elseif $article}
            _ra.addToWishlistInfo = {
                "product_id": {$article}
            };
            if (_ra.ready !== undefined) {
                _ra.addToWishlist(_ra.addToWishlistInfo.product_id);
            }
        {/if}
    </script>
    {$smarty.block.parent}
{/block}

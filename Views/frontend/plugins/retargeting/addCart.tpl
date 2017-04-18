{extends file="parent:frontend/index/header.tpl"}
{block name="frontend_index_header_javascript_tracking"}
        <script>
            var _ra = _ra || {};
            _ra.addToCartInfo = {
                "product_id": {$cartData.id},
                "quantity": {$cartData.quantity},
                "variation": {$cartData.variation}
            };

            if (_ra.ready !== undefined) {
                _ra.addToCart(
                    _ra.addToCartInfo.product_id,
                    _ra.addToCartInfo.quantity,
                    _ra.addToCartInfo.variation
                );
            }
        </script>
    {$smarty.block.parent}
{/block}
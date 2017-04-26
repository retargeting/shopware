{extends file="parent:frontend/index/header.tpl"}
{block name="frontend_index_header_javascript_tracking" append}
    <script>
        var _ra = _ra || {};
        _ra.removeFromCartInfo = {$delete};
        if (_ra.ready !== undefined) {
            _ra.removeFromCart(
                _ra.removeFromCartInfo.product_id,
                _ra.removeFromCartInfo.quantity,
                _ra.removeFromCartInfo.variation);
        }
    </script>
    {$smarty.block.parent}
{/block}
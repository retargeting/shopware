{extends file="parent:frontend/index/header.tpl"}

{block name="frontend_index_header_javascript_tracking"}

    {if $checkoutid}
        <script>
            var _ra = _ra || {};
            _ra.checkoutIdsInfo = {$checkoutid};

            if (_ra.ready !== undefined) {
                _ra.checkoutIds(_ra.checkoutIdsInfo);
            }
        </script>
    {/if}
    {if $cart}
    <script>
        var _ra = _ra || {};
        _ra.setCartUrlInfo = {
            "url": window.location.toString()
        };

        if (_ra.ready !== undefined) {
            _ra.setCartUrl(_ra.setCartUrlInfo.url);
        }
    </script>
    {/if}

    {$smarty.block.parent}
{/block}

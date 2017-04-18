{extends file="parent:frontend/index/header.tpl"}
{block name="frontend_index_header_javascript_tracking"}
    {if $userData|is_array}
    <script>
        var _ra = _ra || {};
        _ra.saveOrderInfo = {
            "order_no": "{$userData.order_no}",
            "lastname": "{$userData.lastname}",
            "firstname": "{$userData.firstname}",
            "email": "{$userData.email}",
            "phone": "{$userData.phone}",
            "state": "{$userData.state}",
            "city": "{$userData.city}",
            "address": "{$userData.address}",
            "birthday": "{$userData.birthday}",
            "discount_code": ["{$userData.discount_code}"],
            "discount": {$userData.discount},
            "shipping": {$userData.shipping},
            "total": {$userData.total}
        };

        _ra.saveOrderProducts = {$userData.products};

        if( _ra.ready !== undefined ){
            _ra.saveOrder(_ra.saveOrderInfo, _ra.saveOrderProducts);
        }
    </script>
    {/if}
    {$smarty.block.parent}
{/block}
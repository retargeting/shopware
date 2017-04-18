{extends file="parent:frontend/index/header.tpl"}

{block name="frontend_index_header_javascript_tracking"}

    <script>
        var _ra = _ra || {};
        _ra.sendBrandInfo = {
            "id": {$brand_id},
            "name": "{$brand_name}"
        };

        if (_ra.ready !== undefined) {
            _ra.sendBrand(_ra.sendBrandInfo);
        }
    </script>

    {$smarty.block.parent}
{/block}

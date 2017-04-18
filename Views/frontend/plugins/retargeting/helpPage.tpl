{extends file="parent:frontend/index/header.tpl"}
{block name="frontend_index_header_javascript_tracking"}
    <script>
        var _ra = _ra || {};
        _ra.visitHelpPageInfo = {
        "visit": true
        };

        if (_ra.ready !== undefined) {
        _ra.visitHelpPage();
        }
    </script>
    {$smarty.block.parent}
{/block}

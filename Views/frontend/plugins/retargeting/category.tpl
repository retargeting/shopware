{extends file="parent:frontend/index/header.tpl"}
{block name="frontend_index_header_javascript_tracking"}
<script>
    var _ra = _ra || {};
    _ra.sendCategoryInfo = {
        "id": {$sCategoryContent.id},
        "name" : "{$sCategoryContent.name}",
        "parent": {$_categoryParent},
        "breadcrumb": {$_categoryBreadcrumb}
    };
    if (_ra.ready !== undefined) {
        _ra.sendCategory(_ra.sendCategoryInfo);
    }
</script>

{$smarty.block.parent}
{/block}

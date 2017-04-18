{extends file="parent:frontend/index/header.tpl"}
{block name="frontend_index_header_javascript_tracking" append}
    <script>
var _ra = _ra || {};
function addEvent(obj, type, fn) {
    if (obj.addEventListener)
        obj.addEventListener(type, fn, false);
    else if (obj.attachEvent)
        obj.attachEvent('on' + type, function() { return fn.apply(obj, [window.event]);});
}
addEvent(window, 'load', function(){
    function _raTriggerClickImage(e){
        _ra.clickImageInfo = {
            "product_id": {$sArticle.articleID}
        };

        if (_ra.ready !== undefined) {
            _ra.clickImage(_ra.clickImageInfo.product_id);
        }
        e.returnValue = false;
        if (e.preventDefault) e.preventDefault();
        return false
    }
    a=document.getElementsByClassName("image-slider--container");
    if (a !== undefined) {
        for (var i = 0, l = a.length; i < l; ++i) {
                addEvent(a[i], 'click', function (e) {
                    _raTriggerClickImage(e);
                });
        }
    }
});
    </script>
    {$smarty.block.parent}
{/block}
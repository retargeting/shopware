{extends file="parent:frontend/detail/comment"}

{block name="frontend_detail_comment_post_form"}
    <script>
        var _ra = _ra || {};
        _ra.commentOnProductInfo = {
            "product_id": {$product_id}
        };

        if (_ra.ready !== undefined) {
            _ra.commentOnProduct(_ra.commentOnProductInfo.product_id, function() {
                console.log("Comment on product FIRED!");
            });
        }
    </script>

    {$smarty.block.parent}
{/block}


{extends file="parent:frontend/index/header.tpl"}
{block name="frontend_index_header_javascript_tracking"}

    {if $retargeting_domain_api}
        <script type="text/javascript"><!--
            {literal}
            (function(){
                ra_key = {/literal}"{$retargeting_domain_api}{literal}";
                ra_params = {
                    add_to_cart_button_id: "add_to_cart_button_id",
                    price_label_id: "product--price'",
                };
                var ra = document.createElement("script"); ra.type ="text/javascript"; ra.async = true; ra.src = ("https:" ==
                    document.location.protocol ? "https://" : "http://") + "tracking.retargeting.biz/v3/rajs/" + ra_key + ".js";
                var s = document.getElementsByTagName("script")[0]; s.parentNode.insertBefore(ra,s);})();
            {/literal}
            --></script>

    {else}

        <script type="text/javascript">
            console.info("Retargeting Tracker Error: Please set the Domain API Key from your Retargeting Account.");
        </script>

    {/if}


    {$smarty.block.parent}
{/block}



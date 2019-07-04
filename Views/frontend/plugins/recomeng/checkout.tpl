{extends file="parent:frontend/index/index.tpl"}

{block name="frontend_index_content" append}
    {$smarty.block.parent}
    {if $recomengCheckoutPage == 'Yes'}
        <div id="retargeting-recommeng-checkout-page"><img src="https://nastyhobbit.org/data/media/3/happy-panda.jpg"></div>
    {/if}
{/block}

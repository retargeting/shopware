{extends file="parent:frontend/index/index.tpl"}

{block name="frontend_index_content" append}
    {$smarty.block.parent}
    {if $recomengProductPage == 'Yes'}
        <div id="retargeting-recommeng-product-page"><img src="https://nastyhobbit.org/data/media/3/happy-panda.jpg"></div>
    {/if}
{/block}
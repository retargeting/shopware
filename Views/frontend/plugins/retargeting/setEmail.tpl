{extends file="parent:frontend/account/index.tpl"}
{block name="frontend_index_content"}
    {if $setEmail|is_array}
        <script>
            var _ra = _ra || {};
            _ra.setEmailInfo = {
                "email": "{$setEmail.email}",
                "name": "{$setEmail.name}",
                "phone": "{$setEmail.phone}",
                "city": "{$setEmail.city}",
                "birthday": "{$setEmail.birthday}"
            };
            if (_ra.ready !== undefined) {
                _ra.setEmail(_ra.setEmailInfo)
            }
        </script>
    {/if}
    {$smarty.block.parent}
{/block}

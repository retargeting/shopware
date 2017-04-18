{extends file="parent:frontend/index/header.tpl"}
{block name="frontend_index_header_javascript_tracking" append}
    <script>
        var _ra = _ra || {};
        {$configurator = $sArticle.sConfigurator}
        function live (eventType, elementQuerySelector, cb) {
            document.addEventListener(eventType, function (event) {
                var qs = document.querySelectorAll(elementQuerySelector);
                if (qs) {
                    var el = event.target, index = -1;
                    while (el && ((index = Array.prototype.indexOf.call(qs, el)) === -1)) {
                        el = el.parentElement;
                    }

                    if (index > -1) {
                        cb.call(el, event);
                    }
                }
            });
        }

        function _ra_triggerSetVariation(e) {
            var _ra_variation = _ra_grabVariation();
            if (_ra.setVariation !== undefined) {
                _ra.setVariation({$product_id}, _ra_variation);
            }
            e.returnValue = false;
            if (e.preventDefault) e.preventDefault();
            return false;
        }

        var input = document.getElementsByClassName("variant--option");
        var field = document.getElementsByClassName("field--select");

        function _ra_grabVariation() {
        if (input.length>0) {
                var _ra_vo = {};
                var _ra_voCode = [];
                var _ra_voDetails = {};

                {foreach $configurator as $configuratorGroup}
                {foreach $configuratorGroup.values as $option}
                if (document.getElementById("group[{$option.groupID}][{$option.optionID}]").checked) {
                    _ra_voCode.push(document.getElementById("group[{$option.groupID}][{$option.optionID}]").title);
                }

                _ra_voDetails[_ra_voCode[_ra_voCode.length - 1]] = {
                    "category_name": '{$configuratorGroup.groupname}',
                    "category": '{$configuratorGroup.groupname}',
                    "value": _ra_voCode[_ra_voCode.length - 1]
                };

                _ra_voCode[_ra_voCode.length - 1] = _ra_voCode[_ra_voCode.length - 1];
                {/foreach}
                {/foreach}

                _ra_vo = {
                    "code": _ra_voCode.join('-'),
                    "stock": 1,
                    "details": _ra_voDetails
                };

                return _ra_vo;
            }
        else {
                _ra_vo = {};
                _ra_voCode = [];
                _ra_voDetails = {};
                {foreach $configurator as $configuratorGroup}
                if (document.getElementsByName('group[{$configuratorGroup.groupID}]')[0] !== null) {
                    _ra_voCode.push(document.getElementsByName('group[{$configuratorGroup.groupID}]')[0].options[document.getElementsByName('group[{$configuratorGroup.groupID}]')[0].selectedIndex].text);
                }
                _ra_voDetails[_ra_voCode[_ra_voCode.length - 1].replace(RegExp("[- ]", 'g'), '')] = {
                    "category_name": '{$configuratorGroup.groupname}',
                    "category": '{$configuratorGroup.groupname}',
                    "value": _ra_voCode[_ra_voCode.length - 1]
                };
                _ra_voCode[_ra_voCode.length - 1] = _ra_voCode[_ra_voCode.length - 1].replace(RegExp("[- ]", 'g'), '');
                {/foreach}
                _ra_vo = {
                    "code": _ra_voCode.join('-'),
                    "stock": 1,
                    "details": _ra_voDetails
                };
                return _ra_vo;
            }
        }


        live('change', 'form.configurator--form > div, .variant--option', function(event) {
            _ra_triggerSetVariation(event);
        });

    </script>
    {$smarty.block.parent}
{/block}
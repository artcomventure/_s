// ---
// Attach custom select.

(function() {

    if (typeof customSelect !== 'function') return;

    const isValidSelector = (selector) => {
        try {
            document.createDocumentFragment().querySelector(selector);
        } catch (e) {
            return false;
        }
        return true;
    };

    const customSelectors = (function() {
        const selectorsString = wp_localize_script_vars.custom_selectors || '';
        return selectorsString.trim().split(/\r?\n/).filter(Boolean).filter(isValidSelector);
    })();

    const attachCustomSelect = ($context) => {
        if (typeof $context.querySelectorAll !== 'function') return;
        if ($context.closest('.customSelect')) return;

        let $selects = [];

        if (customSelectors.length > 0) {
            customSelectors.forEach((selector) => {
                const elements = $context.querySelectorAll(selector.trim());
                elements.forEach((element) => {
                    if (element.nodeName === 'SELECT') {
                        $selects.push(element);
                    }
                });
            });
        } else {
            $selects = Array.from($context.querySelectorAll('select'));
        }

        if (!$selects.length && $context.nodeName === 'SELECT') {
            $selects = [$context];
        }

        $selects.forEach(($select) => {
            $select.style.display = 'none';
            customSelect($select);
        });
    }

    // on page load
    attachCustomSelect(document.documentElement);

    // on ajax load
    new MutationObserver(function(entries) {
        entries.forEach((entry) => {
            entry.addedNodes.forEach(attachCustomSelect);
        });
    }).observe(document.documentElement, {
        subtree: true,
        childList: true
    });

})();
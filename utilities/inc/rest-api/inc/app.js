utilities = utilities || {};
utilities.i18n = {
    __: ( text, domain ) => {
        fetch( `/wp-json/utilities/v1/l10n/__?text=${text}&domain=${domain}&lang=${document.documentElement.getAttribute( 'lang' )}` )
            .then( response => response.json() )
            .then( data => data.t9n )
    }
}
/**
 * Translate strings via REST (e.g. bogo in connection with pjax, see `l10n.php`).
 *
 * ```
 * // enqueue with dependency `utilities-l10n`
 * utilities.i18n.__( 'Text', 'domain' ).then( text => … );
 * utilities.i18n._n( '%s item', '%s items', 2, 'domain' ).then( text => … );
 * ```
 */
window.utilities = window.utilities || {};
window.utilities.i18n = (function() {

    const cache = {};

    const request = ( fn, args, fallback ) => {
        // page language (not the user's admin language)
        args.lang = document.documentElement.getAttribute( 'lang' ) || '';
        args._locale = 'site';

        // `apiFetch` handles REST root (also plain permalinks/subdirectories) and nonce
        const path = wp.url.addQueryArgs( `/utilities/v1/l10n/${fn}`, args );

        // same string is requested once
        return cache[path] = cache[path] || wp.apiFetch( { path } )
            .then( data => data.t9n )
            .catch( () => {
                delete cache[path];
                return fallback;
            } );
    };

    return {
        __: ( text, domain = 'default' ) =>
            request( '__', { text, domain }, text ),
        _x: ( text, context, domain = 'default' ) =>
            request( '_x', { text, context, domain }, text ),
        _n: ( single, plural, number, domain = 'default' ) =>
            request( '_n', { single, plural, number, domain }, number === 1 ? single : plural ),
        _nx: ( single, plural, number, context, domain = 'default' ) =>
            request( '_nx', { single, plural, number, context, domain }, number === 1 ? single : plural ),
    };

})();

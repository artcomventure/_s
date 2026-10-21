/**
 * Alter v1.1.0
 * https://gist.github.com/enoks/a336e8db4594737a98d8bd2c3783c2d6
 *
 * Let variables be altered.
 *
 * ```
 * Alter.add( 'hook', ( variable, param1, param2, ... ) => variable), 10 ); // add callback
 * const variable = Alter.do( 'hook', variable, param1, param2, ... ); // let alter
 * ```
 *
 * Copyright 2023, Stefan Käsche
 * Licensed under MIT
 */

// Changelog:
//
// 1.1.0 - 2026-10-10
// Priorities sorted numerically (negative and float priorities).
// Callbacks compared by reference (like `addEventListener()`), not by source code:
// `add()` no longer removes other callbacks with the same code, `remove()` removes the given callback only.
//
// 1.0.0 - 2023-08-19
// Init.

;
(function ( $, name ) {

    const callbacks = {};

    $[name] = {
        /**
         * @param {string} hook
         * @param {function} callback
         * @param {number} priority (optional)
         */
        add: function ( hook, callback , priority = 10) {
            if ( typeof callback === 'function' ) {
                if ( !callbacks[hook] ) callbacks[hook] = {};
                if ( !callbacks[hook][priority] ) callbacks[hook][priority] = [];

                // like `addEventListener()`: the same callback is added once
                if ( !callbacks[hook][priority].includes( callback ) )
                    callbacks[hook][priority].push( callback );
            }

            return this;
        },

        /**
         * @param {string} hook
         * @param {function} callback
         */
        remove: function( hook, callback ) {
            // like `removeEventListener()`: the given callback (by reference) only
            if ( callbacks[hook] ) Object.keys( callbacks[hook] ).forEach( priority => {
                callbacks[hook][priority] = callbacks[hook][priority].filter( func => func !== callback );
            } );

            return this;
        },

        /**
         * @param {string} hook
         * @param variable
         * Add any number of parameters. They will be passed through.
         */
        do: function ( hook, variable ) {
            if ( callbacks[hook] ) {
                const params = Object.values( arguments ).slice(2);

                // sort by priority (numerically) and loop through callbacks
                Object.keys( callbacks[hook] ).sort( ( a, b ) => a - b ).forEach( priority => {
                    callbacks[hook][priority].forEach( $callback => {
                        variable = $callback.apply( null, [...[variable], ...params] );
                    } )
                } )
            }

            return variable;
        },
    };

})(window.alterScope||window, window.alterName||'Alter' );

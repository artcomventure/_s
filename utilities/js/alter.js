/**
 * Alter v1.0.0
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
                this.remove( hook, callback );

                if ( !callbacks[hook] ) callbacks[hook] = {};
                if ( !callbacks[hook][priority] ) callbacks[hook][priority] = [];

                callbacks[hook][priority].push( callback );
            }

            return this;
        },

        /**
         * @param {string} hook
         * @param {function} callback
         */
        remove: function( hook, callback ) {
            if ( !!callbacks[hook] ) Object.keys( callbacks[hook] ).forEach( priority => {
                callbacks[hook][priority].forEach( ( func, i ) => {
                    if ( func.toString() === callback.toString() ) {
                        delete callbacks[hook][priority][i];
                    }
                } )
            } );

            return this;
        },

        /**
         * @param {string} hook
         * @param variable
         * Add any number of parameters. They will be passed through.
         */
        do: function ( hook, variable ) {
            if ( !!callbacks[hook] ) {
                const params = Object.values( arguments ).slice(2);

                // sort by priority and loop through callbacks
                Object.values( Object.fromEntries( Object.entries( callbacks[hook] ).sort() ) ).forEach( $callbacks => {
                    $callbacks.forEach( $callback => {
                        variable = $callback.apply( null, [...[variable], ...params] );
                    } )
                } )
            }

            return variable;
        },
    };

})(window.alterScope||window, window.alterName||'Alter' );
